{{-- Full-width ticker: client names glide across, one after another. Text only. --}}
@php
    $star = '<svg class="marquee__star" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 0c.6 6.6 5.4 11.4 12 12-6.6.6-11.4 5.4-12 12-.6-6.6-5.4-11.4-12-12C6.6 11.4 11.4 6.6 12 0z"/></svg>';
@endphp
<div class="marquee" role="region" aria-label="Our clients">
    <div class="marquee__track">
        {{-- The list a screen reader reads --}}
        <ul class="marquee__group">
            @foreach (config('site.clients') as $client)
                <li>{{ $client }}</li>
                <li class="marquee__sep" aria-hidden="true">{!! $star !!}</li>
            @endforeach
        </ul>
        {{-- An identical copy so the loop is seamless (hidden from assistive tech) --}}
        <ul class="marquee__group" aria-hidden="true">
            @foreach (config('site.clients') as $client)
                <li>{{ $client }}</li>
                <li class="marquee__sep">{!! $star !!}</li>
            @endforeach
        </ul>
    </div>
</div>
