@extends('layouts.app')

{{--
  PAGE : Services faits — route /indemnites/services-faits.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "indemnites-services-faits" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion des indemnités.
--}}
@section('title', 'SICORE - Gestion des services faits')
@section('content')
  <x-module-page slug="indemnites-services-faits" />
@endsection
