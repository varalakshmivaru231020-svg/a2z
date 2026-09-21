<footer class="site-footer">
    <div class="container footer__grid">
        <div class="footer__brand">
            @php($logo = \App\Support\SiteSettings::logo('footer'))
            <a @class(['footer__logo', 'footer__logo--badge' => $logo['badge']]) href="{{ route('home') }}" aria-label="{{ config('site.name') }} – home">
                <img src="{{ $logo['url'] }}" alt="{{ config('site.name') }} logo" width="{{ $logo['width'] }}" height="{{ $logo['height'] }}" loading="lazy">
            </a>
            <p class="footer__name">{{ config('site.name') }}</p>
            <p class="footer__tagline">{{ config('site.tagline') }}</p>
            <p>{{ config('site.footer_text') }}</p>
            @if (filled(config('site.slogan')))
                <p class="footer__slogan">{{ config('site.slogan') }}</p>
            @endif
        </div>

        <nav aria-label="Footer">
            <h2 class="footer__title">Quick links</h2>
            <ul class="footer__list">
                @foreach (config('site.nav') as $item)
                    <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div>
            <h2 class="footer__title">Our services</h2>
            <ul class="footer__list">
                @foreach (config('site.service_categories') as $key => $category)
                    <li><a href="{{ route('services.index', ['category' => $key]) }}#browse">{{ $category['name'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="footer__title">Contact us</h2>
            <ul class="footer__contact">
                <li><x-icon name="phone" :size="18" /><a href="tel:{{ config('site.phone_link') }}">{{ config('site.phone') }}</a></li>
                <li><x-icon name="mail" :size="18" /><a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></li>
                @foreach (config('site.offices') as $office)
                    <li>
                        <x-icon name="map-pin" :size="18" />
                        <address>
                            <strong>{{ $office['label'] }}</strong><br>
                            {{ $office['street'] }}, {{ $office['locality'] }} {{ $office['postal'] }}
                        </address>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="footer__bar">
        <div class="container footer__bar-inner">
            <p>&copy; {{ date('Y') }} {{ config('site.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>

{{-- Sticky call / WhatsApp / enquire bar for phones --}}
<div class="mobile-bar" role="navigation" aria-label="Quick contact">
    <a href="tel:{{ config('site.phone_link') }}"><x-icon name="phone" :size="20" /> Call</a>
    <a href="https://wa.me/{{ config('site.whatsapp') }}?text={{ rawurlencode('Hello ' . config('site.brand') . ', I would like to enquire about your services.') }}" target="_blank" rel="noopener"><x-icon name="message" :size="20" /> WhatsApp</a>
    <a href="{{ route('contact') }}#enquiry"><x-icon name="mail" :size="20" /> Enquire</a>
</div>
