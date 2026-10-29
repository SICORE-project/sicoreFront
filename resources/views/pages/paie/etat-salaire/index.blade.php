@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : État des salaires — URL /paie/etat-salaires.
  Route/contrôleur : routes/web.php → PayrollController::show() (vue via PayrollController::viewFor()).
  Rendu : components/module-page.blade.php + etat-salaire/partials/salary-statement.blade.php.
  Styles : public/assets/css/paie/etat-salaire.css.
  Colonnes et lignes (backend) : App\Services\Paie\EtatSalaire\EtatSalaireReport::salaryReport().
  Montants sources : modèles PayrollPayslip et PayrollPayslipLine du backend.
--}}
@section('title', 'SICORE - Etat des salaires')
@section('content')
  {{-- Chargée dans le contenu (et non via @push) pour rester après app.css dans la cascade. --}}
  <link rel="stylesheet" href="{{ asset('assets/css/paie/etat-salaire.css') }}?v={{ filemtime(public_path('assets/css/paie/etat-salaire.css')) }}">
  <x-module-page slug="paie-etat-salaires" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
