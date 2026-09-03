@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Salaires par banque — URL /paie/edition-salaires-banque.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données et masquage des comptes : PayrollPageService::bankSalaryReport().
  Export : PayrollPageController::export() dans le backend.
--}}
@section('title', 'SICORE - Edition des salaires par banque')
@section('content')
  <x-module-page slug="paie-edition-salaires-banque" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
