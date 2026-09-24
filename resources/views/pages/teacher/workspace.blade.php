@extends('layouts.app')
@section('title', 'Mon espace enseignant')
@section('content')
<main class="main-content">
<x-topbar :title="$mode === 'bulletins' ? 'Mes bulletins de salaire' : ($mode === 'informations' ? 'Mes informations' : 'Tableau de bord')" subtitle="Mon espace personnel enseignant" icon="fa-solid fa-user" />
<section class="module-content">
@if($error)
<div class="alert alert-warning" role="alert">{{ $error }}</div>
@elseif($mode === 'bulletins')
<form class="filter-panel" method="GET" action="{{ route('teacher.payslips') }}">
<div class="form-group"><label for="teacher-year">Année</label><select class="form-control" id="teacher-year" name="annee"><option value="">Toutes</option>@foreach($data['annees'] ?? [] as $year)<option value="{{ $year }}" @selected((string) request('annee') === (string) $year)>{{ $year }}</option>@endforeach</select></div>
<div class="form-group"><label for="teacher-period">Période</label><select class="form-control" id="teacher-period" name="periode_id"><option value="">Toutes</option>@foreach($data['periodes'] ?? [] as $period)<option value="{{ $period['id'] }}" @selected((string) request('periode_id') === (string) $period['id'])>{{ $period['libelle'] }}</option>@endforeach</select></div>
<div class="actions-group"><button class="btn-primary" type="submit">Filtrer</button><a class="btn-secondary" href="{{ route('teacher.payslips') }}">Réinitialiser</a></div>
</form>
<section class="table-card"><div class="table-responsive"><table class="table"><thead><tr><th>Période</th><th>Année</th><th>Généré le</th><th>Statut</th><th>Net (FCFA)</th><th>Actions</th></tr></thead><tbody>
@forelse(data_get($data, 'data.data', []) as $bulletin)
<tr><td>{{ $bulletin['periode'] }}</td><td>{{ $bulletin['annee'] }}</td><td>{{ $bulletin['date_generation'] }}</td><td>{{ $bulletin['statut'] }}</td><td>{{ number_format((float) $bulletin['net'], 0, ',', ' ') }}</td><td><div class="table-actions-inline"><a class="table-action" target="_blank" rel="noopener" href="{{ route('teacher.pdf', $bulletin['id']) }}">Consulter</a><a class="table-action" href="{{ route('teacher.pdf', ['id' => $bulletin['id'], 'download' => 1]) }}">Télécharger</a></div></td></tr>
@empty<tr><td colspan="6">Aucun bulletin disponible pour ces critères.</td></tr>@endforelse
</tbody></table></div>
@include('components.pagination', ['pagination' => $data['data']])
</section>
@else
@php($profile = $data['data'] ?? [])
<section class="table-card" style="padding:24px;margin-bottom:24px"><h2>{{ $profile['prenom'] ?? '' }} {{ $profile['nom'] ?? '' }}</h2><p>Matricule : {{ $profile['matricule'] ?? 'Non renseigné' }}</p>
@if($mode === 'dashboard')<p>{{ $profile['bulletins_disponibles'] ?? 0 }} bulletin(s) disponible(s)</p><div class="actions-group"><a class="btn-secondary" href="{{ route('teacher.profile') }}">Mes informations</a><a class="btn-primary" href="{{ route('teacher.payslips') }}">Mes bulletins de salaire</a></div>@endif
</section>
@foreach(['Informations personnelles' => ['matricule'=>'Matricule','prenom'=>'Prénom','nom'=>'Nom','date_naissance'=>'Date de naissance','lieu_naissance'=>'Lieu de naissance','genre'=>'Sexe','telephone'=>'Téléphone','email'=>'Adresse email','adresse'=>'Adresse'], 'Informations professionnelles' => ['statut_professionnel'=>'Statut professionnel','indice'=>'Indice','corps'=>'Corps','categorie'=>'Catégorie','specialite'=>'Spécialité','ia'=>'IA','ief'=>'IEF','lieuService'=>'Lieu de service','statut'=>'Situation administrative']] as $heading => $fields)
<section class="table-card" style="padding:24px;margin-bottom:24px"><h2>{{ $heading }}</h2><dl class="teacher-personal-grid">@foreach($fields as $key => $label)@continue($key === 'indice' && ($profile['statut_professionnel'] ?? null) !== 'Fonctionnaire')<div><dt>{{ $label }}</dt><dd>{{ $profile[$key] ?? 'Non renseigné' }}</dd></div>@endforeach</dl></section>
@endforeach
@endif
</section>
</main>
@endsection
@push('styles')
<style>.teacher-personal-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px}.teacher-personal-grid dt{color:#64748b;font-size:14px}.teacher-personal-grid dd{margin:6px 0 0;font-weight:600}</style>
@endpush