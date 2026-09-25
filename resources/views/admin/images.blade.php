@extends('layouts.admin')

@section('title', 'Page images')

@section('content')
    <p class="muted" style="max-width:780px">
        Replace the photos on the Home and About pages. Pick a file for any picture below and press <strong>Save images</strong> —
        the change is live straight away. Until you upload one, the page keeps its built-in photo.
    </p>

    <form method="post" enctype="multipart/form-data" action="{{ route('admin.images.update') }}">
        @csrf @method('put')

        @foreach ($groups as $page => $slots)
            <section class="card">
                <h2>{{ $page }}</h2>

                <div class="image-slots">
                    @foreach ($slots as $slot)
                        @php($image = $slot['image'])
                        <div class="image-slot fld @error("images.{$slot['slot']}") has-error @enderror">
                            <label for="image-{{ $slot['slot'] }}">{{ $slot['label'] }}</label>
                            @if ($image['url'])
                                <img class="image-preview" src="{{ $image['url'] }}" alt="Current image: {{ $slot['label'] }}" loading="lazy">
                            @else
                                <div class="image-preview image-preview--none">Plain blue background</div>
                            @endif
                            <p class="fld__hint" style="margin:0 0 8px">{{ $image['custom'] ? 'Your uploaded image.' : ($image['url'] ? 'Built-in image — nothing uploaded yet.' : 'Nothing uploaded yet — the band shows its default blue background.') }}</p>

                            @if ($image['custom'])
                                <label class="check"><input type="checkbox" name="remove_image[{{ $slot['slot'] }}]" value="1"> <span>Remove my image (go back to the default)</span></label>
                            @endif

                            <input id="image-{{ $slot['slot'] }}" name="images[{{ $slot['slot'] }}]" type="file" accept="image/jpeg,image/png,image/webp" style="margin-top:8px">
                            <p class="fld__hint">{{ $slot['hint'] }} JPG, PNG or WebP · up to 8 MB.</p>
                            @error("images.{$slot['slot']}")<p class="fld__error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <button class="btn btn--primary" type="submit">Save images</button>
    </form>
@endsection
