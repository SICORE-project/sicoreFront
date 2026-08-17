@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Paie non générée — URL /paie/non-generee.
  Tableau et motifs : PayrollPageService::notGeneratedReport().
  Formulaire de correction : payroll-forms.php, configure-teacher-payroll.
  Enregistrement : PayrollActionService::configureTeacherPayroll().
  Cette page montre les profils bloquants avant le calcul.
--}}
@section('title', 'SICORE - Paie non generee')
@section('content')
  <x-module-page slug="paie-non-generee" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
