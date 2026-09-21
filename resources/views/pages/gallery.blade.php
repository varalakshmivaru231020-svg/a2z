@extends('layouts.site')

@section('content')
    <x-page-hero title="Photo gallery" lead="A look at our teams, our services and the places we look after." eyebrow="Gallery" :seo="$seo" />

    <section class="section">
        <div class="container">
            @if ($items->isEmpty())
                <div class="empty-state">
                    <x-icon name="image" :size="40" />
                    <h2>Photos are on their way</h2>
                    <p>We're adding pictures of our work and team. In the meantime, feel free to get in touch.</p>
                    <a class="btn btn--primary" href="{{ route('contact') }}">Contact us</a>
                </div>
            @else
                <div class="gallery" data-lightbox-group>
                    @foreach ($items as $item)
                        <a class="gallery__item" href="{{ $item->url() }}" data-lightbox data-caption="{{ trim($item->title . ($item->title && $item->caption ? ' — ' : '') . $item->caption) }}">
                            <img src="{{ $item->thumbUrl() }}" alt="{{ $item->alt() }}" width="640" height="480" loading="lazy">
                            @if ($item->title)
                                <span class="gallery__caption">{{ $item->title }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{ $items->links() }}
            @endif
        </div>
    </section>

    <x-cta-band title="Want to see us in action?" text="Talk to us about your premises and we'll show you how we can help." />
@endsection
