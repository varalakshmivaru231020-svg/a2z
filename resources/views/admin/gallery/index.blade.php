@extends('layouts.admin')

@section('title', 'Gallery')

@section('content')
    <form class="card" method="post" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
        @csrf
        <h2>Upload photos</h2>
        <div class="dropzone">
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required data-file-count>
            <button class="btn btn--primary" type="submit"><x-icon name="plus" :size="18" /> Upload</button>
        </div>
        <p class="fld__hint">JPG, PNG or WebP · up to 8 MB each · up to 20 at a time. Photos are resized automatically. <span data-file-count-label></span></p>
    </form>

    @if ($items->isEmpty())
        <div class="card"><div class="empty"><x-icon name="image" :size="36" /><p>No photos yet. Upload your first photos above.</p></div></div>
    @else
        <div class="media-grid">
            @foreach ($items as $item)
                <article @class(['media-card', 'is-hidden' => ! $item->is_active])>
                    <img src="{{ $item->thumbUrl() }}" alt="{{ $item->alt() }}" loading="lazy" width="640" height="480">
                    <div class="media-card__body">
                        <form method="post" action="{{ route('admin.gallery.update', $item) }}">
                            @csrf @method('patch')
                            <div class="fld"><label for="t-{{ $item->id }}">Title</label><input id="t-{{ $item->id }}" name="title" type="text" value="{{ $item->title }}" maxlength="150" placeholder="Also used as the image's alt text"></div>
                            <div class="fld"><label for="c-{{ $item->id }}">Caption</label><input id="c-{{ $item->id }}" name="caption" type="text" value="{{ $item->caption }}" maxlength="300"></div>
                            <div class="fld"><label for="o-{{ $item->id }}">Order</label><input id="o-{{ $item->id }}" name="sort_order" type="number" min="0" max="9999" value="{{ $item->sort_order }}"></div>
                            <input type="hidden" name="is_active" value="0">
                            <div class="fld"><label class="check"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> <span>Show on website</span></label></div>
                            <button class="btn btn--primary btn--sm btn--block" type="submit">Save</button>
                        </form>
                        <form method="post" action="{{ route('admin.gallery.destroy', $item) }}" data-confirm="Delete this photo?" style="margin-top:8px">
                            @csrf @method('delete')
                            <button class="btn btn--danger btn--sm btn--block" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $items->links() }}
    @endif
@endsection
