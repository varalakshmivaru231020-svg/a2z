@extends('layouts.admin')

@section('title', 'Service categories')

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.services.index') }}">Back to services</a>
    <a class="btn btn--primary" href="{{ route('admin.services.categories.create') }}"><x-icon name="plus" :size="18" /> Add category</a>
@endsection

@section('content')
    <div class="card card--flush">
        @if ($categories->isEmpty())
            <div class="empty"><x-icon name="briefcase" :size="36" /><p>No categories yet. Add your first one.</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Category</th><th>Services</th><th>Order</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <strong><a href="{{ route('admin.services.categories.edit', $category) }}">{{ $category->name }}</a></strong>
                                    <small>{{ $category->key }}</small>
                                </td>
                                <td>{{ $category->serviceCount() }}</td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn--outline btn--sm" href="{{ route('admin.services.categories.edit', $category) }}">Edit</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.services.categories.destroy', $category) }}" data-confirm="Delete “{{ $category->name }}”? This cannot be undone.">
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
