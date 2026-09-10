@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Exemptions — URL /paie/exemptions.
  Route : routes/web.php. Contrôleur : PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données : sicoreBack/app/Services/PayrollPageService.php::exemptionReport().
  La création d'une exemption se fait depuis une page d'éléments variables.
--}}
@section('title', 'SICORE - Exemptions par enseignant')
@section('content')
  <x-module-page slug="paie-exemptions" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
