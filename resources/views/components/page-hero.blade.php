@props(['title', 'lead' => null, 'eyebrow' => null, 'seo' => null])
<section @class(['page-hero', 'has-banner' => $seo?->banner]) @if ($seo?->banner) style="--hero-image: url('{{ $seo->banner }}')" @endif>
    <div class="container">
        @if ($seo && count($seo->breadcrumbs()) > 1)
            <nav class="crumbs" aria-label="Breadcrumb">
                <ol>
                    @foreach ($seo->breadcrumbs() as [$name, $url])
                        <li>
                            @if (! $loop->last && $url)
                                <a href="{{ $url }}">{{ $name }}</a>
                            @else
                                <span aria-current="page">{{ $name }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        @if ($eyebrow)
            <p class="eyebrow eyebrow--light">{{ $eyebrow }}</p>
        @endif
        <h1>{{ $title }}</h1>
        @if ($lead)
            <p class="page-hero__lead">{{ $lead }}</p>
        @endif

        {{ $slot }}
    </div>
</section>
