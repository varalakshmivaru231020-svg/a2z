@extends('layouts.admin')

@php($isEdit = $category->exists)
@section('title', $isEdit ? 'Edit category' : 'Add category')

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.services.categories.index') }}">Back to categories</a>
@endsection

@section('content')
    <form method="post" action="{{ $isEdit ? route('admin.services.categories.update', $category) : route('admin.services.categories.store') }}">
        @csrf
        @if ($isEdit) @method('put') @endif

        <div class="card">
            <div class="form-grid">
                <x-admin.field class="full" name="name" label="Category name" :value="$category->name" required maxlength="80" />

                <x-admin.field class="full" name="blurb" label="Short description" type="textarea" rows="3" :value="$category->blurb" maxlength="300" hint="Shown on the home page and Services page. Max 300 characters." />

                <x-admin.field name="icon" label="Icon" type="select" required>
                    @foreach (\App\Support\Icons::SERVICE_CHOICES as $key => $label)
                        <option value="{{ $key }}" @selected(old('icon', $category->icon) === $key)>{{ $label }}</option>
                    @endforeach
                </x-admin.field>

                <x-admin.field name="sort_order" label="Display order" type="number" :value="$category->sort_order ?? 0" min="0" max="9999" hint="Lower numbers appear first." />
            </div>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">{{ $isEdit ? 'Save changes' : 'Add category' }}</button>
                <a class="btn btn--outline" href="{{ route('admin.services.categories.index') }}">Cancel</a>
            </div>
        </div>
    </form>
@endsection
