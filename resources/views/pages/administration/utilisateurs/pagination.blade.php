@php
  $pagination = $page['pagination'] ?? [];
  $current = (int) ($pagination['current_page'] ?? 1);
  $last = max(1, (int) ($pagination['last_page'] ?? 1));
  $perPage = (int) ($pagination['per_page'] ?? 10);
  $directions = [
    ['page' => 1, 'label' => 'Première page', 'icon' => 'angles-left', 'disabled' => $current <= 1],
    ['page' => $current - 1, 'label' => 'Page précédente', 'icon' => 'angle-left', 'disabled' => $current <= 1],
    ['page' => $current + 1, 'label' => 'Page suivante', 'icon' => 'angle-right', 'disabled' => $current >= $last],
    ['page' => $last, 'label' => 'Dernière page', 'icon' => 'angles-right', 'disabled' => $current >= $last],
  ];
@endphp
<nav class="pagination" aria-label="Pagination des utilisateurs">
  <p class="pagination-summary">{{ $pagination['total'] ?? 0 }} utilisateur(s)</p>
  <div class="pagination-controls">
    @foreach ($directions as $direction)
      @if ($loop->index === 2)
        <span class="page-btn page-number active" aria-current="page" aria-label="Page {{ $current }} sur {{ $last }}">{{ $current }}</span>
      @endif
      @if ($direction['disabled'])
        <button class="page-btn page-btn-direction" type="button" disabled aria-label="{{ $direction['label'] }}"><i class="fa-solid fa-{{ $direction['icon'] }}" aria-hidden="true"></i></button>
      @else
        <a class="page-btn page-btn-direction" aria-label="{{ $direction['label'] }}" href="{{ route('utilisateurs.index', array_merge(request()->except('page'), ['page' => $direction['page']])) }}"><i class="fa-solid fa-{{ $direction['icon'] }}" aria-hidden="true"></i></a>
      @endif
    @endforeach
    <label class="visually-hidden" for="utilisateurs-page-size">Nombre de lignes par page</label>
    <select class="page-size-select" id="utilisateurs-page-size" name="per_page" form="users-filter-form" aria-label="Nombre de lignes par page">
      @foreach ([10, 20, 50] as $size)
        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
      @endforeach
    </select>
  </div>
</nav>
