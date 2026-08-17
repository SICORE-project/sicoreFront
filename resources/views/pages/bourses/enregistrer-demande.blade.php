@extends('layouts.app')

{{--
  PAGE : Enregistrer une demande — route /bourses/enregistrer-demande.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "bourses-enregistrer-demande" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Bourses et aides.
--}}
@section('title', 'SICORE - Enregistrer demande')
@section('content')
  <x-module-page slug="bourses-enregistrer-demande" />
@endsection
