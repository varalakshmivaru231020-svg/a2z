<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Rules\ResumeFile;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function index(): View
    {
        $jobs = JobOpening::open()->orderByDesc('published_at')->orderByDesc('id')->get();

        $seo = Seo::forPage('recruitment')->schema([
            '@type' => 'CollectionPage',
            'name' => 'Job openings at ' . config('site.brand'),
            'url' => route('recruitment.index'),
            'isPartOf' => ['@id' => url('/') . '#website'],
        ]);

        return view('recruitment.index', ['seo' => $seo, 'jobs' => $jobs]);
    }

    public function show(string $slug): View
    {
        $job = JobOpening::where('slug', $slug)->firstOrFail();
        $isOpen = $job->isOpen();

        $seo = Seo::make(
            $job->meta_title ?: $job->title . ' – Job in ' . $job->location,
            $job->meta_description ?: $job->summary,
            $job->url(),
        )
            ->bannerFrom('recruitment')
            ->crumb('Home', url('/'))
            ->crumb('Recruitment', route('recruitment.index'))
            ->crumb($job->title, $job->url());

        if ($isOpen) {
            $seo->schema($this->jobPostingSchema($job));
        } else {
            // Closed positions stay reachable for old links but should drop out of search.
            $seo->noindex();
        }

        return view('recruitment.show', ['seo' => $seo, 'job' => $job, 'isOpen' => $isOpen]);
    }

    public function apply(Request $request, string $slug): RedirectResponse
    {
        $job = JobOpening::where('slug', $slug)->firstOrFail();

        if (! $job->isOpen()) {
            return redirect($job->url())->with('error', 'Applications for this position are now closed.');
        }

        // Honeypot: real candidates never see or fill this field.
        if (filled($request->input('website'))) {
            return redirect($job->url())->with('applied', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'current_location' => ['nullable', 'string', 'max:120'],
            'experience' => ['nullable', 'string', 'max:60'],
            'cover_note' => ['nullable', 'string', 'max:2000'],
            'resume' => ['required', 'file', 'max:5120', new ResumeFile()],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
            'resume.required' => 'Please attach your resume.',
            'resume.max' => 'Your resume must be 5 MB or smaller.',
            'resume.uploaded' => 'The resume could not be uploaded. Please try a smaller file.',
        ]);

        $alreadyApplied = JobApplication::where('job_opening_id', $job->id)
            ->whereRaw('lower(email) = ?', [Str::lower($data['email'])])
            ->exists();

        if ($alreadyApplied) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This email address has already been used to apply for this position.']);
        }

        $file = $request->file('resume');

        JobApplication::create([
            'job_opening_id' => $job->id,
            'job_title' => $job->title,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'current_location' => $data['current_location'] ?? null,
            'experience' => $data['experience'] ?? null,
            'cover_note' => $data['cover_note'] ?? null,
            // Private disk (storage/app/private): only reachable through the admin download route.
            // Random name + the *validated* extension, never the client's file name.
            'resume_path' => $file->storeAs('resumes', bin2hex(random_bytes(20)) . '.' . ResumeFile::extensionFor($file)),
            'resume_name' => Str::limit(basename($file->getClientOriginalName()), 150, ''),
            'ip' => $request->ip(),
        ]);

        return redirect($job->url() . '#apply')->with('applied', true);
    }

    private function jobPostingSchema(JobOpening $job): array
    {
        $posting = [
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => $this->htmlDescription($job),
            'datePosted' => ($job->published_at ?? $job->created_at)->toDateString(),
            'employmentType' => $job->schemaEmploymentType(),
            'directApply' => true,
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => config('site.name'),
                'sameAs' => url('/'),
                'logo' => \App\Support\SiteSettings::logo('header')['url'],
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $job->location,
                    'addressCountry' => 'IN',
                ],
            ],
            'url' => $job->url(),
        ];

        if ($job->closing_date) {
            $posting['validThrough'] = $job->closing_date->endOfDay()->toIso8601String();
        }

        if ($job->department) {
            $posting['occupationalCategory'] = $job->department;
        }

        return $posting;
    }

    /** JobPosting.description must be HTML; build it from the plain-text fields. */
    private function htmlDescription(JobOpening $job): string
    {
        $html = collect($job->paragraphs())->map(fn ($p) => '<p>' . e($p) . '</p>')->implode('');

        foreach (['responsibilities' => 'Responsibilities', 'requirements' => 'Requirements'] as $column => $label) {
            $lines = $job->lines($column);
            if ($lines) {
                $html .= "<h3>$label</h3><ul>" . collect($lines)->map(fn ($l) => '<li>' . e($l) . '</li>')->implode('') . '</ul>';
            }
        }

        return $html;
    }
}
