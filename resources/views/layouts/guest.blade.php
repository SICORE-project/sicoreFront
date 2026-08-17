@extends('layouts.base')

{{--
  LAYOUT DES VISITEURS NON CONNECTÉS
  Utilisé par resources/views/pages/auth/login.blade.php.
  Il conserve les ressources communes mais n'affiche pas la sidebar.
--}}

@section('body_attributes')
class="guest-body"
@endsection

@section('body')
  {{-- Affiche notamment les erreurs ou confirmations de connexion. --}}
  <x-flash-messages />
  {{-- La page invitée fournit son contenu dans cette section. --}}
  @yield('content')
@endsection
