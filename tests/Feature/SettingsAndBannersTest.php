<?php

namespace Tests\Feature;

use App\Models\JobOpening;
use App\Models\SeoPage;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsAndBannersTest extends TestCase
{
    use RefreshDatabase;

    private const BLANK_OFFICE = ['label' => '', 'street' => '', 'locality' => '', 'region' => '', 'postal' => '', 'map' => ''];

    private function settings(array $override = []): array
    {
        return $override + [
            'phone' => '+91 98765 43210',
            'email' => 'hello@a2z.example',
            'tagline' => 'Reliable care, every day',
            'slogan' => 'Comfort always',
            'footer_text' => 'We look after buildings, big and small.',
            'offices' => [
                ['label' => 'Head Office', 'street' => '12 MG Road', 'locality' => 'Bangalore', 'region' => 'Karnataka', 'postal' => '560001', 'map' => ''],
                ['label' => 'Kochi Office', 'street' => '5 Marine Drive', 'locality' => 'Kochi', 'region' => 'Kerala', 'postal' => '682011', 'map' => ''],
                self::BLANK_OFFICE,
                self::BLANK_OFFICE,
            ],
        ];
    }

    private function saveSettings(array $override = [])
    {
        return $this->put('/admin/settings', $this->settings($override));
    }

    private function saveBanners(array $files = [], array $remove = [])
    {
        return $this->put('/admin/seo', ['pages' => ['about' => ['meta_title' => '', 'meta_description' => '']], 'banners' => $files, 'remove_banner' => $remove]);
    }

    /* ------------------------------------------------------------ access */

    public function test_settings_and_banner_screens_need_a_login(): void
    {
        $this->get('/admin/settings')->assertRedirect('/admin/login');
        $this->put('/admin/settings', $this->settings())->assertRedirect('/admin/login');
        $this->put('/admin/seo', [])->assertRedirect('/admin/login');
    }

    public function test_settings_screen_shows_the_current_values(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/settings')->assertOk()
            ->assertSee('+91 87146 34801')
            ->assertSee(config('site.email'))
            ->assertSee('Header logo')->assertSee('Footer logo')->assertSee('Office addresses');
    }

    /* ---------------------------------------------- settings flow to the site */

    public function test_saved_settings_appear_in_the_footer_contact_page_and_call_buttons(): void
    {
        $this->actingAsAdmin();
        $this->saveSettings()->assertRedirect()->assertSessionHas('status');

        $contact = $this->get('/contact')->assertOk();
        $contact->assertSee('+91 98765 43210')->assertSee('hello@a2z.example')
            ->assertSee('tel:+919876543210', false)
            ->assertSee('wa.me/919876543210', false)
            ->assertSee('mailto:hello@a2z.example', false)
            ->assertSee('Head Office')->assertSee('12 MG Road, Bangalore, Karnataka 560001')
            ->assertSee('Kochi Office')
            ->assertDontSee('+91 87146 34801')
            ->assertDontSee('Registered Office');
        $this->assertSame(2, substr_count($contact->getContent(), 'data-office '), 'only the two saved offices');

        $home = $this->get('/')->assertOk();
        $home->assertSee('Reliable care, every day')
            ->assertSee('We look after buildings, big and small.')
            ->assertSee('Comfort always')
            ->assertDontSee('+91 87146 34801');
    }

    public function test_the_top_bar_shows_only_the_tagline_line_no_phone_or_email(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $topbar = substr($html, strpos($html, 'class="topbar"'), strpos($html, 'class="navbar"') - strpos($html, 'class="topbar"'));

        $this->assertStringContainsString(config('site.tagline'), $topbar);
        $this->assertStringNotContainsString('tel:', $topbar);
        $this->assertStringNotContainsString('mailto:', $topbar);
    }

    public function test_a_plain_ten_digit_number_is_treated_as_indian_for_links(): void
    {
        $this->actingAsAdmin();
        $this->saveSettings(['phone' => '98765 43210']);

        $this->get('/contact')->assertSee('tel:+919876543210', false)->assertSee('wa.me/919876543210', false);
    }

    public function test_clearing_the_slogan_hides_it_and_clearing_footer_text_restores_the_default(): void
    {
        $this->actingAsAdmin();
        $this->saveSettings(['slogan' => null, 'footer_text' => null]);

        $home = $this->get('/')->assertOk();
        $home->assertDontSee('footer__slogan', false)
            ->assertDontSee('Have an exciting comfort always')
            ->assertSee(config('site.footer_text'));
        $this->get('/about')->assertDontSee('class="quote"', false);
    }

    public function test_settings_also_reach_structured_data(): void
    {
        $this->actingAsAdmin();
        $this->saveSettings();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get('/')->getContent(), $m);
        $org = collect($m[1])->map(fn ($b) => json_decode($b, true))->firstWhere('@id', url('/') . '#organization');

        $this->assertSame('+919876543210', $org['telephone']);
        $this->assertSame('hello@a2z.example', $org['email']);
        $this->assertSame('12 MG Road', $org['address']['streetAddress']);
        $this->assertCount(2, $org['location']);
    }

    public function test_settings_validation(): void
    {
        $this->actingAsAdmin();

        $this->saveSettings(['phone' => '', 'email' => 'nope', 'tagline' => ''])->assertSessionHasErrors(['phone', 'email', 'tagline']);
        $this->saveSettings(['phone' => 'call me'])->assertSessionHasErrors('phone');
        $this->saveSettings(['offices' => [self::BLANK_OFFICE]])->assertSessionHasErrors(['offices.0.label', 'offices.0.street', 'offices.0.locality']);

        // A half-filled extra office needs its name, street and city.
        $this->saveSettings(['offices' => [
            $this->settings()['offices'][0],
            ['label' => '', 'street' => '9 Park Street', 'locality' => '', 'region' => '', 'postal' => '', 'map' => ''],
        ]])->assertSessionHasErrors(['offices.1.label', 'offices.1.locality']);

        // Nothing invalid was saved.
        $this->get('/contact')->assertSee('+91 87146 34801');
    }

    /* -------------------------------------------------------------- logos */

    public function test_header_and_footer_logos_can_be_uploaded_badged_and_removed(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->saveSettings([
            'header_logo' => UploadedFile::fake()->image('head.png', 600, 300),
            'footer_logo' => UploadedFile::fake()->image('foot.png', 500, 500),
            'footer_logo_badge' => '1',
        ])->assertSessionHasNoErrors();

        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('#<img src="[^"]*/uploads/branding/[^"]+\.webp\?v=\d+" alt="" width="\d+" height="76" class="brand__logo">#', $html, 'header logo');
        $this->assertMatchesRegularExpression('#footer__logo footer__logo--badge#', $html, 'white badge behind the footer logo');
        $this->assertMatchesRegularExpression('#<img src="[^"]*/uploads/branding/[^"]+\.webp\?v=\d+" alt="[^"]*logo" width="170" height="170"#', $html, 'footer logo keeps its proportions');

        $stored = collect(Storage::disk('uploads')->allFiles('branding'));
        $this->assertCount(2, $stored);

        // Removing goes back to the built-in logos and deletes the files.
        $this->saveSettings(['remove_header_logo' => '1', 'remove_footer_logo' => '1', 'footer_logo_badge' => '0']);
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('img/logo.png', $html);
        $this->assertStringContainsString('img/logo-footer.png', $html);
        $this->assertStringNotContainsString('footer__logo--badge', $html);
        $this->assertSame([], Storage::disk('uploads')->allFiles('branding'));
    }

    public function test_replacing_a_logo_deletes_the_old_file_and_bad_files_are_refused(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->saveSettings(['footer_logo' => UploadedFile::fake()->image('one.png', 400, 400)]);
        [$first] = Storage::disk('uploads')->allFiles('branding');

        $this->saveSettings(['footer_logo' => UploadedFile::fake()->image('two.png', 400, 400)]);
        $files = Storage::disk('uploads')->allFiles('branding');
        $this->assertCount(1, $files);
        $this->assertNotSame($first, $files[0]);

        $this->saveSettings(['header_logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')])->assertSessionHasErrors('header_logo');
        $this->saveSettings(['header_logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>1</script></svg>')])->assertSessionHasErrors('header_logo');
    }

    /* ------------------------------------------------------------ banners */

    public function test_a_banner_uploaded_for_a_page_appears_in_that_pages_hero_only(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->saveBanners(['about' => UploadedFile::fake()->image('about.jpg', 2400, 800)])->assertSessionHasNoErrors();

        $page = SeoPage::firstWhere('page_key', 'about');
        $this->assertNotNull($page->banner);
        Storage::disk('uploads')->assertExists($page->banner);

        $this->get('/about')->assertOk()
            ->assertSee('class="page-hero has-banner"', false)
            ->assertSee('--hero-image: url(', false)
            ->assertSee('/uploads/banners/', false);
        $this->get('/gallery')->assertDontSee('has-banner', false);

        [$width] = getimagesizefromstring(Storage::disk('uploads')->get($page->banner));
        $this->assertLessThanOrEqual(1920, $width, 'large banners are downsized');
    }

    public function test_service_pages_use_the_services_banner_and_job_pages_the_recruitment_banner(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();
        Service::create(['category' => 'maintenance', 'title' => 'Plumbing', 'slug' => 'plumbing', 'summary' => 'Repairs and fittings for homes.', 'icon' => 'wrench']);
        JobOpening::create(['title' => 'Field Executive', 'slug' => 'field-executive', 'location' => 'Bangalore', 'employment_type' => 'full_time', 'vacancies' => 1,
            'summary' => 'Visit customers and verify details.', 'description' => 'Details.', 'status' => 'open', 'published_at' => now()]);

        $this->get('/services/plumbing')->assertDontSee('has-banner', false);

        $this->put('/admin/seo', ['pages' => ['services' => [], 'recruitment' => []], 'banners' => [
            'services' => UploadedFile::fake()->image('s.jpg', 1600, 500),
            'recruitment' => UploadedFile::fake()->image('r.jpg', 1600, 500),
        ]])->assertSessionHasNoErrors();

        $services = SeoPage::firstWhere('page_key', 'services')->banner;
        $jobs = SeoPage::firstWhere('page_key', 'recruitment')->banner;

        $this->get('/services/plumbing')->assertSee('has-banner', false)->assertSee($services, false);
        $this->get('/services')->assertSee($services, false);
        $this->get('/recruitment/field-executive')->assertSee('has-banner', false)->assertSee($jobs, false);
    }

    public function test_banners_can_be_replaced_and_removed_and_the_files_are_cleaned_up(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->saveBanners(['about' => UploadedFile::fake()->image('a.jpg', 1900, 600)]);
        $old = SeoPage::firstWhere('page_key', 'about')->banner;

        $this->saveBanners(['about' => UploadedFile::fake()->image('b.jpg', 1900, 600)]);
        $new = SeoPage::firstWhere('page_key', 'about')->banner;
        $this->assertNotSame($old, $new);
        Storage::disk('uploads')->assertMissing($old);
        Storage::disk('uploads')->assertExists($new);

        $this->saveBanners([], ['about' => '1']);
        $this->assertNull(SeoPage::firstWhere('page_key', 'about')->banner);
        Storage::disk('uploads')->assertMissing($new);
        $this->get('/about')->assertDontSee('has-banner', false);
    }

    public function test_banner_uploads_are_validated_and_the_home_page_has_no_banner_slot(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->saveBanners(['about' => UploadedFile::fake()->create('banner.pdf', 20, 'application/pdf')])->assertSessionHasErrors('banners.about');
        $this->saveBanners(['about' => UploadedFile::fake()->createWithContent('b.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>1</script></svg>')])->assertSessionHasErrors('banners.about');

        // The home page keeps its own hero: a banner sent for it is ignored.
        $this->put('/admin/seo', ['pages' => ['home' => []], 'banners' => ['home' => UploadedFile::fake()->image('h.jpg', 1900, 600)]]);
        $this->assertNull(SeoPage::firstWhere('page_key', 'home')->banner);
        $this->assertSame([], Storage::disk('uploads')->allFiles('banners'));

        $screen = $this->get('/admin/seo')->assertOk()->getContent();
        $this->assertSame(0, substr_count($screen, 'name="banners[home]"'));
        $this->assertSame(1, substr_count($screen, 'name="banners[about]"'));
    }

    /* ------------------------------------------------------ button animation */

    public function test_every_button_slides_a_colour_in_from_the_left_on_hover(): void
    {
        foreach (['site', 'admin'] as $sheet) {
            $css = file_get_contents(public_path("css/$sheet.css"));

            $this->assertMatchesRegularExpression('#\.btn::before\s*\{[^}]*transform:\s*translateX\(-101%\)#', $css, "$sheet: starts off-canvas on the left");
            $this->assertMatchesRegularExpression('#\.btn:hover::before[^{]*\{[^}]*transform:\s*translateX\(0\)#', $css, "$sheet: slides across on hover");
            $this->assertMatchesRegularExpression('#\.btn::before\s*\{[^}]*transition:\s*transform#', $css, "$sheet: animated");
            $this->assertMatchesRegularExpression('#\.btn\s*\{[^}]*overflow:\s*hidden#', $css, "$sheet: clipped to the button shape");
        }

        // Each public button style names the colour that slides in.
        $site = file_get_contents(public_path('css/site.css'));
        foreach (['primary', 'accent', 'outline', 'outline-light', 'whatsapp', 'glass', 'white'] as $variant) {
            $this->assertMatchesRegularExpression("#\\.btn--$variant\\s*\\{[^}]*--slide:#", $site, "btn--$variant");
        }
    }
}
