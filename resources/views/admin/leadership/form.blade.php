@extends('layouts.admin')

@php($isEdit = $leader->exists)
@section('title', $isEdit ? 'Edit person' : 'Add person')

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.leadership.index') }}">Back to leadership</a>
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.leadership.update', $leader) : route('admin.leadership.store') }}">
        @csrf
        @if ($isEdit) @method('put') @endif

        <div class="form-cols">
            <div>
                <div class="card">
                    <div class="form-grid">
                        <x-admin.field name="name" label="Name" :value="$leader->name" required maxlength="150" />
                        <x-admin.field name="role" label="Role" :value="$leader->role" required maxlength="150" />
                        <x-admin.field class="full" name="bio" label="Short bio" type="textarea" rows="4" :value="$leader->bio" required maxlength="2000" />
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2>Publishing</h2>
                    <x-admin.check name="is_active" label="Show on website" :checked="$leader->is_active ?? true" />
                    <x-admin.field name="sort_order" label="Display order" type="number" :value="$leader->sort_order ?? 0" min="0" max="9999" hint="The lowest number becomes the featured leader at the top of the About page." />

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">{{ $isEdit ? 'Save changes' : 'Add person' }}</button>
                        <a class="btn btn--outline" href="{{ route('admin.leadership.index') }}">Cancel</a>
                    </div>
                </div>

                <div class="card">
                    <h2>Photo (optional)</h2>
                    @if ($leader->photoUrl())
                        <img class="current-image" src="{{ $leader->photoUrl() }}" alt="Current photo for {{ $leader->name }}">
                        <x-admin.check name="remove_photo" label="Remove current photo" :checked="false" />
                    @endif
                    <div class="fld @error('photo') has-error @enderror">
                        <label for="f-photo">{{ $leader->photoUrl() ? 'Replace photo' : 'Upload a photo' }}</label>
                        <input id="f-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                        <p class="fld__hint">JPG, PNG or WebP, up to 8 MB. Only the featured leader's photo is shown on the About page.</p>
                        @error('photo')<p class="fld__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
