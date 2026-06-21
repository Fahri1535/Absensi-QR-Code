@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn pagination-btn--nav disabled" aria-disabled="true">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn pagination-btn--nav" rel="prev">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Nomor Halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-btn pagination-btn--dots disabled" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn pagination-btn--nav" rel="next">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="pagination-btn pagination-btn--nav disabled" aria-disabled="true">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
