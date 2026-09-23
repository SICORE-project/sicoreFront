@extends('layouts.app')

@section('title', 'SICORE - Profils / Rôles')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">

    <x-topbar
        title="Profils / Rôles"
        subtitle="Gestion Utilisateur > Profils / Rôles"
        icon="fa-solid fa-users-cog"
    />

    @php
        $rolesData = $roles['data'] ?? [];
        // Accepte une liste plate OU une réponse ['data' => [...]]
        $permissionsList = $permissions['data'] ?? ($permissions ?? []);
    @endphp

    <section class="content-area">

        @if (!empty($rolesError))
    <div class="alert alert-danger">{{ $rolesError }}</div>
@endif

@if(session('success'))
    <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        <strong>Erreurs :</strong>
        <ul style="margin: 4px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ is_array($error) ? implode(', ', $error) : $error }}</li>
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
                    <p class="stat-value">{{ count($rolesData) }}</p>
                    <p class="stat-note">Rôles</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-users-cog"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Actifs</p>
                    <p class="stat-value">{{ collect($rolesData)->where('est_actif', true)->count() }}</p>
                    <p class="stat-note">Rôles actifs</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-check-circle"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Inactifs</p>
                    <p class="stat-value">{{ collect($rolesData)->where('est_actif', false)->count() }}</p>
                    <p class="stat-note">Rôles désactivés</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-clock"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Permissions</p>
                    <p class="stat-value">{{ count($permissionsList) }}</p>
                    <p class="stat-note">Permissions disponibles</p>
                </div>
                <span class="stat-icon red">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
            </article>

        </div>


        <!-- Actions -->
        <div class="actions-row">

            <p class="breadcrumb">
                Gestion Utilisateur > Profils / Rôles
            </p>

            <div class="actions-group">

                <button
                    type="button"
                    class="btn-primary"
                    data-role-modal="create"
                >
                    <i class="fas fa-plus"></i>
                    Nouveau profil
                </button>

                <button
                    class="btn-secondary"
                    type="button"
                    id="btn-exporter"
                >
                    <i class="fas fa-download"></i> Exporter
                </button>

            </div>

        </div>


        <!-- Filtres -->
        <section
            class="filter-panel"
            aria-label="Filtres"
        >

            <div class="form-group">
                <label for="filter-role-search">Rechercher un rôle</label>
                <input
                    type="text"
                    class="form-control"
                    id="filter-role-search"
                    list="roles-datalist"
                    placeholder="Nom du rôle..."
                    autocomplete="off"
                >
                <datalist id="roles-datalist">
                    @foreach (collect($rolesData)->pluck('nom')->filter()->unique() as $nom)
                        <option value="{{ $nom }}"></option>
                    @endforeach
                </datalist>
            </div>


            <div class="form-group">

                <label for="filter-statut">
                    Statut
                </label>

                <select
                    class="form-control"
                    id="filter-statut"
                >
                    <option value="">Tous</option>
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>

            </div>


            <div class="actions-group">

                <button
                    class="btn-secondary"
                    type="button"
                    id="btn-filtrer"
                >
                    Filtrer
                </button>

                <button
                    class="btn-secondary"
                    type="button"
                    id="btn-reset-filtres"
                >
                    Réinitialiser
                </button>

            </div>

        </section>


        <!-- Tableau -->
        <section class="table-card">

            <div class="table-responsive">

                <table
                    class="table"
                    id="moduleTable"
                >

                    <thead>

                        <tr>
                            <th>Profil</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th class="actions-cell">
                                Actions
                            </th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse($rolesData as $role)

                            <tr
                                data-nom="{{ $role['nom'] ?? '' }}"
                                data-description="{{ $role['description'] ?? '' }}"
                                data-statut="{{ ($role['est_actif'] ?? false) ? '1' : '0' }}"
                            >

                                <td>
                                    <strong>
                                        {{ $role['nom'] ?? '-' }}
                                    </strong>
                                </td>


                                <td>
                                    {{ $role['description'] ?? '-' }}
                                </td>


                                <td>

                                    <span
                                        class="badge {{ ($role['est_actif'] ?? false) ? 'badge-success' : 'badge-danger' }}"
                                    >
                                        {{ ($role['est_actif'] ?? false) ? 'Actif' : 'Inactif' }}
                                    </span>

                                </td>


                                <td class="actions-cell">

                                    <div class="table-actions-inline">

                                        <a
                                            href="{{ route('admin.roles.show', $role['id']) }}"
                                            class="table-action"
                                        >Voir</a>


                                        <button
                                            type="button"
                                            class="table-action"
                                            data-role-modal="edit"
                                            data-role="{{ json_encode($role) }}"
                                        >
                                            Modifier
                                        </button>


                                        <form
                                            action="{{ route('admin.roles.destroy', $role['id']) }}"
                                            method="POST"
                                            style="display: inline;"
                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="table-action delete"
                                                onclick="return confirm('Supprimer ce rôle ?')"
                                            >
                                                Supprimer
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center"
                                >

                                    <i
                                        class="fas fa-inbox"
                                        style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"
                                    ></i>

                                    Aucun rôle trouvé

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            <p
                class="empty-message"
                id="empty-message-filtre"
                style="display: none;"
            >
                Aucun résultat pour ce filtre.
            </p>


            <!-- Pagination (gérée en JS) -->
            <div
                class="pagination"
                aria-label="Pagination"
                id="pagination-container"
            ></div>

        </section>

    </section>


    <!-- MODAL AJOUT / MODIFICATION -->
    <div
        id="role-modal"
        class="role-modal"
        hidden
        aria-hidden="true"
    >

        <div
            class="role-modal__backdrop"
            data-role-modal-close
        ></div>


        <section
            class="role-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="role-modal-title"
        >

            <div class="role-modal__header">

                <h2 id="role-modal-title">
                    Ajouter un rôle
                </h2>

                <button
                    type="button"
                    class="role-modal__close"
                    aria-label="Fermer"
                    data-role-modal-close
                >
                    &times;
                </button>

            </div>


            <form
                id="role-form"
                method="POST"
                action="{{ route('admin.roles.store') }}"
            >

                @csrf

                <input
                    type="hidden"
                    name="_method"
                    id="role-form-method"
                    value="POST"
                >


                <!-- Nom -->
                <div class="form-group">

                    <label for="role-nom">
                        Nom *
                    </label>

                    <input
                        id="role-nom"
                        name="nom"
                        class="form-control"
                        required
                        maxlength="50"
                    >

                </div>


                <!-- Description -->
                <div class="form-group">

                    <label for="role-description">
                        Description
                    </label>

                    <textarea
                        id="role-description"
                        name="description"
                        class="form-control"
                        rows="3"
                        maxlength="255"
                    ></textarea>

                </div>


                <!-- Permissions -->
                <div class="form-group role-permissions-group">

                    <div class="permissions-heading">

                        <div>
                            <label class="required">
                                Permissions *
                            </label>

                            <p class="permissions-description">
                                Sélectionnez une ou plusieurs permissions pour ce profil.
                            </p>
                        </div>

                        <div class="permissions-counter">
                            <strong id="selected-permissions-count">0</strong>
                            <span>sélectionnée(s)</span>
                        </div>

                    </div>


                    <!-- Actions globales -->
                    <div class="permissions-global-actions">

                        <button
                            type="button"
                            class="permission-action-btn"
                            id="select-all-permissions"
                        >
                            <i class="fa-solid fa-check-double"></i>
                            Tout sélectionner
                        </button>

                        <button
                            type="button"
                            class="permission-action-btn secondary"
                            id="deselect-all-permissions"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            Tout désélectionner
                        </button>

                    </div>


                    <!-- Liste plate des permissions -->
                    <div class="permissions-list">

                        @forelse($permissionsList as $permission)

                            <label class="permission-item">

                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission['id'] }}"
                                    class="permission-checkbox"
                                    {{ in_array($permission['id'], old('permissions', [])) ? 'checked' : '' }}
                                >

                                <span class="permission-checkmark">
                                    <i class="fa-solid fa-check"></i>
                                </span>

                                <span class="permission-content">

                                    <span class="permission-name">
                                        {{ $permission['nom'] }}
                                    </span>

                                    @if(!empty($permission['description']))

                                        <span class="permission-group">
                                            {{ $permission['description'] }}
                                        </span>

                                    @endif

                                </span>

                            </label>

                        @empty

                            <div class="permissions-empty">

                                <i class="fa-solid fa-shield-halved"></i>

                                <p>
                                    Aucune permission disponible.
                                </p>

                            </div>

                        @endforelse

                    </div>


                    @if($errors->has('permissions'))

                        <div class="invalid-feedback">
                            {{ $errors->first('permissions') }}
                        </div>

                    @endif


                    <span class="help-block">
                        Les permissions déterminent les actions accessibles à ce profil.
                    </span>

                </div>


                <!-- Statut -->
                <div class="form-group">

                    <label for="role-status">
                        Statut *
                    </label>

                    <select
                        id="role-status"
                        name="est_actif"
                        class="form-control"
                        required
                    >

                        <option value="1">
                            Actif
                        </option>

                        <option value="0">
                            Inactif
                        </option>

                    </select>

                </div>


                <!-- Actions -->
                <div
                    class="actions-group"
                    style="justify-content:flex-end; margin-top:24px;"
                >

                    <button
                        type="button"
                        class="btn-secondary"
                        data-role-modal-close
                    >
                        Annuler
                    </button>


                    <button
                        type="submit"
                        class="btn-primary"
                        id="role-form-submit"
                    >
                        Ajouter le rôle
                    </button>

                </div>

            </form>

        </section>

    </div>

