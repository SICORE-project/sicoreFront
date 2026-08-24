@extends('layouts.app')

@section('title', 'SICORE - Type de rôle')

@section('content')
<main class="main-content">
    <x-topbar title="{{ $typeRole ? 'Modifier le type de rôle' : 'Créer un type de rôle' }}" subtitle="Administration > Types de rôle" icon="fa-solid fa-tags" />
    <section class="content-area"><section class="table-card" style="padding:24px">
        <form method="POST" action="{{ $typeRole ? route('admin.type-roles.update', $typeRole['id']) : route('admin.type-roles.store') }}">
            @csrf @if($typeRole) @method('PUT') @endif
            <div class="form-group"><label for="code">Code *</label><input id="code" name="code" class="form-control" required value="{{ old('code', $typeRole['code'] ?? '') }}">@error('code')<p>{{ $message }}</p>@enderror</div>
            <div class="form-group"><label for="libelle">Libellé *</label><input id="libelle" name="libelle" class="form-control" required value="{{ old('libelle', $typeRole['libelle'] ?? '') }}">@error('libelle')<p>{{ $message }}</p>@enderror</div>
            <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" class="form-control">{{ old('description', $typeRole['description'] ?? '') }}</textarea></div>
            <div class="form-group"><label for="est_actif">Statut *</label><select id="est_actif" name="est_actif" class="form-control"><option value="1" @selected(old('est_actif', $typeRole['est_actif'] ?? true))>Actif</option><option value="0" @selected(!old('est_actif', $typeRole['est_actif'] ?? true))>Inactif</option></select></div>
            <div class="actions-group"><a href="{{ route('admin.type-roles.index') }}" class="btn-secondary">Annuler</a><button class="btn-primary">Enregistrer</button></div>
        </form>
    </section></section>
</main>
@endsection
