@extends('layouts.app')

@section('title', 'SICORE - Profils / Rôles')

@section('content')
    <main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
        <x-topbar title="Profils / Rôles" subtitle="Gestion Utilisateur > Profils / Rôles" icon="fa-solid fa-users-cog" />

        <section class="content-area">
            @if (session('success'))
                <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    {{ session('success') }}
                </div>
            @endif

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

            <!-- Objectifs métier -->
            <section class="objective-card">
                <h2>Objectifs métier</h2>
                <ul class="objective-list">
                    <li>Décrire les responsabilités fonctionnelles.</li>
                    <li>Associer les rôles aux modules SICORE.</li>
                    <li>Limiter les accès aux besoins réels des services.</li>
                </ul>
            </section>

            <!-- Statistiques -->
            <div class="stats-grid four">
                <article class="stat-card">
                    <div>
                        <p class="stat-label">Profils</p>
                        <p class="stat-value">{{ isset($roles['data']) ? count($roles['data']) : 0 }}</p>
                        <p class="stat-note">Rôles actifs</p>
                    </div>
                    <span class="stat-icon blue">
                        <i class="fa-solid fa-users-cog"></i>
                    </span>
                </article>
                <article class="stat-card">
                    <div>
                        <p class="stat-label">Niveaux distincts</p>
                        <p class="stat-value">{{ isset($roles['data']) ? collect($roles['data'])->pluck('niveau')->filter()->unique()->count() : 0 }}</p>
                        <p class="stat-note">Hiérarchie d'accès</p>
                    </div>
                    <span class="stat-icon green">
                        <i class="fa-solid fa-th-large"></i>
                    </span>
                </article>
                <article class="stat-card">
                    <div>
                        <p class="stat-label">En revue</p>
                        <p class="stat-value">{{ isset($roles['data']) ? collect($roles['data'])->where('est_actif', false)->count() : 0 }}</p>
                        <p class="stat-note">Validation interne</p>
                    </div>
                    <span class="stat-icon yellow">
                        <i class="fa-solid fa-clock"></i>
                    </span>
                </article>
                <article class="stat-card">
                    <div>
                        <p class="stat-label">Sensibles</p>
                        <p class="stat-value">{{ isset($roles['data']) ? collect($roles['data'])->where('niveau', 'systeme')->count() : 0 }}</p>
                        <p class="stat-note">Accès renforcés</p>
                    </div>
                    <span class="stat-icon red">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </article>
            </div>

            <!-- Actions -->
            <div class="actions-row">
                <p class="breadcrumb">Gestion Utilisateur > Profils / Rôles</p>
                <div class="actions-group">
                    <button type="button" class="btn-primary" id="btn-open-modal">
                        <i class="fas fa-plus"></i> Nouveau profil
                    </button>
                    <button class="btn-secondary" type="button">Exporter</button>
                </div>
            </div>

            <!-- Filtres -->
            <section class="filter-panel" aria-label="Filtres">
                <div class="form-group">
                    <label for="filter-niveau">Niveau</label>
                    <select class="form-control" id="filter-niveau">
                        <option value="">Tous</option>
                        @foreach (collect($roles['data'] ?? [])->pluck('niveau')->filter()->unique() as $niveau)
                            <option value="{{ $niveau }}">{{ ucfirst($niveau) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-statut">Statut</label>
                    <select class="form-control" id="filter-statut">
                        <option value="">Tous</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
                <div class="actions-group">
                    <button class="btn-secondary" type="button" id="btn-filtrer">Filtrer</button>
                    <button class="btn-secondary" type="button" id="btn-reset-filtres">Réinitialiser</button>
                </div>
            </section>

            <!-- Tableau -->
            <section class="table-card">
                <div class="table-responsive">
                    <table class="table" id="moduleTable">
                        <thead>
                            <tr>
                                <th>Profil</th>
                                <th>Description</th>
                                <th>Niveau d'accès</th>
                                <th>Statut</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($roles['data'] ?? []) as $role)
                                <tr data-niveau="{{ $role['niveau'] ?? '' }}" data-statut="{{ ($role['est_actif'] ?? false) ? '1' : '0' }}">
                                    <td><strong>{{ $role['nom'] ?? '-' }}</strong></td>
                                    <td>{{ $role['description'] ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge 
                                    @if ($role['niveau'] == 'systeme') badge-danger
                                    @elseif($role['niveau'] == 'admin_metier') badge-purple
                                    @elseif($role['niveau'] == 'gestionnaire') badge-info
                                    @else badge-secondary @endif">
                                            {{ ucfirst($role['niveau'] ?? '') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $role['est_actif'] ?? false ? 'badge-success' : 'badge-danger' }}">
                                            {{ $role['est_actif'] ?? false ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.roles.show', $role['id']) }}" class="table-action">
                                                Voir
                                            </a>
                                            <a href="{{ route('admin.roles.edit', $role['id']) }}" class="table-action">
                                                Modifier
                                            </a>
                                            <form action="{{ route('admin.roles.destroy', $role['id']) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="table-action danger"
                                                    onclick="return confirm('Supprimer ce rôle ?')"> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-inbox"
                                            style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                        Aucun rôle trouvé
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="empty-message" id="empty-message-filtre" style="display: none;">Aucun résultat pour ce filtre.</p>
                <p class="empty-message">Aucune donnée trouvée.</p>
                <div class="pagination" aria-label="Pagination">
                    <button class="page-btn" type="button">←</button>
                    <button class="page-btn active" type="button">1</button>
                    <button class="page-btn" type="button">2</button>
                    <button class="page-btn" type="button">→</button>
                </div>
            </section>
        </section>
    </main>

    <!-- Modale : Nouveau profil -->
    <div class="modal-overlay" id="modal-nouveau-role" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Nouveau profil</h3>
                <button type="button" class="modal-close" id="btn-close-modal">&times;</button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST" id="form-nouveau-role">
                @csrf
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-nom">Nom *</label>
                        <input type="text" id="modal-nom" name="nom" required class="form-control" placeholder="Ex: Administrateur">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-slug">Slug</label>
                        <input type="text" id="modal-slug" name="slug" class="form-control" readonly style="background:#f3f4f6; cursor:not-allowed;">
                        <small style="color: #6b7280; font-size: 12px;">Généré automatiquement à partir du nom</small>
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-description">Description</label>
                        <textarea id="modal-description" name="description" rows="2" class="form-control" placeholder="Décrire les responsabilités..."></textarea>
                    </div>
                    <div class="filter-panel" style="margin-bottom: 0;">
                        <div class="form-group">
                            <label for="modal-niveau">Niveau</label>
                            <select id="modal-niveau" name="niveau" class="form-control">
                                <option value="systeme">Système</option>
                                <option value="admin_metier">Admin Métier</option>
                                <option value="gestionnaire">Gestionnaire</option>
                                <option value="consultation">Consultation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modal-est_actif">Statut</label>
                            <select id="modal-est_actif" name="est_actif" class="form-control">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                    <p style="font-size: 13px; color: #6b7280; margin-top: 12px;">
                        Les permissions pourront être assignées après la création, via l'action "Voir" du rôle.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btn-modal-annuler">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #dc2626;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function () {
            // ============================================================
            // 1. GESTION DE LA MODALE
            // ============================================================
            const modal = document.getElementById('modal-nouveau-role');
            const btnOpen = document.getElementById('btn-open-modal');
            const btnClose = document.getElementById('btn-close-modal');
            const btnAnnuler = document.getElementById('btn-modal-annuler');
            const form = document.getElementById('form-nouveau-role');

            // Ouvrir la modale
            function openModal() {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                // Réinitialiser le formulaire
                form.reset();
                document.getElementById('modal-slug').value = '';
                // Focus sur le premier champ
                setTimeout(() => {
                    document.getElementById('modal-nom').focus();
                }, 100);
            }

            // Fermer la modale
            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            // Événements d'ouverture
            if (btnOpen) {
                btnOpen.addEventListener('click', openModal);
            }

            // Événements de fermeture
            if (btnClose) {
                btnClose.addEventListener('click', closeModal);
            }

            if (btnAnnuler) {
                btnAnnuler.addEventListener('click', closeModal);
            }

            // Fermer en cliquant à l'extérieur
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            // Fermer avec la touche Echap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });

            // ============================================================
            // 2. GÉNÉRATION AUTOMATIQUE DU SLUG
            // ============================================================
            const nomInput = document.getElementById('modal-nom');
            const slugInput = document.getElementById('modal-slug');

            function generateSlug(value) {
                return value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            if (nomInput && slugInput) {
                // Génération en temps réel
                nomInput.addEventListener('input', function() {
                    slugInput.value = generateSlug(this.value);
                });

                // Si le nom est déjà rempli (erreur de validation)
                if (nomInput.value) {
                    slugInput.value = generateSlug(nomInput.value);
                }
            }

            // ============================================================
            // 3. FILTRES
            // ============================================================
            const btnFiltrer = document.getElementById('btn-filtrer');
            const btnReset = document.getElementById('btn-reset-filtres');
            const selectNiveau = document.getElementById('filter-niveau');
            const selectStatut = document.getElementById('filter-statut');
            const rows = document.querySelectorAll('#moduleTable tbody tr[data-niveau]');
            const emptyMessage = document.getElementById('empty-message-filtre');

            function applyFilters() {
                const niveau = selectNiveau.value;
                const statut = selectStatut.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchNiveau = !niveau || row.dataset.niveau === niveau;
                    const matchStatut = !statut || row.dataset.statut === statut;
                    const visible = matchNiveau && matchStatut;

                    row.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                if (emptyMessage) {
                    emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            if (btnFiltrer) {
                btnFiltrer.addEventListener('click', applyFilters);
            }

            if (btnReset) {
                btnReset.addEventListener('click', function () {
                    selectNiveau.value = '';
                    selectStatut.value = '';
                    applyFilters();
                });
            }

            // ============================================================
            // 4. RÉOUVERTURE AUTOMATIQUE EN CAS D'ERREUR
            // ============================================================
            @if ($errors->any())
                openModal();
            @endif

            // ============================================================
            // 5. VALIDATION DU FORMULAIRE
            // ============================================================
            if (form) {
                form.addEventListener('submit', function(e) {
                    const nom = document.getElementById('modal-nom').value.trim();
                    if (!nom) {
                        e.preventDefault();
                        alert('Le nom est obligatoire.');
                        document.getElementById('modal-nom').focus();
                    }
                });
            }

        })();
    </script>
    @endpush
@endsection