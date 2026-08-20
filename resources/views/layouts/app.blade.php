@extends('layouts.base')

{{--
  LAYOUT DES PAGES CONNECTÉES
  Inclut la sidebar, les messages serveur et la section "content".
  L'accès est protégé par sicore.auth dans routes/web.php.
--}}

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
@endpush

@section('body_attributes')
class="app-body" @yield('page_attributes')
@endsection

@section('body')
  {{-- Menu construit depuis config/navigation.php. --}}
  <x-sidebar />
  {{-- Messages success, warning, error ou info placés en session. --}}
  <x-flash-messages />

  {{-- Chaque fichier resources/views/pages/... fournit cette section. --}}
  @yield('content')

  {{-- Calque utilisé par app.js pour fermer le menu sur mobile. --}}
  <div class="mobile-overlay" data-sidebar-close aria-hidden="true"></div>
@endsection
