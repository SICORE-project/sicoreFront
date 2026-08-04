@props([
    'title',
    'subtitle' => null,
    'icon' => 'fa-solid fa-circle',
    'searchId' => null,
    'searchPlaceholder' => 'Rechercher…',
    'filterTarget' => null,
])

<header class="topbar">
  <div class="page-title-wrap">
    <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">
      <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>
    <span class="title-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
    <div>
      <h1>{{ $title }}</h1>
      @if ($subtitle)
        <p>{{ $subtitle }}</p>
      @endif
    </div>
  </div>

  @if ($searchId)
    <div class="search-wrap">
      <label class="sr-only" for="{{ $searchId }}">Rechercher</label>
      <input
        class="search-input"
        id="{{ $searchId }}"
        type="search"
        placeholder="{{ $searchPlaceholder }}"
        @if ($filterTarget) data-table-filter="{{ $filterTarget }}" @endif
      >
    </div>
  @endif

  {{ $slot }}
</header>
