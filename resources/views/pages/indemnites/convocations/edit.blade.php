@extends('layouts.app')

@section('title', 'SICORE - Modifier convocation')

@section('content')

<main class="main-content">

<x-topbar
    title="Modifier la convocation"
    subtitle="Indemnites > Convocations > Modifier"
    icon="fa-solid fa-envelope-open-text"
/>

<section class="content-area">

    <section class="form-card wizard-card convocation-card">

        <div class="form-card-header">
            <div>
                <h2>Modifier la convocation</h2>
                <p class="breadcrumb">Mise à jour de la convocation et de ses centres d'examen</p>
            </div>
           
            <x-module-indemnite type="statut-convocation" :statut="$convocation->statut ?? 'brouillon'" />
        </div>

        <form
            id="convocationForm"
            class="convocation-form"
            role="form"
            method="POST"
            action="{{ route('indemnites.convocations.update', $id) }}"
            enctype="multipart/form-data"
            data-convocation-wizard
            data-wizard-mode="edit"
            data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}"
            aria-describedby="{{ $errors->any() ? 'form-errors' : '' }}"
            novalidate
        >

            @csrf
            @method('PUT')

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
                                value="{{ old('objet', $convocation->objet ?? '') }}"
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
                                value="{{ old('session', $convocation->session ?? '') }}"
                            >
                            @error('session')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="date_emission">Date d'émission <span class="required">*</span></label>
                            <input
                                class="form-control @error('date_emission') is-invalid @enderror"
                                id="date_emission"
                                name="date_emission"
                                type="date"
                                value="{{ old('date_emission', optional($convocation->date_emission ?? null)->format('Y-m-d')) }}"
                                required
                            >
                            @error('date_emission')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select class="form-control @error('statut') is-invalid @enderror" id="statut" name="statut">
                                @foreach (['brouillon' => 'Brouillon', 'emise' => 'Émise', 'envoyee' => 'Envoyée', 'cloturee' => 'Clôturée'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('statut', $convocation->statut ?? 'brouillon') === $value)>
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
                                value="{{ old('date_debut', optional($convocation->date_debut ?? null)->format('Y-m-d')) }}"
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
                                value="{{ old('date_fin', optional($convocation->date_fin ?? null)->format('Y-m-d')) }}"
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
                                value="{{ old('heure_debut', $convocation->heure_debut ?? '08:00') }}"
                                required
                            >
                            @error('heure_debut')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="lieu_affectation">Lieu d'affectation</label>
                            <input
                                class="form-control @error('lieu_affectation') is-invalid @enderror"
                                id="lieu_affectation"
                                name="lieu_affectation"
                                type="text"
                                value="{{ old('lieu_affectation', $convocation->lieu_affectation ?? '') }}"
                                placeholder="Ex : Dakar"
                            >
                            @error('lieu_affectation')<p class="field-error">{{ $message }}</p>@enderror
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
                            <label>Provenance du président du jury</label>
                            <input class="form-control" type="text" placeholder="Ex : Dakar" data-field="president_jury_provenance">
                            <p class="section-description" style="margin: 6px 0 0;">Lieu où il exerce habituellement — sert au calcul des frais de déplacement.</p>
                        </div>

                        <div class="form-group">
                            <label>Catégorie de personnel du président du jury</label>
                            <select class="form-control" data-field="president_jury_categorie_personnel">
                                <option value="">Sélectionner</option>
                                <option value="fonctionnaire">Fonctionnaire</option>
                                <option value="contractuel">Contractuelle</option>
                                <option value="vacataire">Vacataire</option>
                            </select>
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

                        <div class="form-group">
                            <label>Provenance du chef de centre</label>
                            <input class="form-control" type="text" placeholder="Ex : Dakar" data-field="chef_centre_provenance">
                            <p class="section-description" style="margin: 6px 0 0;">Lieu où il exerce habituellement — sert au calcul des frais de déplacement.</p>
                        </div>

                        <div class="form-group">
                            <label>Catégorie de personnel du chef de centre</label>
                            <select class="form-control" data-field="chef_centre_categorie_personnel">
                                <option value="">Sélectionner</option>
                                <option value="fonctionnaire">Fonctionnaire</option>
                                <option value="contractuel">Contractuelle</option>
                                <option value="vacataire">Vacataire</option>
                            </select>
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
                                    <th>Type de convocation</th>
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
                    <td data-label="Type de convocation">
                        <select class="form-control" data-member-fonction>
                            <option value="">Sélectionner</option>
                            <option value="Correction">Correction</option>
                            <option value="Surveillant">Surveillant</option>
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
                <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}" data-wizard-cancel>
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
<script>
    window.__convocationWizardPrefill = @json($wizardData ?? []);
</script>
<script src="{{ asset('assets/js/indemnites/convocation-wizard.js') }}"></script>
@endpush

{{-- ================================================================
     STYLES
================================================================ --}}

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/indemnites/convocation-wizard.css') }}">
@endpush
