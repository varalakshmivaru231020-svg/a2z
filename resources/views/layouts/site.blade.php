<!DOCTYPE html>
<html lang="en-IN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b57a8">

    @include('partials.seo')

    <link rel="icon" href="{{ \App\Support\Assets::url('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ \App\Support\Assets::url('img/icon-192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ \App\Support\Assets::url('img/apple-touch-icon.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ @filemtime(public_path('css/site.css')) }}">
</head>
<body>
    <a class="skip-link" href="#main">Skip to main content</a>

    @include('partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/site.js') }}?v={{ @filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
