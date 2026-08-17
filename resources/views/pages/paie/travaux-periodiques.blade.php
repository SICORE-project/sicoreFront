@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Travaux périodiques — URL /paie/travaux-periodiques.
  Rendu : components/module-page.blade.php.
  Données et boutons : PayrollPageService::periodReport().
  Actions : PayrollActionService::createPeriod(), calculate() et validatePayroll().
  Calculs financiers : sicoreBack/app/Services/PayrollCalculationService.php.
--}}
@section('title', 'SICORE - Travaux periodiques')
@section('content')
  <x-module-page slug="paie-travaux-periodiques" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
