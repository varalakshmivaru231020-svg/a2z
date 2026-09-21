@extends('layouts.site')

@section('content')
    <x-page-hero title="About A2Z Global Maintenance" lead="A one-stop facility management and manpower company that cares about your needs." eyebrow="About us" :seo="$seo" />

    {{-- Company profile --}}
    <section class="section">
        <div class="container split">
            <div class="split__copy">
                <p class="eyebrow">Our journey</p>
                <h2>Welcome to one-stop solutions</h2>
                <p>{{ config('site.name') }} was established in {{ config('site.founded') }} as a customer care company, able to provide for the requirements of establishments in and around Bangalore and nearby areas. Our best practice covers the complete requirement of the customer, and we sincerely believe that customer satisfaction is what makes you come back again.</p>
                <p>Today we look after facility management, maintenance and renovation, government water and sewerage projects, and financial and field services — from our registered office in Halasuru, our branch in Horamavu, Bangalore, and our branch in Edappally, Kochi.</p>
                @if (filled(config('site.slogan')))
                    <p class="quote">{{ config('site.slogan') }}</p>
                @endif
            </div>
            <div class="split__media">
                <img src="{{ asset('img/office.jpg') }}" alt="A bright open-plan office of the kind A2Z Global Maintenance keeps running smoothly" width="991" height="551" loading="lazy">
            </div>
        </div>
    </section>

    {{-- Vision & mission: photo background, frosted-glass cards, button --}}
    <section class="section">
        <div class="container">
            <div class="vm-section">
                <h2 class="vm-section__title">Our vision and mission</h2>

                <div class="vm-section__grid">
                    <article class="vm-card">
                        <h3>Our vision</h3>
                        <p>To build a reputation for quality, consistency and security that makes A2Z Global Maintenance the leader of a new style in living — a trusted, comprehensive care house for every establishment we serve.</p>
                    </article>
                    <article class="vm-card">
                        <h3>Our mission</h3>
                        <p>To deliver dependable facility, maintenance, manpower and field services through trained, well-supported people — acting with responsibility and conviction, and putting customer satisfaction first, every time.</p>
                    </article>
                </div>

                <div class="vm-section__action">
                    <a class="btn btn--white btn--lg btn--caps" href="{{ route('services.index') }}">Explore our services</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Market outlook --}}
    <section class="section">
        <div class="container narrow">
            <x-section-head eyebrow="Why facility management, why now" title="Growing with Bengaluru" align="left" />
            <p>With the IT and software industry booming in India, more and more MNCs and companies are establishing their units in Bengaluru, often called the Modern Silicon Valley of India. Facility management is one of the largest service industries in the country, and demand is expected to keep growing — giving ample scope for consistent business growth.</p>
            <p>A2Z Global Maintenance has ambitious plans to take advantage of these emerging needs, and to act with responsibility and conviction as we grow into a comprehensive care house.</p>
        </div>
    </section>

    {{-- Crew culture --}}
    <section class="section section--blue">
        <div class="container split split--reverse">
            <div class="split__copy">
                <p class="eyebrow eyebrow--light">Our crew</p>
                <h2>People who are trained, and trusted</h2>
                <p>We strongly believe in staff training and development, and provide innovative learning opportunities to all our team members — both at the office and in our operational units.</p>
                <p>We also maintain a strong teamwork ethic throughout the organisation. Our culture is one of opportunity, from both a professional and a personal perspective.</p>
                <a class="btn btn--accent btn--lg" href="{{ route('recruitment.index') }}">Join our crew</a>
            </div>
            <div class="split__media">
                <img src="{{ asset('img/crew.jpg') }}" alt="Illustration of a facility team collaborating on a project plan" width="599" height="681" loading="lazy">
            </div>
        </div>
    </section>

    {{-- Leadership --}}
    <section class="section">
        <div class="container">
            <x-section-head eyebrow="Leadership" title="The people behind A2Z Global Maintenance" />

            @php($leaders = collect(config('site.leadership')))
            @php($lead = $leaders->first())
            <article class="leader">
                @if (! empty($lead['photo']))
                    <img src="{{ asset($lead['photo']) }}" alt="{{ $lead['name'] }}, {{ $lead['role'] }} of {{ config('site.name') }}" width="900" height="1020" loading="lazy">
                @endif
                <div>
                    <p class="eyebrow">{{ $lead['role'] }}</p>
                    <h3>{{ $lead['name'] }}</h3>
                    <p>{{ $lead['bio'] }}</p>
                    <p>He is the anchorman and Managing Director of {{ config('site.name') }}, and draws his strength from the overwhelming support of the team.</p>
                </div>
            </article>

            <div class="grid grid--3 team">
                @foreach ($leaders->slice(1) as $member)
                    <article class="card card--pad team__member">
                        <span class="avatar" aria-hidden="true">{{ Str::of($member['name'])->replaceMatches('/^(Mr|Mrs|Ms|Dr)\.?\s+/', '')->substr(0, 1) }}</span>
                        <h3>{{ $member['name'] }}</h3>
                        <p class="team__role">{{ $member['role'] }}</p>
                        <p>{{ $member['bio'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Clients --}}
    <section class="section section--tight clients-section">
        <div class="container">
            <x-section-head eyebrow="Our clients" title="Organisations that trust us" />
        </div>
        @include('partials.clients')
    </section>

    <x-cta-band title="Let's talk about what you need" text="Tell us about your premises and requirements — we'll suggest the right services." />
@endsection
