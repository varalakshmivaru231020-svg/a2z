@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pagination__item is-disabled" aria-disabled="true">&laquo; Previous</span>
        @else
            <a class="pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Previous</a>
        @endif

        @if (isset($elements))
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination__item is-disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination__item is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination__item" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endif

        @if ($paginator->hasMorePages())
            <a class="pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
        @else
            <span class="pagination__item is-disabled" aria-disabled="true">Next &raquo;</span>
        @endif
    </nav>
@endif
