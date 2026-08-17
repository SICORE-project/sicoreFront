@extends('layouts.app')

{{--
  PAGE : Édition des délégations — route /credits/edition-delegations.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "credit-edition-delegations" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion de la paie.
--}}
@section('title', 'SICORE - Edition des delegations de credits')
@section('content')
  <x-module-page slug="credit-edition-delegations" />
@endsection
