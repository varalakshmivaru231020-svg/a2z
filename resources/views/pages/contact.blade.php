@extends('layouts.site')

@section('content')
    <x-page-hero title="Contact us" lead="Call, message or visit — we'd love to hear what you need." eyebrow="Get in touch" :seo="$seo" />

    {{-- 1. How to reach us + the enquiry form --}}
    <section class="section">
        <div class="container contact-grid">
            <div class="contact-info">
                <h2>How to reach us</h2>
                <p class="contact-info__lead">Tell us about your premises and what you need — call, message us on WhatsApp or send an enquiry and our team will get back to you.</p>
                <ul class="contact-quick">
                    <li><x-icon name="phone" :size="22" /><div><span>Phone</span><a href="tel:{{ config('site.phone_link') }}">{{ config('site.phone') }}</a></div></li>
                    <li><x-icon name="message" :size="22" /><div><span>WhatsApp</span><a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener">Message us on WhatsApp</a></div></li>
                    <li><x-icon name="mail" :size="22" /><div><span>Email</span><a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></div></li>
                </ul>
            </div>

            <div>
                <h2>Send us an enquiry</h2>
                <x-enquiry-form :prefill="$prefillSubject" />
            </div>
        </div>
    </section>

    {{-- 2. Our offices: three cards in a row, below the enquiry form, with the map they control --}}
    <section class="section section--soft" id="offices">
        <div class="container">
            <x-section-head eyebrow="Find us" title="Our offices" lead="Visit us in Bangalore or Kochi. Choose an office to see it on the map." />

            <div class="offices">
                @foreach ($offices as $office)
                    <article class="office @if ($loop->first) is-active @endif" data-office data-map-query="{{ $office['map'] }}">
                        <span class="icon-badge"><x-icon name="map-pin" :size="24" /></span>
                        <h3>{{ $office['label'] }}</h3>
                        <address>{{ $office['street'] }}, {{ $office['locality'] }}, {{ $office['region'] }} {{ $office['postal'] }}</address>
                        <button class="link-btn" type="button" data-show-map>Show on map <x-icon name="arrow-right" :size="14" /></button>
                    </article>
                @endforeach
            </div>

            @php($first = $offices[0])
            <div class="map">
                <iframe
                    id="office-map"
                    title="Map of {{ config('site.name') }} offices"
                    src="https://www.google.com/maps?q={{ urlencode($first['map']) }}&amp;output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
            </div>
            <p class="map__link"><a id="office-map-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($first['map']) }}" target="_blank" rel="noopener">Open in Google Maps <x-icon name="external" :size="14" /></a></p>
        </div>
    </section>
@endsection
