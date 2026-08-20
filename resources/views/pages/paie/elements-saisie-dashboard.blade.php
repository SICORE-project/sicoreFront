@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Éléments de saisie — URL /paie/elements-saisie-dashboard.
  Rendu : components/module-page.blade.php.
  Synthèse backend : PayrollPageService::elementsDashboard().
  Formulaire : config/payroll-forms.php, action add-element.
  Traitement : sicoreBack/app/Services/PayrollActionService.php::addElement().
--}}
@section('title', 'SICORE - Dashboard elements de saisie')
@section('content')
  <x-module-page slug="paie-elements-saisie-dashboard" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
