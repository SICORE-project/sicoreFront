@extends('layouts.app')

{{--
  PAGE : Édition des engagements — route /credits/edition-engagements.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "credit-edition-engagements" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion de la paie.
--}}
@section('title', 'SICORE - Edition des engagements')
@section('content')
  <x-module-page slug="credit-edition-engagements" />
@endsection
