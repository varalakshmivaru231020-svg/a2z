@extends('layouts.admin')

@section('title', 'Enquiries')

@section('content')
    <div class="tabs">
        <a href="{{ route('admin.enquiries.index') }}" @class(['is-active' => $filter !== 'unread'])>All</a>
        <a href="{{ route('admin.enquiries.index', ['filter' => 'unread']) }}" @class(['is-active' => $filter === 'unread'])>Unread ({{ $unreadCount }})</a>
    </div>

    <div class="card card--flush">
        @if ($enquiries->isEmpty())
            <div class="empty"><x-icon name="inbox" :size="36" /><p>{{ $filter === 'unread' ? 'No unread enquiries.' : 'No enquiries yet.' }}</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>From</th><th>About</th><th>Message</th><th>Received</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($enquiries as $enquiry)
                            <tr @class(['is-unread' => ! $enquiry->isRead()])>
                                <td>
                                    <strong><a href="{{ route('admin.enquiries.show', $enquiry) }}">{{ $enquiry->name }}</a></strong>
                                    <small>{{ $enquiry->email }} · {{ $enquiry->phone }}</small>
                                </td>
                                <td>{{ $enquiry->subject ?: 'General enquiry' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($enquiry->message, 70) }}</td>
                                <td class="nowrap">{{ $enquiry->created_at->format('j M Y') }}<small>{{ $enquiry->created_at->format('g:i a') }}</small></td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn--primary btn--sm" href="{{ route('admin.enquiries.show', $enquiry) }}">Open</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-confirm="Delete this enquiry?">
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

    {{ $enquiries->links() }}
@endsection
