@extends('layouts.admin')

@section('title', 'Job openings')

@section('actions')
    <a class="btn btn--primary" href="{{ route('admin.jobs.create') }}"><x-icon name="plus" :size="18" /> Post a job</a>
@endsection

@section('content')
    <div class="tabs">
        <a href="{{ route('admin.jobs.index') }}" @class(['is-active' => ! $filter])>All</a>
        <a href="{{ route('admin.jobs.index', ['status' => 'open']) }}" @class(['is-active' => $filter === 'open'])>Open</a>
        <a href="{{ route('admin.jobs.index', ['status' => 'closed']) }}" @class(['is-active' => $filter === 'closed'])>Closed</a>
    </div>

    <div class="card card--flush">
        @if ($jobs->isEmpty())
            <div class="empty"><x-icon name="clipboard" :size="36" /><p>No job openings here yet.</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Position</th><th>Location</th><th>Closes</th><th>Status</th><th>Applications</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($jobs as $job)
                            <tr>
                                <td>
                                    <strong><a href="{{ route('admin.jobs.edit', $job) }}">{{ $job->title }}</a></strong>
                                    <small>{{ $job->typeLabel() }}@if ($job->department) · {{ $job->department }}@endif</small>
                                </td>
                                <td>{{ $job->location }}</td>
                                <td class="nowrap">{{ $job->closing_date ? $job->closing_date->format('j M Y') : '—' }}</td>
                                <td>
                                    @if ($job->isOpen())
                                        <span class="badge badge--green">Open</span>
                                    @elseif ($job->status === 'open')
                                        <span class="badge badge--amber">Expired</span>
                                    @else
                                        <span class="badge badge--grey">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.applications.index', ['job' => $job->id]) }}">{{ $job->applications_count }}</a>
                                    @if ($job->new_applications_count)<span class="badge badge--blue">{{ $job->new_applications_count }} new</span>@endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn--outline btn--sm" href="{{ route('admin.jobs.edit', $job) }}">Edit</a>
                                        @if ($job->isOpen())
                                            <form class="inline-form" method="post" action="{{ route('admin.jobs.close', $job) }}" data-confirm="Close “{{ $job->title }}”? It will stop accepting applications.">
                                                @csrf @method('patch')
                                                <button class="btn btn--outline btn--sm" type="submit">Close</button>
                                            </form>
                                        @else
                                            <form class="inline-form" method="post" action="{{ route('admin.jobs.reopen', $job) }}">
                                                @csrf @method('patch')
                                                <button class="btn btn--success btn--sm" type="submit">Reopen</button>
                                            </form>
                                        @endif
                                        <form class="inline-form" method="post" action="{{ route('admin.jobs.destroy', $job) }}" data-confirm="Delete “{{ $job->title }}”? Existing applications will be kept.">
                                            @csrf @method('delete')
                                            <button class="btn btn--danger btn--sm" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
