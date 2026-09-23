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
        @if (!empty($permissionsError))
            <div class="alert alert-danger">{{ $permissionsError }}</div>
        @endif

        @if(session('success'))
            <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                <strong>Erreurs :</strong>
                <ul style="margin: 4px 0 0 20px;">
                    @foreach($errors->all() as $error)
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
                <button type="button" class="btn-primary" data-permission-modal="create">
                    <i class="fas fa-plus"></i> Nouvelle permission
                </button>
                <button class="btn-secondary" type="button" id="btn-exporter">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
        </div>

        <!-- Filtres -->
        <section class="filter-panel" aria-label="Filtres">
            <div class="form-group">
                <label for="filter-permission-search">Rechercher une permission</label>
                <input
                    type="text"
                    class="form-control"
                    id="filter-permission-search"
                    list="permissions-datalist"
                    placeholder="Nom de la permission..."
                    autocomplete="off"
                >
                <datalist id="permissions-datalist">
                    @foreach (collect($permissionsData)->pluck('nom')->filter()->unique() as $nom)
                        <option value="{{ $nom }}"></option>
                    @endforeach
                </datalist>
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
                            <th>Permission</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissionsData as $permission)
                        <tr
                            data-permission="{{ $permission['nom'] ?? '' }}"
                            data-statut="{{ ($permission['est_actif'] ?? false) ? '1' : '0' }}"
                            data-id="{{ $permission['id'] ?? '' }}"
                            data-description="{{ $permission['description'] ?? '' }}"
                        >
                            <td><strong>{{ $permission['nom'] ?? '-' }}</strong></td>

                            <td>
                                <span class="badge {{ ($permission['est_actif'] ?? false) ? 'badge-success' : 'badge-danger' }}">
                                    {{ ($permission['est_actif'] ?? false) ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions-inline">
                                    <a href="{{ route('admin.permissions.show', $permission['id']) }}" class="table-action">Voir</a>
                                    <a
                                        href="{{ route('admin.permissions.edit', $permission['id']) }}"
                                        class="table-action"
                                        data-permission-edit-trigger
                                    >Modifier</a>
                                    <form action="{{ route('admin.permissions.destroy', $permission['id']) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-action delete"
                                                onclick="return confirm('Supprimer cette permission ?')">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                <i class="fas fa-inbox" style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                Aucune permission trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="empty-message" id="empty-message-filtre" style="display: none;">Aucun résultat pour ce filtre.</p>

            <!-- Pagination (gérée en JS) -->
            <div class="pagination" aria-label="Pagination" id="pagination-container"></div>
        </section>
    </section>


    <!-- MODAL AJOUT PERMISSION -->
    <div
        id="permission-modal"
        class="role-modal"
        hidden
        aria-hidden="true"
    >

        <div
            class="role-modal__backdrop"
            data-permission-modal-close
        ></div>


        <section
            class="role-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="permission-modal-title"
        >

            <div class="role-modal__header">

                <h2 id="permission-modal-title">
                    Ajouter une permission
                </h2>

                <button
                    type="button"
                    class="role-modal__close"
                    aria-label="Fermer"
                    data-permission-modal-close
                >
                    &times;
                </button>

            </div>


            <form
                id="permission-form"
                method="POST"
                action="{{ route('admin.permissions.store') }}"
            >

                @csrf

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="permission-nom">Nom *</label>
                    <input
                        type="text"
                        id="permission-nom"
                        name="nom"
                        class="form-control"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="permission-description">Description</label>
                    <textarea
                        id="permission-description"
                        name="description"
                        rows="3"
                        class="form-control"
                    ></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="permission-est-actif" name="est_actif" value="1" checked>
                        <span style="font-size: 14px; font-weight: 500;">Actif</span>
                    </label>
                </div>

                <div
                    class="actions-group"
                    style="justify-content:flex-end; margin-top:24px;"
                >

                    <button
                        type="button"
                        class="btn-secondary"
                        data-permission-modal-close
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        <i class="fas fa-save"></i> Enregistrer
                    </button>

                </div>

            </form>

        </section>

    </div>


    <!-- MODAL MODIFIER PERMISSION -->
    <div
        id="permission-edit-modal"
        class="role-modal"
        hidden
        aria-hidden="true"
    >

        <div
            class="role-modal__backdrop"
            data-permission-edit-modal-close
        ></div>


        <section
            class="role-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="permission-edit-modal-title"
        >

            <div class="role-modal__header">

                <h2 id="permission-edit-modal-title">
                    Modifier la permission
                </h2>

                <button
                    type="button"
                    class="role-modal__close"
                    aria-label="Fermer"
                    data-permission-edit-modal-close
                >
                    &times;
                </button>

            </div>


            <form
                id="permission-edit-form"
                method="POST"
                action=""
            >

                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="permission-edit-nom">Nom *</label>
                    <input
                        type="text"
                        id="permission-edit-nom"
                        name="nom"
                        class="form-control"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="permission-edit-description">Description</label>
                    <textarea
                        id="permission-edit-description"
                        name="description"
                        rows="3"
                        class="form-control"
                    ></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="permission-edit-est-actif" name="est_actif" value="1">
                        <span style="font-size: 14px; font-weight: 500;">Actif</span>
                    </label>
                </div>

                <div
                    class="actions-group"
                    style="justify-content:flex-end; margin-top:24px;"
                >

                    <button
                        type="button"
                        class="btn-secondary"
                        data-permission-edit-modal-close
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>

                </div>

            </form>

        </section>

    </div>


    @push('styles')
    <style>

        .role-modal[hidden] {
            display: none;
        }

        .role-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: grid;
            place-items: center;
            padding: 1rem;
        }

        .role-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(17, 24, 39, .55);
        }

        .role-modal__dialog {
            position: relative;
            width: min(600px, calc(100vw - 40px));
            max-width: 600px;
            max-height: calc(100vh - 40px);
            overflow: auto;
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 24px 50px rgba(0, 0, 0, .2);
        }

        .role-modal__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .role-modal__header h2 {
            margin: 0;
            font-size: 1.25rem;
        }

        .role-modal__close {
            border: 0;
            background: none;
            font-size: 2rem;
            line-height: 1;
            cursor: pointer;
        }

        @media (max-width: 700px) {

            .role-modal {
                padding: .5rem;
            }

            .role-modal__dialog {
                width: calc(100vw - 16px);
                max-height: calc(100vh - 16px);
                padding: 1rem;
            }

        }

    </style>
    @endpush

    @push('scripts')
    <script>
        (function () {
            const btnFiltrer = document.getElementById('btn-filtrer');
            const btnReset = document.getElementById('btn-reset-filtres');
            const searchInput = document.getElementById('filter-permission-search');
            const selectStatut = document.getElementById('filter-statut');
            const allRows = Array.from(document.querySelectorAll('#moduleTable tbody tr[data-permission]'));
            const emptyMessage = document.getElementById('empty-message-filtre');
            const paginationContainer = document.getElementById('pagination-container');

            const perPage = 10;
            let currentPage = 1;

            function getFilteredRows() {
                const query = (searchInput.value || '').trim().toLowerCase();
                const statut = selectStatut ? selectStatut.value : '';

                return allRows.filter(function (row) {
                    const permission = (row.dataset.permission || '').toLowerCase();
                    const matchQuery = !query || permission.includes(query);
                    const matchStatut = !statut || row.dataset.statut === statut;
                    return matchQuery && matchStatut;
                });
            }

            function renderPagination(totalPages) {
                paginationContainer.innerHTML = '';

                if (totalPages <= 1) {
                    return;
                }

                function makeButton(label, page, disabled, active) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'page-btn' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
                    btn.textContent = label;
                    if (!disabled) {
                        btn.addEventListener('click', function () {
                            currentPage = page;
                            render();
                        });
                    }
                    return btn;
                }

                paginationContainer.appendChild(
                    makeButton('←', currentPage - 1, currentPage === 1, false)
                );

                for (let i = 1; i <= totalPages; i++) {
                    paginationContainer.appendChild(
                        makeButton(String(i), i, false, i === currentPage)
                    );
                }

                paginationContainer.appendChild(
                    makeButton('→', currentPage + 1, currentPage === totalPages, false)
                );
            }

            function render() {
                const filtered = getFilteredRows();
                const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));

                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                const visibleThisPage = filtered.slice(start, end);

                allRows.forEach(function (row) {
                    row.style.display = 'none';
                });

                visibleThisPage.forEach(function (row) {
                    row.style.display = '';
                });

                emptyMessage.style.display = filtered.length === 0 ? 'block' : 'none';

                renderPagination(totalPages);
            }

            function applyFilters() {
                currentPage = 1;
                render();
            }

            if (btnFiltrer) {
                btnFiltrer.addEventListener('click', applyFilters);
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }

            if (selectStatut) {
                selectStatut.addEventListener('change', applyFilters);
            }

            if (btnReset) {
                btnReset.addEventListener('click', function () {
                    searchInput.value = '';
                    if (selectStatut) {
                        selectStatut.value = '';
                    }
                    applyFilters();
                });
            }

            /*
             * ================================
             * EXPORT CSV
             * ================================
             */

            const btnExporter = document.getElementById('btn-exporter');

            if (btnExporter) {
                btnExporter.addEventListener('click', function () {
                    const filtered = getFilteredRows();

                    if (filtered.length === 0) {
                        alert('Aucune permission à exporter.');
                        return;
                    }

                    let csv = 'Permission,Statut\n';

                    filtered.forEach(function (row) {
                        const nom = row.dataset.permission || '';
                        const statut = row.dataset.statut === '1' ? 'Actif' : 'Inactif';
                        csv += '"' + nom.replace(/"/g, '""') + '","' + statut + '"\n';
                    });

                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');

                    link.href = url;
                    link.download = 'permissions.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }

            /*
             * ================================
             * MODAL PERMISSION (CREATE)
             * ================================
             */

            const permissionModal = document.getElementById('permission-modal');
            const permissionForm = document.getElementById('permission-form');

            function openPermissionModal() {
                permissionModal.hidden = false;
                permissionModal.setAttribute('aria-hidden', 'false');
                document.getElementById('permission-nom').focus();
            }

            function closePermissionModal() {
                permissionModal.hidden = true;
                permissionModal.setAttribute('aria-hidden', 'true');
                permissionForm.reset();
            }

            document.querySelectorAll('[data-permission-modal="create"]').forEach(function (button) {
                button.addEventListener('click', openPermissionModal);
            });

            document.querySelectorAll('[data-permission-modal-close]').forEach(function (button) {
                button.addEventListener('click', closePermissionModal);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && permissionModal && !permissionModal.hidden) {
                    closePermissionModal();
                }
            });

            @if($errors->any())
                openPermissionModal();
            @endif

            /*
             * ================================
             * MODAL PERMISSION (EDIT)
             * ================================
             */

            const permissionEditModal = document.getElementById('permission-edit-modal');
            const permissionEditForm = document.getElementById('permission-edit-form');
            const editNomInput = document.getElementById('permission-edit-nom');
            const editDescriptionInput = document.getElementById('permission-edit-description');
            const editActifInput = document.getElementById('permission-edit-est-actif');

            // Template d'URL avec un id factice (0) qu'on remplace dynamiquement.
            const editUrlTemplate = "{{ route('admin.permissions.update', ['id' => '__ID__']) }}";

            function openPermissionEditModal(row) {
                const id = row.dataset.id || '';
                const nom = row.dataset.permission || '';
                const description = row.dataset.description || '';
                const isActif = row.dataset.statut === '1';

                permissionEditForm.action = editUrlTemplate.replace('__ID__', encodeURIComponent(id));
                editNomInput.value = nom;
                editDescriptionInput.value = description;
                editActifInput.checked = isActif;

                permissionEditModal.hidden = false;
                permissionEditModal.setAttribute('aria-hidden', 'false');
                editNomInput.focus();
            }

            function closePermissionEditModal() {
                permissionEditModal.hidden = true;
                permissionEditModal.setAttribute('aria-hidden', 'true');
                permissionEditForm.reset();
            }

            document.querySelectorAll('[data-permission-edit-trigger]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const row = link.closest('tr[data-permission]');
                    if (row) {
                        openPermissionEditModal(row);
                    }
                });
            });

            document.querySelectorAll('[data-permission-edit-modal-close]').forEach(function (button) {
                button.addEventListener('click', closePermissionEditModal);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && permissionEditModal && !permissionEditModal.hidden) {
                    closePermissionEditModal();
                }
            });

            /*
             * Initialisation
             */

            render();
        })();
    </script>
    @endpush
</main>
@endsection