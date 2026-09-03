{{--
  LAYOUT RACINE DU FRONTEND
  Toutes les pages héritent directement ou indirectement de ce fichier.
  Styles : public/assets/css/style.css et responsive.css.
  Scripts communs : public/assets/js/app.js et notifications.js.
  app.blade.php et guest.blade.php remplissent la section "body".
--}}
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#087f5b">
  <title>@yield('title', 'SICORE')</title>

  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}?v={{ filemtime(public_path('assets/css/responsive.css')) }}">
  {{-- Une page peut ajouter une feuille avec @push('styles'). --}}
  @stack('styles')
</head>
<body @yield('body_attributes')>
  {{-- Contenu fourni par layouts/app.blade.php ou layouts/guest.blade.php. --}}
  @yield('body')

  <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}" defer></script>
  <script src="{{ asset('assets/js/notifications.js') }}?v={{ filemtime(public_path('assets/js/notifications.js')) }}" defer></script>
  {{-- Une page peut ajouter un script avec @push('scripts'). --}}
  @stack('scripts')
</body>
</html>
