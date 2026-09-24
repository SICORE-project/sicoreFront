@extends('layouts.app')

@section('title', 'SICORE - Communes')

@section('content')

<main class="main-content">

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
                <h1>Communes</h1>
                <p>
                    Gestion des communes administratives enregistrées dans SICORE
                </p>
            </div>

        </div>
    </header>


    <section class="content-area">

        {{-- ===================================================== --}}
        {{-- MESSAGES --}}
        {{-- ===================================================== --}}

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


        {{-- ===================================================== --}}
        {{-- OBJECTIFS --}}
        {{-- ===================================================== --}}

        <section class="objective-card">

            <h2>Objectifs métier</h2>

            <ul class="objective-list">

                <li>
                    Consulter les communes administratives enregistrées dans SICORE.
                </li>

                <li>
                    Rechercher une commune par son code ou son libellé.
                </li>

                <li>
                    Filtrer les communes par région, département et statut.
                </li>

                <li>
                    Créer et modifier une commune.
                </li>

                <li>
                    Identifier les communes actives et inactives.
                </li>

            </ul>

        </section>


        {{-- ===================================================== --}}
        {{-- STATISTIQUES --}}
        {{-- ===================================================== --}}

        <div class="stats-grid">

            <article class="stat-card">

                <div>

                    <p class="stat-label">
                        Communes
                    </p>

                    <p class="stat-value">
                        {{ $pagination['total'] ?? 0 }}
                    </p>

                    <p class="stat-note">
                        Communes enregistrées
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


        {{-- ===================================================== --}}
        {{-- ACTIONS --}}
        {{-- ===================================================== --}}

        <div class="actions-row">

            <p class="breadcrumb">
                Paramétrage &gt; Communes
            </p>

            <div class="actions-group">

                <button
                    class="btn-primary"
                    type="button"
                    data-modal-open="commune-create-modal"
                >
                    + Nouvelle commune
                </button>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- FILTRES --}}
        {{-- ===================================================== --}}

        <form
            class="filter-panel parametrage-filters"
            id="communeFilterForm"
            method="GET"
            action="{{ route('parametres.communes.index') }}"
        >

            {{-- RECHERCHE --}}

            <div class="form-group">

                <label for="communeSearch">
                    Rechercher
                </label>

                <input
                    class="form-control"
                    id="communeSearch"
                    name="search"
                    type="search"
                    value="{{ request('search') }}"
                    placeholder="Code ou libellé"
                >

            </div>


            {{-- RÉGION --}}

            <div class="form-group">

                <label for="communeRegionFilter">
                    Région
                </label>

                <select
                    class="form-control"
                    id="communeRegionFilter"
                    name="region_id"
                >

                    <option value="">
                        Toutes les régions
                    </option>

                    @foreach ($regions as $region)

                        @php
                            $filterRegionId =
                                data_get($region, 'id');

                            $filterRegionLibelle =
                                data_get($region, 'libelle', '');
                        @endphp

                        <option
                            value="{{ $filterRegionId }}"
                            @selected(
                                (string) request('region_id') ===
                                (string) $filterRegionId
                            )
                        >
                            {{ $filterRegionLibelle }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- DÉPARTEMENT --}}

            <div class="form-group">

                <label for="communeDepartementFilter">
                    Département
                </label>

                <select
                    class="form-control"
                    id="communeDepartementFilter"
                    name="departement_id"
                >

                    <option value="">
                        Tous les départements
                    </option>

                    @foreach ($departements as $departement)

                        @php
                            $filterDepartementId =
                                data_get($departement, 'id');

                            $filterDepartementLibelle =
                                data_get(
                                    $departement,
                                    'libelle',
                                    ''
                                );

                            $filterDepartementRegionId =
                                data_get(
                                    $departement,
                                    'region_id'
                                );
                        @endphp

                        <option
                            value="{{ $filterDepartementId }}"
                            data-region-id="{{ $filterDepartementRegionId }}"
                            @selected(
                                (string) request('departement_id') ===
                                (string) $filterDepartementId
                            )
                        >
                            {{ $filterDepartementLibelle }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- STATUT --}}

            <div class="form-group">

                <label for="communeStatusFilter">
                    Statut
                </label>

                <select
                    class="form-control"
                    id="communeStatusFilter"
                    name="est_actif"
                >

                    <option value="">
                        Tous les statuts
                    </option>

                    <option
                        value="1"
                        @selected(
                            (string) request('est_actif') === '1'
                        )
                    >
                        Actifs
                    </option>

                    <option
                        value="0"
                        @selected(
                            (string) request('est_actif') === '0'
                        )
                    >
                        Inactifs
                    </option>

                </select>

            </div>


            <div class="actions-group">

                <a
                    class="btn-secondary"
                    href="{{ route('parametres.communes.index') }}"
                >
                    Réinitialiser
                </a>

            </div>

        </form>


        {{-- ===================================================== --}}
        {{-- ERREUR API --}}
        {{-- ===================================================== --}}

        @if ($error ?? false)

            <div
                class="alert alert-error"
                role="alert"
            >
                {{ $error }}
            </div>

        @endif


        {{-- ===================================================== --}}
        {{-- LISTE --}}
        {{-- ===================================================== --}}

        <section
            class="table-card"
            aria-labelledby="communeListTitle"
        >

            <div class="table-card-header">

                <div>

                    <h2 id="communeListTitle">
                        Liste des communes
                    </h2>

                    <p class="table-card-subtitle">

                        {{ $pagination['total'] ?? 0 }}

                        commune{{ ($pagination['total'] ?? 0) > 1 ? 's' : '' }}

                        enregistrée{{ ($pagination['total'] ?? 0) > 1 ? 's' : '' }}

                    </p>

                </div>

            </div>


            <div class="table-responsive">

                <table
                    class="table"
                    id="communeTable"
                >

                    <thead>

                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Région</th>
                            <th>Département</th>
                            <th>Statut</th>
                            <th class="actions-cell">
                                Actions
                            </th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($communes as $commune)

                            @php
                                $communeId =
                                    data_get($commune, 'id');

                                $communeCode =
                                    data_get(
                                        $commune,
                                        'code',
                                        ''
                                    );

                                $communeLibelle =
                                    data_get(
                                        $commune,
                                        'libelle',
                                        ''
                                    );

                                $communeRegion =
                                    data_get(
                                        $commune,
                                        'region.libelle',
                                        '—'
                                    );

                                $communeDepartement =
                                    data_get(
                                        $commune,
                                        'departement.libelle',
                                        '—'
                                    );

                                $communeActif =
                                    (bool) data_get(
                                        $commune,
                                        'est_actif',
                                        false
                                    );
                            @endphp


                            <tr data-commune-row>

                                <td>
                                    {{ $communeCode ?: '—' }}
                                </td>

                                <td>
                                    {{ $communeLibelle ?: '—' }}
                                </td>

                                <td>
                                    {{ $communeRegion ?: '—' }}
                                </td>

                                <td>
                                    {{ $communeDepartement ?: '—' }}
                                </td>

                                <td>

                                    @if ($communeActif)

                                        <span
                                            class="commune-status commune-status-active"
                                        >
                                            Actif
                                        </span>

                                    @else

                                        <span
                                            class="commune-status commune-status-inactive"
                                        >
                                            Inactif
                                        </span>

                                    @endif

                                </td>


                                <td class="actions-cell">

                                    {{-- MODIFIER --}}

                                    <button
                                        class="icon-action"
                                        type="button"
                                        data-modal-open="commune-edit-modal"
                                        data-commune-edit='@json($commune)'
                                        title="Modifier"
                                        aria-label="Modifier {{ $communeCode }}"
                                    >
                                        <i
                                            class="fa-solid fa-pen-to-square"
                                            aria-hidden="true"
                                        ></i>
                                    </button>


                                    {{-- ACTIVER / DÉSACTIVER --}}

                                    @if ($communeId)

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'parametres.communes.status',
                                                ['commune' => $communeId]
                                            ) }}"
                                            class="inline-form"
                                            onsubmit="return confirm(
                                                '{{ $communeActif
                                                    ? 'Voulez-vous désactiver cette commune ?'
                                                    : 'Voulez-vous activer cette commune ?'
                                                }}'
                                            );"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="est_actif"
                                                value="{{ $communeActif ? '0' : '1' }}"
                                            >

                                            <button
                                                class="icon-action"
                                                type="submit"
                                                title="{{ $communeActif ? 'Désactiver' : 'Activer' }}"
                                                aria-label="{{ $communeActif ? 'Désactiver' : 'Activer' }} {{ $communeCode }}"
                                            >
                                                <i
                                                    class="fa-solid {{ $communeActif ? 'fa-toggle-on' : 'fa-toggle-off' }}"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>

                                        </form>

                                    @endif


                                    {{-- SUPPRIMER --}}

                                    @if (
                                        $communeId &&
                                        in_array(
                                            session('sicore_user.role_slug'),
                                            ['admin', 'super_admin'],
                                            true
                                        )
                                    )

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'parametres.communes.destroy',
                                                ['commune' => $communeId]
                                            ) }}"
                                            class="inline-form"
                                            onsubmit="return confirm(
                                                'Voulez-vous vraiment supprimer cette commune ?'
                                            );"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="icon-action delete"
                                                type="submit"
                                                title="Supprimer"
                                                aria-label="Supprimer {{ $communeCode }}"
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

                                <td colspan="6">

                                    <x-table-empty-state>
                                        Aucune commune trouvée.
                                    </x-table-empty-state>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ================================================= --}}
            {{-- PAGINATION --}}
            {{-- ================================================= --}}

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
                                'parametres.communes.index',
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
{{-- MODALE CRÉATION --}}
{{-- ============================================================= --}}

