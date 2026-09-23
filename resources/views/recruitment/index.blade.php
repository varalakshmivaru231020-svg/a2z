@extends('layouts.site')

@section('content')
    <x-page-hero title="Careers at AKS Global Maintenance" lead="Join a team that values training, teamwork and opportunity." eyebrow="Recruitment" :seo="$seo" />

    <section class="section section--tight">
        <div class="container narrow">
            <p class="lead-text">We strongly believe in staff training and development and provide innovative learning opportunities to all our team members, both at the office and in our operational units. Our culture is one of opportunity from both a professional and a personal perspective.</p>
        </div>
    </section>

    <section class="section section--soft" id="openings">
        <div class="container">
            <x-section-head eyebrow="Current openings" :title="$jobs->count() ? 'Open positions' : 'No open positions right now'" />

            @if ($jobs->isEmpty())
                <div class="empty-state">
                    <x-icon name="briefcase" :size="40" />
                    <h2>We're not hiring at the moment</h2>
                    <p>New positions are posted on this page as they open. You can also call us on {{ config('site.phone') }} to ask about upcoming vacancies.</p>
                    <a class="btn btn--primary" href="{{ route('contact') }}">Contact us</a>
                </div>
            @else
                <div class="job-list">
                    @foreach ($jobs as $job)
                        <article class="job-card">
                            <div class="job-card__main">
                                <h3><a href="{{ $job->url() }}">{{ $job->title }}</a></h3>
                                <ul class="meta">
                                    <li><x-icon name="map-pin" :size="16" /> {{ $job->location }}</li>
                                    <li><x-icon name="briefcase" :size="16" /> {{ $job->typeLabel() }}</li>
                                    @if ($job->experience)<li><x-icon name="award" :size="16" /> {{ $job->experience }}</li>@endif
                                    @if ($job->vacancies > 1)<li><x-icon name="users" :size="16" /> {{ $job->vacancies }} vacancies</li>@endif
                                    @if ($job->closing_date)<li><x-icon name="calendar" :size="16" /> Apply by {{ $job->closing_date->format('j M Y') }}</li>@endif
                                </ul>
                                <p>{{ $job->summary }}</p>
                            </div>
                            <a class="btn btn--primary" href="{{ $job->url() }}">View &amp; apply</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-cta-band title="Have a question about working with us?" text="Call or message our team — we're happy to help." />
@endsection
