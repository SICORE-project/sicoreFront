@extends('layouts.app')

{{--
  PAGE : Accusés de réception — route /indemnites/accuses-reception.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "indemnites-accuses-reception" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion des indemnités.
--}}
@section('title', 'SICORE - Gestion des accuses de reception')
@section('content')
  <x-module-page slug="indemnites-accuses-reception" />
@endsection
