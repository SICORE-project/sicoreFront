@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Cotisations sociales — URL /paie/cotisations-sociales.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données : PayrollPageService::contributionReport().
  Calcul initial des cotisations : PayrollCalculationService.php.
--}}
@section('title', 'SICORE - Cotisations sociales')
@section('content')
  <x-module-page slug="paie-cotisations-sociales" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
