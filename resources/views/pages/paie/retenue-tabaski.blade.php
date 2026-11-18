@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Retenue Tabaski — URL /paie/retenue-tabaski.
  Route : routes/web.php. Contrôleur : PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données : PayrollPageService::elementReport() avec le code TABASKI_RETENUE.
  Action collective : apply-tabaski-deduction, sans matricule ni catégorie.
  Champs : statut, IA et premier mois à venir. Montants fixes et bénéficiaires
  conservés dans PayrollTabaskiSchedule, sans créer dix périodes à l'avance.
--}}
@section('title', 'SICORE - Retenue Tabaski')
@section('content')
  <x-module-page slug="paie-retenue-tabaski" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
