@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Effectifs par corps — URL /paie/effectifs-corps.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Regroupement et masse de base : PayrollPageService::workforceReport().
  Sources : modèles backend Enseignant et corps_enseignants.
--}}
@section('title', 'SICORE - Effectifs par corps')
@section('content')
  <x-module-page slug="paie-effectifs-corps" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
