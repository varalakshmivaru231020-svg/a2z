<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\GalleryItem;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\SeoPage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function service(array $attrs = []): Service
    {
        return Service::create($attrs + ['category' => 'maintenance', 'title' => 'Plumbing', 'slug' => 'plumbing', 'summary' => 'Repairs.', 'icon' => 'wrench']);
    }

    private function job(array $attrs = []): JobOpening
    {
        return JobOpening::create($attrs + [
            'title' => 'Field Executive', 'slug' => 'field-executive', 'location' => 'Bangalore', 'employment_type' => 'full_time',
            'vacancies' => 1, 'summary' => 'Visit customers.', 'description' => 'Details.', 'status' => 'open', 'published_at' => now(),
        ]);
    }

    private function application(array $attrs = []): JobApplication
    {
        return JobApplication::create($attrs + [
            'job_title' => 'Field Executive', 'name' => 'Asha Rao', 'email' => 'asha@example.com', 'phone' => '9876543210',
            'resume_path' => 'resumes/abc.pdf', 'resume_name' => 'asha-cv.pdf',
        ]);
    }

    /* ---------------------------------------------------------------- auth */

    public function test_guests_are_sent_to_the_login_page_from_every_admin_route(): void
    {
        $service = $this->service();
        $job = $this->job();
        $application = $this->application();
        $enquiry = Enquiry::create(['name' => 'A', 'email' => 'a@example.com', 'phone' => '9876543210', 'message' => 'Hello there friend']);

        foreach ([
            '/admin', '/admin/services', '/admin/services/create', "/admin/services/{$service->id}/edit", '/admin/jobs', '/admin/jobs/create',
            "/admin/jobs/{$job->id}/edit", '/admin/applications', '/admin/applications/export', "/admin/applications/{$application->id}",
            "/admin/applications/{$application->id}/resume", '/admin/gallery', '/admin/enquiries', "/admin/enquiries/{$enquiry->id}", '/admin/seo',
        ] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }

        $this->post('/admin/services', [])->assertRedirect('/admin/login');
        $this->delete("/admin/services/{$service->id}")->assertRedirect('/admin/login');
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_admin_can_log_in_and_out(): void
    {
        User::factory()->create(['email' => 'boss@example.com', 'password' => 'correct-horse']);

        $this->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'correct-horse'])->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->get('/admin')->assertOk()->assertSee('Dashboard');

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_repeated_failures(): void
    {
        User::factory()->create(['email' => 'boss@example.com', 'password' => 'correct-horse']);

        foreach (range(1, 5) as $i) {
            $this->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'nope']);
        }

        // Even the right password is refused while locked out.
        $this->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'correct-horse'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_pages_render_for_a_logged_in_admin(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();
        $service = $this->service();
        $job = $this->job();
        $application = $this->application();
        $enquiry = Enquiry::create(['name' => 'A', 'email' => 'a@example.com', 'phone' => '9876543210', 'message' => 'Hello there friend']);

        foreach ([
            '/admin', '/admin/services', '/admin/services/create', "/admin/services/{$service->id}/edit", '/admin/jobs', '/admin/jobs/create',
            "/admin/jobs/{$job->id}/edit", '/admin/applications', "/admin/applications/{$application->id}", '/admin/gallery',
            '/admin/enquiries', "/admin/enquiries/{$enquiry->id}", '/admin/seo',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    /* ------------------------------------------------------------ services */

    public function test_admin_can_add_edit_and_delete_a_service(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->post('/admin/services', [
            'category' => 'maintenance', 'title' => 'Tile Polishing', 'summary' => 'Shiny floors.', 'icon' => 'grid',
            'features' => "Point one\n\n  Point two  ", 'is_active' => '1', 'is_featured' => '0', 'sort_order' => '5',
            'image' => UploadedFile::fake()->image('tile.jpg', 1800, 1000),
        ])->assertRedirect('/admin/services');

        $service = Service::firstWhere('title', 'Tile Polishing');
        $this->assertSame('tile-polishing', $service->slug);
        $this->assertSame(['Point one', 'Point two'], $service->features);
        $this->assertTrue($service->is_active);
        $this->assertFalse($service->is_featured);
        Storage::disk('uploads')->assertExists($service->image);
        $this->get('/services/tile-polishing')->assertOk()->assertSee('Point two');

        $oldImage = $service->image;
        $this->put("/admin/services/{$service->id}", [
            'category' => 'field', 'title' => 'Tile Polishing Pro', 'summary' => 'Even shinier.', 'icon' => 'grid',
            'is_active' => '0', 'remove_image' => '1', 'meta_title' => 'Tile Polishing Bangalore',
        ])->assertRedirect('/admin/services');

        $service->refresh();
        $this->assertSame('Tile Polishing Pro', $service->title);
        $this->assertSame('tile-polishing', $service->slug, 'slug stays stable when the title changes');
        $this->assertFalse($service->is_active);
        $this->assertNull($service->image);
        Storage::disk('uploads')->assertMissing($oldImage);
        $this->get('/services/tile-polishing')->assertNotFound();

        $this->delete("/admin/services/{$service->id}")->assertRedirect('/admin/services');
        $this->assertModelMissing($service);
    }

    public function test_service_validation_and_unique_slugs(): void
    {
        $this->actingAsAdmin();
        $this->service();

        $this->post('/admin/services', ['title' => ''])->assertSessionHasErrors(['category', 'title', 'summary', 'icon']);
        $this->post('/admin/services', ['category' => 'nope', 'title' => 'X', 'summary' => 'y', 'icon' => 'not-an-icon'])
            ->assertSessionHasErrors(['category', 'icon']);
        $this->post('/admin/services', ['category' => 'facility', 'title' => 'Other', 'slug' => 'plumbing', 'summary' => 'y', 'icon' => 'star'])
            ->assertSessionHasErrors('slug');
        $this->post('/admin/services', ['category' => 'facility', 'title' => 'Bad', 'slug' => 'Bad Slug!', 'summary' => 'y', 'icon' => 'star'])
            ->assertSessionHasErrors('slug');

        // Same title twice gets a distinct slug rather than an error.
        $this->post('/admin/services', ['category' => 'facility', 'title' => 'Plumbing', 'summary' => 'again', 'icon' => 'star'])->assertSessionHasNoErrors();
        $this->assertSame(['plumbing', 'plumbing-2'], Service::orderBy('id')->pluck('slug')->all());
    }

    /* ---------------------------------------------------------------- jobs */

    public function test_admin_can_post_edit_close_reopen_and_delete_a_job(): void
    {
        $this->actingAsAdmin();

        $this->post('/admin/jobs', [
            'title' => 'Housekeeping Supervisor', 'location' => 'Bangalore', 'employment_type' => 'full_time', 'vacancies' => 2,
            'summary' => 'Lead a team.', 'description' => 'About the role.', 'status' => 'open',
            'closing_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect('/admin/jobs');

        $job = JobOpening::firstOrFail();
        $this->assertSame('housekeeping-supervisor', $job->slug);
        $this->assertNotNull($job->published_at);
        $this->get('/recruitment')->assertSee('Housekeeping Supervisor');

        $this->put("/admin/jobs/{$job->id}", [
            'title' => 'Housekeeping Supervisor (Senior)', 'location' => 'Kochi', 'employment_type' => 'contract', 'vacancies' => 1,
            'summary' => 'Lead a bigger team.', 'description' => 'About the role.', 'status' => 'open',
        ])->assertRedirect('/admin/jobs');
        $this->assertSame('Kochi', $job->refresh()->location);
        $this->assertSame('housekeeping-supervisor', $job->slug);

        $this->patch("/admin/jobs/{$job->id}/close")->assertRedirect();
        $job->refresh();
        $this->assertSame('closed', $job->status);
        $this->assertNotNull($job->closed_at);
        $this->get('/recruitment')->assertDontSee('Housekeeping Supervisor');

        $this->patch("/admin/jobs/{$job->id}/reopen")->assertRedirect();
        $this->assertTrue($job->refresh()->isOpen());
        $this->assertNull($job->closed_at);

        $this->delete("/admin/jobs/{$job->id}")->assertRedirect('/admin/jobs');
        $this->assertModelMissing($job);
    }

    public function test_reopening_an_expired_job_clears_the_past_closing_date(): void
    {
        $this->actingAsAdmin();
        $job = $this->job(['status' => 'closed', 'closing_date' => now()->subWeek()->toDateString()]);

        $this->patch("/admin/jobs/{$job->id}/reopen");

        $job->refresh();
        $this->assertNull($job->closing_date);
        $this->assertTrue($job->isOpen());
    }

    public function test_deleting_a_job_keeps_its_applications(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();
        $job = $this->job();
        $application = $this->application(['job_opening_id' => $job->id]);

        $this->delete("/admin/jobs/{$job->id}");

        $application->refresh();
        $this->assertNull($application->job_opening_id);
        $this->assertSame('Field Executive', $application->job_title);
        $this->get("/admin/applications/{$application->id}")->assertOk()->assertSee('Job deleted');
    }

    public function test_job_validation(): void
    {
        $this->actingAsAdmin();

        $this->post('/admin/jobs', ['title' => ''])->assertSessionHasErrors(['title', 'location', 'employment_type', 'vacancies', 'summary', 'description', 'status']);
        $this->post('/admin/jobs', ['title' => 'X', 'location' => 'Y', 'employment_type' => 'bogus', 'vacancies' => 0, 'summary' => 's', 'description' => 'd', 'status' => 'weird', 'closing_date' => 'not-a-date'])
            ->assertSessionHasErrors(['employment_type', 'vacancies', 'status', 'closing_date']);
    }

    /* -------------------------------------------------------- applications */

    public function test_admin_can_view_and_download_a_resume_but_guests_cannot(): void
    {
        $this->fakeDisks();
        Storage::disk('local')->put('resumes/abc.pdf', '%PDF-1.4 resume body');
        $application = $this->application();

        $this->get("/admin/applications/{$application->id}/resume")->assertRedirect('/admin/login');

        $this->actingAsAdmin();
        $response = $this->get("/admin/applications/{$application->id}/resume")->assertOk();
        $response->assertDownload('asha-rao-field-executive.pdf');
        $this->assertSame('%PDF-1.4 resume body', $response->streamedContent());

        $this->get("/admin/applications/{$application->id}")->assertOk()->assertSee('asha-cv.pdf')->assertSee('Download');
    }

    public function test_missing_resume_file_gives_a_404_not_a_crash(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();
        $application = $this->application();

        $this->get("/admin/applications/{$application->id}/resume")->assertNotFound();
    }

    public function test_admin_can_filter_update_and_delete_applications(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();
        Storage::disk('local')->put('resumes/abc.pdf', 'x');
        $keep = $this->application(['name' => 'Someone Else', 'email' => 'else@example.com', 'resume_path' => 'resumes/other.pdf']);
        $target = $this->application();

        $this->get('/admin/applications?q=Asha')->assertSee('Asha Rao')->assertDontSee('Someone Else');
        $this->get('/admin/applications?status=hired')->assertDontSee('Asha Rao');

        $this->patch("/admin/applications/{$target->id}", ['status' => 'shortlisted', 'admin_notes' => 'Call on Monday'])->assertRedirect();
        $this->assertSame('shortlisted', $target->refresh()->status);
        $this->patch("/admin/applications/{$target->id}", ['status' => 'bogus'])->assertSessionHasErrors('status');

        $this->delete("/admin/applications/{$target->id}")->assertRedirect('/admin/applications');
        $this->assertModelMissing($target);
        Storage::disk('local')->assertMissing('resumes/abc.pdf'); // resume removed with the record
        $this->assertModelExists($keep);
    }

    public function test_csv_export_is_complete_and_neutralises_spreadsheet_formulas(): void
    {
        $this->actingAsAdmin();
        $this->application(['name' => '=HYPERLINK("http://evil.example","click")', 'cover_note' => "+1+1\nsecond line"]);
        $this->application(['name' => 'Plain Person', 'email' => 'plain@example.com']);

        $response = $this->get('/admin/applications/export')->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Plain Person', $csv);
        $this->assertStringContainsString('"\'=HYPERLINK', $csv, 'formula cells must be prefixed so they stay text');
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
        $this->assertStringContainsString("'+1+1", $csv);

        $filtered = $this->get('/admin/applications/export?q=Plain')->streamedContent();
        $this->assertStringContainsString('Plain Person', $filtered);
        $this->assertStringNotContainsString('HYPERLINK', $filtered);
    }

    /* ------------------------------------------------------------- gallery */

    public function test_admin_can_upload_several_photos_edit_and_delete_them(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->post('/admin/gallery', ['images' => [
            UploadedFile::fake()->image('a.jpg', 2400, 1600),
            UploadedFile::fake()->image('b.png', 800, 600),
        ]])->assertRedirect()->assertSessionHas('status', '2 photos uploaded.');

        $this->assertSame(2, GalleryItem::count());
        $first = GalleryItem::orderBy('id')->first();
        Storage::disk('uploads')->assertExists([$first->path, $first->thumb_path]);

        [$width] = getimagesizefromstring(Storage::disk('uploads')->get($first->path));
        $this->assertLessThanOrEqual(1600, $width, 'large photos are downsized');
        [$thumbWidth] = getimagesizefromstring(Storage::disk('uploads')->get($first->thumb_path));
        $this->assertLessThanOrEqual(640, $thumbWidth);

        $this->patch("/admin/gallery/{$first->id}", ['title' => 'Front office', 'caption' => 'Reception', 'sort_order' => 3, 'is_active' => '0'])->assertRedirect();
        $first->refresh();
        $this->assertSame('Front office', $first->title);
        $this->assertFalse($first->is_active);
        $this->get('/gallery')->assertDontSee('Front office');

        $this->delete("/admin/gallery/{$first->id}")->assertRedirect();
        Storage::disk('uploads')->assertMissing($first->path);
        Storage::disk('uploads')->assertMissing($first->thumb_path);
    }

    public function test_gallery_rejects_non_images_and_svg(): void
    {
        $this->fakeDisks();
        $this->actingAsAdmin();

        $this->post('/admin/gallery', ['images' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')]])->assertSessionHasErrors('images.0');
        $this->post('/admin/gallery', ['images' => [UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')]])->assertSessionHasErrors('images.0');
        $this->post('/admin/gallery', [])->assertSessionHasErrors('images');

        $this->assertSame(0, GalleryItem::count());
    }

    /* ------------------------------------------------------ enquiries + seo */

    public function test_opening_an_enquiry_marks_it_read(): void
    {
        $this->actingAsAdmin();
        $enquiry = Enquiry::create(['name' => 'Rahul', 'email' => 'r@example.com', 'phone' => '9876543210', 'message' => 'Please call me back.']);

        $this->assertFalse($enquiry->isRead());
        $this->get('/admin/enquiries?filter=unread')->assertSee('Rahul');

        $this->get("/admin/enquiries/{$enquiry->id}")->assertOk()->assertSee('Please call me back.');
        $this->assertTrue($enquiry->refresh()->isRead());
        $this->get('/admin/enquiries?filter=unread')->assertDontSee('Rahul');

        $this->delete("/admin/enquiries/{$enquiry->id}")->assertRedirect('/admin/enquiries');
        $this->assertModelMissing($enquiry);
    }

    public function test_admin_can_edit_seo_for_static_pages_and_blank_reverts_to_default(): void
    {
        $this->actingAsAdmin();

        $this->put('/admin/seo', ['pages' => ['home' => ['meta_title' => 'Best Facility Team in Bangalore', 'meta_description' => 'Our custom home description.'], 'about' => ['meta_title' => '', 'meta_description' => '']]])
            ->assertRedirect();

        $this->assertSame('Best Facility Team in Bangalore', SeoPage::firstWhere('page_key', 'home')->meta_title);
        $this->assertNull(SeoPage::firstWhere('page_key', 'about')->meta_title);
        $this->assertSame(count(config('seo.pages')), SeoPage::count());

        $this->get('/')->assertSee('<title>Best Facility Team in Bangalore | A2Z Global Maintenance</title>', false);
        $this->get('/about')->assertSee(config('seo.pages.about.title'));

        $this->put('/admin/seo', ['pages' => ['home' => ['meta_title' => str_repeat('x', 71)]]])->assertSessionHasErrors('pages.home.meta_title');
    }
}
