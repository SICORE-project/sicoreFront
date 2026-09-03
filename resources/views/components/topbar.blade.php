{{--
  COMPOSANT D'EN-TÊTE DE PAGE
  Appel : <x-topbar title="..." subtitle="..." icon="..." />.
  La recherche facultative utilise app.js via data-table-filter.
  Le contenu entre les balises du composant arrive dans $slot.
--}}
@props([
    'title',
    'subtitle' => null,
    'icon' => 'fa-solid fa-circle',
    'searchId' => null,
    'searchPlaceholder' => 'Rechercher…',
    'filterTarget' => null,
])

<header class="topbar">
  {{-- Bloc gauche : bouton mobile, icône, titre et fil d'Ariane. --}}
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
  
  {{-- Cloche de notification --}}
  @if(session()->has('sicore_user'))
    <x-notification-bell />
  @endif

  @if ($searchId)
    {{-- Recherche affichée uniquement lorsqu'un searchId est fourni. --}}
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

  {{-- Zone facultative pour les boutons propres à la page. --}}
  {{ $slot }}
</header>
