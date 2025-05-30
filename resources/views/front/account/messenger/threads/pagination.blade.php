@php
    $apiResult ??= [];
	$isPageable = (!empty(data_get($apiResult, 'links.prev')) || !empty(data_get($apiResult, 'links.next')));
	$paginator = data_get($apiResult, 'links');
@endphp
@if ($isPageable)
    <div class="btn-group btn-group-sm">
        {{-- Previous Page Link --}}
        @if (!data_get($apiResult, 'links.prev'))
            <button type="button" class="btn btn-secondary disabled" aria-disabled="true">
                <span class="fa-solid fa-arrow-left"></span>
            </button>
        @else
            <a class="btn btn-secondary" href="{{ data_get($paginator, 'prev') }}" rel="prev">
                <span class="fa-solid fa-arrow-left"></span>
            </a>
        @endif
    
        {{-- Next Page Link --}}
        @if (data_get($paginator, 'next'))
            <a class="btn btn-secondary" href="{{ data_get($paginator, 'next') }}" rel="next">
                <span class="fa-solid fa-arrow-right"></span>
            </a>
        @else
            <button type="button" class="btn btn-secondary disabled" aria-disabled="true">
                <span class="fa-solid fa-arrow-right"></span>
            </button>
        @endif
    </div>
@endif
