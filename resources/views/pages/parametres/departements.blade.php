@extends('layouts.app')

@section('title', 'SICORE - Départements')

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
                <i class="fa-solid fa-map"></i>
            </span>

            <div>
                <h1>Départements</h1>
                <p>Gestion des départements administratifs enregistrés dans SICORE</p>
            </div>

        </div>
    </header>


    <section class="content-area">

        {{-- MESSAGES --}}

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


        {{-- OBJECTIFS --}}

        <section class="objective-card">

            <h2>Objectifs métier</h2>

            <ul class="objective-list">
                <li>
                    Consulter les départements administratifs enregistrés dans SICORE.
                </li>

                <li>
                    Rechercher un département par son code ou son libellé.
                </li>

                <li>
                    Filtrer les départements par région et par statut.
                </li>

                <li>
                    Créer et modifier un département.
                </li>

                <li>
                    Identifier les départements actifs et inactifs.
                </li>
            </ul>

        </section>


        {{-- STATISTIQUES --}}

        <div class="stats-grid">

            <article class="stat-card">

                <div>

                    <p class="stat-label">
                        Départements
                    </p>

                    <p class="stat-value">
                        {{ $pagination['total'] ?? 0 }}
                    </p>

                    <p class="stat-note">
                        Départements enregistrés
                    </p>

                </div>

                <span class="stat-icon green">
                    <i
                        class="fa-solid fa-map"
                        aria-hidden="true"
                    ></i>
                </span>

            </article>

        </div>


        {{-- ACTIONS --}}

        <div class="actions-row">

            <p class="breadcrumb">
                Paramétrage &gt; Départements
            </p>

            <div class="actions-group">

                <button
                    class="btn-primary"
                    type="button"
                    data-modal-open="departement-create-modal"
                >
                    + Nouveau département
                </button>

            </div>

        </div>


        {{-- FILTRES --}}

        <form
            class="filter-panel parametrage-filters"
            id="departementFilterForm"
            method="GET"
            action="{{ route('parametres.departements.index') }}"
        >

            {{-- RECHERCHE --}}

            <div class="form-group">

                <label for="departementSearch">
                    Rechercher
                </label>

                <input
                    class="form-control"
                    id="departementSearch"
                    name="search"
                    type="search"
                    value="{{ request('search') }}"
                    placeholder="Code ou libellé"
                >

            </div>


            {{-- RÉGION --}}

            <div class="form-group">

                <label for="departementRegionFilter">
                    Région
                </label>

                <select
                    class="form-control"
                    id="departementRegionFilter"
                    name="region_id"
                >

                    <option value="">
                        Toutes les régions
                    </option>

                    @foreach ($regions as $region)

                        @php
                            $filterRegionId = data_get($region, 'id');
                            $filterRegionLibelle = data_get($region, 'libelle', '');
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


            {{-- STATUT --}}

            <div class="form-group">

                <label for="departementStatusFilter">
                    Statut
                </label>

                <select
                    class="form-control"
                    id="departementStatusFilter"
                    name="est_actif"
                >

                    <option value="">
                        Tous les statuts
                    </option>

                    <option
                        value="1"
                        @selected((string) request('est_actif') === '1')
                    >
                        Actifs
                    </option>

                    <option
                        value="0"
                        @selected((string) request('est_actif') === '0')
                    >
                        Inactifs
                    </option>

                </select>

            </div>


            <div class="actions-group">

                <a
                    class="btn-secondary"
                    href="{{ route('parametres.departements.index') }}"
                >
                    Réinitialiser
                </a>

            </div>

        </form>


        {{-- ERREUR API --}}

        @if ($error ?? false)

            <div
                class="alert alert-error"
                role="alert"
            >
                {{ $error }}
            </div>

        @endif


        {{-- LISTE --}}

        <section
            class="table-card"
            aria-labelledby="departementListTitle"
        >

            <div class="table-card-header">

                <div>

                    <h2 id="departementListTitle">
                        Liste des départements
                    </h2>

                    <p class="table-card-subtitle">

                        {{ $pagination['total'] ?? 0 }}

                        département{{ ($pagination['total'] ?? 0) > 1 ? 's' : '' }}

                        enregistré{{ ($pagination['total'] ?? 0) > 1 ? 's' : '' }}

                    </p>

                </div>

            </div>


            <div class="table-responsive">

                <table
                    class="table"
                    id="departementTable"
                >

                    <thead>

                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Région</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($items as $departement)

                            @php
                                $departementId =
                                    data_get($departement, 'id');

                                $departementCode =
                                    data_get($departement, 'code', '');

                                $departementLibelle =
                                    data_get($departement, 'libelle', '');

                                $departementRegion =
                                    data_get(
                                        $departement,
                                        'region.libelle',
                                        '—'
                                    );

                                $departementActif =
                                    (bool) data_get(
                                        $departement,
                                        'est_actif',
                                        false
                                    );
                            @endphp


                            <tr data-departement-row>

                                <td>
                                    {{ $departementCode ?: '—' }}
                                </td>

                                <td>
                                    {{ $departementLibelle ?: '—' }}
                                </td>

                                <td>
                                    {{ $departementRegion ?: '—' }}
                                </td>

                                <td>

                                    @if ($departementActif)

                                        <span class="departement-status departement-status-active">
                                            Actif
                                        </span>

                                    @else

                                        <span class="departement-status departement-status-inactive">
                                            Inactif
                                        </span>

                                    @endif

                                </td>


                                <td class="actions-cell">

                                    {{-- MODIFIER --}}

                                    <button
                                        class="icon-action"
                                        type="button"
                                        data-modal-open="departement-edit-modal"
                                        data-departement-edit='@json($departement)'
                                        title="Modifier"
                                        aria-label="Modifier {{ $departementCode }}"
                                    >
                                        <i
                                            class="fa-solid fa-pen-to-square"
                                            aria-hidden="true"
                                        ></i>
                                    </button>


                                    {{-- ACTIVER / DÉSACTIVER --}}

                                    @if ($departementId)

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'parametres.departements.status',
                                                ['departement' => $departementId]
                                            ) }}"
                                            class="inline-form"
                                            onsubmit="return confirm(
                                                '{{ $departementActif
                                                    ? 'Voulez-vous désactiver ce département ?'
                                                    : 'Voulez-vous activer ce département ?'
                                                }}'
                                            );"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="est_actif"
                                                value="{{ $departementActif ? '0' : '1' }}"
                                            >

                                            <button
                                                class="icon-action"
                                                type="submit"
                                                title="{{ $departementActif ? 'Désactiver' : 'Activer' }}"
                                                aria-label="{{ $departementActif ? 'Désactiver' : 'Activer' }} {{ $departementCode }}"
                                            >
                                                <i
                                                    class="fa-solid {{ $departementActif ? 'fa-toggle-on' : 'fa-toggle-off' }}"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>

                                        </form>

                                    @endif


                                    {{-- SUPPRIMER --}}

                                    @if (
                                        $departementId &&
                                        in_array(
                                            session('sicore_user.role_slug'),
                                            ['admin', 'super_admin'],
                                            true
                                        )
                                    )

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'parametres.departements.destroy',
                                                ['departement' => $departementId]
                                            ) }}"
                                            class="inline-form"
                                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce département ?');"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="icon-action delete"
                                                type="submit"
                                                title="Supprimer"
                                                aria-label="Supprimer {{ $departementCode }}"
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

                                <td colspan="5">

                                    <x-table-empty-state>
                                        Aucun département trouvé.
                                    </x-table-empty-state>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}

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
                                'parametres.departements.index',
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
    id="departement-create-modal"
    title="Créer un département"
