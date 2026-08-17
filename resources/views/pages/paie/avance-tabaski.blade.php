@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Avance Tabaski — URL /paie/avance-tabaski.
  Route : routes/web.php. Contrôleur : PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données : PayrollPageService::elementReport() avec le code TABASKI_AVANCE.
  Action collective : apply-tabaski-advance, sans matricule ni catégorie.
  Champs : corps, IA, IEF, année académique, mois d'application et montant.
--}}
@section('title', 'SICORE - Avance Tabaski')
@section('content')
  <x-module-page slug="paie-avance-tabaski" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
