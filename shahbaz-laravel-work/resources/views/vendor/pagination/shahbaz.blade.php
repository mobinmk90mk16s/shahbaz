@if ($paginator->hasPages())
    @php
        $rangeStart = max($paginator->currentPage() - 2, 1);
        $rangeEnd = min($rangeStart + 4, $paginator->lastPage());
        $rangeStart = max($rangeEnd - 4, 1);
    @endphp
    <nav class="shahbaz-pagination" role="navigation" aria-label="صفحه‌بندی نتایج">
        @if ($paginator->onFirstPage())
            <span class="pagination-link is-disabled" aria-disabled="true">قبلی</span>
        @else
            <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">قبلی</a>
        @endif

        <div class="pagination-pages">
            @for ($page = $rangeStart; $page <= $rangeEnd; $page++)
                @if ($page === $paginator->currentPage())
                    <span class="pagination-link is-current" aria-current="page">{{ $page }}</span>
                @else
                    <a class="pagination-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                @endif
            @endfor
        </div>

        @if ($paginator->hasMorePages())
            <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">بعدی</a>
        @else
            <span class="pagination-link is-disabled" aria-disabled="true">بعدی</span>
        @endif
    </nav>
@endif
