@extends('layouts.app')

@section('title', 'SICORE - Régions')

@section('content')

<main class="main-content">

    {{-- ========================================================= --}}
    {{-- EN-TÊTE --}}
    {{-- ========================================================= --}}

    <header class="topbar">
        <div class="page-title-wrap">

            <button
                class="mobile-menu-btn"
                type="button"
                data-sidebar-toggle
                aria-label="Ouvrir le menu"
            >
                &#9776;
            </button>

            <span class="title-icon" aria-hidden="true">
                <i class="fa-solid fa-map-location-dot"></i>
            </span>

            <div>
                <h1>Régions</h1>
                <p>Gestion des régions administratives enregistrées dans SICORE</p>
            </div>

        </div>
    </header>


    <section class="content-area">

        {{-- ========================================================= --}}
        {{-- MESSAGES --}}
        {{-- ========================================================= --}}

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $validationError)
                        <li>{{ $validationError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- OBJECTIFS --}}
        {{-- ========================================================= --}}

        <section class="objective-card">

            <h2>Objectifs métier</h2>

            <ul class="objective-list">
                <li>
                    Consulter les régions administratives enregistrées dans SICORE.
                </li>

                <li>
                    Rechercher une région par son code ou son libellé.
                </li>

                <li>
                    Créer et modifier une région.
                </li>

                <li>
                    Identifier les régions actives et inactives.
                </li>
            </ul>

        </section>


        {{-- ========================================================= --}}
        {{-- STATISTIQUES --}}
        {{-- ========================================================= --}}

        <div class="stats-grid">

            <article class="stat-card">

                <div>

                    <p class="stat-label">
                        Régions
                    </p>

                    <p class="stat-value">
                        {{ $pagination['total'] ?? 0 }}
                    </p>

                    <p class="stat-note">
                        Régions enregistrées
                    </p>

                </div>

                <span class="stat-icon green">
                    <i
                        class="fa-solid fa-map-location-dot"
                        aria-hidden="true"
                    ></i>
                </span>

            </article>

        </div>


        {{-- ========================================================= --}}
        {{-- ACTIONS --}}
        {{-- ========================================================= --}}

        <div class="actions-row">

            <p class="breadcrumb">
                Paramétrage &gt; Régions
            </p>

            <div class="actions-group">

                <button
                    class="btn-primary"
                    type="button"
                    data-modal-open="region-create-modal"
                >
                    + Nouvelle région
                </button>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- FILTRES --}}
        {{-- ========================================================= --}}

        <form
            class="filter-panel parametrage-filters"
            id="regionFilterForm"
            method="GET"
            action="{{ route('parametres.regions.index') }}"
        >

            {{-- RECHERCHE --}}
            <div class="form-group">

                <label for="regionSearch">
                    Rechercher
                </label>

                <input
                    class="form-control"
                    id="regionSearch"
                    name="search"
                    type="search"
                    value="{{ request('search') }}"
                    placeholder="Code ou libellé"
                >

            </div>


            {{-- STATUT --}}
            <div class="form-group">

                <label for="regionStatusFilter">
                    Statut
                </label>

                <select
                    class="form-control"
                    id="regionStatusFilter"
                    name="est_actif"
                >

                    <option value="">
                        Tous les statuts
                    </option>

                    <option
                        value="1"
                        @selected((string) request('est_actif') === '1')
                    >
                        Actives
                    </option>

                    <option
                        value="0"
                        @selected((string) request('est_actif') === '0')
                    >
                        Inactives
                    </option>

                </select>

            </div>


            {{-- RÉINITIALISER --}}
            <div class="actions-group">

                <a
                    class="btn-secondary"
                    href="{{ route('parametres.regions.index') }}"
                >
                    Réinitialiser
                </a>

            </div>

        </form>


        {{-- ========================================================= --}}
        {{-- ERREUR API --}}
        {{-- ========================================================= --}}

        @if ($error ?? false)

            <div
                class="alert alert-error"
                role="alert"
            >
                {{ $error }}
            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- LISTE DES RÉGIONS --}}
        {{-- ========================================================= --}}

        <section
            class="table-card"
            aria-labelledby="regionListTitle"
        >

            <div class="table-card-header">

                <div>

                    <h2 id="regionListTitle">
                        Liste des régions
                    </h2>

                    <p class="table-card-subtitle">

                        {{ $pagination['total'] ?? 0 }}

                        région{{ ($pagination['total'] ?? 0) > 1 ? 's' : '' }}

                        enregistrée{{ ($pagination['total'] ?? 0) > 1 ? 's' : '' }}

                    </p>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- TABLEAU --}}
            {{-- ===================================================== --}}

            <div class="table-responsive">

                <table
                    class="table"
                    id="regionTable"
                >

                    <thead>

                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($items as $region)

                            @php
                                $regionId = data_get($region, 'id');

                                $regionCode = data_get(
                                    $region,
                                    'code',
                                    ''
                                );

                                $regionLibelle = data_get(
                                    $region,
                                    'libelle',
                                    ''
                                );

                                $regionActif = (bool) data_get(
                                    $region,
                                    'est_actif',
                                    false
                                );
                            @endphp


                            <tr data-region-row>

                                {{-- CODE --}}
                                <td>
                                    {{ $regionCode ?: '—' }}
                                </td>


                                {{-- LIBELLÉ --}}
                                <td>
                                    {{ $regionLibelle ?: '—' }}
                                </td>


                                {{-- STATUT --}}
                                <td>

                                    @if ($regionActif)

                                        <span class="region-status region-status-active">
                                            Active
                                        </span>

                                    @else

                                        <span class="region-status region-status-inactive">
                                            Inactive
                                        </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}
                                <td class="actions-cell">

                                    {{-- MODIFIER --}}
                                    <button
                                        class="icon-action"
                                        type="button"
                                        data-modal-open="region-edit-modal"
                                        data-region-edit='@json($region)'
                                        title="Modifier"
                                        aria-label="Modifier {{ $regionCode }}"
                                    >
                                        <i
                                            class="fa-solid fa-pen-to-square"
                                            aria-hidden="true"
                                        ></i>
                                    </button>


                                    {{-- SUPPRIMER --}}
                                    @if (
                                        $regionId &&
                                        in_array(
                                            session('sicore_user.role_slug'),
                                            ['admin', 'super_admin'],
                                            true
                                        )
                                    )

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'parametres.regions.destroy',
                                                ['region' => $regionId]
                                            ) }}"
                                            class="inline-form"
                                            onsubmit="return confirm('Voulez-vous vraiment supprimer cette région ?');"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="icon-action delete"
                                                type="submit"
                                                title="Supprimer"
                                                aria-label="Supprimer {{ $regionCode }}"
                                            >
                                                <i
                                                    class="fa-solid fa-trash-can"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>

                                        </form>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4">

                                    <x-table-empty-state>
                                        Aucune région trouvée.
                                    </x-table-empty-state>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ===================================================== --}}
            {{-- PAGINATION --}}
            {{-- ===================================================== --}}

            @if (($pagination['last_page'] ?? 1) > 1)

                <nav
                    class="pagination"
                    aria-label="Pagination"
                >

                    @for (
                        $page = 1;
                        $page <= ($pagination['last_page'] ?? 1);
                        $page++
                    )

                        <a
                            class="page-btn {{ $page === ($pagination['current_page'] ?? 1) ? 'active' : '' }}"
                            href="{{ route(
                                'parametres.regions.index',
                                array_merge(
                                    request()->except('page'),
                                    ['page' => $page]
                                )
                            ) }}"
                            @if (
                                $page ===
                                ($pagination['current_page'] ?? 1)
                            )
                                aria-current="page"
                            @endif
                        >
                            {{ $page }}
                        </a>

                    @endfor

                </nav>

            @endif

        </section>

    </section>

