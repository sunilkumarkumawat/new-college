@if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->hasPages())
    <nav class="credential-pagination" aria-label="Page navigation">
        <ul class="pagination pagination-sm m-0">
            {{-- Previous Page Link --}}
            @if ($data->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="fa fa-angle-left"></i> Prev</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $data->previousPageUrl() }}" data-page="{{ $data->currentPage() - 1 }}" rel="prev"><i class="fa fa-angle-left"></i> Prev</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @php
                $currentPage = $data->currentPage();
                $lastPage = $data->lastPage();
                $start = max(1, $currentPage - 2);
                $end = min($lastPage, $currentPage + 2);
            @endphp

            @if($start > 1)
                <li class="page-item"><a class="page-link" href="{{ $data->url(1) }}" data-page="1">1</a></li>
                @if($start > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $data->url($page) }}" data-page="{{ $page }}">{{ $page }}</a></li>
                @endif
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="{{ $data->url($lastPage) }}" data-page="{{ $lastPage }}">{{ $lastPage }}</a></li>
            @endif

            {{-- Next Page Link --}}
            @if ($data->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $data->nextPageUrl() }}" data-page="{{ $data->currentPage() + 1 }}" rel="next">Next <i class="fa fa-angle-right"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">Next <i class="fa fa-angle-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
