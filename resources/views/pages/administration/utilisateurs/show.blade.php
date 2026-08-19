@extends('layouts.app')
@section('title', 'SICORE - Fiche utilisateur')
@section('content')
@php
  $access = data_get($user, 'acces_organisationnel', []);
  $role = data_get($user, 'role.nom', data_get($user, 'role', '—'));
  $structure = data_get($access, 'lieu_service', data_get($access, 'ief', data_get($access, 'ia', data_get($access, 'structure'))));
  $structureLabel = is_array($structure) ? collect([data_get($structure, 'code'), data_get($structure, 'libelle', data_get($structure, 'nom')])->filter()->join(' — ') : ($structure ?: '—');
  $type = data_get($access, 'niveau', data_get($access, 'type_structure', data_get($access, 'ief') ? 'IEF' : (data_get($access, 'ia') ? 'IA' : 'National')));
@endphp
<main class="main-content"><x-topbar title="Fiche utilisateur" subtitle="Administration > Utilisateurs" icon="fa-solid fa-user-shield" />
<section class="content-area"><section class="panel"><div class="panel-header"><div><h2>{{ trim(data_get($user, 'prenom').' '.data_get($user, 'nom')) }}</h2><p>{{ data_get($user, 'email') }}</p></div><div class="actions-group"><a class="btn-secondary" href="{{ route('utilisateurs.index') }}">Retour</a><a class="btn-primary" href="{{ route('utilisateurs.edit', data_get($user, 'id')) }}">Modifier</a></div></div>
<div class="form-grid form-grid--balanced"><div class="form-group"><strong>Rôle</strong><p>{{ is_array($role) ? data_get($role, 'nom', '—') : $role }}</p></div><div class="form-group"><strong>Statut</strong><p>{{ data_get($user, 'statut', data_get($user, 'status', '—')) }}</p></div><div class="form-group"><strong>Type de structure</strong><p>{{ strtoupper((string) $type) }}</p></div><div class="form-group"><strong>Rattachement organisationnel</strong><p>{{ $structureLabel }}</p></div></div>
</section></section></main>
@endsection