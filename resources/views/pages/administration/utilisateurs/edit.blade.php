@extends('layouts.app')
@section('title', 'SICORE - Modifier un utilisateur')
@section('content')
@php $access = data_get($user, 'acces_organisationnel', []); @endphp
<main class="main-content"><x-topbar title="Modifier l’utilisateur" subtitle="Administration > Utilisateurs" icon="fa-solid fa-user-pen" />
<section class="content-area"><section class="panel"><form class="teacher-form" method="POST" action="{{ route('utilisateurs.update', data_get($user, 'id')) }}">@csrf @method('PUT')
@if($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="form-grid form-grid--balanced">
<div class="form-group"><label for="nom">Nom</label><input class="form-control" id="nom" name="nom" value="{{ old('nom', data_get($user, 'nom')) }}" required></div>
<div class="form-group"><label for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" value="{{ old('prenom', data_get($user, 'prenom')) }}" required></div>
<div class="form-group"><label for="email">E-mail</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email', data_get($user, 'email')) }}" required></div>
<div class="form-group"><label for="role_id">Rôle</label><select class="form-control" id="role_id" name="role_id" required>@foreach($roles as $role)<option value="{{ $role['id'] }}" @selected((string) old('role_id', data_get($user, 'role.id', data_get($user, 'role_id'))) === (string) $role['id'])>{{ $role['nom'] }}</option>@endforeach</select></div>
<div class="form-group"><label for="statut">Statut</label><select class="form-control" id="statut" name="statut"><option value="actif" @selected(old('statut', data_get($user, 'statut')) === 'actif')>Actif</option><option value="inactif" @selected(old('statut', data_get($user, 'statut')) === 'inactif')>Inactif</option></select></div>
<div class="form-group full"><strong>Rattachement organisationnel</strong><small>Renseignez une structure nationale, ou une IA et éventuellement une IEF.</small></div>
<div class="form-group"><label for="structure_organisationnelle_id">ID structure nationale</label><input class="form-control" id="structure_organisationnelle_id" type="number" name="structure_organisationnelle_id" value="{{ old('structure_organisationnelle_id', data_get($access, 'structure_organisationnelle_id', data_get($access, 'structure.id'))) }}"></div>
<div class="form-group"><label for="ia_id">ID IA</label><input class="form-control" id="ia_id" type="number" name="ia_id" value="{{ old('ia_id', data_get($access, 'ia_id', data_get($access, 'ia.id'))) }}"></div>
<div class="form-group"><label for="ief_id">ID IEF</label><input class="form-control" id="ief_id" type="number" name="ief_id" value="{{ old('ief_id', data_get($access, 'ief_id', data_get($access, 'ief.id'))) }}"></div>
</div><div class="actions-group"><a class="btn-secondary" href="{{ route('utilisateurs.show', data_get($user, 'id')) }}">Annuler</a><button class="btn-primary" type="submit">Enregistrer</button></div></form></section></section></main>
@endsection