>

    <form
        class="teacher-form"
        id="departementCreateModalForm"
        method="POST"
        action="{{ route('parametres.departements.store') }}"
    >

        @csrf

        <p class="form-required-note">
            <span class="required">*</span>
            Champs obligatoires
        </p>


        <div class="form-grid form-grid--balanced">

            <div class="form-group">

                <label for="departementCreateCode">
                    Code
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="departementCreateCode"
                    name="code"
                    type="text"
                    maxlength="20"
                    required
                    autocomplete="off"
                    value="{{ old('code') }}"
                    placeholder="Ex. DKR"
                >

            </div>


            <div class="form-group">

                <label for="departementCreateLibelle">
                    Libellé
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="departementCreateLibelle"
                    name="libelle"
                    type="text"
                    maxlength="100"
                    required
                    autocomplete="off"
                    value="{{ old('libelle') }}"
                    placeholder="Ex. Dakar"
                >

            </div>


            <div class="form-group">

                <label for="departementCreateRegion">
                    Région
                    <span class="required">*</span>
                </label>

                <select
                    class="form-control"
                    id="departementCreateRegion"
                    name="region_id"
                    required
                >

                    <option value="">
                        Sélectionner une région
                    </option>

                    @foreach ($regions as $region)

                        @php
                            $regionId = data_get($region, 'id');
                            $regionCode = data_get($region, 'code', '');
                            $regionLibelle = data_get($region, 'libelle', '');
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
                Créer le département
            </button>

        </div>

    </form>

