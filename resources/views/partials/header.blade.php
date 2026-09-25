@php
    $announcement = config('site.announcement');
    $showAnnouncement = $announcement['enabled'] && filled($announcement['text']) && (! $announcement['home_only'] || request()->routeIs('home'));
@endphp
{{-- Without the bar the sticky header must not reserve room for it. --}}
<header class="site-header" @unless ($showAnnouncement)style="--topbar-h: 0px"@endunless>
    @if ($showAnnouncement)
        <div class="topbar">
            <div class="container topbar__inner">
                <p>{{ $announcement['text'] }}</p>
            </div>
        </div>
    @endif

    <div class="navbar">
        <div class="container navbar__inner">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ config('site.name') }} – home">
                @php($logo = \App\Support\SiteSettings::logo('header'))
                <img src="{{ $logo['url'] }}" alt="" width="{{ $logo['width'] }}" height="{{ $logo['height'] }}" class="brand__logo">
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
                <span class="sr-only">Menu</span>
                <x-icon name="menu" :size="26" class="nav-toggle__open" />
                <x-icon name="x" :size="26" class="nav-toggle__close" />
            </button>

            <nav id="primary-nav" class="nav" aria-label="Primary">
                <ul>
                    @foreach (config('site.nav') as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['match'])]) @if (request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
                <a class="btn btn--accent nav__cta" href="{{ route('contact') }}#enquiry">Enquire Now</a>
            </nav>
        </div>
    </div>
</header>
