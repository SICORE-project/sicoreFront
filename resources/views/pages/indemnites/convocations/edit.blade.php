@extends('layouts.app')

@section('title', 'SICORE - Modifier la convocation')

@section('content')

<main class="main-content">

<x-topbar
    title="Modifier la convocation"
    subtitle="Indemnites > Convocations > Modifier"
    icon="fa-solid fa-envelope-open-text"
/>

<section class="content-area">

    <section class="form-card convocation-card">

        <div class="form-card-header">
            <div>
                <h2>Modifier la convocation</h2>
                <p class="breadcrumb">{{ $convocation->objet ?? '—' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('indemnites.convocations.update', $id) }}" class="convocation-edit-form">

            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="form-errors" role="alert">
                    <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section">

                <div class="form-grid">

                    <div class="form-group full">
                        <label for="objet">
                            Objet <span class="required">*</span>
                        </label>
                        <input
                            class="form-control @error('objet') is-invalid @enderror"
                            id="objet"
                            name="objet"
                            type="text"
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
                            value="{{ old('session', $convocation->session ?? '') }}"
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
                                    @selected((string) old('type_convocation_id', $convocation->typeConvocation['id'] ?? '') === (string) $type['id'])
                                >
                                    {{ $type['libelle'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_convocation_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="date_emission">
                            Date d'émission <span class="required">*</span>
                        </label>
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

                    <div class="form-group">
                        <label for="date_debut">Du</label>
                        <input
                            class="form-control @error('date_debut') is-invalid @enderror"
                            id="date_debut"
                            name="date_debut"
                            type="date"
                            value="{{ old('date_debut', optional($convocation->date_debut ?? null)->format('Y-m-d')) }}"
                        >
                        @error('date_debut')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="date_fin">Au</label>
                        <input
                            class="form-control @error('date_fin') is-invalid @enderror"
                            id="date_fin"
                            name="date_fin"
                            type="date"
                            value="{{ old('date_fin', optional($convocation->date_fin ?? null)->format('Y-m-d')) }}"
                        >
                        @error('date_fin')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="heure_debut">À partir de</label>
                        <input
                            class="form-control @error('heure_debut') is-invalid @enderror"
                            id="heure_debut"
                            name="heure_debut"
                            type="time"
                            value="{{ old('heure_debut', $convocation->heure_debut ?? '') }}"
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
                            value="{{ old('lieu_examen', $convocation->lieu_examen ?? '') }}"
                        >
                        @error('lieu_examen')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="lieu_affectation">Lieu d'affectation</label>
                        <input
                            class="form-control @error('lieu_affectation') is-invalid @enderror"
                            id="lieu_affectation"
                            name="lieu_affectation"
                            type="text"
                            value="{{ old('lieu_affectation', $convocation->lieu_affectation ?? '') }}"
                        >
                        @error('lieu_affectation')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                </div>

            </div>

            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
                    Annuler
                </a>
                <button class="btn-primary" type="submit">
                    Enregistrer les modifications
                </button>
            </div>

        </form>

        {{-- ============================================================
             CENTRES D'EXAMEN
             Formulaires separes du formulaire principal ci-dessus : chaque
             ajout est un POST independant vers un endpoint deja utilise a
             la creation (ConvocationCentreController::store()), qui accepte
             tres bien une convocation existante.
        ============================================================ --}}

        <div class="form-section sub-form-section">

            <div class="panel-header">
                <h3>Centres d'examen</h3>
            </div>

            @if (empty($centres))

                <p class="empty-message">Aucun centre ajouté pour le moment.</p>

            @else

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Centre</th>
                                <th>Jury</th>
                                <th>Métier</th>
                                <th>Chef de centre</th>
                                <th>Téléphone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($centres as $centre)
                                <tr>
                                    <td>{{ $centre['centre'] ?? '—' }}</td>
                                    <td>{{ $centre['jury'] ?? '—' }}</td>
                                    <td>{{ $centre['metier'] ?? '—' }}</td>
                                    <td>{{ $centre['chefCentre']['nom'] ?? '—' }}</td>
                                    <td>{{ $centre['chef_centre_telephone'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

            <form method="POST" action="{{ route('indemnites.convocations.centres.store', $id) }}" class="add-sub-form">

                @csrf

                <div class="form-grid">

                    <div class="form-group">
                        <label for="centre">Centre d'examen <span class="required">*</span></label>
                        <input class="form-control" id="centre" name="centre" type="text" placeholder="Ex : Centre LTP FXN/THIES" required>
                    </div>

                    <div class="form-group">
                        <label for="jury">Jury</label>
                        <input class="form-control" id="jury" name="jury" type="text" placeholder="Ex : Jury 1">
                    </div>

                    <div class="form-group">
                        <label>Chef de centre</label>
                        <div class="enseignant-search" data-enseignant-search data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}">
                            <input class="form-control" type="text" placeholder="Rechercher..." autocomplete="off" data-enseignant-search-input>
                            <input type="hidden" name="chef_centre_id" data-enseignant-id-input>
                            <ul class="enseignant-suggestions" data-enseignant-suggestions hidden></ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="chef_centre_telephone">Téléphone du chef de centre</label>
                        <input class="form-control" id="chef_centre_telephone" name="chef_centre_telephone" type="text" data-enseignant-telephone-target>
                    </div>

                </div>

                <div class="form-actions">
                    <button class="btn-secondary" type="submit">Ajouter le centre</button>
                </div>

            </form>

        </div>

        {{-- ============================================================
             MEMBRES DU JURY
        ============================================================ --}}

        <div class="form-section sub-form-section">

            <div class="panel-header">
                <h3>Membres du jury</h3>
            </div>

            @if (empty($beneficiaires))

                <p class="empty-message">Aucun membre ajouté pour le moment.</p>

            @else

                @php
                    $statutsPersonnel = [
                        'fonctionnaire' => 'Fonctionnaire',
                        'contractuel' => 'Contractuelle',
                        'vacataire' => 'Vacataire',
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Fonction</th>
                                <th>Statut</th>
                                <th>Provenance</th>
                                <th>Téléphone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($beneficiaires as $beneficiaire)
                                <tr>
                                    <td>{{ trim(($beneficiaire['prenom'] ?? '') . ' ' . ($beneficiaire['nom'] ?? '')) ?: '—' }}</td>
                                    <td>{{ $beneficiaire['pivot']['fonction'] ?? '—' }}</td>
                                    <td>{{ $statutsPersonnel[$beneficiaire['categorie_personnel'] ?? null] ?? '—' }}</td>
                                    <td>{{ $beneficiaire['pivot']['provenance'] ?? '—' }}</td>
                                    <td>{{ $beneficiaire['telephone'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

            <form method="POST" action="{{ route('indemnites.convocations.beneficiaires.store', $id) }}" class="add-sub-form">

                @csrf

                <div class="form-grid">

                    <div class="form-group full">
                        <label>Enseignant <span class="required">*</span></label>
                        <div class="enseignant-search" data-enseignant-search data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}">
                            <input class="form-control" type="text" placeholder="Rechercher un enseignant..." autocomplete="off" data-enseignant-search-input>
                            <input type="hidden" name="enseignant_id" data-enseignant-id-input>
                            <ul class="enseignant-suggestions" data-enseignant-suggestions hidden></ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fonction">Fonction</label>
                        <select class="form-control" id="fonction" name="fonction">
                            <option value="">Sélectionner</option>
                            <option value="Président de jury">Président de jury</option>
                            <option value="Membre du jury">Membre du jury</option>
                            <option value="Surveillant/correcteur">Surveillant/correcteur</option>
                            <option value="Chef de centre">Chef de centre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categorie_personnel">Statut</label>
                        <select class="form-control" id="categorie_personnel" name="categorie_personnel" data-enseignant-categorie-target>
                            <option value="">Sélectionner</option>
                            <option value="fonctionnaire">Fonctionnaire</option>
                            <option value="contractuel">Contractuelle</option>
                            <option value="vacataire">Vacataire</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="provenance">Provenance</label>
                        <input class="form-control" id="provenance" name="provenance" type="text" placeholder="Ex : LTP-FXN/THIES">
                    </div>

                    @if (! empty($centres))
                        <div class="form-group">
                            <label for="centre_id">Centre</label>
                            <select class="form-control" id="centre_id" name="centre_id">
                                <option value="">Aucun</option>
                                @foreach ($centres as $centre)
                                    <option value="{{ $centre['id'] }}">{{ $centre['centre'] ?? ('Centre #' . $centre['id']) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                </div>

                <div class="form-actions">
                    <button class="btn-secondary" type="submit">Ajouter le membre</button>
                </div>

            </form>

        </div>

    </section>

</section>

</main>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/indemnites/convocation-edit.js') }}"></script>
@endpush

{{-- ================================================================
     STYLES
     Même correctif que show.blade.php : le .form-card global (app.css)
     est plafonné à 720px et sans padding, ce qui écrasait le formulaire.
     Ici pas de wrapper .convocation-form (le <form> encapsule
     directement une seule .form-section), donc le padding cible
     .convocation-edit-form au lieu de .convocation-form.
================================================================ --}}

@push('styles')
<style>

    .convocation-card {
        width: calc(100% - 40px);
        max-width: 980px;
        margin: 24px auto 40px;
    }

    .convocation-card .convocation-edit-form {
        display: grid;
        gap: 22px;
        padding: 26px 30px 30px;
        box-sizing: border-box;
    }

    .convocation-card .form-section {
        padding: 22px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .convocation-card .sub-form-section {
        margin: 0 30px 24px;
    }

    .convocation-card .sub-form-section:first-of-type {
        margin-top: 24px;
    }

    .convocation-card .sub-form-section:last-of-type {
        margin-bottom: 30px;
    }

    .convocation-card .sub-form-section .panel-header {
        margin-bottom: 14px;
    }

    .convocation-card .sub-form-section .table-responsive {
        margin-bottom: 20px;
    }

    .convocation-card .add-sub-form .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px 20px;
    }

    .convocation-card .add-sub-form .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 16px;
        padding-top: 0;
        border-top: none;
    }

    .convocation-card .enseignant-search {
        position: relative;
    }

    .convocation-card .enseignant-suggestions {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 20;
        margin: 0;
        padding: 4px;
        list-style: none;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        max-height: 220px;
        overflow-y: auto;
    }

    .convocation-card .enseignant-suggestions li {
        padding: 8px 10px;
        border-radius: 6px;
        cursor: pointer;
    }

    .convocation-card .enseignant-suggestions li:hover {
        background: #f3f4f6;
    }

    @media (max-width: 768px) {

        .convocation-card {
            width: calc(100% - 20px);
            margin-top: 15px;
        }

        .convocation-card .convocation-edit-form {
            padding: 18px;
        }

        .convocation-card .form-section {
            padding: 16px;
        }

        .convocation-card .sub-form-section {
            margin: 0 18px 18px;
        }

        .convocation-card .add-sub-form .form-grid {
            grid-template-columns: 1fr;
        }

    }

</style>
@endpush
