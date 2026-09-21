@if ($paginator->hasPages())
    @php
        $chevron = fn ($dir) => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="'.($dir === 'left' ? '15 6 9 12 15 18' : '9 6 15 12 9 18').'"></polyline></svg>';
    @endphp

    {{-- Prev --}}
    @if ($paginator->onFirstPage())
        <span class="disabled" aria-hidden="true">{!! $chevron('left') !!}</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}">{!! $chevron('left') !!}</a>
    @endif

    {{-- Page numbers --}}
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

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}">{!! $chevron('right') !!}</a>
    @else
        <span class="disabled" aria-hidden="true">{!! $chevron('right') !!}</span>
    @endif
@endif