<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobOpeningController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('status');

        $jobs = JobOpening::query()
            ->withCount(['applications', 'applications as new_applications_count' => fn ($q) => $q->where('status', 'new')])
            ->when($filter === 'open', fn ($q) => $q->open())
            ->when($filter === 'closed', fn ($q) => $q->whereNotIn('id', JobOpening::open()->select('id')))
            ->latest('id')
            ->get();

        return view('admin.jobs.index', ['jobs' => $jobs, 'filter' => $filter]);
    }

    public function create(): View
    {
        return view('admin.jobs.form', ['job' => new JobOpening([
            'employment_type' => 'full_time', 'status' => JobOpening::OPEN, 'vacancies' => 1, 'location' => 'Bangalore',
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $job = new JobOpening($this->payload($data));
        $job->published_at = now();
        $job->closed_at = $job->status === JobOpening::CLOSED ? now() : null;
        $job->save();

        return redirect()->route('admin.jobs.index')->with('status', "Job “{$job->title}” posted.");
    }

    public function edit(JobOpening $job): View
    {
        return view('admin.jobs.form', ['job' => $job]);
    }

    public function update(Request $request, JobOpening $job): RedirectResponse
    {
        $data = $request->validate($this->rules($job));

        $wasClosed = $job->status === JobOpening::CLOSED;
        $job->fill($this->payload($data, $job));

        if ($job->status === JobOpening::CLOSED && ! $wasClosed) {
            $job->closed_at = now();
        } elseif ($job->status === JobOpening::OPEN) {
            $job->closed_at = null;
        }
        $job->save();

        return redirect()->route('admin.jobs.index')->with('status', "Job “{$job->title}” updated.");
    }

    public function close(JobOpening $job): RedirectResponse
    {
        $job->update(['status' => JobOpening::CLOSED, 'closed_at' => now()]);

        return back()->with('status', "Job “{$job->title}” closed. It no longer accepts applications.");
    }

    public function reopen(JobOpening $job): RedirectResponse
    {
        // A past closing date would keep it closed, so clear it when reopening.
        $job->update([
            'status' => JobOpening::OPEN,
            'closed_at' => null,
            'closing_date' => $job->closing_date?->isPast() && ! $job->closing_date->isToday() ? null : $job->closing_date,
        ]);

        return back()->with('status', "Job “{$job->title}” reopened.");
    }

    public function destroy(JobOpening $job): RedirectResponse
    {
        // Applications are kept (job_opening_id becomes null, job_title is a snapshot).
        $job->delete();

        return redirect()->route('admin.jobs.index')->with('status', "Job “{$job->title}” deleted. Its applications were kept.");
    }

    private function rules(?JobOpening $job = null): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('job_openings', 'slug')->ignore($job)],
            'department' => ['nullable', 'string', 'max:80'],
            'location' => ['required', 'string', 'max:120'],
            'employment_type' => ['required', Rule::in(array_keys(config('site.employment_types')))],
            'experience' => ['nullable', 'string', 'max:80'],
            'salary' => ['nullable', 'string', 'max:80'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:999'],
            'summary' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string', 'max:10000'],
            'responsibilities' => ['nullable', 'string', 'max:5000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'closing_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in([JobOpening::OPEN, JobOpening::CLOSED])],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:200'],
        ];
    }

    private function payload(array $data, ?JobOpening $job = null): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : ($job?->slug ?: JobOpening::uniqueSlug($data['title']));

        return $data;
    }
}
