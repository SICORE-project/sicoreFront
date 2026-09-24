@php
    $pagination = $pagination instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $pagination->toArray() : $pagination;
    $pagination = $pagination['meta'] ?? $pagination;
    $pagination = array_merge(['current_page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 10], $pagination);
    $ajax = $ajax ?? false;
    $current = (int) $pagination['current_page'];
    $last = (int) $pagination['last_page'];
    $total = (int) $pagination['total'];
    $perPage = (int) $pagination['per_page'];
    $firstResult = $total ? ($current - 1) * $perPage + 1 : 0;
    $lastResult = min($current * $perPage, $total);
    $pages = collect([1, $last])->merge(range(max(1, $current - 2), min($last, $current + 2)))->unique()->sort()->values();
@endphp
<nav @if($ajax) data-ajax-region="pagination" @endif class="pagination app-pagination" aria-label="Pagination">
    <p class="pagination-summary">{{ $firstResult }}–{{ $lastResult }} sur {{ $total }} résultat(s)</p>
    <div class="pagination-controls">
        @if ($current > 1)
            <a @if($ajax) data-ajax-lien @endif class="page-btn page-btn-direction" href="{{ request()->fullUrlWithQuery(['page' => $current - 1]) }}" rel="prev">Précédent</a>
        @else
            <button class="page-btn page-btn-direction" type="button" disabled>Précédent</button>
        @endif
        @foreach ($pages as $number)
            @if ($loop->index > 0 && $number > $pages[$loop->index - 1] + 1)
                <span aria-hidden="true">…</span>
            @endif
            @if ($number === $current)
                <span class="page-btn active" aria-current="page" aria-label="Page {{ $number }}">{{ $number }}</span>
            @else
                <a @if($ajax) data-ajax-lien @endif class="page-btn" href="{{ request()->fullUrlWithQuery(['page' => $number]) }}" aria-label="Page {{ $number }}">{{ $number }}</a>
            @endif
        @endforeach
        @if ($current < $last)
            <a @if($ajax) data-ajax-lien @endif class="page-btn page-btn-direction" href="{{ request()->fullUrlWithQuery(['page' => $current + 1]) }}" rel="next">Suivant</a>
        @else
            <button class="page-btn page-btn-direction" type="button" disabled>Suivant</button>
        @endif
        @if ($pageSizeControl ?? false)
            <form method="GET" action="{{ route('parametres.lieux-service.index') }}" id="lieuPageSizeForm">
              @foreach ($filters as $name => $value)
                @if ($value !== null)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
              @endforeach
              <input type="hidden" name="page" value="1">
              <label class="visually-hidden" for="lieuPageSize">Nombre de lignes par page</label>
              <select class="page-size-select" id="lieuPageSize" name="per_page" aria-label="Nombre de lignes par page">
                @foreach ([10, 20, 50] as $size)<option value="{{ $size }}" @selected($pagination['per_page'] === $size)>{{ $size }}</option>@endforeach
              </select>
              <noscript><button class="btn-secondary" type="submit">Appliquer</button></noscript>
            </form>
        @endif
    </div>
</nav>
