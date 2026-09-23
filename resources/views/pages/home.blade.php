@extends('layouts.site')

@section('content')
    {{-- Hero --}}
    <section class="hero">
        <div class="container hero__inner">
            <div class="hero__copy">
                <p class="eyebrow">Facility management · Manpower · Field services</p>
                <h1>One-stop <span class="accent">facility management</span> services in Bangalore &amp; Kochi</h1>
                <p class="hero__lead">{{ ucfirst(config('site.tagline')) }} — housekeeping, maintenance, government projects and field staffing, all under one roof. {{ config('site.slogan') }}</p>
                <div class="hero__actions">
                    <a class="btn btn--primary btn--lg" href="{{ route('services.index') }}">Explore our services <x-icon name="arrow-right" :size="20" /></a>
                    <a class="btn btn--accent btn--lg" href="#enquiry">Enquire now</a>
                </div>
                <ul class="hero__points">
                    <li><x-icon name="check" :size="18" /> Trained &amp; supervised teams</li>
                    <li><x-icon name="check" :size="18" /> Bangalore &amp; Kochi</li>
                    <li><x-icon name="check" :size="18" /> Quality, consistency &amp; security</li>
                </ul>
            </div>

            <div class="hero__media">
                <img src="{{ asset('img/office.jpg') }}" alt="Bright, tidy open-plan office maintained by AKS Global Maintenance facility services" width="991" height="551" fetchpriority="high">
                <div class="hero__badge">
                    @php($badgeLogo = \App\Support\SiteSettings::logo('header'))
                    <img src="{{ $badgeLogo['url'] }}" alt="" width="{{ $badgeLogo['width'] }}" height="{{ $badgeLogo['height'] }}">
                    <div>
                        <strong>Since {{ config('site.founded') }}</strong>
                        <span>Customer care you can count on</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Numbers --}}
    <section class="stats" aria-label="AKS Global Maintenance at a glance">
        <div class="container stats__grid">
            <div class="stat"><strong>{{ config('site.founded') }}</strong><span>Established</span></div>
            <div class="stat"><strong>{{ count(config('site.offices')) }}</strong><span>Offices in Bangalore &amp; Kochi</span></div>
            <div class="stat"><strong>{{ $services->count() }}</strong><span>Services under one roof</span></div>
            <div class="stat"><strong>7+ yrs</strong><span>Leadership experience</span></div>
        </div>
    </section>

    {{-- Clients: names glide across a full-width strip --}}
    <section class="section section--tight clients-section">
        <div class="container">
            <x-section-head eyebrow="Our clients" title="Trusted by organisations across Bangalore" />
        </div>
        @include('partials.clients')
    </section>

    {{-- Service highlights --}}
    <section class="section">
        <div class="container">
            <x-section-head eyebrow="What we do" title="Services for every need" lead="From daily housekeeping to technical maintenance, government water and sewerage projects, banking support and field marketing." />

            @if ($featured->isNotEmpty())
                <div class="grid grid--4">
                    @foreach ($featured as $service)
                        <a class="service-card" href="{{ $service->url() }}">
                            <span class="icon-badge"><x-icon :name="$service->icon" :size="26" /></span>
                            <h3>{{ $service->title }}</h3>
                            <p>{{ $service->summary }}</p>
                            <span class="service-card__more">Learn more <x-icon name="arrow-right" :size="16" /></span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="empty">Our service list is being updated — please check back shortly.</p>
            @endif

            <div class="section__actions">
                <a class="btn btn--primary" href="{{ route('services.index') }}">View all services</a>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <section class="section section--soft">
        <div class="container">
            <x-section-head eyebrow="Explore by category" title="Find the right service faster" />
            <div class="grid grid--4">
                @foreach ($categories as $key => $category)
                    <a class="category-card" href="{{ route('services.index', ['category' => $key]) }}#browse">
                        <span class="icon-badge icon-badge--accent"><x-icon :name="$category['icon']" :size="26" /></span>
                        <h3>{{ $category['name'] }}</h3>
                        <p>{{ $category['blurb'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Why us + about teaser --}}
    <section class="section">
        <div class="container split">
            <div class="split__media">
                <img src="{{ asset('img/crew.jpg') }}" alt="Illustration of a facility team planning a project around a table" width="599" height="681" loading="lazy">
            </div>
            <div class="split__copy">
                <p class="eyebrow">Why AKS Global Maintenance</p>
                <h2>A customer-care company that stays with you</h2>
                <p>Established in {{ config('site.founded') }}, we provide for the requirements of establishments in and around Bangalore. Our best practice covers the complete requirement of the customer — and we sincerely believe that customer satisfaction is what brings you back again.</p>
                <ul class="feature-list">
                    <li><x-icon name="check-circle" :size="22" /><div><strong>One-stop solutions</strong><span>Facility, maintenance, government and field services from a single partner.</span></div></li>
                    <li><x-icon name="check-circle" :size="22" /><div><strong>Trained, supported teams</strong><span>We invest in staff training and development, in the office and on site.</span></div></li>
                    <li><x-icon name="check-circle" :size="22" /><div><strong>Quality, consistency &amp; security</strong><span>The reputation we are building — one dependable visit at a time.</span></div></li>
                </ul>
                <a class="btn btn--primary" href="{{ route('about') }}">More about us</a>
            </div>
        </div>
    </section>

    {{-- Gallery preview --}}
    @if ($gallery->isNotEmpty())
        <section class="section">
            <div class="container">
                <x-section-head eyebrow="Gallery" title="Our work and team" />
                <div class="gallery gallery--preview">
                    @foreach ($gallery as $item)
                        <a class="gallery__item" href="{{ route('gallery') }}">
                            <img src="{{ $item->thumbUrl() }}" alt="{{ $item->alt() }}" width="640" height="480" loading="lazy">
                        </a>
                    @endforeach
                </div>
                <div class="section__actions">
                    <a class="btn btn--outline" href="{{ route('gallery') }}">See the full gallery</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Enquiry call to action --}}
    <section class="section" id="get-in-touch">
        <div class="container split split--top">
            <div class="split__copy">
                <p class="eyebrow">Enquiry</p>
                <h2>Tell us what you need</h2>
                <p>Share a few details and our team will get back to you. Prefer to talk? Call us directly.</p>
                <ul class="contact-quick">
                    <li><x-icon name="phone" :size="22" /><div><span>Call us</span><a href="tel:{{ config('site.phone_link') }}">{{ config('site.phone') }}</a></div></li>
                    <li><x-icon name="message" :size="22" /><div><span>WhatsApp</span><a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener">Chat with us</a></div></li>
                    <li><x-icon name="mail" :size="22" /><div><span>Email</span><a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></div></li>
                </ul>
            </div>
            <x-enquiry-form />
        </div>
    </section>
@endsection