</main>


{{-- ============================================================= --}}
{{-- MODALE : CRÉER UNE RÉGION --}}
{{-- ============================================================= --}}

<x-module-indemnite
    type="modal"
    id="region-create-modal"
    title="Créer une région"
>

    <form
        class="teacher-form"
        id="regionCreateModalForm"
        method="POST"
        action="{{ route('parametres.regions.store') }}"
    >

        @csrf


        <div
            class="alert alert-success"
            id="regionCreateModalFeedback"
            role="status"
            hidden
        >
            La nouvelle région est valide et prête à être enregistrée.
        </div>


        <p class="form-required-note">
            <span class="required">*</span>
            Champs obligatoires
        </p>


        <div class="form-grid form-grid--balanced">

            {{-- CODE --}}
            <div class="form-group">

                <label for="regionCreateCode">
                    Code
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="regionCreateCode"
                    name="code"
                    type="text"
                    maxlength="10"
                    required
                    autocomplete="off"
                    value="{{ old('code') }}"
                    placeholder="Ex. DK"
                >

            </div>


            {{-- LIBELLÉ --}}
            <div class="form-group">

                <label for="regionCreateLibelle">
                    Libellé
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="regionCreateLibelle"
                    name="libelle"
                    type="text"
                    maxlength="50"
                    required
                    autocomplete="off"
                    value="{{ old('libelle') }}"
                    placeholder="Ex. Dakar"
                >

            </div>

        </div>


        <div class="form-actions">

            <button
                class="btn-secondary"
                type="button"
                data-modal-close
            >
                Annuler
            </button>


            <button
                class="btn-primary"
                type="submit"
            >
                Créer la région
            </button>

        </div>

    </form>

