@extends('layouts.app')

{{--
  PAGE : Pièces justificatives — route /indemnites/pieces-justificatives.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "indemnites-pieces-justificatives" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion des indemnités.
--}}
@section('title', 'SICORE - Gestion des pieces justificatives')
@section('content')
  <x-module-page slug="indemnites-pieces-justificatives" />
@endsection
