@extends('layouts.admin')

@section('title', $application->name)

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.applications.index') }}">Back to applications</a>
@endsection

@section('content')
    <div class="form-cols">
        <div>
            <div class="card">
                <h2>Candidate</h2>
                <dl class="details">
                    <dt>Name</dt><dd>{{ $application->name }}</dd>
                    <dt>Email</dt><dd><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></dd>
                    <dt>Phone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $application->phone) }}">{{ $application->phone }}</a></dd>
                    <dt>Current location</dt><dd>{{ $application->current_location ?: '—' }}</dd>
                    <dt>Experience</dt><dd>{{ $application->experience ?: '—' }}</dd>
                    <dt>Applied for</dt>
                    <dd>
                        @if ($application->job)
                            <a href="{{ route('admin.jobs.edit', $application->job) }}">{{ $application->job_title }}</a>
                        @else
                            {{ $application->job_title }} <span class="badge badge--grey">Job deleted</span>
                        @endif
                    </dd>
                    <dt>Applied on</dt><dd>{{ $application->created_at->format('j M Y, g:i a') }}</dd>
                </dl>
            </div>

            @if ($application->cover_note)
                <div class="card">
                    <h2>Note from the candidate</h2>
                    <div class="prose">{{ $application->cover_note }}</div>
                </div>
            @endif

            <div class="card">
                <h2>Resume</h2>
                <div class="resume-card">
                    <x-icon name="file" :size="28" />
                    <p>{{ $application->resume_name }}</p>
                    <a class="btn btn--primary" href="{{ route('admin.applications.resume', $application) }}"><x-icon name="download" :size="18" /> Download</a>
                </div>
            </div>
        </div>

        <div>
            <form class="card" method="post" action="{{ route('admin.applications.update', $application) }}">
                @csrf @method('patch')
                <h2>Review</h2>
                <x-admin.field name="status" label="Status" type="select" required>
                    @foreach (\App\Models\JobApplication::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $application->status) === $key)>{{ $label }}</option>
                    @endforeach
                </x-admin.field>
                <x-admin.field name="admin_notes" label="Internal notes" type="textarea" rows="5" :value="$application->admin_notes" maxlength="3000" hint="Only visible to admins." />
                <button class="btn btn--primary btn--block" type="submit">Save</button>
            </form>

            <form class="card" method="post" action="{{ route('admin.applications.destroy', $application) }}" data-confirm="Delete this application and the candidate's resume? This cannot be undone.">
                @csrf @method('delete')
                <h2>Delete</h2>
                <p class="muted">Permanently removes this application and the uploaded resume file.</p>
                <button class="btn btn--danger btn--block" type="submit">Delete application</button>
            </form>
        </div>
    </div>
@endsection
