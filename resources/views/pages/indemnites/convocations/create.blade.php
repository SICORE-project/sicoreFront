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
                <p class="breadcrumb">Création d'une convocation pour un examen de certification</p>
            </div>
            <span class="badge badge-primary">Brouillon</span>
        </div>

        <form
            id="convocationForm"
            class="convocation-form"
            role="form"
            method="POST"
            action="{{ route('indemnites.convocations.store') }}"
            enctype="multipart/form-data"
            data-convocation-wizard
            data-wizard-mode="create"
            data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}"
            aria-describedby="{{ $errors->any() ? 'form-errors' : '' }}"
            novalidate
        >

            @csrf

            {{-- ============================================================
                 PROGRESSION
            ============================================================ --}}

            <div class="wizard-progress" aria-label="Progression du formulaire">
                <button class="wizard-step active" type="button" data-step-indicator="1">
                    <span class="wizard-step-number">1</span>
                    <span>Informations générales</span>
                </button>
                <button class="wizard-step" type="button" data-step-indicator="2">
                    <span class="wizard-step-number">2</span>
                    <span>Centres, jurys et membres</span>
                </button>
            </div>

            {{-- ============================================================
                 ERREURS
            ============================================================ --}}

            @if ($errors->any())
                <div id="form-errors" class="form-errors" role="alert">
                    <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ============================================================
                 ÉTAPE 1 — INFORMATIONS GÉNÉRALES
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="1">

                <div class="form-section">

                    <h3>Informations de la convocation</h3>
                    <p class="section-description">Renseignez les informations générales de la convocation.</p>

                    <div class="form-grid">

                        <div class="form-group full">
                            <label for="objet">Objet <span class="required">*</span></label>
                            <input
                                class="form-control @error('objet') is-invalid @enderror"
                                id="objet"
                                name="objet"
                                type="text"
                                placeholder="Ex : Examen de certification en Brevet de Technicien (BT)"
                                value="{{ old('objet') }}"
                                required
                            >
                            @error('objet')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group full">
                            <label for="session">Session (ex : BFEM 2026)</label>
                            <input
                                class="form-control @error('session') is-invalid @enderror"
                                id="session"
                                name="session"
                                type="text"
                                placeholder="Ex : BT 2026"
                                value="{{ old('session') }}"
                            >
                            @error('session')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group full">
                            <label for="type_convocation_id">Type de convocation</label>
                            <select
                                class="form-control @error('type_convocation_id') is-invalid @enderror"
                                id="type_convocation_id"
                                name="type_convocation_id"
                            >
                                <option value="">Sélectionner</option>
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
                            @error('type_convocation_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="date_emission">Date d'émission <span class="required">*</span></label>
                            <input
                                class="form-control @error('date_emission') is-invalid @enderror"
                                id="date_emission"
                                name="date_emission"
                                type="date"
                                value="{{ old('date_emission', date('Y-m-d')) }}"
                                required
                            >
                            @error('date_emission')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select class="form-control @error('statut') is-invalid @enderror" id="statut" name="statut">
                                @foreach (['brouillon' => 'Brouillon', 'emise' => 'Émise', 'envoyee' => 'Envoyée', 'cloturee' => 'Clôturée'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('statut', 'brouillon') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('statut')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>Période de l'examen</h3>
                    <p class="section-description">Indiquez la période et l'heure prévues pour l'examen.</p>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="date_debut">Du <span class="required">*</span></label>
                            <input
                                class="form-control @error('date_debut') is-invalid @enderror"
                                id="date_debut"
                                name="date_debut"
                                type="date"
                                value="{{ old('date_debut') }}"
                                required
                            >
                            @error('date_debut')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="date_fin">Au <span class="required">*</span></label>
                            <input
                                class="form-control @error('date_fin') is-invalid @enderror"
                                id="date_fin"
                                name="date_fin"
                                type="date"
                                value="{{ old('date_fin') }}"
                                required
                            >
                            @error('date_fin')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="heure_debut">À partir de <span class="required">*</span></label>
                            <input
                                class="form-control @error('heure_debut') is-invalid @enderror"
                                id="heure_debut"
                                name="heure_debut"
                                type="time"
                                value="{{ old('heure_debut', '08:00') }}"
                                required
                            >
                            @error('heure_debut')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="lieu_examen">Lieu d'examen</label>
                            <input
                                class="form-control @error('lieu_examen') is-invalid @enderror"
                                id="lieu_examen"
                                name="lieu_examen"
                                type="text"
                                value="{{ old('lieu_examen') }}"
                            >
                            @error('lieu_examen')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ÉTAPE 2 — CENTRES, JURYS ET MEMBRES
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="2" hidden>

                <div class="form-section">

                    <div class="panel-header">
                        <div>
                            <h3>Centres d'examen</h3>
                            <p>
                                Ajoutez les différents centres concernés par la convocation. Pour
                                chaque centre, précisez le jury et le chef de centre, puis ajoutez
                                un groupe par métier (ex : MVM, puis FC) avec ses propres membres.
                            </p>
                        </div>
                        <button class="btn-secondary" type="button" data-add-centre>
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            Ajouter un centre
                        </button>
                    </div>

                    <div class="centres-container" data-centres-container></div>

                    <p class="empty-message" data-centres-empty>Aucun centre ajouté pour le moment.</p>

                    @error('centres')<p class="field-error">{{ $message }}</p>@enderror

                </div>

            </section>

            {{-- ============================================================
                 TEMPLATE CENTRE
            ============================================================ --}}

            <template data-centre-template>

                <div class="centre-card" data-centre-card>

                    {{-- Vide en creation ; pre-rempli par edit.blade.php avec
                         l'id reel du centre existant, pour que le back sache
                         METTRE A JOUR cette ligne plutot que d'en creer une
                         nouvelle (cf. ConvocationSyncController). --}}
                    <input type="hidden" data-field="id">

                    <div class="centre-card-header">
                        <div>
                            <h4>Centre d'examen <span data-centre-number></span></h4>
                            <p>Centre, métier, jury et chef de centre</p>
                        </div>
                        <button type="button" class="icon-action" title="Supprimer le centre" aria-label="Supprimer le centre" data-remove-centre>
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label>Centre d'examen <span class="required">*</span></label>
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
                            <label>Jury</label>
                            <input class="form-control" type="text" placeholder="Ex : Jury 1" data-jury-input data-field="jury">
                        </div>

                        <div class="form-group">
                            <label>Président du jury</label>
                            <div class="enseignant-search" data-president-search>
                                <input class="form-control" type="text" placeholder="Rechercher le président du jury..." autocomplete="off" data-president-search-input>
                                <input type="hidden" data-president-id-input data-field="president_jury_id">
                                <ul class="enseignant-suggestions" data-president-suggestions hidden></ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Téléphone du président du jury</label>
                            <input class="form-control" type="text" placeholder="33 901 10 71" data-president-telephone-input data-field="president_jury_telephone">
                        </div>

                        <div class="form-group">
                            <label>Chef de centre</label>
                            <div class="enseignant-search" data-chef-search>
                                <input class="form-control" type="text" placeholder="Rechercher le chef de centre..." autocomplete="off" data-chef-search-input>
                                <input type="hidden" data-chef-id-input data-field="chef_centre_id">
                                <ul class="enseignant-suggestions" data-chef-suggestions hidden></ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Téléphone du chef de centre</label>
                            <input class="form-control" type="text" placeholder="33 901 10 71" data-chef-telephone-input data-field="chef_centre_telephone">
                        </div>

                    </div>

                    <div class="metier-groups-section">

                        <div class="panel-header">
                            <div>
                                <h4>Métiers &amp; membres du jury</h4>
                                <p>
                                    Un centre peut regrouper plusieurs métiers (ex : MVM puis FC).
                                    Ajoutez un groupe par métier, avec ses propres membres.
                                </p>
                            </div>
                            <button type="button" class="btn-secondary" data-add-metier-group>
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                Ajouter un groupe métier
                            </button>
                        </div>

                        <div class="metier-groups-container" data-metiers-container></div>

                        <p class="empty-message" data-metiers-empty>Aucun groupe métier ajouté pour ce centre.</p>

                        @error('centres.*.metier')<p class="field-error">{{ $message }}</p>@enderror

                    </div>

                </div>

            </template>

            {{-- ============================================================
                 TEMPLATE GROUPE MÉTIER
            ============================================================ --}}

            <template data-metier-group-template>

                <div class="metier-group" data-metier-group>

                    {{-- Vide en creation ; pre-rempli par edit.blade.php avec
                         l'id reel du metier existant . --}}
                    <input type="hidden" data-field="id">

                    <div class="metier-group-header">
                        <div>
                            <h5>Groupe métier <span data-metier-number></span></h5>
                            <p>
                                Laissez le métier vide pour un groupe "général"
                                (ex : président de jury, sans métier associé).
                            </p>
                        </div>
                        <button type="button" class="icon-action" title="Supprimer ce groupe" aria-label="Supprimer ce groupe métier" data-remove-metier-group>
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Métier / spécialité</label>
                            <input
                                class="form-control"
                                type="text"
                                placeholder="Ex : Technicien en Maintenance Véhicules Moteurs (MVM)"
                                data-metier-input
                                data-field="metier"
                            >
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table members-table">
                            <thead>
                                <tr>
                                    <th>Prénom</th>
                                    <th>Nom</th>
                                    <th>Fonction</th>
                                    <th>Statut</th>
                                    <th>Provenance</th>
                                    <th>Téléphone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody data-members-body></tbody>
                        </table>
                    </div>

                    <div class="member-import-actions">

                        <button type="button" class="btn-secondary" data-add-member>
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            Ajouter un membre
                        </button>

                        <label class="btn-secondary" data-import-members-label title="Fichier CSV avec les colonnes : matricule, fonction, statut, provenance">
                            <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                            Importer une liste (CSV)
                            <input type="file" accept=".csv,text/csv,text/plain" data-import-members-input hidden>
                        </label>

                    </div>

                    <p class="import-members-status" data-import-members-status hidden></p>

                    <p class="empty-message" data-members-empty>Aucun membre ajouté pour ce groupe.</p>

                </div>

            </template>

            {{-- ============================================================
                 TEMPLATE MEMBRE
            ============================================================ --}}

            <template data-member-template>

                <tr class="member-row">
                    <td data-label="Prénom">
                        <div class="enseignant-search" data-member-search>
                            <input class="form-control" type="text" placeholder="Rechercher..." autocomplete="off" data-member-search-input>
                            <input type="hidden" data-member-id-input>
                            <ul class="enseignant-suggestions" data-member-suggestions hidden></ul>
                        </div>
                    </td>
                    <td data-label="Nom">
                        <input class="form-control" type="text" placeholder="Nom" data-member-nom>
                    </td>
                    <td data-label="Fonction">
                        <select class="form-control" data-member-fonction>
                            <option value="">Sélectionner</option>
                            <option value="Président de jury">Président de jury</option>
                            <option value="Membre du jury">Membre du jury</option>
                            <option value="Surveillant/correcteur">Surveillant/correcteur</option>
                            <option value="Chef de centre">Chef de centre</option>
                        </select>
                    </td>
                    <td data-label="Statut">
                        <select class="form-control" data-member-categorie>
                            <option value="">Sélectionner</option>
                            <option value="fonctionnaire">Fonctionnaire</option>
                            <option value="contractuel">Contractuelle</option>
                            <option value="vacataire">Vacataire</option>
                        </select>
                    </td>
                    <td data-label="Provenance">
                        <input class="form-control" type="text" placeholder="Ex : LTP-FXN/THIES" data-member-provenance>
                    </td>
                    <td data-label="Téléphone">
                        <input class="form-control" type="text" placeholder="77 000 00 00" data-member-telephone>
                    </td>
                    <td class="actions-cell" data-label="Action">
                        <button type="button" class="icon-action" title="Retirer" aria-label="Retirer le membre" data-remove-member>
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>

            </template>

            {{-- ============================================================
                 MESSAGE
            ============================================================ --}}

            <p class="form-status" data-form-status aria-live="polite"></p>

            {{-- ============================================================
                 ACTIONS
            ============================================================ --}}

            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('indemnites.convocations') }}" data-wizard-cancel>
                    Annuler
                </a>
                <button class="btn-secondary" type="button" data-wizard-prev hidden>
                    <i class="fa-solid fa-arrow-left"></i>
                    Précédent
                </button>
                <button class="btn-primary" type="button" data-wizard-next>
                    Suivant
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button class="btn-primary" type="submit" data-wizard-submit hidden>
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
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
     Carte élargie et espacée : le .form-card global (app.css) est
     plafonné à 720px sans padding, insuffisant pour un formulaire à
     étapes avec tableaux de membres (min-width 1050px).
================================================================ --}}

@push('styles')
<style>

    .convocation-card {
        width: calc(100% - 40px);
        max-width: 1500px;
        margin: 24px auto 40px;
        overflow: hidden;
    }

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

    .convocation-card .convocation-form {
        width: 100%;
        padding: 30px 34px 34px;
        box-sizing: border-box;
    }

    .convocation-card .wizard-progress {
        margin: 0 0 30px;
        padding: 0;
    }

    .convocation-card .wizard-panel {
        width: 100%;
        box-sizing: border-box;
    }

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

    .convocation-card .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .convocation-card .required {
        margin-left: 3px;
    }

    .convocation-card .form-control {
        width: 100%;
        box-sizing: border-box;
    }

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

    .convocation-card .metier-groups-section {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .convocation-card .metier-groups-container {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin-top: 4px;
    }

    .convocation-card .metier-group {
        width: 100%;
        box-sizing: border-box;
        padding: 20px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .convocation-card .metier-group .form-grid {
        margin-bottom: 18px;
    }

    .convocation-card .metier-group .table-responsive {
        margin-bottom: 14px;
    }

    .convocation-card .metier-group-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    .convocation-card .metier-group-header h5 {
        margin: 0 0 5px;
    }

    .convocation-card .metier-group-header p {
        margin: 0;
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

    /* ------------------------------------------------------------
       Le tableau des membres du jury (7 colonnes) ne rentre pas de
       facon fiable sur toutes les tailles d'ecran/fenetre. Plutot que
       de compter sur un defilement horizontal (peu visible selon les
       systemes/navigateurs, source de confusion), chaque membre est
       affiche en carte empilee : toutes les colonnes restent visibles
       en permanence, sans jamais avoir a glisser quoi que ce soit.
    ------------------------------------------------------------ */

    .convocation-card .table-responsive {
        width: 100%;
        overflow: visible;
    }

    .convocation-card .members-table {
        display: block;
        width: 100%;
    }

    .convocation-card .members-table thead {
        display: none;
    }

    .convocation-card .members-table tbody {
        display: block;
    }

    .convocation-card .members-table tbody:empty {
        display: none;
    }

    .convocation-card .members-table tr.member-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 14px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fafafa;
    }

    .convocation-card .members-table td {
        display: block;
        padding: 0;
    }

    .convocation-card .members-table td[data-label]::before {
        content: attr(data-label);
        display: block;
        margin-bottom: 4px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6b7280;
    }

    .convocation-card .members-table .form-control {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .convocation-card .actions-cell {
        display: flex;
        align-items: flex-end;
    }

    .convocation-card .enseignant-search {
        position: relative;
    }

    .convocation-card .enseignant-suggestions {
        position: absolute;
        z-index: 20;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        margin: 0;
        padding: 4px 0;
        list-style: none;
        max-height: 220px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    .convocation-card .enseignant-suggestions li {
        padding: 8px 12px;
        cursor: pointer;
        white-space: nowrap;
    }

    .convocation-card .enseignant-suggestions li:hover {
        background: #f3f4f6;
    }

    .convocation-card .empty-message {
        margin: 18px 0 0;
        padding: 14px 16px;
        text-align: center;
        border: 1px dashed #d1d5db;
        border-radius: 8px;
    }

    .convocation-card .member-import-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .convocation-card .member-import-actions label.btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        margin: 0;
    }

    .convocation-card .import-members-status {
        margin: 10px 0 0;
        font-size: 13px;
        color: #4b5563;
    }

    .convocation-card .form-status {
        margin: 24px 0 0;
    }

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

    .convocation-card .form-errors {
        margin: 0 0 24px;
    }

    .convocation-card .field-error {
        margin-top: 6px;
    }

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
