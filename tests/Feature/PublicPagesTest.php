<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\JobOpening;
use App\Models\SeoPage;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private function service(array $attrs = []): Service
    {
        return Service::create($attrs + [
            'category' => 'maintenance', 'title' => 'Plumbing', 'slug' => 'plumbing',
            'summary' => 'Prompt plumbing repairs and installations.', 'icon' => 'wrench', 'is_active' => true,
        ]);
    }

    private function job(array $attrs = []): JobOpening
    {
        return JobOpening::create($attrs + [
            'title' => 'Field Executive', 'slug' => 'field-executive', 'location' => 'Bangalore',
            'employment_type' => 'full_time', 'vacancies' => 1, 'summary' => 'Visit customers and verify their details on site.',
            'description' => "First paragraph.\n\nSecond paragraph.", 'requirements' => "Own bike\nGood English",
            'status' => 'open', 'published_at' => now(),
        ]);
    }

    public function test_every_public_page_loads_with_full_seo_markup(): void
    {
        $this->service();
        $this->job();

        foreach (['/', '/about', '/services', '/services/plumbing', '/recruitment', '/recruitment/field-executive', '/gallery', '/contact'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#<title>[^<]{10,}</title>#', $html, "$path title");
            $this->assertMatchesRegularExpression('#<meta name="description" content="[^"]{20,}#', $html, "$path description");
            $this->assertStringContainsString('<link rel="canonical" href="' . url($path === '/' ? '/' : $path), $html, "$path canonical");
            $this->assertStringContainsString('property="og:title"', $html, "$path og:title");
            $this->assertStringContainsString('property="og:image"', $html, "$path og:image");
            $this->assertStringContainsString('name="twitter:card"', $html, "$path twitter");
            $this->assertStringContainsString('application/ld+json', $html, "$path json-ld");
            $this->assertSame(1, substr_count($html, '<h1'), "$path should have exactly one h1");
            $this->assertStringContainsString('content="index, follow', $html, "$path should be indexable");
        }
    }

    public function test_json_ld_blocks_are_valid_json(): void
    {
        $this->service();
        $this->job();

        foreach (['/', '/services/plumbing', '/recruitment/field-executive', '/contact'] as $path) {
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($path)->getContent(), $m);
            $this->assertNotEmpty($m[1], $path);
            foreach ($m[1] as $block) {
                $this->assertIsArray(json_decode($block, true, flags: JSON_THROW_ON_ERROR), $path);
            }
        }
    }

    public function test_job_posting_schema_contains_the_fields_google_requires(): void
    {
        $this->job(['closing_date' => now()->addWeek()->toDateString()]);

        $html = $this->get('/recruitment/field-executive')->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $posting = collect($m[1])->map(fn ($b) => json_decode($b, true))->firstWhere('@type', 'JobPosting');

        $this->assertNotNull($posting);
        foreach (['title', 'description', 'datePosted', 'validThrough', 'hiringOrganization', 'jobLocation', 'employmentType'] as $field) {
            $this->assertArrayHasKey($field, $posting, $field);
        }
        $this->assertSame('FULL_TIME', $posting['employmentType']);
        $this->assertStringContainsString('<li>Own bike</li>', $posting['description']);
    }

    public function test_service_meta_overrides_and_fallbacks(): void
    {
        $this->service(['slug' => 'custom', 'title' => 'Custom', 'meta_title' => 'My Custom Title', 'meta_description' => 'A hand written description for search engines.']);
        $this->service(['slug' => 'plain', 'title' => 'Plain']);

        $this->get('/services/custom')
            ->assertSee('<title>My Custom Title | A2Z Global Maintenance</title>', false)
            ->assertSee('content="A hand written description for search engines."', false);

        $this->get('/services/plain')
            ->assertSee('<title>Plain in Bangalore | A2Z Global Maintenance</title>', false)
            ->assertSee('content="Prompt plumbing repairs and installations."', false);
    }

    public function test_admin_edited_seo_for_static_pages_is_used(): void
    {
        SeoPage::create(['page_key' => 'about', 'meta_title' => 'Custom About Title', 'meta_description' => 'Custom about description that is long enough.']);

        $this->get('/about')
            ->assertSee('<title>Custom About Title | A2Z Global Maintenance</title>', false)
            ->assertSee('content="Custom about description that is long enough."', false);

        // Untouched pages keep the config defaults.
        $this->get('/contact')->assertSee(config('seo.pages.contact.title'));
    }

    public function test_home_page_has_no_careers_block_even_when_jobs_are_open(): void
    {
        $this->job();

        $this->get('/')->assertOk()
            ->assertDontSee('Grow with our crew')
            ->assertDontSee('View 1 open')
            ->assertDontSee('No openings right now')
            ->assertDontSee('Field Executive');
        // Recruitment itself is still reachable from the menu.
        $this->get('/')->assertSee(route('recruitment.index'), false);
    }

    public function test_cta_renders_as_a_centred_rounded_card_with_two_buttons(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('class="cta-box"', $html);
        $this->assertMatchesRegularExpression('#class="btn btn--glass btn--lg" href="tel:[^"]+">.*?Call now#s', $html);
        $this->assertMatchesRegularExpression('#class="btn btn--white btn--lg" href="[^"]+\#enquiry">\s*Send an enquiry#', $html);
        $this->assertMatchesRegularExpression('#\.cta-box\s*\{[^}]*border-radius:\s*48px#', file_get_contents(public_path('css/site.css')));
    }

    public function test_clients_are_a_text_only_scrolling_strip_placed_above_what_we_do(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Every client name appears twice (the readable list + an aria-hidden copy that makes the loop seamless).
        foreach (config('site.clients') as $client) {
            $this->assertSame(2, substr_count($html, '<li>' . e($client) . '</li>'), $client);
        }
        $this->assertSame(2, substr_count($html, 'class="marquee__group"'), 'the readable list + its copy');
        $this->assertSame(1, substr_count($html, 'class="marquee__group" aria-hidden="true"'), 'only the copy is hidden from screen readers');
        $this->assertStringContainsString('Building trust through reliable service', $html);
        $this->assertStringNotContainsString('client-logo', $html, 'text only: no logo tiles or monograms');

        // The strip comes before the "What we do" services section.
        $this->assertLessThan(strpos($html, 'Services for every need'), strpos($html, 'class="marquee"'));

        $this->get('/about')->assertOk()->assertSee('class="marquee"', false);
    }

    public function test_about_vision_and_mission_uses_a_photo_background_with_glass_cards_and_a_button(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('class="vm-section"', $html);
        $this->assertSame(2, substr_count($html, 'class="vm-card"'), 'a vision card and a mission card');
        $this->assertLessThan(strpos($html, 'Our mission'), strpos($html, 'class="vm-section"'), 'both cards sit inside the section');
        $this->assertMatchesRegularExpression('#class="btn btn--white btn--lg btn--caps" href="' . preg_quote(route('services.index'), '#') . '">Explore our services#', $html);

        $css = file_get_contents(public_path('css/site.css'));
        $this->assertMatchesRegularExpression('#\.vm-section\s*\{[^}]*url\(.\.\./img/office\.jpg.\)#', $css, 'photo background');
        $this->assertMatchesRegularExpression('#\.vm-card\s*\{[^}]*backdrop-filter:\s*blur#', $css, 'frosted glass');
        $this->assertFileExists(public_path('img/office.jpg'));
        $this->assertStringNotContainsString('vm-panel', $html . $css);
    }

    public function test_services_page_has_all_and_category_tabs_with_counts(): void
    {
        $this->service(['slug' => 'a', 'title' => 'House Keeping', 'category' => 'facility']);
        $this->service(['slug' => 'b', 'title' => 'Pantry Boy', 'category' => 'facility']);
        $this->service(['slug' => 'c', 'title' => 'Plumbing', 'category' => 'maintenance']);

        $html = $this->get('/services')->assertOk()->getContent();

        // "All" is active by default and every category section is visible.
        $this->assertMatchesRegularExpression('#data-filter=""[^>]*class="is-active"[^>]*>\s*All\s*<span>3</span>#', $html);
        $this->assertMatchesRegularExpression('#data-filter="facility"[^>]*>\s*Facility Management\s*<span>2</span>#', $html);
        $this->assertMatchesRegularExpression('#data-filter="maintenance"[^>]*>\s*Maintenance &amp; Renovation\s*<span>1</span>#', $html);
        $this->assertDoesNotMatchRegularExpression('#data-filter="government"#', $html, 'categories with no services get no tab');
        $this->assertDoesNotMatchRegularExpression('#<section[^>]*\shidden\s*>#', $html, 'no section is hidden under "All"');
        $this->get('/services')->assertSee('House Keeping')->assertSee('Plumbing');
    }

    public function test_choosing_a_category_shows_only_its_services(): void
    {
        $this->service(['slug' => 'a', 'title' => 'House Keeping', 'category' => 'facility']);
        $this->service(['slug' => 'c', 'title' => 'Plumbing', 'category' => 'maintenance']);

        $html = $this->get('/services?category=facility')->assertOk()->getContent();

        // The other category's section is rendered hidden; the chosen tab is active.
        $this->assertMatchesRegularExpression('#data-category="maintenance"[^>]*\shidden\s*>#', $html);
        $this->assertDoesNotMatchRegularExpression('#data-category="facility"[^>]*\shidden\s*>#', $html);
        $this->assertMatchesRegularExpression('#data-filter="facility"[^>]*class="is-active"#', $html);
        $this->assertDoesNotMatchRegularExpression('#data-filter=""[^>]*class="is-active"#', $html);

        // Filtered views are not separate pages for search engines: canonical stays /services.
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/services') . '"', $html);
    }

    public function test_unknown_or_empty_category_falls_back_to_all(): void
    {
        $this->service(['slug' => 'a', 'title' => 'House Keeping', 'category' => 'facility']);

        foreach (['nonsense', 'government', '', '<script>'] as $value) {
            $html = $this->get('/services?category=' . urlencode($value))->assertOk()->getContent();
            $this->assertMatchesRegularExpression('#data-filter=""[^>]*class="is-active"#', $html, "category=$value");
            $this->assertDoesNotMatchRegularExpression('#<section[^>]*\shidden\s*>#', $html, "category=$value");
        }
    }

    public function test_inactive_service_is_hidden_everywhere(): void
    {
        $this->service(['is_active' => false]);

        $this->get('/services/plumbing')->assertNotFound();
        $this->get('/services')->assertDontSee('Plumbing');
        $this->get('/sitemap.xml')->assertDontSee('/services/plumbing');
    }

    public function test_closed_job_is_noindex_without_schema_and_not_in_sitemap_or_list(): void
    {
        $this->job(['status' => 'closed', 'closed_at' => now()]);

        $this->get('/recruitment/field-executive')
            ->assertOk()
            ->assertSee('This position is closed')
            ->assertSee('content="noindex, nofollow"', false)
            ->assertDontSee('"JobPosting"', false)
            ->assertDontSee('Submit application');

        $this->get('/recruitment')->assertDontSee('Field Executive');
        $this->get('/sitemap.xml')->assertDontSee('field-executive');
    }

    public function test_job_past_its_closing_date_counts_as_closed_but_today_is_still_open(): void
    {
        $this->job(['slug' => 'expired', 'closing_date' => now()->subDay()->toDateString()]);
        $this->job(['slug' => 'today', 'title' => 'Today Job', 'closing_date' => now()->toDateString()]);

        $this->get('/recruitment/expired')->assertSee('This position is closed');
        $this->get('/recruitment/today')->assertSee('Submit application');
        $this->assertSame(['today'], JobOpening::open()->pluck('slug')->all());
    }

    public function test_sitemap_lists_public_urls_and_robots_points_to_it(): void
    {
        $this->service();
        $this->job();

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertNotFalse(simplexml_load_string($xml), 'sitemap must be well-formed XML');
        foreach (['/about', '/services', '/services/plumbing', '/recruitment', '/recruitment/field-executive', '/gallery', '/contact'] as $path) {
            $this->assertStringContainsString('<loc>' . url($path) . '</loc>', $xml);
        }

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: ' . url('/sitemap.xml'));
    }

    public function test_unknown_pages_return_a_noindex_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('noindex', false);
        $this->get('/services/nope')->assertNotFound();
        $this->get('/recruitment/nope')->assertNotFound();
    }

    public function test_gallery_paginates_and_only_shows_active_photos(): void
    {
        foreach (range(1, 26) as $i) {
            GalleryItem::create(['title' => "Photo $i", 'path' => "gallery/p$i.webp", 'is_active' => true]);
        }
        GalleryItem::create(['title' => 'Hidden photo', 'path' => 'gallery/h.webp', 'is_active' => false]);

        $this->get('/gallery')->assertOk()->assertDontSee('Hidden photo')->assertSee('rel="next"', false);
        $this->get('/gallery?page=2')->assertOk()
            ->assertSee('<link rel="canonical" href="' . url('/gallery') . '?page=2"', false)
            ->assertSee('Page 2');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
