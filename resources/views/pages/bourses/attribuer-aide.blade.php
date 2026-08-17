@extends('layouts.app')

{{--
  PAGE : Attribuer une aide — route /bourses/attribuer-aide.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "bourses-attribuer-aide" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Bourses et aides.
--}}
@section('title', 'SICORE - Attribuer aide')
@section('content')
  <x-module-page slug="bourses-attribuer-aide" />
@endsection
