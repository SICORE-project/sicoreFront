@php
    $navigation = config('navigation', []);
    $user = session('sicore_user', []);
    $userName = data_get($user, 'name', data_get($user, 'email', 'Utilisateur SICORE'));
    $userRole = data_get($user, 'role', data_get($user, 'roles.0.name', 'Utilisateur'));
@endphp

<aside class="sidebar" id="sidebar" aria-label="Menu principal SICORE">
  <div class="sidebar-header">
    <a class="sidebar-logo" href="{{ route('dashboard') }}" data-tooltip="SICORE" title="SICORE" aria-label="Accueil SICORE">
      <span class="sidebar-logo-mark">
        <img src="{{ asset('assets/images/image-fcfa.png') }}" alt="Logo SICORE">
      </span>
      <span class="sidebar-logo-text">SICORE</span>
    </a>
  </div>

  <nav class="sidebar-nav" aria-label="Navigation principale">
    <div class="sidebar-section">
      @foreach ($navigation as $item)
        @php
            $patterns = (array) ($item['active'] ?? $item['route'] ?? []);
            $groupIsActive = collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
        @endphp

        @if (($item['type'] ?? 'link') === 'link')
          <a
            class="sidebar-link {{ $groupIsActive ? 'active' : '' }}"
            href="{{ route($item['route']) }}"
            data-page-link
            data-tooltip="{{ $item['label'] }}"
            title="{{ $item['label'] }}"
            aria-label="{{ $item['label'] }}"
            @if ($groupIsActive) aria-current="page" @endif
          >
            <span class="nav-icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
            <span class="nav-label">{{ $item['label'] }}</span>
          </a>
        @else
          <button
            class="sidebar-link {{ $groupIsActive ? 'active' : '' }}"
            type="button"
            data-submenu-toggle
            data-tooltip="{{ $item['label'] }}"
            title="{{ $item['label'] }}"
            aria-label="{{ $item['label'] }}"
            aria-expanded="{{ $groupIsActive ? 'true' : 'false' }}"
          >
            <span class="nav-icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
            <span class="nav-label">{{ $item['label'] }}</span>
            <span class="chevron"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
          </button>

          <div class="sidebar-submenu {{ $groupIsActive ? 'open' : '' }}">
            @foreach ($item['links'] as $link)
              @php
                  $linkIsActive = request()->routeIs($link['route'])
                      && (! isset($link['fragment']) || request()->fullUrlIs('*#'.$link['fragment']));
                  $href = route($link['route']).(isset($link['fragment']) ? '#'.$link['fragment'] : '');
              @endphp
              <a
                class="{{ $linkIsActive ? 'active' : '' }}"
                href="{{ $href }}"
                data-page-link
                title="{{ $link['label'] }}"
                aria-label="{{ $link['label'] }}"
                @if ($linkIsActive) aria-current="page" @endif
              >
                <span class="nav-icon submenu-icon"><i class="{{ $link['icon'] }}" aria-hidden="true"></i></span>
                <span class="submenu-label">{{ $link['label'] }}</span>
              </a>
            @endforeach
          </div>
        @endif
      @endforeach
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card" title="{{ $userName }} — {{ $userRole }}">
      <span class="avatar" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
      <div>
        <p class="user-name">{{ $userName }}</p>
        <p class="user-role">{{ $userRole }}</p>
      </div>
    </div>

    <button class="logout-btn" type="button" data-modal-open="logout-confirm-modal" data-tooltip="Déconnexion" title="Déconnexion" aria-label="Déconnexion">
        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        <span class="logout-label">Déconnexion</span>
    </button>
  </div>
</aside>

<x-module-indemnite type="modal" id="logout-confirm-modal" title="Confirmer la déconnexion">
  @push('styles')
    <style>
      #logout-confirm-modal .modal-header h2 { color: #b91c1c; }
      .logout-confirm-message { display: flex; align-items: center; gap: 12px; color: #7f1d1d; }
      .logout-confirm-message i { color: #dc2626; font-size: 1.4rem; }
      #logout-confirm-modal .btn-danger-soft { color: #ffffff; background: #dc2626; }
      #logout-confirm-modal .btn-danger-soft:hover { background: #b91c1c; }
    </style>
  @endpush
  <div class="logout-confirm-message" role="alert">
    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
    <p>Voulez-vous vraiment vous déconnecter de votre compte&nbsp;?</p>
  </div>
  <form method="POST" action="{{ route('logout') }}" class="form-actions">
    @csrf
    <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
    <button class="btn-danger-soft" type="submit">
      <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
      Confirmer la déconnexion
    </button>
  </form>
</x-module-indemnite>
