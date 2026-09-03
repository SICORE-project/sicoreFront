{{--
  COMPOSANT DU MENU LATÉRAL
  Liens : config/navigation.php.
  Comportement et défilement : public/assets/js/app.js.
  Utilisateur : session "sicore_user" créée par AuthController.php.
--}}
@php
    // Charger le menu et l'identité de l'utilisateur connecté.
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
      {{-- Une entrée est soit un lien direct, soit un groupe déroulant. --}}
      @foreach ($navigation as $item)
        @php
            // routeIs indique si l'entrée correspond à la route actuelle.
            $patterns = (array) ($item['active'] ?? $item['route'] ?? []);
            $groupIsActive = collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
        @endphp

        @if (($item['type'] ?? 'link') === 'link')
          {{-- Cas 1 : lien simple, par exemple Tableau de bord. --}}
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
          {{-- Cas 2 : groupe avec sous-menu, par exemple Gestion de la paie. --}}
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
                  $linkIsGroup = ($link['type'] ?? 'link') === 'group';
                  $linkPatterns = (array) ($link['active'] ?? $link['route'] ?? []);
                  $linkIsActive = collect($linkPatterns)->contains(fn ($pattern) => request()->routeIs($pattern));
              @endphp

              @if ($linkIsGroup)
                <button
                  class="sidebar-link sidebar-subgroup-toggle {{ $linkIsActive ? 'active expanded' : '' }}"
                  type="button"
                  data-submenu-toggle
                  title="{{ $link['label'] }}"
                  aria-label="{{ $link['label'] }}"
                  aria-expanded="{{ $linkIsActive ? 'true' : 'false' }}"
                >
                  <span class="nav-icon submenu-icon"><i class="{{ $link['icon'] }}" aria-hidden="true"></i></span>
                  <span class="submenu-label">{{ $link['label'] }}</span>
                  <span class="chevron"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                </button>

                <div class="sidebar-submenu sidebar-submenu-nested {{ $linkIsActive ? 'open' : '' }}">
                  @foreach ($link['links'] as $nestedLink)
                    @php
                        $nestedLinkIsActive = request()->routeIs($nestedLink['route'])
                            && (! isset($nestedLink['fragment']) || request()->fullUrlIs('*#'.$nestedLink['fragment']));
                        $nestedHref = route($nestedLink['route'])
                            .(isset($nestedLink['fragment']) ? '#'.$nestedLink['fragment'] : '');
                    @endphp
                    <a
                      class="{{ $nestedLinkIsActive ? 'active' : '' }}"
                      href="{{ $nestedHref }}"
                      data-page-link
                      title="{{ $nestedLink['label'] }}"
                      aria-label="{{ $nestedLink['label'] }}"
                      @if ($nestedLinkIsActive) aria-current="page" @endif
                    >
                      <span class="nav-icon submenu-icon"><i class="{{ $nestedLink['icon'] }}" aria-hidden="true"></i></span>
                      <span class="submenu-label">{{ $nestedLink['label'] }}</span>
                    </a>
                  @endforeach
                </div>
              @else
                @php
                    // Construire le lien et ajouter éventuellement une ancre #...
                    $href = route($link['route']).(isset($link['fragment']) ? '#'.$link['fragment'] : '');
                    $linkIsActive = $linkIsActive
                        && (! isset($link['fragment']) || request()->fullUrlIs('*#'.$link['fragment']));
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
              @endif
            @endforeach
          </div>
        @endif
      @endforeach
    </div>
  </nav>

  <div class="sidebar-footer">
    {{-- Identité connectée et formulaire sécurisé de déconnexion. --}}
    <div class="user-card" title="{{ $userName }} — {{ $userRole }}">
      <span class="avatar" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
      <div>
        <p class="user-name">{{ $userName }}</p>
        <p class="user-role">{{ $userRole }}</p>
      </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
      @csrf
      @if (request()->is('paie', 'paie/*'))
        <input type="hidden" name="next" value="{{ request()->getRequestUri() }}">
      @endif
      <button class="logout-btn" type="submit" data-tooltip="Déconnexion" title="Déconnexion" aria-label="Déconnexion">
        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        <span class="logout-label">Déconnexion</span>
      </button>
    </form>
  </div>
</aside>
