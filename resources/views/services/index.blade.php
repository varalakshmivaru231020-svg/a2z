@extends('layouts.site')

@section('content')
    <x-page-hero title="Our services" lead="Facility, maintenance, government and field services — everything an establishment needs, from one dependable partner." eyebrow="What we do" :seo="$seo" />

    <section class="section">
        <div class="container">
            @if ($grouped->isEmpty())
                <div class="empty-state">
                    <x-icon name="briefcase" :size="40" />
                    <h2>Our service list is being updated</h2>
                    <p>Please check back shortly, or contact us to discuss what you need.</p>
                    <a class="btn btn--primary" href="{{ route('contact') }}">Contact us</a>
                </div>
            @else
                {{-- Filter tabs. Real links (work without JavaScript); site.js filters instantly without a reload. --}}
                <nav class="filter-tabs" id="browse" aria-label="Filter services by category">
                    <a href="{{ route('services.index') }}#browse" data-filter="" @class(['is-active' => ! $active]) @if (! $active) aria-current="true" @endif>
                        All <span>{{ $total }}</span>
                    </a>
                    @foreach ($categories as $key => $category)
                        @if ($grouped->has($key))
                            <a href="{{ route('services.index', ['category' => $key]) }}#browse" data-filter="{{ $key }}" @class(['is-active' => $active === $key]) @if ($active === $key) aria-current="true" @endif>
                                {{ $category['name'] }} <span>{{ $grouped[$key]->count() }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>
                <p class="sr-only" role="status" aria-live="polite" data-filter-status></p>

                @foreach ($categories as $key => $category)
                    @if ($grouped->has($key))
                        <section class="service-group" id="{{ $key }}" data-category="{{ $key }}" aria-labelledby="{{ $key }}-title" @if ($active && $active !== $key) hidden @endif>
                            <div class="service-group__head">
                                <span class="icon-badge icon-badge--accent"><x-icon :name="$category['icon']" :size="26" /></span>
                                <div>
                                    <h2 id="{{ $key }}-title">{{ $category['name'] }}</h2>
                                    <p>{{ $category['blurb'] }}</p>
                                </div>
                            </div>

                            <div class="grid grid--3">
                                @foreach ($grouped[$key] as $service)
                                    @include('partials.service-card', ['service' => $service])
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach

                {{-- Services filed under a category that no longer exists in config still show up (under "All") --}}
                @foreach ($grouped->except(array_keys($categories)) as $key => $orphans)
                    <section class="service-group" id="{{ $key }}" data-category="{{ $key }}" @if ($active) hidden @endif>
                        <div class="service-group__head"><div><h2>{{ ucfirst($key) }}</h2></div></div>
                        <div class="grid grid--3">
                            @foreach ($orphans as $service)
                                @include('partials.service-card', ['service' => $service])
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif
        </div>
    </section>

    <x-cta-band title="Not sure which service you need?" text="Describe your requirement and we'll recommend the right team and scope." />
@endsection
