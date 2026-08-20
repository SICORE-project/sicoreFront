@extends('layouts.app')

@section('title', 'SICORE - Créer un rôle')

@section('content')
<main class="main-content">
    <x-topbar title="Créer un rôle" subtitle="Gestion Utilisateur > Profils / Rôles > Nouveau" icon="fa-solid fa-user-plus" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf

                <div class="filter-panel" aria-label="Informations du rôle" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" required class="form-control">
                        @error('nom') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control"></textarea>
                    @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="filter-panel" aria-label="Niveau et statut" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="niveau">Niveau *</label>
                        <select id="niveau" name="niveau" class="form-control" required>
                            <option value="systeme">Système</option>
                            <option value="admin_metier">Admin Métier</option>
                            <option value="gestionnaire">Gestionnaire</option>
                            <option value="consultation">Consultation</option>
                        </select>
                        @error('niveau') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="est_actif">Statut *</label>
                        <select id="est_actif" name="est_actif" class="form-control" required>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                </div>

                <hr style="margin: 24px 0; border-color: #e5e7eb;">

                <h3 style="font-weight: 700; font-size: 1.125rem; margin-bottom: 16px;">Permissions</h3>

                @foreach ($permissions as $module => $perms)
                    <div class="objective-card" style="margin-bottom: 16px;">
                        <h4 style="font-weight: 700; color: #1b5e3a; margin-bottom: 8px;">{{ ucfirst($module) }}</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
                            @foreach ($perms as $permission)
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission['id'] }}">
                                    <span style="font-size: 14px;">{{ $permission['nom'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="actions-group" style="justify-content: flex-end; margin-top: 24px;">
                    <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Retour</a>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>

        </section>
    </section>
</main>
@endsection
