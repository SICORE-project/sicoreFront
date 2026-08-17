@extends('layouts.app')

@section('title', 'SICORE - Créer une permission')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <x-topbar title="Nouvelle permission" subtitle="Gestion Utilisateur > Permissions > Nouvelle" icon="fa-solid fa-lock" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            @if ($errors->any())
                <div style="background:#fee2e2; border:1px solid #dc2626; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    <strong>Erreur :</strong>
                    <ul style="margin: 4px 0 0 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                <!-- Nom -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required class="form-control" placeholder="Ex: Gérer les utilisateurs">
                    @error('nom') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <!-- Slug (généré automatiquement) -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control" readonly 
                           style="background:#f3f4f6; cursor:not-allowed; color:#6b7280;" 
                           placeholder="Généré automatiquement">
                    <small style="color: #6b7280; font-size: 12px;">Généré automatiquement à partir du nom</small>
                    @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <!-- Groupe et Module -->
                <div class="filter-panel" aria-label="Groupe et module" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="groupe">Groupe *</label>
                        <input type="text" id="groupe" name="groupe" required class="form-control" placeholder="Ex: administration">
                        @error('groupe') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="module">Module *</label>
                        <input type="text" id="module" name="module" required class="form-control" placeholder="Ex: users">
                        @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Action -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="action">Action *</label>
                    <input type="text" id="action" name="action" required class="form-control" placeholder="Ex: manage">
                    @error('action') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control" placeholder="Décrivez cette permission..."></textarea>
                    @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <!-- Actif -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="est_actif" value="1" checked>
                        <span style="font-size: 14px; font-weight: 500;">Actif</span>
                    </label>
                </div>

                <!-- Boutons -->
                <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                    <a href="{{ route('admin.permissions.index') }}" class="btn-secondary" style="padding: 10px 20px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; background: #f9fafb; text-decoration: none; transition: all 0.2s;">
                        Annuler
                    </a>
                    <button type="submit" class="btn-primary" style="padding: 10px 24px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>

        </section>
    </section>
</main>

@push('scripts')
<script>
    (function () {
        // Génération automatique du slug à partir du nom
        const nomInput = document.getElementById('nom');
        const slugInput = document.getElementById('slug');

        function generateSlug(value) {
            return value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        if (nomInput && slugInput) {
            // Génération en temps réel
            nomInput.addEventListener('input', function() {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
            });

            // Si le nom est déjà rempli (erreur de validation)
            if (nomInput.value) {
                slugInput.value = generateSlug(nomInput.value);
            }

            // Désactiver la saisie manuelle du slug
            slugInput.addEventListener('keydown', function(e) {
                e.preventDefault();
                return false;
            });
        }
    })();
</script>
@endpush
@endsection