<x-module-indemnite
    type="modal"
    id="commune-create-modal"
    title="Créer une commune"
>

    <form
        class="teacher-form"
        id="communeCreateModalForm"
        method="POST"
        action="{{ route('parametres.communes.store') }}"
    >

        @csrf

        <p class="form-required-note">
            <span class="required">*</span>
            Champs obligatoires
        </p>


        <div class="form-grid form-grid--balanced">

            {{-- CODE --}}

            <div class="form-group">

                <label for="communeCreateCode">
                    Code
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="communeCreateCode"
                    name="code"
                    type="text"
                    maxlength="20"
                    required
                    autocomplete="off"
                    value="{{ old('code') }}"
                    placeholder="Ex. DKR-PLT"
                >

            </div>


            {{-- LIBELLÉ --}}

            <div class="form-group">

                <label for="communeCreateLibelle">
                    Libellé
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="communeCreateLibelle"
                    name="libelle"
                    type="text"
                    maxlength="100"
                    required
                    autocomplete="off"
                    value="{{ old('libelle') }}"
                    placeholder="Ex. Plateau"
                >

            </div>


            {{-- RÉGION --}}

            <div class="form-group">

                <label for="communeCreateRegion">
                    Région
                    <span class="required">*</span>
                </label>

                <select
                    class="form-control"
                    id="communeCreateRegion"
                    name="region_id"
                    required
                >

                    <option value="">
                        Sélectionner une région
                    </option>

                    @foreach ($regions as $region)

                        @php
                            $regionId =
                                data_get($region, 'id');

                            $regionCode =
                                data_get($region, 'code', '');

                            $regionLibelle =
                                data_get($region, 'libelle', '');
                        @endphp

                        <option
                            value="{{ $regionId }}"
                            @selected(
                                (string) old('region_id') ===
                                (string) $regionId
                            )
                        >
                            {{ $regionCode ? $regionCode . ' - ' : '' }}
                            {{ $regionLibelle }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- DÉPARTEMENT --}}

            <div class="form-group">

                <label for="communeCreateDepartement">
                    Département
                    <span class="required">*</span>
                </label>

                <select
                    class="form-control"
                    id="communeCreateDepartement"
                    name="departement_id"
                    required
                >

                    <option value="">
                        Sélectionner d'abord une région
                    </option>

                    @foreach ($departements as $departement)

                        @php
                            $departementId =
                                data_get($departement, 'id');

                            $departementCode =
                                data_get(
                                    $departement,
                                    'code',
                                    ''
                                );

                            $departementLibelle =
                                data_get(
                                    $departement,
                                    'libelle',
                                    ''
                                );

                            $departementRegionId =
                                data_get(
                                    $departement,
                                    'region_id'
                                );
                        @endphp

                        <option
                            value="{{ $departementId }}"
                            data-region-id="{{ $departementRegionId }}"
                            @selected(
                                (string) old('departement_id') ===
                                (string) $departementId
                            )
                        >
                            {{ $departementCode ? $departementCode . ' - ' : '' }}
                            {{ $departementLibelle }}
                        </option>

                    @endforeach

                </select>

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
                Créer la commune
            </button>

        </div>

    </form>

