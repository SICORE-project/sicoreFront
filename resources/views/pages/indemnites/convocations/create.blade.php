@extends('layouts.app')

@section('title', 'SICORE - Nouvelle convocation')

@section('content')

<main class="main-content">

<x-topbar
    title="Nouvelle convocation"
    subtitle="Indemnites > Convocations > Nouvelle convocation"
    icon="fa-solid fa-envelope-open-text"
/>

<section class="content-area">

    <section class="form-card wizard-card convocation-card">

      <div class="form-card-header">

    <div>
        <h2>Nouvelle convocation</h2>

        <p class="breadcrumb">
            Création d'une convocation pour un examen de certification
        </p>
    </div>

    <span class="badge badge-primary">
        Brouillon
    </span>

</div>


        <form
            id="convocationForm"
            class="convocation-form"
            role="form"
            method="POST"
            action="{{ route('indemnites.convocations.store') }}"
            data-convocation-wizard
            data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}"
            aria-describedby="{{ $errors->any() ? 'form-errors' : '' }}"
            novalidate
        >

            @csrf


            {{-- ============================================================
                 PROGRESSION
            ============================================================ --}}

            <div
                class="wizard-progress"
                aria-label="Progression du formulaire"
            >

                <button
                    class="wizard-step active"
                    type="button"
                    data-step-indicator="1"
                >

                    <span class="wizard-step-number">
                        1
                    </span>

                    <span>
                        Informations générales
                    </span>

                </button>


                <button
                    class="wizard-step"
                    type="button"
                    data-step-indicator="2"
                >

                    <span class="wizard-step-number">
                        2
                    </span>

                    <span>
                        Centres d'examen
                    </span>

                </button>


                <button
                    class="wizard-step"
                    type="button"
                    data-step-indicator="3"
                >

                    <span class="wizard-step-number">
                        3
                    </span>

                    <span>
                        Membres du jury
                    </span>

                </button>

            </div>


            {{-- ============================================================
                 ERREURS
            ============================================================ --}}

            @if ($errors->any())

                <div
                    id="form-errors"
                    class="form-errors"
                    role="alert"
                >

                    <p>
                        <strong>
                            Veuillez corriger les erreurs suivantes :
                        </strong>
                    </p>

                    <ul>

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- ============================================================
                 ÉTAPE 1
            ============================================================ --}}

            <section
                class="wizard-panel"
                data-wizard-panel="1"
            >

                <div class="form-section">

                    <h3>
                        Informations de la convocation
                    </h3>

                    <p class="section-description">
                        Renseignez les informations générales de la convocation.
                    </p>


                    <div class="form-grid">

                        <div class="form-group full">

                            <label for="objet">

                                Objet

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                class="form-control @error('objet') is-invalid @enderror"
                                id="objet"
                                name="objet"
                                type="text"
                                placeholder="Ex : Examen de certification en Brevet de Technicien (BT)"
                                value="{{ old('objet') }}"
                                required
                            >

                            @error('objet')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div class="form-group full">

                            <label for="type_convocation_id">
                                Type de convocation
                            </label>

                            <select
                                class="form-control @error('type_convocation_id') is-invalid @enderror"
                                id="type_convocation_id"
                                name="type_convocation_id"
                            >

                                <option value="">
                                    Sélectionner
                                </option>

                                @foreach ($typesConvocation ?? [] as $type)

                                    <option
                                        value="{{ $type['id'] }}"
                                        @selected((string) old('type_convocation_id') === (string) $type['id'])
                                    >
                                        {{ $type['libelle'] }}
                                    </option>

                                @endforeach

                            </select>

                            <p class="section-description" style="margin: 6px 0 0;">
                                Détermine le modèle utilisé pour le PDF (ex : tableau centre/jury/métier pour un jury d'examen).
                            </p>

                            @error('type_convocation_id')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div class="form-group">

                            <label for="date_emission">

                                Date d'émission

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                class="form-control @error('date_emission') is-invalid @enderror"
                                id="date_emission"
                                name="date_emission"
                                type="date"
                                value="{{ old('date_emission', date('Y-m-d')) }}"
                                required
                            >

                            @error('date_emission')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div class="form-group">

                            <label for="statut">
                                Statut
                            </label>

                            <select
                                class="form-control @error('statut') is-invalid @enderror"
                                id="statut"
                                name="statut"
                            >

                                <option
                                    value="brouillon"
                                    @selected(old('statut', 'brouillon') === 'brouillon')
                                >
                                    Brouillon
                                </option>

                                <option
                                    value="emise"
                                    @selected(old('statut') === 'emise')
                                >
                                    Émise
                                </option>

                                <option
                                    value="envoyee"
                                    @selected(old('statut') === 'envoyee')
                                >
                                    Envoyée
                                </option>

                                <option
                                    value="cloturee"
                                    @selected(old('statut') === 'cloturee')
                                >
                                    Clôturée
                                </option>

                            </select>

                            @error('statut')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                <div class="form-section">

                    <h3>
                        Période de l'examen
                    </h3>

                    <p class="section-description">
                        Indiquez la période et l'heure prévues pour l'examen.
                    </p>


                    <div class="form-grid">

                        <div class="form-group">

                            <label for="date_debut">

                                Du

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                class="form-control @error('date_debut') is-invalid @enderror"
                                id="date_debut"
                                name="date_debut"
                                type="date"
                                value="{{ old('date_debut') }}"
                                required
                            >

                            @error('date_debut')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div class="form-group">

                            <label for="date_fin">

                                Au

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                class="form-control @error('date_fin') is-invalid @enderror"
                                id="date_fin"
                                name="date_fin"
                                type="date"
                                value="{{ old('date_fin') }}"
                                required
                            >

                            @error('date_fin')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div class="form-group">

                            <label for="heure_debut">

                                À partir de

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                class="form-control @error('heure_debut') is-invalid @enderror"
                                id="heure_debut"
                                name="heure_debut"
                                type="time"
                                value="{{ old('heure_debut', '08:00') }}"
                                required
                            >

                            @error('heure_debut')

                                <p class="field-error">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                <div class="form-section">

                    <h3>
                        Documents associés
                    </h3>

                    <div class="form-grid">

                        <div class="form-group full">

                            <label
                                class="checkbox-label"
                                for="ordre_de_mission"
                            >

                                <input
                                    id="ordre_de_mission"
                                    name="ordre_de_mission"
                                    type="checkbox"
                                    value="1"
                                    @checked(old('ordre_de_mission'))
                                >

                                Joindre un ordre de mission

                            </label>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ============================================================
                 ÉTAPE 2
            ============================================================ --}}

            <section
                class="wizard-panel"
                data-wizard-panel="2"
                hidden
            >

                <div class="form-section">

                    <div class="panel-header">

                        <div>

                            <h3>
                                Centres d'examen
                            </h3>

                            <p>
                                Ajoutez les différents centres concernés par la convocation.
                            </p>

                        </div>


                        <button
                            class="btn-secondary"
                            type="button"
                            data-add-centre
                        >

                            <i
                                class="fa-solid fa-plus"
                                aria-hidden="true"
                            ></i>

                            Ajouter un centre

                        </button>

                    </div>


                    <div
                        class="centres-container"
                        data-centres-container
                    ></div>


                    <p
                        class="empty-message"
                        data-centres-empty
                    >
                        Aucun centre ajouté pour le moment.
                    </p>

                    @error('centres')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            </section>


            {{-- ============================================================
                 TEMPLATE CENTRE
            ============================================================ --}}

            <template data-centre-template>

                <div
                    class="centre-card"
                    data-centre-card
                >

                    <div class="centre-card-header">

                        <div>

                            <h4>

                                Centre d'examen

                                <span data-centre-number></span>

                            </h4>

                            <p>
                                Centre, métier, jury et chef de centre
                            </p>

                        </div>


                        <button
                            type="button"
                            class="icon-action"
                            title="Supprimer le centre"
                            aria-label="Supprimer le centre"
                            data-remove-centre
                        >

                            <i
                                class="fa-solid fa-trash"
                                aria-hidden="true"
                            ></i>

                        </button>

                    </div>


                    <div class="form-grid">

                        <div class="form-group">

                            <label>

                                Centre d'examen

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                class="form-control"
                                type="text"
                                placeholder="Ex : Centre LTP FXN/THIES"
                                data-centre-input
                                data-field="centre"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Jury
                            </label>

                            <input
                                class="form-control"
                                type="text"
                                placeholder="Ex : Jury 1"
                                data-jury-input
                                data-field="jury"
                            >

                        </div>


                        <div class="form-group full">

                            <label>
                                Métier / spécialité
                            </label>

                            <input
                                class="form-control"
                                type="text"
                                placeholder="Ex : Technicien en Maintenance Véhicules Moteurs (MVM)"
                                data-metier-input
                                data-field="metier"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Chef de centre
                            </label>

                            <div
                                class="enseignant-search"
                                data-chef-search
                            >

                                <input
                                    class="form-control"
                                    type="text"
                                    placeholder="Rechercher le chef de centre..."
                                    autocomplete="off"
                                    data-chef-search-input
                                >

                                <input
                                    type="hidden"
                                    data-chef-id-input
                                    data-field="chef_centre_id"
                                >

                                <ul
                                    class="enseignant-suggestions"
                                    data-chef-suggestions
                                    hidden
                                ></ul>

                            </div>

                        </div>


                        <div class="form-group">

                            <label>
                                Téléphone du chef de centre
                            </label>

                            <input
                                class="form-control"
                                type="text"
                                placeholder="33 901 10 71"
                                data-chef-telephone-input
                                data-field="chef_centre_telephone"
                            >

                        </div>

                    </div>


                    <div class="centre-members-section">

                        <div class="panel-header">

                            <div>

                                <h4>
                                    Membres du jury
                                </h4>

                                <p>
                                    Ajoutez les personnes affectées à ce centre.
                                </p>

                            </div>


                            <button
                                type="button"
                                class="btn-secondary"
                                data-add-member
                            >

                                <i
                                    class="fa-solid fa-plus"
                                    aria-hidden="true"
                                ></i>

                                Ajouter un membre

                            </button>

                        </div>


                        <div class="table-responsive">

                            <table class="table members-table">

                                <thead>

                                    <tr>

                                        <th>
                                            Prénom
                                        </th>

                                        <th>
                                            Nom
                                        </th>

                                        <th>
                                            Fonction
                                        </th>

                                        <th>
                                            Provenance
                                        </th>

                                        <th>
                                            Téléphone
                                        </th>

                                        <th>
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody data-members-body></tbody>

                            </table>

                        </div>


                        <p
                            class="empty-message"
                            data-members-empty
                        >
                            Aucun membre ajouté pour ce centre.
                        </p>

                    </div>

                </div>

            </template>


            {{-- ============================================================
                 TEMPLATE MEMBRE
            ============================================================ --}}

            <template data-member-template>

                <tr class="member-row">

                    <td>

                        <div
                            class="enseignant-search"
                            data-member-search
                        >

                            <input
                                class="form-control"
                                type="text"
                                placeholder="Rechercher..."
                                autocomplete="off"
                                data-member-search-input
                            >

                            <input
                                type="hidden"
                                data-member-id-input
                            >

                            <ul
                                class="enseignant-suggestions"
                                data-member-suggestions
                                hidden
                            ></ul>

                        </div>

                    </td>


                    <td>

                        <input
                            class="form-control"
                            type="text"
                            placeholder="Nom"
                            data-member-nom
                        >

                    </td>


                    <td>

                        <select
                            class="form-control"
                            data-member-fonction
                        >

                            <option value="">
                                Sélectionner
                            </option>

                            <option value="Président de jury">
                                Président de jury
                            </option>

                            <option value="Membre du jury">
                                Membre du jury
                            </option>

                            <option value="Surveillant/correcteur">
                                Surveillant/correcteur
                            </option>

                            <option value="Chef de centre">
                                Chef de centre
                            </option>

                        </select>

                    </td>


                    <td>

                        <input
                            class="form-control"
                            type="text"
                            placeholder="Ex : LTP-FXN/THIES"
                            data-member-provenance
                        >

                    </td>


                    <td>

                        <input
                            class="form-control"
                            type="text"
                            placeholder="77 000 00 00"
                            data-member-telephone
                        >

                    </td>


                    <td class="actions-cell">

                        <button
                            type="button"
                            class="icon-action"
                            title="Retirer"
                            aria-label="Retirer le membre"
                            data-remove-member
                        >

                            <i
                                class="fa-solid fa-trash"
                                aria-hidden="true"
                            ></i>

                        </button>

                    </td>

                </tr>

            </template>


            {{-- ============================================================
                 MESSAGE
            ============================================================ --}}

            <p
                class="form-status"
                data-form-status
                aria-live="polite"
            ></p>


            {{-- ============================================================
                 ACTIONS
            ============================================================ --}}

            <div class="form-actions">

                <a
                    class="btn-secondary"
                    href="{{ route('indemnites.convocations') }}"
                    data-wizard-cancel
                >
                    Annuler
                </a>


                <button
                    class="btn-secondary"
                    type="button"
                    data-wizard-prev
                    hidden
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Précédent
                </button>


                <button
                    class="btn-primary"
                    type="button"
                    data-wizard-next
                >
                    Suivant
                    <i class="fa-solid fa-arrow-right"></i>
                </button>


                <button
                    class="btn-primary"
                    type="submit"
                    data-wizard-submit
                    hidden
                >

                    <i
                        class="fa-solid fa-floppy-disk"
                        aria-hidden="true"
                    ></i>

                    Enregistrer la convocation

                </button>

            </div>

        </form>

    </section>

