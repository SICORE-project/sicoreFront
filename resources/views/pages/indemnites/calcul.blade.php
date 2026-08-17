@extends('layouts.app')

{{--
  PAGE : Calcul des indemnités — route /indemnites/calcul.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu et calcul de présentation : clé "indemnites-calcul" dans
  config/module-pages.php. Menu : config/navigation.php.
--}}
@section('title', 'SICORE - Calcul des indemnites')
@section('content')
  <x-module-page slug="indemnites-calcul" />
@endsection
