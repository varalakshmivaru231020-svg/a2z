@extends('layouts.admin')

@section('title', 'Applications')

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.applications.export', $filters) }}"><x-icon name="download" :size="18" /> Export CSV</a>
@endsection

@section('content')
    <form class="toolbar" method="get" action="{{ route('admin.applications.index') }}">
        <div class="fld fld--grow">
            <label for="q">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, phone or position">
        </div>
        <div class="fld">
            <label for="job">Position</label>
            <select id="job" name="job">
                <option value="">All positions</option>
                @foreach ($jobs as $job)
                    <option value="{{ $job->id }}" @selected(($filters['job'] ?? null) == $job->id)>{{ $job->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="fld">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach (\App\Models\JobApplication::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? null) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn--primary" type="submit">Filter</button>
        @if (array_filter($filters))
            <a class="btn btn--outline" href="{{ route('admin.applications.index') }}">Reset</a>
        @endif
    </form>

    <div class="card card--flush">
        @if ($applications->isEmpty())
            <div class="empty"><x-icon name="users" :size="36" /><p>{{ array_filter($filters) ? 'No applications match these filters.' : 'No applications yet.' }}</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Candidate</th><th>Position</th><th>Experience</th><th>Applied</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr @class(['is-unread' => $application->status === 'new'])>
                                <td>
                                    <strong><a href="{{ route('admin.applications.show', $application) }}">{{ $application->name }}</a></strong>
                                    <small>{{ $application->email }} · {{ $application->phone }}</small>
                                </td>
                                <td>{{ $application->job_title }}</td>
                                <td>{{ $application->experience ?: '—' }}</td>
                                <td class="nowrap">{{ $application->created_at->format('j M Y') }}<small>{{ $application->created_at->format('g:i a') }}</small></td>
                                <td>
                                    @php($tone = ['new' => 'blue', 'reviewed' => 'grey', 'shortlisted' => 'amber', 'rejected' => 'red', 'hired' => 'green'][$application->status] ?? 'grey')
                                    <span class="badge badge--{{ $tone }}">{{ $application->statusLabel() }}</span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn--outline btn--sm" href="{{ route('admin.applications.resume', $application) }}"><x-icon name="download" :size="15" /> Resume</a>
                                        <a class="btn btn--primary btn--sm" href="{{ route('admin.applications.show', $application) }}">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{ $applications->links() }}
@endsection