</x-module-indemnite>


{{-- ============================================================= --}}
{{-- MODALE MODIFICATION --}}
{{-- ============================================================= --}}

<x-module-indemnite
    type="modal"
    id="commune-edit-modal"
    title="Modifier une commune"
>

    <form
        class="teacher-form"
        id="communeEditForm"
        method="POST"
    >

        @csrf
        @method('PUT')

        <input
            id="communeEditId"
            type="hidden"
        >


        <p class="form-required-note">
            <span class="required">*</span>
            Champs obligatoires
        </p>


        <div class="form-grid form-grid--balanced">

            {{-- CODE --}}

            <div class="form-group">

                <label for="communeEditCode">
                    Code
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="communeEditCode"
                    name="code"
                    type="text"
                    maxlength="20"
                    required
                    autocomplete="off"
                >

            </div>


            {{-- LIBELLÉ --}}

            <div class="form-group">

                <label for="communeEditLibelle">
                    Libellé
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="communeEditLibelle"
                    name="libelle"
                    type="text"
                    maxlength="100"
                    required
                    autocomplete="off"
                >

            </div>


            {{-- RÉGION --}}

            <div class="form-group">

                <label for="communeEditRegion">
                    Région
                    <span class="required">*</span>
                </label>

                <select
                    class="form-control"
                    id="communeEditRegion"
                    name="region_id"
                    required
                >

                    <option value="">
                        Sélectionner une région
                    </option>

                    @foreach ($regions as $region)

                        @php
                            $regionId =
                                data_get($region, 'id');

                            $regionCode =
                                data_get($region, 'code', '');

                            $regionLibelle =
                                data_get($region, 'libelle', '');
                        @endphp

                        <option value="{{ $regionId }}">
                            {{ $regionCode ? $regionCode . ' - ' : '' }}
                            {{ $regionLibelle }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- DÉPARTEMENT --}}

            <div class="form-group">

                <label for="communeEditDepartement">
                    Département
                    <span class="required">*</span>
                </label>

                <select
                    class="form-control"
                    id="communeEditDepartement"
                    name="departement_id"
                    required
                >

                    <option value="">
                        Sélectionner un département
                    </option>

                    @foreach ($departements as $departement)

                        @php
                            $departementId =
                                data_get($departement, 'id');

                            $departementCode =
                                data_get(
                                    $departement,
                                    'code',
                                    ''
                                );

                            $departementLibelle =
                                data_get(
                                    $departement,
                                    'libelle',
                                    ''
                                );

                            $departementRegionId =
                                data_get(
                                    $departement,
                                    'region_id'
                                );
                        @endphp

                        <option
                            value="{{ $departementId }}"
                            data-region-id="{{ $departementRegionId }}"
                        >
                            {{ $departementCode ? $departementCode . ' - ' : '' }}
                            {{ $departementLibelle }}
                        </option>

                    @endforeach

                </select>

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


