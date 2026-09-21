@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="stat-grid">
        <a class="stat-card" href="{{ route('admin.services.index') }}"><x-icon name="briefcase" /><div><strong>{{ $stats['services'] }}</strong><span>Services</span></div></a>
        <a class="stat-card" href="{{ route('admin.jobs.index', ['status' => 'open']) }}"><x-icon name="clipboard" /><div><strong>{{ $stats['open_jobs'] }}</strong><span>Open positions</span></div></a>
        <a class="stat-card" href="{{ route('admin.applications.index', ['status' => 'new']) }}"><x-icon name="users" /><div><strong>{{ $stats['new_applications'] }}</strong><span>New applications</span></div></a>
        <a class="stat-card" href="{{ route('admin.enquiries.index', ['filter' => 'unread']) }}"><x-icon name="inbox" /><div><strong>{{ $stats['unread_enquiries'] }}</strong><span>Unread enquiries</span></div></a>
        <a class="stat-card" href="{{ route('admin.gallery.index') }}"><x-icon name="image" /><div><strong>{{ $stats['photos'] }}</strong><span>Gallery photos</span></div></a>
    </div>

    <div class="two-col">
        <section class="card card--flush">
            <div class="card__head"><h2>Latest applications</h2><a class="btn btn--outline btn--sm" href="{{ route('admin.applications.index') }}">View all</a></div>
            @if ($applications->isEmpty())
                <p class="empty">No applications yet.</p>
            @else
                <ul class="list">
                    @foreach ($applications as $application)
                        <li>
                            <div>
                                <a href="{{ route('admin.applications.show', $application) }}">{{ $application->name }}</a>
                                <small>{{ $application->job_title }}</small>
                            </div>
                            <small class="nowrap">{{ $application->created_at->diffForHumans() }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="card card--flush">
            <div class="card__head"><h2>Latest enquiries</h2><a class="btn btn--outline btn--sm" href="{{ route('admin.enquiries.index') }}">View all</a></div>
            @if ($enquiries->isEmpty())
                <p class="empty">No enquiries yet.</p>
            @else
                <ul class="list">
                    @foreach ($enquiries as $enquiry)
                        <li>
                            <div>
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}">{{ $enquiry->name }} @unless ($enquiry->isRead())<span class="badge badge--blue">New</span>@endunless</a>
                                <small>{{ $enquiry->subject ?: 'General enquiry' }}</small>
                            </div>
                            <small class="nowrap">{{ $enquiry->created_at->diffForHumans() }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
