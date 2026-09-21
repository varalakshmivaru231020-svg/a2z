<?php

namespace Database\Seeders;

use App\Models\Enquiry;
use App\Models\GalleryItem;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Support\ImageUploader;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SAMPLE content for trying the site and admin panel locally:
 *   php artisan db:seed --class=DemoContentSeeder
 * Do NOT run this on the live site — the jobs and applications are made up.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = $this->jobs();

        foreach ($jobs as $job) {
            JobOpening::firstOrCreate(
                ['slug' => Str::slug($job['title'])],
                $job + ['published_at' => now()->subDays(rand(1, 10))],
            );
        }

        if (GalleryItem::count() === 0) {
            foreach (['office.jpg' => 'A bright, tidy office', 'crew.jpg' => 'Our team planning a project'] as $file => $title) {
                $upload = new UploadedFile(public_path("img/$file"), $file, 'image/jpeg', null, true);
                $stored = app(ImageUploader::class)->store($upload, 'gallery', 1600, 640);
                GalleryItem::create(['title' => $title, 'path' => $stored['path'], 'thumb_path' => $stored['thumb']]);
            }
        }

        Enquiry::firstOrCreate(
            ['email' => 'rahul.demo@example.com'],
            ['name' => 'Rahul Menon', 'phone' => '+91 98765 43210', 'subject' => 'House Keeping', 'message' => 'We run a 40-seat office in Whitefield and are looking for daily housekeeping. Could you share a quote?'],
        );

        $job = JobOpening::where('slug', 'housekeeping-supervisor')->first();
        foreach ([['Asha Rao', 'asha.demo@example.com', '3 years'], ['Imran Khan', 'imran.demo@example.com', '5 years']] as [$name, $email, $experience]) {
            if (JobApplication::where('email', $email)->exists()) {
                continue;
            }

            $path = 'resumes/demo-' . Str::random(10) . '.pdf';
            Storage::disk('local')->put($path, "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 100]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");

            JobApplication::create([
                'job_opening_id' => $job?->id,
                'job_title' => $job?->title ?? 'Housekeeping Supervisor',
                'name' => $name,
                'email' => $email,
                'phone' => '+91 90000 0' . rand(1000, 9999),
                'current_location' => 'Horamavu, Bangalore',
                'experience' => $experience,
                'cover_note' => 'I have supervised housekeeping teams in commercial buildings and would like to join A2Z Global Maintenance.',
                'resume_path' => $path,
                'resume_name' => Str::slug($name) . '-resume.pdf',
            ]);
        }
    }

    private function jobs(): array
    {
        return [
            [
                'title' => 'Housekeeping Supervisor',
                'department' => 'Facility Management',
                'location' => 'Bangalore',
                'employment_type' => 'full_time',
                'experience' => '2–4 years',
                'salary' => null,
                'vacancies' => 2,
                'summary' => 'Lead a housekeeping team at a client site in Bangalore and keep standards high every day.',
                'description' => "We are looking for a reliable housekeeping supervisor to manage our team at a commercial client site.\n\nYou will plan daily work, check quality and be the first point of contact for the client's facility team.",
                'responsibilities' => "Plan and allocate daily housekeeping work\nInspect cleanliness and quality on site\nTrain and support the housekeeping team\nManage attendance and consumables\nReport to the operations manager",
                'requirements' => "2+ years of housekeeping or facility experience\nAble to lead and motivate a team\nGood communication in English and Kannada or Hindi\nWilling to work on client sites",
                'closing_date' => now()->addDays(30)->toDateString(),
                'status' => 'open',
            ],
            [
                'title' => 'Field Executive',
                'department' => 'Field Services',
                'location' => 'Bangalore',
                'employment_type' => 'full_time',
                'experience' => '0–2 years',
                'salary' => null,
                'vacancies' => 5,
                'summary' => 'Represent our clients on the ground with customer visits, verification and follow-ups.',
                'description' => "Field executives are the face of our clients in the market. You will visit customers, verify details and follow up on leads, reporting daily to your team lead.",
                'responsibilities' => "Visit customers and prospects in an assigned area\nVerify details and collect information accurately\nFollow up on leads and pending work\nSubmit daily reports",
                'requirements' => "Freshers are welcome to apply\nOwn two-wheeler and smartphone preferred\nPolite, presentable and punctual\nKnowledge of Bangalore areas is a plus",
                'closing_date' => null,
                'status' => 'open',
            ],
            [
                'title' => 'Tele Marketing Executive',
                'department' => 'Financial Services',
                'location' => 'Kochi',
                'employment_type' => 'full_time',
                'experience' => 'Freshers welcome',
                'salary' => null,
                'vacancies' => 3,
                'summary' => 'Call prospects, explain our clients\' offers clearly and follow up on interest.',
                'description' => "Join our Kochi team as a tele-marketing executive. You will speak to prospects on behalf of our clients, explain offers in a clear, polite way and record outcomes.",
                'responsibilities' => "Make outbound calls using approved scripts\nRecord call outcomes accurately\nFollow up on interested prospects\nMeet daily call targets",
                'requirements' => "Fluent Malayalam and English; Hindi is a plus\nClear, confident phone manner\nBasic computer skills",
                'closing_date' => now()->addDays(14)->toDateString(),
                'status' => 'open',
            ],
            [
                'title' => 'Electrician',
                'department' => 'Maintenance',
                'location' => 'Bangalore',
                'employment_type' => 'contract',
                'experience' => '3+ years',
                'salary' => null,
                'vacancies' => 1,
                'summary' => 'Electrical maintenance and repairs at client premises across Bangalore.',
                'description' => 'Sample of a closed position — it stays reachable by link but is hidden from the openings list and search engines.',
                'responsibilities' => null,
                'requirements' => null,
                'closing_date' => null,
                'status' => 'closed',
                'closed_at' => now()->subDays(3),
            ],
        ];
    }
}
