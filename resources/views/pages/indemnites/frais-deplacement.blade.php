@extends('layouts.app')

{{--
  PAGE : Frais de déplacement — route /indemnites/frais-deplacement.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "indemnites-frais-deplacement" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion des indemnités.
--}}
@section('title', 'SICORE - Gestion des frais de deplacement')
@section('content')
  <x-module-page slug="indemnites-frais-deplacement" />
@endsection
