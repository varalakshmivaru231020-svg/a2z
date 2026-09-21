@extends('layouts.admin')

@section('title', 'Banners & SEO')

@section('content')
    <p class="muted" style="max-width:780px">
        Upload a <strong>banner photo</strong> for each page — it appears behind the page title, under a dark blue overlay so the text stays readable.
        Wide photos work best (about 1920 × 600). Service pages use the Services banner and job pages use the Recruitment banner.
        Below each banner, set the title and description search engines show; leave those blank to use the default (shown as placeholder text).
        Aim for titles up to about 60 characters and descriptions up to about 160. The site name is added to titles automatically.
    </p>

    <form method="post" enctype="multipart/form-data" action="{{ route('admin.seo.update') }}">
        @csrf @method('put')

        @foreach ($pages as $key => $defaults)
            @php
                $row = $saved->get($key);
                $titleId = "seo-{$key}-title";
                $descId = "seo-{$key}-desc";
            @endphp
            <section class="card seo-card">
                <h2>
                    <span>{{ $defaults['label'] }}</span>
                    <a href="{{ url($defaults['path']) }}" target="_blank" rel="noopener">{{ $defaults['path'] }} <x-icon name="external" :size="13" /></a>
                </h2>

                @if ($key !== 'home')
                    <div class="fld @error("banners.$key") has-error @enderror">
                        <label for="banner-{{ $key }}">Banner image</label>
                        @if ($row?->bannerUrl())
                            <img class="banner-preview" src="{{ $row->bannerUrl() }}" alt="Current banner for {{ $defaults['label'] }}" loading="lazy">
                            <label class="check"><input type="checkbox" name="remove_banner[{{ $key }}]" value="1"> <span>Remove this banner</span></label>
                        @else
                            <p class="fld__hint" style="margin:0 0 8px">No banner yet — this page shows the default blue background.</p>
                        @endif
                        <input id="banner-{{ $key }}" name="banners[{{ $key }}]" type="file" accept="image/jpeg,image/png,image/webp" style="margin-top:8px">
                        <p class="fld__hint">{{ $row?->bannerUrl() ? 'Choose a file to replace it. ' : '' }}JPG, PNG or WebP · up to 8 MB.</p>
                        @error("banners.$key")<p class="fld__error">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div class="fld @error("pages.$key.meta_title") has-error @enderror">
                    <label for="{{ $titleId }}">Meta title</label>
                    <input id="{{ $titleId }}" name="pages[{{ $key }}][meta_title]" type="text" maxlength="70" data-counter="60"
                           value="{{ old("pages.$key.meta_title", $row?->meta_title) }}" placeholder="{{ $defaults['title'] }}">
                    <p class="fld__count" data-counter-for="{{ $titleId }}" aria-live="polite"></p>
                    @error("pages.$key.meta_title")<p class="fld__error">{{ $message }}</p>@enderror
                </div>

                <div class="fld @error("pages.$key.meta_description") has-error @enderror">
                    <label for="{{ $descId }}">Meta description</label>
                    <textarea id="{{ $descId }}" name="pages[{{ $key }}][meta_description]" rows="2" maxlength="200" data-counter="160"
                              placeholder="{{ $defaults['description'] }}">{{ old("pages.$key.meta_description", $row?->meta_description) }}</textarea>
                    <p class="fld__count" data-counter-for="{{ $descId }}" aria-live="polite"></p>
                    @error("pages.$key.meta_description")<p class="fld__error">{{ $message }}</p>@enderror
                </div>
            </section>
        @endforeach

        <button class="btn btn--primary" type="submit">Save banners &amp; SEO</button>
    </form>
@endsection