</x-module-indemnite>


{{-- ============================================================= --}}
{{-- MODALE : MODIFIER UNE RÉGION --}}
{{-- ============================================================= --}}

<x-module-indemnite
    type="modal"
    id="region-edit-modal"
    title="Modifier une région"
>

    <form
        class="teacher-form"
        id="regionEditForm"
        method="POST"
    >

        @csrf
        @method('PUT')


        <input
            id="regionEditId"
            name="id"
            type="hidden"
        >


        <div
            class="alert alert-success"
            id="regionEditFeedback"
            role="status"
            hidden
        >
            Les modifications sont valides et prêtes à être enregistrées.
        </div>


        <p class="form-required-note">
            <span class="required">*</span>
            Champs obligatoires
        </p>


        <div class="form-grid form-grid--balanced">

            {{-- CODE --}}
            <div class="form-group">

                <label for="regionEditCode">
                    Code
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="regionEditCode"
                    name="code"
                    type="text"
                    maxlength="10"
                    required
                    autocomplete="off"
                >

            </div>


            {{-- LIBELLÉ --}}
            <div class="form-group">

                <label for="regionEditLibelle">
                    Libellé
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="regionEditLibelle"
                    name="libelle"
                    type="text"
                    maxlength="50"
                    required
                    autocomplete="off"
                >

            </div>

        </div>


        <div class="form-actions">

            <button
                class="btn-secondary"
                type="button"
                data-modal-close
            >
                Annuler
            </button>


            <button
                class="btn-primary"
                type="submit"
            >
                Enregistrer les modifications
            </button>

        </div>

    </form>

</x-module-indemnite>


{{-- ============================================================= --}}
{{-- STYLES --}}
{{-- ============================================================= --}}

