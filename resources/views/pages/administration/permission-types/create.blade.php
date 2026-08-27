@extends('layouts.app')

@php($isModule = $type === 'modules')
@section('title', 'SICORE - '.($isModule ? 'Nouveau module' : 'Nouveau groupe'))

@section('content')
<main class="main-content">
    <x-topbar :title="$isModule ? 'Créer un module' : 'Créer un groupe'" subtitle="Gestion Utilisateur > Permissions" :icon="$isModule ? 'fa-solid fa-cubes' : 'fa-solid fa-layer-group'" />
    <section class="content-area"><section class="table-card" style="padding:24px">
        <form method="POST" action="{{ route('admin.permissions.types.prepare', $type) }}">
            @csrf
            <div class="form-group" style="margin-bottom:20px">
                <label for="libelle">Nom / libellé du {{ $isModule ? 'module' : 'groupe' }} *</label>
                <input id="libelle" name="libelle" class="form-control" value="{{ old('libelle') }}" maxlength="150" required autofocus>
                @error('libelle') <p style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</p> @enderror
            </div>
            <div class="form-group" style="margin-bottom:20px">
                <label for="est_actif">Statut *</label>
                <select id="est_actif" name="est_actif" class="form-control" required>
                    <option value="1" @selected((string) old('est_actif', '1') === '1')>Actif</option>
                    <option value="0" @selected((string) old('est_actif') === '0')>Inactif</option>
                </select>
                @error('est_actif') <p style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</p> @enderror
            </div>
            @if($errors->has('error')) <div class="alert alert-danger">{{ $errors->first('error') }}</div> @endif
            <div class="actions-group" style="justify-content:flex-end">
                <a href="{{ route('admin.permissions.types.index', $type) }}" class="btn-secondary">Annuler</a>
                <button class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </section></section>
</main>
@endsection
