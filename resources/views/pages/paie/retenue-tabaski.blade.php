@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Retenue Tabaski — URL /paie/retenue-tabaski.
  Route : routes/web.php. Contrôleur : PayrollController::show().
  Rendu : components/module-page.blade.php.
  Données : PayrollPageService::elementReport() avec le code TABASKI_RETENUE.
  Action collective : apply-tabaski-deduction, sans matricule ni catégorie.
  Champs : corps, IA, IEF, année académique, mois d'application et montant.
--}}
@section('title', 'SICORE - Retenue Tabaski')
@section('content')
  <x-module-page slug="paie-retenue-tabaski" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