@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | MODALES
    |--------------------------------------------------------------------------
    */

    #region-create-modal .modal-dialog,
    #region-edit-modal .modal-dialog {
        max-width: 920px;
        width: calc(100% - 32px);
    }


    /*
    |--------------------------------------------------------------------------
    | BADGES STATUT
    |--------------------------------------------------------------------------
    */

    .region-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVE
    |--------------------------------------------------------------------------
    */

    .region-status-active {
        color: #00a63e;
        background-color: #dcfce7;
    }


    /*
    |--------------------------------------------------------------------------
    | INACTIVE
    |--------------------------------------------------------------------------
    */

    .region-status-inactive {
        color: #f97316;
        background-color: #fff3d6;
    }

</style>

@endpush


{{-- ============================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ============================================================= --}}

@push('scripts')

<script>

(function () {

    /*
    |--------------------------------------------------------------------------
    | FILTRES
    |--------------------------------------------------------------------------
    */

    var filterForm =
        document.getElementById('regionFilterForm');

    var searchInput =
        document.getElementById('regionSearch');

    var statusFilter =
        document.getElementById('regionStatusFilter');

    var searchTimer;


    /*
    |--------------------------------------------------------------------------
    | RECHERCHE AUTOMATIQUE
    |--------------------------------------------------------------------------
    */

    if (searchInput && filterForm) {

        searchInput.addEventListener(
            'input',
            function () {

                window.clearTimeout(searchTimer);

                searchTimer = window.setTimeout(
                    function () {

                        filterForm.requestSubmit();

                    },
                    400
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FILTRE PAR STATUT
    |--------------------------------------------------------------------------
    */

    if (statusFilter && filterForm) {

        statusFilter.addEventListener(
            'change',
            function () {

                filterForm.requestSubmit();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | URL DE MODIFICATION
    |--------------------------------------------------------------------------
    */

    var regionUpdateUrl =
        @json(
            route(
                'parametres.regions.update',
                ['region' => '__REGION__']
            )
        );


    /*
    |--------------------------------------------------------------------------
    | FONCTION POUR RÉCUPÉRER UNE VALEUR
    |--------------------------------------------------------------------------
    */

    function regionValue(data, paths, fallback) {

        for (
            var i = 0;
            i < paths.length;
            i += 1
        ) {

            var current = data;

            var parts =
                paths[i].split('.');


            for (
                var j = 0;
                j < parts.length && current != null;
                j += 1
            ) {

                current =
                    current[parts[j]];

            }


            if (
                current !== undefined &&
                current !== null &&
                typeof current !== 'object'
            ) {

                return current;

            }

        }

        return fallback || '';

    }


    /*
    |--------------------------------------------------------------------------
    | REMPLISSAGE DE LA MODALE MODIFIER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-region-edit]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    var region =
                        JSON.parse(
                            button.getAttribute(
                                'data-region-edit'
                            )
                        );


                    var regionId =
                        regionValue(
                            region,
                            ['id'],
                            ''
                        );


                    /*
                    | ID
                    */
                    document
                        .getElementById('regionEditId')
                        .value =
                            regionId;


                    /*
                    | URL DU FORMULAIRE PUT
                    */
                    document
                        .getElementById('regionEditForm')
                        .action =
                            regionUpdateUrl.replace(
                                '__REGION__',
                                regionId
                            );


                    /*
                    | CODE
                    */
                    document
                        .getElementById('regionEditCode')
                        .value =
                            regionValue(
                                region,
                                ['code'],
                                ''
                            );


                    /*
                    | LIBELLÉ
                    */
                    document
                        .getElementById('regionEditLibelle')
                        .value =
                            regionValue(
                                region,
                                ['libelle'],
                                ''
                            );


                    /*
                    | MASQUER LE FEEDBACK
                    */
                    var feedback =
                        document.getElementById(
                            'regionEditFeedback'
                        );

                    if (feedback) {
                        feedback.hidden = true;
                    }

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | TOASTS
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            @if (session('error'))

                window.showToast?.(
                    'error',
                    @json(session('error'))
                );

            @elseif (session('success'))

                window.showToast?.(
                    'success',
                    @json(session('success'))
                );

            @endif

        }
    );

}());

</script>

@endpush

@endsection