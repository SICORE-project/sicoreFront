@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Récapitulatif par banque — URL /paie/recap-banque.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Agrégation backend : PayrollPageService::bankSummaryReport().
  Export CSV : PayrollController::export() puis PayrollPageController::export().
--}}
@section('title', 'SICORE - Etat recapitulatif par banque')
@section('content')
  <x-module-page slug="paie-recap-banque" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
