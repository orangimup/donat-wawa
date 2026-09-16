@if ($paginator->hasPages())
    @php
        $chevron = fn ($dir) => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="'.($dir === 'left' ? '15 6 9 12 15 18' : '9 6 15 12 9 18').'"></polyline></svg>';
    @endphp

    <div class="admin-pagination-links">
        @if ($paginator->onFirstPage())
            <span class="disabled" aria-hidden="true">{!! $chevron('left') !!}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">{!! $chevron('left') !!}</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <a href="{{ $url }}" class="is-active" aria-current="page">{{ $page }}</a>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">{!! $chevron('right') !!}</a>
        @else
            <span class="disabled" aria-hidden="true">{!! $chevron('right') !!}</span>
        @endif
    </div>
@endif
