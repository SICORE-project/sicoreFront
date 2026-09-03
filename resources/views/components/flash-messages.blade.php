{{--
  COMPOSANT DES MESSAGES SERVEUR
  Les contrôleurs créent ces messages avec with('success', ...), withErrors(),
  etc. public/assets/js/notifications.js gère leur disparition visuelle.
--}}
@php
    // Regrouper les niveaux permet une seule boucle d'affichage.
    $messages = [
        'success' => session('success'),
        'warning' => session('warning'),
        'error' => session('error'),
        'info' => session('info'),
    ];
@endphp

<div class="server-flashes" aria-live="polite" aria-atomic="true">
  @foreach ($messages as $type => $message)
    @if ($message)
      <div class="server-flash server-flash-{{ $type }}" role="status">
        <i class="fa-solid {{ $type === 'success' ? 'fa-circle-check' : ($type === 'warning' ? 'fa-triangle-exclamation' : ($type === 'error' ? 'fa-circle-xmark' : 'fa-circle-info')) }}" aria-hidden="true"></i>
        <span>{{ $message }}</span>
      </div>
    @endif
  @endforeach
</div>
