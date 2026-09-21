<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    private function pdf(string $name = 'cv.pdf', string $body = "%PDF-1.4\n1 0 obj<<>>endobj\n%%EOF"): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $body);
    }

    private function job(array $attrs = []): JobOpening
    {
        return JobOpening::create($attrs + [
            'title' => 'Field Executive', 'slug' => 'field-executive', 'location' => 'Bangalore',
            'employment_type' => 'full_time', 'vacancies' => 1, 'summary' => 'Visit customers.',
            'description' => 'Details.', 'status' => 'open', 'published_at' => now(),
        ]);
    }

    private function applicant(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Asha Rao', 'email' => 'asha@example.com', 'phone' => '+91 98765 43210',
            'current_location' => 'Horamavu', 'experience' => '3 years', 'cover_note' => 'Keen to join.',
            'resume' => $this->pdf(),
        ];
    }

    /* ------------------------------------------------------------ enquiry */

    public function test_enquiry_is_saved_and_visitor_sees_confirmation(): void
    {
        $this->from('/contact')->post('/contact', [
            'name' => 'Rahul Menon', 'email' => 'rahul@example.com', 'phone' => '9876543210',
            'subject' => 'House Keeping', 'message' => 'Please share a quote for our office.',
        ])->assertRedirect('/contact#enquiry')->assertSessionHas('enquiry_sent');

        $this->assertDatabaseHas('enquiries', ['email' => 'rahul@example.com', 'subject' => 'House Keeping']);
        // The confirmation is a one-time flash: shown on the next page view, gone after that.
        $this->get('/contact')->assertSee('your enquiry has been sent');
        $this->get('/contact')->assertDontSee('your enquiry has been sent');
    }

    public function test_confirmation_message_renders_after_submission(): void
    {
        $this->withSession(['enquiry_sent' => true])->get('/contact')->assertSee('your enquiry has been sent');
    }

    public function test_enquiry_validation(): void
    {
        $this->post('/contact', ['name' => '', 'email' => 'nope', 'phone' => 'abc', 'message' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'message']);

        $this->assertSame(0, Enquiry::count());
    }

    public function test_enquiry_honeypot_silently_discards_bots(): void
    {
        $this->post('/contact', [
            'name' => 'Bot', 'email' => 'bot@example.com', 'phone' => '9876543210',
            'message' => 'Buy cheap stuff now please', 'website' => 'http://spam.example',
        ])->assertSessionHas('enquiry_sent');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_contact_form_is_rate_limited(): void
    {
        $payload = ['name' => 'A B', 'email' => 'a@example.com', 'phone' => '9876543210', 'message' => 'A perfectly valid message.'];

        foreach (range(1, 6) as $i) {
            $this->post('/contact', $payload)->assertSessionHasNoErrors();
        }
        $this->post('/contact', $payload)->assertStatus(429);
    }

    public function test_contact_page_shows_the_three_office_cards_below_the_enquiry_form(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'data-office '), 'one card per office');
        $this->assertLessThan(strpos($html, 'class="offices"'), strpos($html, 'id="enquiry"'), 'offices come after the enquiry form');
        foreach (config('site.offices') as $office) {
            $this->assertStringContainsString(e($office['label']), $html);
        }
    }

    public function test_contact_page_preselects_the_service_from_the_query_string(): void
    {
        Service::create(['category' => 'maintenance', 'title' => 'Plumbing', 'slug' => 'plumbing', 'summary' => 'x', 'icon' => 'wrench']);

        $this->get('/contact?service=plumbing')->assertSee('<option value="Plumbing" selected>', false);
    }

    /* -------------------------------------------------------- job application */

    public function test_candidate_can_apply_and_resume_is_stored_privately(): void
    {
        $this->fakeDisks();
        $job = $this->job();

        $this->post("/recruitment/{$job->slug}/apply", $this->applicant())
            ->assertRedirect($job->url() . '#apply')
            ->assertSessionHas('applied');

        $application = JobApplication::firstOrFail();
        $this->assertSame($job->id, $application->job_opening_id);
        $this->assertSame('Field Executive', $application->job_title);
        $this->assertSame('cv.pdf', $application->resume_name);
        $this->assertSame('new', $application->status);

        // Stored on the private disk, under a random name, and never in the public web root.
        Storage::disk('local')->assertExists($application->resume_path);
        $this->assertStringStartsWith('resumes/', $application->resume_path);
        $this->assertStringEndsWith('.pdf', $application->resume_path);
        $this->assertStringNotContainsString('cv.pdf', $application->resume_path, "the candidate's file name is never used on disk");
        $this->assertFileDoesNotExist(public_path($application->resume_path));
    }

    public function test_application_validation_rejects_bad_input_and_dangerous_files(): void
    {
        $this->fakeDisks();
        $job = $this->job();

        $this->post("/recruitment/{$job->slug}/apply", ['name' => '', 'email' => 'bad', 'phone' => '1'])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'resume']);

        // A PHP script renamed to .pdf must not be accepted. Real temp file, so the MIME type is sniffed from its contents.
        $script = tempnam(sys_get_temp_dir(), 'evil');
        file_put_contents($script, '<?php system($_GET["c"]);');
        $disguised = new UploadedFile($script, 'evil.pdf', 'application/pdf', null, true);
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['resume' => $disguised]))->assertSessionHasErrors('resume');
        @unlink($script);

        // Wrong type and oversized files.
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['resume' => UploadedFile::fake()->image('me.png')]))
            ->assertSessionHasErrors('resume');
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['resume' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')]))
            ->assertSessionHasErrors('resume');

        $this->assertSame(0, JobApplication::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_genuine_docx_and_legacy_doc_resumes_are_accepted_and_stored_with_a_safe_extension(): void
    {
        $this->fakeDisks();
        $job = $this->job();

        // Real files (not Laravel fakes) so the type is sniffed from the contents.
        $docx = tempnam(sys_get_temp_dir(), 'cv');
        $zip = new \ZipArchive();
        $zip->open($docx, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body/></w:document>');
        $zip->close();

        $doc = tempnam(sys_get_temp_dir(), 'cv');
        file_put_contents($doc, hex2bin('D0CF11E0A1B11AE1') . str_repeat("\0", 600)); // OLE2 container = legacy Word

        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['resume' => new UploadedFile($docx, 'My CV.DOCX', null, null, true)]))->assertSessionHas('applied');
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['email' => 'old@example.com', 'resume' => new UploadedFile($doc, 'old cv.doc', null, null, true)]))->assertSessionHas('applied');

        $paths = JobApplication::orderBy('id')->pluck('resume_path')->all();
        $this->assertStringEndsWith('.docx', $paths[0]);
        $this->assertStringEndsWith('.doc', $paths[1]);
        Storage::disk('local')->assertExists($paths);
        @unlink($docx);
        @unlink($doc);
    }

    public function test_a_plain_zip_or_mismatched_extension_is_not_a_resume(): void
    {
        $this->fakeDisks();
        $job = $this->job();

        $zip = tempnam(sys_get_temp_dir(), 'zip');
        $archive = new \ZipArchive();
        $archive->open($zip, \ZipArchive::OVERWRITE);
        $archive->addFromString('notes.txt', 'not a word document');
        $archive->close();

        // A zip named .pdf, or with a disallowed extension, is never a resume.
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['resume' => new UploadedFile($zip, 'cv.pdf', null, null, true)]))->assertSessionHasErrors('resume');
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['resume' => new UploadedFile($zip, 'cv.exe', null, null, true)]))->assertSessionHasErrors('resume');

        $this->assertSame(0, JobApplication::count());
        @unlink($zip);
    }

    public function test_same_email_cannot_apply_twice_to_the_same_job(): void
    {
        $this->fakeDisks();
        $job = $this->job();

        $this->post("/recruitment/{$job->slug}/apply", $this->applicant())->assertSessionHas('applied');
        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['email' => 'ASHA@example.com', 'resume' => $this->pdf()]))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, JobApplication::count());
    }

    public function test_applications_to_closed_jobs_are_refused(): void
    {
        $this->fakeDisks();
        $job = $this->job(['status' => 'closed']);

        $this->post("/recruitment/{$job->slug}/apply", $this->applicant())->assertSessionHas('error');
        $this->assertSame(0, JobApplication::count());
    }

    public function test_application_honeypot(): void
    {
        $this->fakeDisks();
        $job = $this->job();

        $this->post("/recruitment/{$job->slug}/apply", $this->applicant(['website' => 'http://spam']))->assertSessionHas('applied');
        $this->assertSame(0, JobApplication::count());
    }
}
