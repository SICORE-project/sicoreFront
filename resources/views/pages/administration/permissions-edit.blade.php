@extends('layouts.app')

@section('title', 'SICORE - Modifier une permission')

@section('content')
<main class="main-content">
    <x-topbar title="Modifier la permission" subtitle="Gestion Utilisateur > Permissions > Modifier" icon="fa-solid fa-lock" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            <form action="{{ route('admin.permissions.update', $permission['id']) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $permission['nom'] ?? '') }}" required class="form-control">
                    @error('nom') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="slug">Slug (non modifiable)</label>
                    <input type="text" id="slug" name="slug" value="{{ $permission['slug'] ?? '' }}" class="form-control" placeholder="" readonly>
                    @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="groupe">Groupe</label>
                    <input type="text" id="groupe" name="groupe" value="{{ old('groupe', $permission['groupe'] ?? '') }}" class="form-control" placeholder="ex. indemnites">
                    @error('groupe') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="module">Module</label>
                    <input type="text" id="module" name="module" value="{{ old('module', $permission['module'] ?? '') }}" class="form-control" placeholder="ex. rapports">
                    @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="action">Action</label>
                    <input type="text" id="action" name="action" value="{{ old('action', $permission['action'] ?? '') }}" class="form-control" placeholder="ex. export">
                    @error('action') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $permission['description'] ?? '') }}</textarea>
                    @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="est_actif" value="1" @checked($permission['est_actif'] ?? false)>
                        <span style="font-size: 14px; font-weight: 500;">Actif</span>
                    </label>
                </div>

                <div class="actions-group" style="justify-content: flex-end;">
                    <a href="{{ route('admin.permissions.index') }}" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>

        </section>
    </section>
</main>
@endsection