</main>


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
        width: min(900px, calc(100vw - 40px));
        max-width: 900px;
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

    .role-modal .form-group {
        margin-bottom: 1rem;
    }


    /* =========================================================
       PERMISSIONS
       ========================================================= */

    .role-permissions-group {
        margin-top: 1.25rem;
    }

    .permissions-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 12px;
    }

    .permissions-heading label {
        display: block;
        margin-bottom: 4px;
        font-weight: 700;
        color: #111827;
    }

    .permissions-description {
        margin: 0;
        font-size: .85rem;
        color: #6b7280;
    }

    .permissions-counter {
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        padding: 7px 11px;
        border-radius: 8px;
        background: #f3f6fb;
        color: #4b5563;
        font-size: .82rem;
    }

    .permissions-counter strong {
        color: #2563eb;
        font-size: .95rem;
    }


    /* Actions globales */

    .permissions-global-actions {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .permission-action-btn {
        border: 1px solid #dbe3ef;
        background: #f8fafc;
        color: #374151;
        border-radius: 7px;
        padding: 7px 11px;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        transition: .2s ease;
    }

    .permission-action-btn:hover {
        background: #eef4ff;
        border-color: #bfd3ff;
        color: #2563eb;
    }

    .permission-action-btn.secondary:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }


    /* Liste des permissions */

    .permissions-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        max-height: 390px;
        overflow-y: auto;
        padding: 4px 4px 4px 0;
    }

    .permission-item {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px;
        border: 1px solid #edf0f4;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: .2s ease;
    }

    .permission-item:hover {
        border-color: #c7d8f7;
        background: #f8fbff;
    }


    /* Checkbox natif caché */

    .permission-checkbox {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }


    /* Case personnalisée */

    .permission-checkmark {
        width: 19px;
        height: 19px;
        flex: 0 0 19px;
        margin-top: 1px;
        border: 1.5px solid #cbd5e1;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #fff;
        transition: .2s ease;
    }

    .permission-checkmark i {
        font-size: 10px;
        opacity: 0;
        transform: scale(.6);
        transition: .15s ease;
    }


    /* Permission sélectionnée */

    .permission-checkbox:checked + .permission-checkmark {
        background: #2563eb;
        border-color: #2563eb;
    }

    .permission-checkbox:checked + .permission-checkmark i {
        opacity: 1;
        transform: scale(1);
    }

    .permission-checkbox:checked ~ .permission-content .permission-name {
        color: #1d4ed8;
    }

    .permission-item:has(.permission-checkbox:checked) {
        border-color: #bfdbfe;
        background: #eff6ff;
    }


    /* Contenu */

    .permission-content {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .permission-name {
        color: #374151;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .permission-group {
        color: #9ca3af;
        font-size: .72rem;
    }


    /* Aucun résultat */

    .permissions-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 130px;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        color: #9ca3af;
        text-align: center;
        grid-column: 1 / -1;
    }

    .permissions-empty i {
        font-size: 1.8rem;
        margin-bottom: 8px;
    }

    .permissions-empty p {
        margin: 0;
        font-size: .85rem;
    }


    /* Scrollbar */

    .permissions-list::-webkit-scrollbar {
        width: 6px;
    }

    .permissions-list::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }

    .permissions-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }


    /* Mobile */

    @media (max-width: 700px) {

        .role-modal {
            padding: .5rem;
        }

        .role-modal__dialog {
            width: calc(100vw - 16px);
            max-height: calc(100vh - 16px);
            padding: 1rem;
        }

        .permissions-heading {
            flex-direction: column;
            gap: 8px;
        }

        .permissions-counter {
            align-self: flex-start;
        }

        .permissions-list {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush


@push('scripts')

<script>

    (function () {

        /*
         * ================================
         * FILTRES + PAGINATION + EXPORT
         * ================================
         */

        const btnFiltrer          = document.getElementById('btn-filtrer');
        const btnReset            = document.getElementById('btn-reset-filtres');
        const btnExporter         = document.getElementById('btn-exporter');
        const searchInput         = document.getElementById('filter-role-search');
        const selectStatut        = document.getElementById('filter-statut');
        const allRows             = Array.from(document.querySelectorAll('#moduleTable tbody tr[data-nom]'));
        const emptyMessage        = document.getElementById('empty-message-filtre');
        const paginationContainer = document.getElementById('pagination-container');

        const perPage = 10;
        let currentPage = 1;

        function getFilteredRows() {
            const query  = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const statut = selectStatut ? selectStatut.value : '';

            return allRows.filter(function (row) {
                const nom = (row.dataset.nom || '').toLowerCase();
                return (!query || nom.includes(query)) &&
                       (!statut || row.dataset.statut === statut);
            });
        }

        function renderPagination(totalPages) {
            paginationContainer.innerHTML = '';
            if (totalPages <= 1) return;

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

            paginationContainer.appendChild(makeButton('←', currentPage - 1, currentPage === 1, false));
            for (let i = 1; i <= totalPages; i++) {
                paginationContainer.appendChild(makeButton(String(i), i, false, i === currentPage));
            }
            paginationContainer.appendChild(makeButton('→', currentPage + 1, currentPage === totalPages, false));
        }

        function render() {
            const filtered   = getFilteredRows();
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;

            allRows.forEach(function (row) { row.style.display = 'none'; });
            filtered.slice(start, start + perPage).forEach(function (row) { row.style.display = ''; });

            if (emptyMessage) {
                emptyMessage.style.display =
                    (allRows.length > 0 && filtered.length === 0) ? 'block' : 'none';
            }

            renderPagination(totalPages);
        }

        function applyFilters() {
            currentPage = 1;
            render();
        }

        if (btnFiltrer)   btnFiltrer.addEventListener('click', applyFilters);
        if (searchInput)  searchInput.addEventListener('input', applyFilters);
        if (selectStatut) selectStatut.addEventListener('change', applyFilters);

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                if (selectStatut) selectStatut.value = '';
                applyFilters();
            });
        }

        if (btnExporter) {
            btnExporter.addEventListener('click', function () {
                const filtered = getFilteredRows();

                if (filtered.length === 0) {
                    alert('Aucun rôle à exporter.');
                    return;
                }

                const esc = function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; };
                let csv = 'Profil,Description,Statut\n';

                filtered.forEach(function (row) {
                    const statut = row.dataset.statut === '1' ? 'Actif' : 'Inactif';
                    csv += [esc(row.dataset.nom || ''), esc(row.dataset.description || ''), esc(statut)].join(',') + '\n';
                });

                // BOM pour les accents dans Excel
                const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const url  = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'roles.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        }


        /*
         * ================================
         * MODAL
         * ================================
         */

        const modal            = document.getElementById('role-modal');
        const roleForm         = document.getElementById('role-form');
        const methodField      = document.getElementById('role-form-method');
        const nomField         = document.getElementById('role-nom');
        const descriptionField = document.getElementById('role-description');
        const statusField      = document.getElementById('role-status');
        const title            = document.getElementById('role-modal-title');
        const submit           = document.getElementById('role-form-submit');

        const storeUrl      = @json(route('admin.roles.store'));
        const updateBaseUrl = @json(route('admin.roles.index'));


        /*
         * ================================
         * PERMISSIONS
         * ================================
         */

        const permissionCheckboxes     = document.querySelectorAll('.permission-checkbox');
        const selectAllPermissions     = document.getElementById('select-all-permissions');
        const deselectAllPermissions   = document.getElementById('deselect-all-permissions');
        const selectedPermissionsCount = document.getElementById('selected-permissions-count');

        function updatePermissionCount() {
            if (selectedPermissionsCount) {
                selectedPermissionsCount.textContent =
                    document.querySelectorAll('.permission-checkbox:checked').length;
            }
        }

        if (selectAllPermissions) {
            selectAllPermissions.addEventListener('click', function () {
                permissionCheckboxes.forEach(function (cb) { cb.checked = true; });
                updatePermissionCount();
            });
        }

        if (deselectAllPermissions) {
            deselectAllPermissions.addEventListener('click', function () {
                permissionCheckboxes.forEach(function (cb) { cb.checked = false; });
                updatePermissionCount();
            });
        }

        permissionCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', updatePermissionCount);
        });


        /*
         * ================================
         * OUVERTURE DU MODAL
         * ================================
         */

        function openRoleModal(role) {

            const editing = Boolean(role);

            title.textContent  = editing ? 'Modifier le rôle' : 'Ajouter un rôle';
            submit.textContent = editing ? 'Enregistrer les modifications' : 'Ajouter le rôle';
            roleForm.action    = editing ? `${updateBaseUrl}/${role.id}` : storeUrl;
            methodField.value  = editing ? 'PUT' : 'POST';

            nomField.value         = role?.nom ?? '';
            descriptionField.value = role?.description ?? '';
            statusField.value      = role ? (role.est_actif ? '1' : '0') : '1';

            // Permissions du rôle en modification
            const ids = (editing && Array.isArray(role.permissions))
                ? role.permissions.map(function (p) {
                      return String(typeof p === 'object' && p !== null ? p.id : p);
                  })
                : [];

            permissionCheckboxes.forEach(function (cb) {
                cb.checked = ids.includes(String(cb.value));
            });

            updatePermissionCount();

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            nomField.focus();
        }


        /*
         * ================================
         * FERMETURE DU MODAL
         * ================================
         */

        function closeRoleModal() {

            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            roleForm.reset();

            permissionCheckboxes.forEach(function (cb) { cb.checked = false; });
            updatePermissionCount();

            methodField.value = 'POST';
            roleForm.action   = storeUrl;

            if (statusField) {
                statusField.value = '1';
            }
        }


        /*
         * ================================
         * AJOUTER / MODIFIER / FERMER
         * ================================
         */

        document.querySelectorAll('[data-role-modal="create"]').forEach(function (btn) {
            btn.addEventListener('click', function () { openRoleModal(null); });
        });

        document.querySelectorAll('[data-role-modal="edit"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                try {
                    openRoleModal(JSON.parse(btn.dataset.role));
                } catch (error) {
                    console.error('Erreur lors du chargement du rôle :', error);
                }
            });
        });

        document.querySelectorAll('[data-role-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', closeRoleModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && !modal.hidden) {
                closeRoleModal();
            }
        });


        /*
         * Initialisation
         */

        updatePermissionCount();
        render();

    })();

</script>

@endpush

@endsection