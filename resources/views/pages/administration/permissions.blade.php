@extends('layouts.app')

@section('title', 'SICORE - Permissions')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <x-topbar title="Permissions" subtitle="Gestion Utilisateur > Permissions" icon="fa-solid fa-lock" />

    @php
        $permissionsData = $permissions['data'] ?? [];

        $countByGroupe = collect($permissionsData)
            ->countBy(fn ($p) => $p['groupe'] ?? 'Non classé')
            ->sortDesc();

        $topGroupes = $countByGroupe->take(3);

        $iconsCycle = ['fa-solid fa-eye', 'fa-solid fa-check-double', 'fa-solid fa-shield-halved'];
        $colorsCycle = ['green', 'yellow', 'red'];
    @endphp

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
                <li>Visualiser les droits par module.</li>
                <li>Séparer les permissions de consultation, saisie, validation et administration.</li>
                <li>Préparer les paramètres sans inventer de backend.</li>
            </ul>
        </section>

        <!-- Statistiques -->
        <div class="stats-grid four">
            <article class="stat-card">
                <div>
                    <p class="stat-label">Permissions</p>
                    <p class="stat-value">{{ count($permissionsData) }}</p>
                    <p class="stat-note">Droits définis</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-lock"></i>
                </span>
            </article>

            @foreach ($topGroupes as $groupeNom => $groupeCount)
                <article class="stat-card">
                    <div>
                        <p class="stat-label">{{ ucfirst($groupeNom) }}</p>
                        <p class="stat-value">{{ $groupeCount }}</p>
                        <p class="stat-note">Permissions</p>
                    </div>
                    <span class="stat-icon {{ $colorsCycle[$loop->index] ?? 'blue' }}">
                        <i class="{{ $iconsCycle[$loop->index] ?? 'fa-solid fa-circle' }}"></i>
                    </span>
                </article>
            @endforeach
        </div>

        <!-- Actions -->
        <div class="actions-row">
            <p class="breadcrumb">Gestion Utilisateur > Permissions</p>
            <div class="actions-group">
                <button type="button" class="btn-primary" id="btn-open-modal-permission">
                    <i class="fas fa-plus"></i> Nouvelle permission
                </button>
                <button class="btn-secondary" type="button">Exporter</button>
                <a href="{{ route('admin.permissions.sync') }}" class="btn-warning" style="background: #f59e0b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-sync"></i> Synchroniser
                </a>
            </div>
        </div>

        <!-- Filtres -->
        <section class="filter-panel" aria-label="Filtres">
            <div class="form-group">
                <label for="filter-module">Module</label>
                <select class="form-control" id="filter-module">
                    <option value="">Tous</option>
                    @foreach (collect($permissionsData)->pluck('module')->filter()->unique() as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="filter-permission">Permission</label>
                <select class="form-control" id="filter-permission">
                    <option value="">Tous</option>
                    @foreach (collect($permissionsData)->pluck('nom')->filter()->unique() as $nom)
                        <option value="{{ $nom }}">{{ $nom }}</option>
                    @endforeach
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
                            <th>Module</th>
                            <th>Permission</th>
                            <th>Groupe</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissionsData as $permission)
                        <tr data-module="{{ $permission['module'] ?? '' }}" data-permission="{{ $permission['nom'] ?? '' }}">
                            <td>{{ $permission['module'] ?? '-' }}</td>
                            <td><strong>{{ $permission['nom'] ?? '-' }}</strong></td>
                            <td>{{ $permission['groupe'] ?? '-' }}</td>
                            <td>
                                <span class="badge {{ ($permission['est_actif'] ?? false) ? 'badge-success' : 'badge-danger' }}">
                                    {{ ($permission['est_actif'] ?? false) ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.permissions.edit', $permission['id']) }}" class="table-action">
                                        Modifier
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission['id']) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-action danger" 
                                                onclick="return confirm('Supprimer cette permission ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                <i class="fas fa-inbox" style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                Aucune permission trouvée
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

    <!-- Modale : Nouvelle permission -->
    <div class="modal-overlay" id="modal-nouvelle-permission" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Nouvelle permission</h3>
                <button type="button" class="modal-close" id="btn-close-modal-permission">&times;</button>
            </div>
            <form action="{{ route('admin.permissions.store') }}" method="POST" id="form-permission">
                @csrf
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-p-nom">Nom *</label>
                        <input type="text" id="modal-p-nom" name="nom" required class="form-control" placeholder="Ex: Gérer les utilisateurs">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-p-slug">Slug</label>
                        <input type="text" id="modal-p-slug" name="slug" class="form-control" readonly 
                               style="background:#f3f4f6; cursor:not-allowed; color:#6b7280;" 
                               placeholder="Généré automatiquement">
                        <small style="color: #6b7280; font-size: 12px;">Généré automatiquement à partir du nom</small>
                    </div>
                    <div class="filter-panel" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label for="modal-p-groupe">Groupe *</label>
                            <input type="text" id="modal-p-groupe" name="groupe" required class="form-control" placeholder="Ex: administration">
                        </div>
                        <div class="form-group">
                            <label for="modal-p-module">Module *</label>
                            <input type="text" id="modal-p-module" name="module" required class="form-control" placeholder="Ex: users">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-p-action">Action *</label>
                        <input type="text" id="modal-p-action" name="action" required class="form-control" placeholder="Ex: manage">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="modal-p-description">Description</label>
                        <textarea id="modal-p-description" name="description" rows="2" class="form-control" placeholder="Décrivez cette permission..."></textarea>
                    </div>
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="est_actif" value="1" checked>
                            <span>Actif</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btn-modal-annuler-permission">Annuler</button>
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
            // 1. GESTION DE LA MODALE PERMISSION
            // ============================================================
            const modal = document.getElementById('modal-nouvelle-permission');
            const btnOpen = document.getElementById('btn-open-modal-permission');
            const btnClose = document.getElementById('btn-close-modal-permission');
            const btnAnnuler = document.getElementById('btn-modal-annuler-permission');
            const form = document.getElementById('form-permission');

            function openModal() {
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    form?.reset();
                    document.getElementById('modal-p-slug').value = '';
                    setTimeout(() => {
                        document.getElementById('modal-p-nom').focus();
                    }, 100);
                }
            }

            function closeModal() {
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            if (btnOpen) {
                btnOpen.addEventListener('click', openModal);
            }

            if (btnClose) {
                btnClose.addEventListener('click', closeModal);
            }

            if (btnAnnuler) {
                btnAnnuler.addEventListener('click', closeModal);
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
                    closeModal();
                }
            });

            // ============================================================
            // 2. GÉNÉRATION AUTOMATIQUE DU SLUG (PERMISSION)
            // ============================================================
            const nomInput = document.getElementById('modal-p-nom');
            const slugInput = document.getElementById('modal-p-slug');

            function generateSlug(value) {
                return value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            if (nomInput && slugInput) {
                nomInput.addEventListener('input', function() {
                    slugInput.value = generateSlug(this.value);
                });

                if (nomInput.value) {
                    slugInput.value = generateSlug(nomInput.value);
                }
            }

            // ============================================================
            // 3. FILTRES
            // ============================================================
            const btnFiltrer = document.getElementById('btn-filtrer');
            const btnReset = document.getElementById('btn-reset-filtres');
            const selectModule = document.getElementById('filter-module');
            const selectPermission = document.getElementById('filter-permission');
            const rows = document.querySelectorAll('#moduleTable tbody tr[data-module]');
            const emptyMessage = document.getElementById('empty-message-filtre');

            function applyFilters() {
                const module = selectModule.value;
                const permission = selectPermission.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchModule = !module || row.dataset.module === module;
                    const matchPermission = !permission || row.dataset.permission === permission;
                    const visible = matchModule && matchPermission;

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
                    selectModule.value = '';
                    selectPermission.value = '';
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
                    const nom = document.getElementById('modal-p-nom').value.trim();
                    if (!nom) {
                        e.preventDefault();
                        alert('Le nom est obligatoire.');
                        document.getElementById('modal-p-nom').focus();
                    }
                });
            }

        })();
    </script>
    @endpush
</main>
@endsection