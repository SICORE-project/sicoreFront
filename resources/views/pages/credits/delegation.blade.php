@extends('layouts.app')

{{--
  PAGE : Délégation de crédit — route /credits/delegation.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "credit-delegation" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion de la paie.
--}}
@section('title', 'SICORE - Delegation de credit')
@section('content')
  <x-module-page slug="credit-delegation" />
@endsection
