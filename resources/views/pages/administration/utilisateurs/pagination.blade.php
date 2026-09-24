@php
    $current = (int) $pagination['current_page'];
    $last = (int) $pagination['last_page'];
    $total = (int) $pagination['total'];
    $perPage = (int) $pagination['per_page'];
    $firstResult = $total ? ($current - 1) * $perPage + 1 : 0;
    $lastResult = min($current * $perPage, $total);
    $pages = collect([1, $last])->merge(range(max(1, $current - 2), min($last, $current + 2)))->unique()->sort()->values();
@endphp
<nav class="pagination users-pagination" aria-label="Pagination des utilisateurs">
    <p class="pagination-summary">{{ $firstResult }}–{{ $lastResult }} sur {{ $total }} utilisateur(s)</p>
    <div class="pagination-controls">
        @if ($current > 1)
            <a class="page-btn users-page-direction" href="{{ request()->fullUrlWithQuery(['page' => $current - 1]) }}" rel="prev">Précédent</a>
        @else
            <button class="page-btn users-page-direction" type="button" disabled>Précédent</button>
        @endif
        @foreach ($pages as $number)
            @if ($loop->index > 0 && $number > $pages[$loop->index - 1] + 1)
                <span aria-hidden="true">…</span>
            @endif
            @if ($number === $current)
                <span class="page-btn active" aria-current="page" aria-label="Page {{ $number }}">{{ $number }}</span>
            @else
                <a class="page-btn" href="{{ request()->fullUrlWithQuery(['page' => $number]) }}" aria-label="Page {{ $number }}">{{ $number }}</a>
            @endif
        @endforeach
        @if ($current < $last)
            <a class="page-btn users-page-direction" href="{{ request()->fullUrlWithQuery(['page' => $current + 1]) }}" rel="next">Suivant</a>
        @else
            <button class="page-btn users-page-direction" type="button" disabled>Suivant</button>
        @endif
    </div>
</nav>
@once
@push('styles')
<style>
    .users-pagination { flex-wrap: wrap; border-radius: 0 0 16px 16px; }
    .users-pagination .pagination-controls { flex-wrap: wrap; }
    .users-pagination .page-btn { text-decoration: none; }
    .users-pagination .users-page-direction { padding: 0 14px; border: 1px solid #e2e8f0; border-radius: 10px; color: #334155; font: inherit; font-size: 13px; font-weight: 700; }
    .users-pagination .page-btn.active { color: #176637; background: #e9f7ee; cursor: default; }
</style>
@endpush
@endonce