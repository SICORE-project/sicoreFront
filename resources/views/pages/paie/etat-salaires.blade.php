@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : État des salaires — URL /paie/etat-salaires.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Colonnes et lignes : PayrollPageService::salaryReport().
  Montants sources : modèles PayrollPayslip et PayrollPayslipLine du backend.
--}}
@section('title', 'SICORE - Etat des salaires')
@section('content')
  <x-module-page slug="paie-etat-salaires" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
