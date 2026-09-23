@extends('layouts.admin')

@section('title', 'Leadership')

@section('actions')
    <a class="btn btn--primary" href="{{ route('admin.leadership.create') }}"><x-icon name="plus" :size="18" /> Add person</a>
@endsection

@section('content')
    <div class="card card--flush">
        @if ($leaders->isEmpty())
            <div class="empty"><x-icon name="award" :size="36" /><p>No leadership team members yet. Add your first one.</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Role</th><th>Order</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($leaders as $leader)
                            <tr>
                                <td><strong><a href="{{ route('admin.leadership.edit', $leader) }}">{{ $leader->name }}</a></strong></td>
                                <td>{{ $leader->role }}</td>
                                <td>{{ $leader->sort_order }}</td>
                                <td>
                                    @if ($leader->is_active)<span class="badge badge--green">Live</span>@else<span class="badge badge--grey">Hidden</span>@endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn--outline btn--sm" href="{{ route('admin.leadership.edit', $leader) }}">Edit</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.leadership.destroy', $leader) }}" data-confirm="Delete “{{ $leader->name }}”? This cannot be undone.">
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
    <p class="muted">The lowest display order shown here appears as the featured leader at the top of the About page; everyone else appears below in a grid.</p>
@endsection
