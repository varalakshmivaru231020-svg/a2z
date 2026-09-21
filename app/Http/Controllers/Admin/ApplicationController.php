<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = $this->filtered($request)->latest()->paginate(20)->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'jobs' => JobOpening::orderBy('title')->get(['id', 'title']),
            'filters' => $request->only(['job', 'status', 'q']),
        ]);
    }

    public function show(JobApplication $application): View
    {
        return view('admin.applications.show', ['application' => $application->load('job')]);
    }

    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(JobApplication::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $application->update($data);

        return back()->with('status', 'Application updated.');
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        $application->delete(); // also removes the stored resume

        return redirect()->route('admin.applications.index')->with('status', 'Application and resume deleted.');
    }

    /** Streams the private resume file to the (authenticated) admin. */
    public function resume(JobApplication $application): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($application->resume_path), 404, 'Resume file not found.');

        return Storage::disk('local')->download($application->resume_path, $application->resumeDownloadName());
    }

    /** CSV of the (filtered) applications. */
    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filtered($request)->latest()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads names correctly
            fputcsv($out, ['ID', 'Applied on', 'Position', 'Name', 'Email', 'Phone', 'Location', 'Experience', 'Status', 'Cover note', 'Resume file', 'Resume link'], ',', '"', '');

            foreach ($rows as $a) {
                fputcsv($out, array_map([$this, 'csvSafe'], [
                    $a->id,
                    $a->created_at->format('Y-m-d H:i'),
                    $a->job_title,
                    $a->name,
                    $a->email,
                    $a->phone,
                    $a->current_location,
                    $a->experience,
                    $a->statusLabel(),
                    $a->cover_note,
                    $a->resume_name,
                    route('admin.applications.resume', $a),
                ]), ',', '"', '');
            }

            fclose($out);
        }, 'job-applications-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        return JobApplication::query()
            ->when($request->query('job'), fn (Builder $q, $job) => $q->where('job_opening_id', $job))
            ->when($request->query('status'), fn (Builder $q, $status) => $q->where('status', $status))
            ->when($request->query('q'), function (Builder $q, $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('job_title', 'like', $like));
            });
    }

    /** Stops spreadsheet formula injection: a cell like "=HYPERLINK(…)" from a candidate must stay text. */
    private function csvSafe(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}
