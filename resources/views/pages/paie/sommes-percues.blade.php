@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Sommes perçues — URL /paie/sommes-percues.
  Route/contrôleur : routes/web.php → PayrollController::show().
  Rendu : components/module-page.blade.php.
  Paiements affichés : PayrollPageService::paidReport().
  Paiements créés depuis Bulletins par PayrollActionService::markPaid().
--}}
@section('title', 'SICORE - Sommes percues')
@section('content')
  <x-module-page slug="paie-sommes-percues" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
