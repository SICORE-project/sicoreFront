@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Paie générée par IEF — URL /paie/generee-ief.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Regroupement administratif : PayrollPageService::iefSummaryReport().
  Relations IA/IEF : modèles backend Etablissement, Ief et Ia.
--}}
@section('title', 'SICORE - Paie generee par IEF')
@section('content')
  <x-module-page slug="paie-generee-ief" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