</section>


</main>

@endsection

{{-- ================================================================
JAVASCRIPT
================================================================ --}}

@push('scripts')


<script src="{{ asset('assets/js/indemnites/convocation-wizard.js') }}"></script>


@endpush

{{-- ================================================================
STYLES
================================================================ --}}

@push('styles')

<style>

    /* ============================================================
       CARTE PRINCIPALE
    ============================================================ */

    .convocation-card {
        width: calc(100% - 40px);
        max-width: 1500px;
        margin: 24px auto 40px;
        overflow: hidden;
    }


    /* ============================================================
       EN-TÊTE DE LA CARTE
    ============================================================ */

    .convocation-card .form-card-header {
        padding: 28px 34px;
        margin: 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .convocation-card .form-card-header h2 {
        margin: 0 0 6px;
    }

    .convocation-card .form-card-header .breadcrumb {
        margin: 0;
    }


    /* ============================================================
       FORMULAIRE
    ============================================================ */

    .convocation-card .convocation-form {
        width: 100%;
        padding: 30px 34px 34px;
        box-sizing: border-box;
    }


    /* ============================================================
       PROGRESSION
    ============================================================ */

    .convocation-card .wizard-progress {
        margin: 0 0 30px;
        padding: 0;
    }


    /* ============================================================
       PANELS
    ============================================================ */

    .convocation-card .wizard-panel {
        width: 100%;
        box-sizing: border-box;
    }


    /* ============================================================
       SECTIONS
    ============================================================ */

    .convocation-card .form-section {
        width: 100%;
        margin: 0 0 24px;
        padding: 26px 28px;
        box-sizing: border-box;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .convocation-card .form-section:last-child {
        margin-bottom: 0;
    }

    .convocation-card .form-section h3 {
        margin: 0 0 6px;
    }

    .convocation-card .section-description {
        margin: 0 0 24px;
    }


    /* ============================================================
       GRILLE DU FORMULAIRE
    ============================================================ */

    .convocation-card .form-grid {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px 24px;
    }

    .convocation-card .form-group {
        min-width: 0;
        width: 100%;
    }

    .convocation-card .form-group.full {
        grid-column: 1 / -1;
    }


    /* ============================================================
       LABELS
    ============================================================ */

    .convocation-card .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .convocation-card .required {
        margin-left: 3px;
    }


    /* ============================================================
       CHAMPS
    ============================================================ */

    .convocation-card .form-control {
        width: 100%;
        box-sizing: border-box;
    }


    /* ============================================================
       CENTRES
    ============================================================ */

    .convocation-card .centres-container {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 22px;
        margin-top: 24px;
    }

    .convocation-card .centre-card {
        width: 100%;
        box-sizing: border-box;
        padding: 24px;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        background: #fafbfc;
    }


    /* ============================================================
       EN-TÊTE CENTRE
    ============================================================ */

    .convocation-card .centre-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .convocation-card .centre-card-header h4 {
        margin: 0 0 5px;
    }

    .convocation-card .centre-card-header p {
        margin: 0;
    }


    /* ============================================================
       MEMBRES DU JURY
    ============================================================ */

    .convocation-card .centre-members-section {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .convocation-card .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 18px;
    }

    .convocation-card .panel-header h3,
    .convocation-card .panel-header h4 {
        margin: 0 0 5px;
    }

    .convocation-card .panel-header p {
        margin: 0;
    }


    /* ============================================================
       TABLEAU DES MEMBRES
    ============================================================ */

    .convocation-card .table-responsive {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .convocation-card .members-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .convocation-card .members-table th,
    .convocation-card .members-table td {
        padding: 12px 14px;
        vertical-align: middle;
    }

    .convocation-card .members-table th {
        white-space: nowrap;
    }

    .convocation-card .members-table .form-control {
        min-width: 120px;
    }

    .convocation-card .actions-cell {
        width: 70px;
        text-align: center;
        white-space: nowrap;
    }


    /* ============================================================
       MESSAGE VIDE
    ============================================================ */

    .convocation-card .empty-message {
        margin: 18px 0 0;
        padding: 14px 16px;
        text-align: center;
        border: 1px dashed #d1d5db;
        border-radius: 8px;
    }


    /* ============================================================
       MESSAGE DE STATUT
    ============================================================ */

    .convocation-card .form-status {
        margin: 24px 0 0;
    }


    /* ============================================================
       ACTIONS
    ============================================================ */

    .convocation-card .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 30px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .convocation-card .form-actions .btn-secondary,
    .convocation-card .form-actions .btn-primary {
        white-space: nowrap;
    }


    /* ============================================================
       ERREURS
    ============================================================ */

    .convocation-card .form-errors {
        margin: 0 0 24px;
    }

    .convocation-card .field-error {
        margin-top: 6px;
    }


    /* ============================================================
       RESPONSIVE
    ============================================================ */

    @media (max-width: 1100px) {

        .convocation-card {
            width: calc(100% - 30px);
        }

        .convocation-card .convocation-form {
            padding: 24px;
        }

        .convocation-card .form-section {
            padding: 22px;
        }

    }


    @media (max-width: 768px) {

        .convocation-card {
            width: calc(100% - 20px);
            margin-top: 15px;
        }

        .convocation-card .form-card-header {
            padding: 22px;
        }

        .convocation-card .convocation-form {
            padding: 18px;
        }

        .convocation-card .form-section {
            padding: 18px;
        }

        .convocation-card .form-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .convocation-card .form-group.full {
            grid-column: auto;
        }

        .convocation-card .panel-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .convocation-card .panel-header .btn-secondary {
            width: 100%;
        }

        .convocation-card .centre-card {
            padding: 18px;
        }

        .convocation-card .centre-card-header {
            gap: 12px;
        }

        .convocation-card .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .convocation-card .form-actions a,
        .convocation-card .form-actions button {
            width: 100%;
            justify-content: center;
        }

    }

</style>

@endpush