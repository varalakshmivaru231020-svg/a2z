@extends('layouts.admin')

@php($isEdit = $service->exists)
@section('title', $isEdit ? 'Edit service' : 'Add service')

@section('actions')
    <a class="btn btn--outline" href="{{ route('admin.services.index') }}">Back to services</a>
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.services.update', $service) : route('admin.services.store') }}">
        @csrf
        @if ($isEdit) @method('put') @endif

        <div class="form-cols">
            <div>
                <div class="card">
                    <div class="form-grid">
                        <x-admin.field class="full" name="title" label="Title" :value="$service->title" required maxlength="150" />

                        <x-admin.field name="category" label="Category" type="select" required>
                            @foreach (config('site.service_categories') as $key => $cat)
                                <option value="{{ $key }}" @selected(old('category', $service->category) === $key)>{{ $cat['name'] }}</option>
                            @endforeach
                        </x-admin.field>

                        <x-admin.field name="icon" label="Icon" type="select" required>
                            @foreach (\App\Support\Icons::SERVICE_CHOICES as $key => $label)
                                <option value="{{ $key }}" @selected(old('icon', $service->icon) === $key)>{{ $label }}</option>
                            @endforeach
                        </x-admin.field>

                        <x-admin.field class="full" name="summary" label="Short summary" type="textarea" rows="2" :value="$service->summary" required maxlength="300" hint="Shown on service cards and used as the default meta description. Max 300 characters." />

                        <x-admin.field class="full" name="description" label="Full description" type="textarea" rows="8" :value="$service->description" hint="Separate paragraphs with a blank line." />

                        <x-admin.field class="full" name="features" label="What's included" type="textarea" rows="5" :value="implode(PHP_EOL, $service->features ?? [])" hint="One point per line — shown as a checklist on the service page." />
                    </div>

                    <h2 class="section-title">Search engine listing (SEO)</h2>
                    <div class="form-grid">
                        <x-admin.field class="full" name="meta_title" label="Meta title" :value="$service->meta_title" maxlength="70" counter="60" :hint="'Leave blank to use “' . ($service->title ?: 'Service name') . ' in Bangalore”.'" />
                        <x-admin.field class="full" name="meta_description" label="Meta description" type="textarea" rows="2" :value="$service->meta_description" maxlength="200" counter="160" hint="Leave blank to use the short summary." />
                        <x-admin.field class="full" name="slug" label="URL slug" :value="$service->slug" maxlength="100" hint="Leave blank to generate from the title. Lowercase letters, numbers and hyphens only." />
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2>Publishing</h2>
                    <x-admin.check name="is_active" label="Show on website" :checked="$service->is_active ?? true" />
                    <x-admin.check name="is_featured" label="Feature on the home page" :checked="$service->is_featured" />
                    <x-admin.field name="sort_order" label="Display order" type="number" :value="$service->sort_order ?? 0" min="0" max="9999" hint="Lower numbers appear first." />

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">{{ $isEdit ? 'Save changes' : 'Add service' }}</button>
                        <a class="btn btn--outline" href="{{ route('admin.services.index') }}">Cancel</a>
                    </div>
                </div>

                <div class="card">
                    <h2>Photo (optional)</h2>
                    @if ($service->imageUrl())
                        <img class="current-image" src="{{ $service->imageUrl() }}" alt="Current photo for {{ $service->title }}">
                        <x-admin.check name="remove_image" label="Remove current photo" :checked="false" />
                    @endif
                    <div class="fld @error('image') has-error @enderror">
                        <label for="f-image">{{ $service->imageUrl() ? 'Replace photo' : 'Upload a photo' }}</label>
                        <input id="f-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                        <p class="fld__hint">JPG, PNG or WebP, up to 8 MB. Wide (16:9) photos look best.</p>
                        @error('image')<p class="fld__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
