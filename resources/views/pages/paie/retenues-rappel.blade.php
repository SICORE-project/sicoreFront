@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Retenues rappel — URL /paie/retenues-rappel.
  Route : routes/web.php. Contrôleur : PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données : PayrollPageService::elementReport() avec le code RAPPEL_RETENUE.
  Actions : add-element, exempt-element et validate-elements.
--}}
@section('title', 'SICORE - Retenues rappel')
@section('content')
  <x-module-page slug="paie-retenues-rappel" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
