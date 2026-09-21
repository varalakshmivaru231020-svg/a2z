@extends('layouts.admin')

@section('title', 'Services')

@section('actions')
    <a class="btn btn--primary" href="{{ route('admin.services.create') }}"><x-icon name="plus" :size="18" /> Add service</a>
@endsection

@section('content')
    <div class="tabs">
        <a href="{{ route('admin.services.index') }}" @class(['is-active' => ! $category])>All</a>
        @foreach (config('site.service_categories') as $key => $cat)
            <a href="{{ route('admin.services.index', ['category' => $key]) }}" @class(['is-active' => $category === $key])>{{ $cat['name'] }}</a>
        @endforeach
    </div>

    <div class="card card--flush">
        @if ($services->isEmpty())
            <div class="empty"><x-icon name="briefcase" :size="36" /><p>No services yet. Add your first one.</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Service</th><th>Category</th><th>Order</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>
                                    <strong><a href="{{ route('admin.services.edit', $service) }}">{{ $service->title }}</a></strong>
                                    <small>/services/{{ $service->slug }}</small>
                                </td>
                                <td>{{ $service->categoryName() }}</td>
                                <td>{{ $service->sort_order }}</td>
                                <td>
                                    @if ($service->is_active)<span class="badge badge--green">Live</span>@else<span class="badge badge--grey">Hidden</span>@endif
                                    @if ($service->is_featured)<span class="badge badge--amber">Featured</span>@endif
                                </td>
                                <td>
                                    <div class="actions">
                                        @if ($service->is_active)
                                            <a class="btn btn--outline btn--sm" href="{{ $service->url() }}" target="_blank" rel="noopener">View</a>
                                        @endif
                                        <a class="btn btn--outline btn--sm" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.services.destroy', $service) }}" data-confirm="Delete “{{ $service->title }}”? This cannot be undone.">
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