@push('styles')

<style>

    #commune-create-modal .modal-dialog,
    #commune-edit-modal .modal-dialog {
        max-width: 920px;
        width: calc(100% - 32px);
    }

    .commune-status {
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

    .commune-status-active {
        color: #00a63e;
        background-color: #dcfce7;
    }

    .commune-status-inactive {
        color: #f97316;
        background-color: #fff3d6;
    }

</style>

@endpush


@push('scripts')

<script>

(function () {

    /*
    |--------------------------------------------------------------------------
    | FILTRES
    |--------------------------------------------------------------------------
    */

    var filterForm =
        document.getElementById('communeFilterForm');

    var searchInput =
        document.getElementById('communeSearch');

    var regionFilter =
        document.getElementById('communeRegionFilter');

    var departementFilter =
        document.getElementById('communeDepartementFilter');

    var statusFilter =
        document.getElementById('communeStatusFilter');

    var searchTimer;


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
    | FILTRAGE RÉGION -> DÉPARTEMENT
    |--------------------------------------------------------------------------
    */

    function filterDepartements(
        selectElement,
        regionId,
        selectedDepartementId,
        showAllWhenEmpty
    ) {

        if (!selectElement) {
            return;
        }

        regionId = String(regionId || '');

        selectedDepartementId =
            selectedDepartementId !== undefined &&
            selectedDepartementId !== null
                ? String(selectedDepartementId)
                : '';

        var options =
            selectElement.querySelectorAll(
                'option[data-region-id]'
            );

        var selectedFound = false;

        options.forEach(function (option) {

            var optionRegionId =
                String(
                    option.getAttribute(
                        'data-region-id'
                    ) || ''
                );

            var visible =
                regionId === ''
                    ? Boolean(showAllWhenEmpty)
                    : optionRegionId === regionId;

            option.hidden = !visible;
            option.disabled = !visible;

            if (
                visible &&
                selectedDepartementId !== '' &&
                String(option.value) ===
                    selectedDepartementId
            ) {
                option.selected = true;
                selectedFound = true;
            } else {
                option.selected = false;
            }

        });


        var placeholder =
            selectElement.querySelector(
                'option:not([data-region-id])'
            );

        if (placeholder) {

            placeholder.hidden = false;
            placeholder.disabled = false;

            if (!selectedFound) {
                placeholder.selected = true;
            }
        }

    }


    /*
    |--------------------------------------------------------------------------
    | FILTRES LISTE
    |--------------------------------------------------------------------------
    */

    if (
        regionFilter &&
        departementFilter
    ) {

        var currentDepartement =
            @json(request('departement_id'));

        filterDepartements(
            departementFilter,
            regionFilter.value,
            currentDepartement,
            true
        );


        regionFilter.addEventListener(
            'change',
            function () {

                /*
                 * On réinitialise le département
                 * lorsqu'on change de région.
                 */
                departementFilter.value = '';

                filterDepartements(
                    departementFilter,
                    regionFilter.value,
                    '',
                    true
                );

                filterForm.requestSubmit();
            }
        );


        departementFilter.addEventListener(
            'change',
            function () {
                filterForm.requestSubmit();
            }
        );

    } else if (regionFilter && filterForm) {

        regionFilter.addEventListener(
            'change',
            function () {
                filterForm.requestSubmit();
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CRÉATION : RÉGION -> DÉPARTEMENT
    |--------------------------------------------------------------------------
    */

    var createRegion =
        document.getElementById(
            'communeCreateRegion'
        );

    var createDepartement =
        document.getElementById(
            'communeCreateDepartement'
        );


    if (
        createRegion &&
        createDepartement
    ) {

        var oldDepartementId =
            @json(old('departement_id'));

        filterDepartements(
            createDepartement,
            createRegion.value,
            oldDepartementId,
            false
        );


        createRegion.addEventListener(
            'change',
            function () {

                filterDepartements(
                    createDepartement,
                    createRegion.value,
                    '',
                    false
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | URL MODIFICATION
    |--------------------------------------------------------------------------
    */

    var communeUpdateUrl =
        @json(
            route(
                'parametres.communes.update',
                ['commune' => '__COMMUNE__']
            )
        );


    /*
    |--------------------------------------------------------------------------
    | RÉCUPÉRATION DES VALEURS
    |--------------------------------------------------------------------------
    */

    function communeValue(
        data,
        paths,
        fallback
    ) {

        for (
            var i = 0;
            i < paths.length;
            i += 1
        ) {

            var current = data;
            var parts = paths[i].split('.');

            for (
                var j = 0;
                j < parts.length &&
                current != null;
                j += 1
            ) {
                current = current[parts[j]];
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
    | MODIFICATION
    |--------------------------------------------------------------------------
    */

    var editRegion =
        document.getElementById(
            'communeEditRegion'
        );

    var editDepartement =
        document.getElementById(
            'communeEditDepartement'
        );


    document
        .querySelectorAll(
            '[data-commune-edit]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    var commune =
                        JSON.parse(
                            button.getAttribute(
                                'data-commune-edit'
                            )
                        );


                    var communeId =
                        communeValue(
                            commune,
                            ['id'],
                            ''
                        );


                    var regionId =
                        communeValue(
                            commune,
                            ['region_id', 'region.id'],
                            ''
                        );


                    var departementId =
                        communeValue(
                            commune,
                            [
                                'departement_id',
                                'departement.id'
                            ],
                            ''
                        );


                    document
                        .getElementById(
                            'communeEditId'
                        )
                        .value =
                            communeId;


                    document
                        .getElementById(
                            'communeEditForm'
                        )
                        .action =
                            communeUpdateUrl.replace(
                                '__COMMUNE__',
                                communeId
                            );


                    document
                        .getElementById(
                            'communeEditCode'
                        )
                        .value =
                            communeValue(
                                commune,
                                ['code'],
                                ''
                            );


                    document
                        .getElementById(
                            'communeEditLibelle'
                        )
                        .value =
                            communeValue(
                                commune,
                                ['libelle'],
                                ''
                            );


                    if (editRegion) {

                        editRegion.value =
                            String(regionId);

                    }


                    if (editDepartement) {

                        filterDepartements(
                            editDepartement,
                            regionId,
                            departementId,
                            false
                        );

                    }

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | MODIFICATION : CHANGEMENT DE RÉGION
    |--------------------------------------------------------------------------
    */

    if (
        editRegion &&
        editDepartement
    ) {

        editRegion.addEventListener(
            'change',
            function () {

                filterDepartements(
                    editDepartement,
                    editRegion.value,
                    '',
                    false
                );

            }
        );

    }


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