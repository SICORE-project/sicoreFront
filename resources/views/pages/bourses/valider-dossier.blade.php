@extends('layouts.app')

{{--
  PAGE : Valider un dossier — route /bourses/valider-dossier.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "bourses-valider-dossier" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Bourses et aides.
--}}
@section('title', 'SICORE - Valider dossier')
@section('content')
  <x-module-page slug="bourses-valider-dossier" />
@endsection
