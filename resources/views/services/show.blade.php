@extends('layouts.site')

@section('content')
    <x-page-hero :title="$service->title" :lead="$service->summary" :eyebrow="$service->categoryName()" :seo="$seo" />

    <section class="section">
        <div class="container detail">
            <article class="detail__main">
                @if ($service->imageUrl())
                    <img class="detail__image" src="{{ $service->imageUrl() }}" alt="{{ $service->title }} by {{ config('site.brand') }}" width="1400" height="788" fetchpriority="high">
                @endif

                <h2>About this service</h2>
                @forelse ($service->paragraphs() as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @empty
                    <p>{{ $service->summary }}</p>
                @endforelse

                @if (! empty($service->features))
                    <h2>What's included</h2>
                    <ul class="check-list">
                        @foreach ($service->features as $feature)
                            <li><x-icon name="check-circle" :size="22" /> <span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </article>

            <aside class="detail__side">
                <div class="card card--pad side-card">
                    <h2>Need {{ $service->title }}?</h2>
                    <p>Tell us about your requirement and we'll get back to you.</p>
                    <a class="btn btn--primary btn--block" href="{{ route('contact', ['service' => $service->slug]) }}#enquiry">Send an enquiry</a>
                    <a class="btn btn--outline btn--block" href="tel:{{ config('site.phone_link') }}"><x-icon name="phone" :size="18" /> {{ config('site.phone') }}</a>
                    <a class="btn btn--whatsapp btn--block" href="https://wa.me/{{ config('site.whatsapp') }}?text={{ rawurlencode('Hello, I would like to enquire about ' . $service->title . '.') }}" target="_blank" rel="noopener"><x-icon name="message" :size="18" /> WhatsApp us</a>
                </div>

                @if ($related->isNotEmpty())
                    <div class="card card--pad side-card">
                        <h2>More in {{ $service->categoryName() }}</h2>
                        <ul class="side-list">
                            @foreach ($related as $item)
                                <li><a href="{{ $item->url() }}"><x-icon :name="$item->icon" :size="18" /> {{ $item->title }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <x-cta-band title="Ready to get started?" :text="'Speak to our team about ' . $service->title . ' for your premises.'" />
@endsection
