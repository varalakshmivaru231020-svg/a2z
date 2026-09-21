@php
    // Pages that forget to pass $seo (e.g. framework error pages) are kept out of search results.
    $seo ??= \App\Support\Seo::make(config('site.name'), config('seo.pages.home.description'), url()->current())->noindex();
@endphp
<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->metaDescription() }}">
<meta name="robots" content="{{ $seo->robots() }}">
<link rel="canonical" href="{{ $seo->canonical }}">

<meta property="og:locale" content="{{ config('seo.locale') }}">
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:site_name" content="{{ config('site.name') }}">
<meta property="og:title" content="{{ $seo->fullTitle() }}">
<meta property="og:description" content="{{ $seo->metaDescription() }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:image" content="{{ $seo->image }}">
@if ($seo->imageAlt)
    <meta property="og:image:alt" content="{{ $seo->imageAlt }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->metaDescription() }}">
<meta name="twitter:image" content="{{ $seo->image }}">

@foreach ($seo->jsonLd() as $block)
    <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
