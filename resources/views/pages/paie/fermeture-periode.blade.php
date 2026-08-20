@extends('layouts.app')

<<<<<<< HEAD
@section('title', 'SICORE - Fermer la periode de paie')
@section('content')
  <x-module-page slug="paie-fermeture-periode" />
=======
{{--
  PAGE PAIE DYNAMIQUE SENSIBLE : Fermeture — URL /paie/fermeture-periode.
  Tableau et bouton : PayrollPageService::closingReport().
  Modale : config/payroll-forms.php, action close-period.
  Validation et verrouillage : PayrollActionRequest.php puis
  PayrollActionService::closePeriod() dans le backend.
--}}
@section('title', 'SICORE - Fermer la periode de paie')
@section('content')
  <x-module-page slug="paie-fermeture-periode" :data="$moduleData ?? []" :error="$apiError ?? null" />
>>>>>>> origin/module-paie
@endsection
