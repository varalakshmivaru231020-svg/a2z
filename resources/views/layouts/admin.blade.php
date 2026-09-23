<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · Admin · {{ config('site.brand') }}</title>
    <link rel="icon" href="{{ \App\Support\Assets::url('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin">
    @php
        $nav = [
            ['admin.dashboard', 'Dashboard', 'home', 'admin.dashboard', null],
            ['admin.services.index', 'Services', 'briefcase', 'admin.services.*', null],
            ['admin.leadership.index', 'Leadership', 'award', 'admin.leadership.*', null],
            ['admin.jobs.index', 'Job openings', 'clipboard', 'admin.jobs.*', null],
            ['admin.applications.index', 'Applications', 'users', 'admin.applications.*', $newApplications],
            ['admin.gallery.index', 'Gallery', 'image', 'admin.gallery.*', null],
            ['admin.enquiries.index', 'Enquiries', 'inbox', 'admin.enquiries.*', $unreadEnquiries],
            ['admin.seo.edit', 'Banners & SEO', 'globe', 'admin.seo.*', null],
            ['admin.settings.edit', 'Site settings', 'sliders', 'admin.settings.*', null],
        ];
    @endphp

    <aside class="sidebar" id="sidebar">
        @php($sidebarLogo = \App\Support\SiteSettings::logo('header'))
        <a class="sidebar__brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ $sidebarLogo['url'] }}" alt="" width="{{ $sidebarLogo['width'] }}" height="{{ $sidebarLogo['height'] }}">
            <span><strong>AKS Global Maintenance</strong><small>Admin panel</small></span>
        </a>

        <nav class="sidebar__nav" aria-label="Admin">
            @foreach ($nav as [$route, $label, $icon, $match, $count])
                <a href="{{ route($route) }}" @class(['sidebar__link', 'is-active' => request()->routeIs($match)])>
                    <x-icon :name="$icon" :size="19" />
                    <span>{{ $label }}</span>
                    @if ($count)<em class="sidebar__badge">{{ $count }}</em>@endif
                </a>
            @endforeach
        </nav>

        <div class="sidebar__foot">
            <a class="sidebar__link" href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="external" :size="19" /><span>View website</span></a>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="sidebar__link sidebar__logout" type="submit"><x-icon name="log-out" :size="19" /><span>Log out</span></button>
            </form>
            <p class="sidebar__user">{{ auth()->user()->email }}</p>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <button class="admin-top__menu" type="button" aria-controls="sidebar" aria-expanded="false" data-sidebar-toggle>
                <span class="sr-only">Menu</span><x-icon name="menu" :size="24" />
            </button>
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="admin-top__actions">@yield('actions')</div>
        </header>

        <div class="admin-content">
            @if (session('status'))
                <div class="flash" role="status"><x-icon name="check-circle" :size="20" /> {{ session('status') }}</div>
            @endif
            @if ($errors->any() && ! isset($hideErrorSummary))
                <div class="flash flash--error" role="alert">
                    <x-icon name="alert" :size="20" />
                    <div>
                        <strong>Please fix the following:</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="{{ asset('js/admin.js') }}?v={{ @filemtime(public_path('js/admin.js')) }}" defer></script>
</body>
</html>
