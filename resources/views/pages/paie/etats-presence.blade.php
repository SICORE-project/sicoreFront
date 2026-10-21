@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : États de présence — URL /paie/etats-presence.
  Route et contrôleur : routes/web.php → PayrollController::show().
  Rendu commun : resources/views/components/module-page.blade.php.
  Données : sicoreBack/app/Services/PayrollPageService.php::attendanceReport().
  Actions : payroll-forms.php → PayrollActionService::saveAttendance()/validateInputs().
--}}
@section('title', 'SICORE - Etats de presence')
@section('content')
  <x-module-page slug="paie-etats-presence" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
