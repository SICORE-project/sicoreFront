@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Bulletins des salaires — URL /paie/bulletins.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Tableau : components/module-page.blade.php + PayrollPageService::payslipReport().
  Bouton Consulter : route paie.payslip → pages/paie/payslip.blade.php.
  Paiement : action mark-paid → PayrollActionService::markPaid().
--}}
@section('title', 'SICORE - Bulletins des salaires')
@section('content')
  <x-module-page slug="paie-bulletins" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
