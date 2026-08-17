@extends('layouts.app')

@section('title', 'SICORE - Modifier une permission')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <x-topbar title="Modifier une permission" subtitle="Gestion Utilisateur > Permissions > Modifier" icon="fa-solid fa-lock" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            @if(session('success'))
                <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:#fee2e2; border:1px solid #dc2626; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    <strong>Erreur :</strong>
                    <ul style="margin: 4px 0 0 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.permissions.update', $permission['id']) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" value="{{ $permission['nom'] }}" required class="form-control">
                    @error('nom') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="slug">Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ $permission['slug'] }}" required class="form-control">
                    @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="filter-panel" aria-label="Groupe et module" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="groupe">Groupe *</label>
                        <input type="text" id="groupe" name="groupe" value="{{ $permission['groupe'] }}" required class="form-control">
                        @error('groupe') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="module">Module *</label>
                        <input type="text" id="module" name="module" value="{{ $permission['module'] }}" required class="form-control">
                        @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="action">Action *</label>
                    <input type="text" id="action" name="action" value="{{ $permission['action'] }}" required class="form-control">
                    @error('action') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ $permission['description'] }}</textarea>
                    @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="est_actif" value="1" @checked($permission['est_actif'] ?? false)>
                        <span style="font-size: 14px; font-weight: 500;">Actif</span>
                    </label>
                </div>

                <div class="actions-group" style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('admin.permissions.index') }}" class="btn-secondary" style="padding: 10px 20px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; background: #f9fafb; text-decoration: none; transition: all 0.2s;">
                        Annuler
                    </a>
                    <button type="submit" class="btn-primary" style="padding: 10px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>

        </section>
    </section>
</main>
@endsection