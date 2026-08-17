@extends('layouts.app')

{{--
  PAGE : Convocations — route /indemnites/convocations.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "indemnites-convocations" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion des indemnités.
--}}
@section('title', 'SICORE - Gestion des convocations')
@section('content')
  <x-module-page slug="indemnites-convocations" />
@endsection