</x-module-indemnite>


{{-- ============================================================= --}}
{{-- MODALE MODIFICATION --}}
{{-- ============================================================= --}}

<x-module-indemnite
    type="modal"
    id="departement-edit-modal"
    title="Modifier un département"
>

    <form
        class="teacher-form"
        id="departementEditForm"
        method="POST"
    >

        @csrf
        @method('PUT')

        <input
            id="departementEditId"
            type="hidden"
        >


        <p class="form-required-note">
            <span class="required">*</span>
            Champs obligatoires
        </p>


        <div class="form-grid form-grid--balanced">

            <div class="form-group">

                <label for="departementEditCode">
                    Code
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="departementEditCode"
                    name="code"
                    type="text"
                    maxlength="20"
                    required
                    autocomplete="off"
                >

            </div>


            <div class="form-group">

                <label for="departementEditLibelle">
                    Libellé
                    <span class="required">*</span>
                </label>

                <input
                    class="form-control"
                    id="departementEditLibelle"
                    name="libelle"
                    type="text"
                    maxlength="100"
                    required
                    autocomplete="off"
                >

            </div>


            <div class="form-group">

                <label for="departementEditRegion">
                    Région
                    <span class="required">*</span>
                </label>

                <select
                    class="form-control"
                    id="departementEditRegion"
                    name="region_id"
                    required
                >

                    <option value="">
                        Sélectionner une région
                    </option>

                    @foreach ($regions as $region)

                        @php
                            $regionId = data_get($region, 'id');
                            $regionCode = data_get($region, 'code', '');
                            $regionLibelle = data_get($region, 'libelle', '');
                        @endphp

                        <option value="{{ $regionId }}">
                            {{ $regionCode ? $regionCode . ' - ' : '' }}
                            {{ $regionLibelle }}
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

    #departement-create-modal .modal-dialog,
    #departement-edit-modal .modal-dialog {
        max-width: 920px;
        width: calc(100% - 32px);
    }

    .departement-status {
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

    .departement-status-active {
        color: #00a63e;
        background-color: #dcfce7;
    }

    .departement-status-inactive {
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
        document.getElementById('departementFilterForm');

    var searchInput =
        document.getElementById('departementSearch');

    var regionFilter =
        document.getElementById('departementRegionFilter');

    var statusFilter =
        document.getElementById('departementStatusFilter');

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


    if (regionFilter && filterForm) {

        regionFilter.addEventListener(
            'change',
            function () {
                filterForm.requestSubmit();
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
    | URL MODIFICATION
    |--------------------------------------------------------------------------
    */

    var departementUpdateUrl =
        @json(
            route(
                'parametres.departements.update',
                ['departement' => '__DEPARTEMENT__']
            )
        );


    /*
    |--------------------------------------------------------------------------
    | RÉCUPÉRATION DES VALEURS
    |--------------------------------------------------------------------------
    */

    function departementValue(data, paths, fallback) {

        for (
            var i = 0;
            i < paths.length;
            i += 1
        ) {

            var current = data;
            var parts = paths[i].split('.');

            for (
                var j = 0;
                j < parts.length && current != null;
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

    document
        .querySelectorAll('[data-departement-edit]')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    var departement =
                        JSON.parse(
                            button.getAttribute(
                                'data-departement-edit'
                            )
                        );

                    var departementId =
                        departementValue(
                            departement,
                            ['id'],
                            ''
                        );


                    document
                        .getElementById('departementEditId')
                        .value = departementId;


                    document
                        .getElementById('departementEditForm')
                        .action =
                            departementUpdateUrl.replace(
                                '__DEPARTEMENT__',
                                departementId
                            );


                    document
                        .getElementById('departementEditCode')
                        .value =
                            departementValue(
                                departement,
                                ['code'],
                                ''
                            );


                    document
                        .getElementById('departementEditLibelle')
                        .value =
                            departementValue(
                                departement,
                                ['libelle'],
                                ''
                            );


                    document
                        .getElementById('departementEditRegion')
                        .value =
                            String(
                                departementValue(
                                    departement,
                                    ['region_id'],
                                    ''
                                )
                            );

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