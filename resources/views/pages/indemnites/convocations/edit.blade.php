@extends('layouts.app')

@section('title', 'SICORE - Modifier la convocation')

@section('content')

<x-topbar
 title="Modifier la convocation"
 subtitle="Indemnites > Convocations > Modifier"
 icon="fa-solid fa-envelope-open-text"
/>

<section class="content-area">

```
<section class="form-card wizard-card convocation-card">

    {{-- ============================================================
         EN-TÊTE
    ============================================================ --}}

    <div class="form-card-header">

        <div>
            <h2>Modifier la convocation</h2>

            <p class="breadcrumb">
                Modification des informations de la convocation
            </p>
        </div>

        <x-convocation-statut-badge
            :statut="old('statut', $convocation['statut'] ?? 'brouillon')"
        />

    </div>


    {{-- ============================================================
         FORMULAIRE
    ============================================================ --}}

    <form
        id="convocationEditForm"
        class="convocation-form"
        method="POST"
        action="{{ route('indemnites.convocations.update', $id) }}"
        novalidate
    >

        @csrf
        @method('PUT')


        {{-- ========================================================
             ERREURS GLOBALES
        ======================================================== --}}

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


        {{-- ========================================================
             SECTION 1 : INFORMATIONS GÉNÉRALES
        ======================================================== --}}

        <div class="form-section">

            <div class="section-heading">

                <div>

                    <h3>
                        Informations générales
                    </h3>

                    <p class="section-description">
                        Modifiez les informations principales de la convocation.
                    </p>

                </div>

            </div>


            <div class="form-grid">

                {{-- Objet --}}

                <div class="form-group full">

                    <label for="objet">

                        Objet

                        <span class="required">*</span>

                    </label>

                    <input
                        class="form-control @error('objet') is-invalid @enderror"
                        id="objet"
                        name="objet"
                        type="text"
                        placeholder="Ex : Examen de certification en Brevet de Technicien (BT)"
                        value="{{ old('objet', $convocation['objet'] ?? '') }}"
                        required
                    >

                    @error('objet')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Date émission --}}

                <div class="form-group">

                    <label for="date_emission">

                        Date d'émission

                        <span class="required">*</span>

                    </label>

                    <input
                        class="form-control @error('date_emission') is-invalid @enderror"
                        id="date_emission"
                        name="date_emission"
                        type="date"
                        value="{{ old('date_emission', isset($convocation['date_emission']) ? \Illuminate\Support\Carbon::parse($convocation['date_emission'])->format('Y-m-d') : '') }}"
                        required
                    >

                    @error('date_emission')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Statut --}}

                <div class="form-group">

                    <label for="statut">
                        Statut
                    </label>

                    @php

                        $statutActuel = old(
                            'statut',
                            $convocation['statut'] ?? 'brouillon'
                        );

                    @endphp

                    <select
                        class="form-control @error('statut') is-invalid @enderror"
                        id="statut"
                        name="statut"
                    >

                        <option
                            value="brouillon"
                            @selected($statutActuel === 'brouillon')
                        >
                            Brouillon
                        </option>

                        <option
                            value="emise"
                            @selected($statutActuel === 'emise')
                        >
                            Émise
                        </option>

                        <option
                            value="envoyee"
                            @selected($statutActuel === 'envoyee')
                        >
                            Envoyée
                        </option>

                        <option
                            value="cloturee"
                            @selected($statutActuel === 'cloturee')
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


        {{-- ========================================================
             SECTION 2 : LIEUX
        ======================================================== --}}

        <div class="form-section">

            <div class="section-heading">

                <div>

                    <h3>
                        Lieux de la convocation
                    </h3>

                    <p class="section-description">
                        Renseignez le centre d'examen et le lieu d'affectation.
                    </p>

                </div>

            </div>


            <div class="form-grid">

                {{-- Centre d'examen --}}

                <div class="form-group">

                    <label for="lieu_examen">
                        Centre d'examen
                    </label>

                    <input
                        class="form-control @error('lieu_examen') is-invalid @enderror"
                        id="lieu_examen"
                        name="lieu_examen"
                        type="text"
                        placeholder="Ex : Centre LTP FXN/Thies"
                        value="{{ old('lieu_examen', $convocation['lieu_examen'] ?? '') }}"
                    >

                    @error('lieu_examen')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Lieu affectation --}}

                <div class="form-group">

                    <label for="lieu_affectation">
                        Lieu d'affectation
                    </label>

                    <input
                        class="form-control @error('lieu_affectation') is-invalid @enderror"
                        id="lieu_affectation"
                        name="lieu_affectation"
                        type="text"
                        placeholder="Ex : Dakar"
                        value="{{ old('lieu_affectation', $convocation['lieu_affectation'] ?? '') }}"
                    >

                    @error('lieu_affectation')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            </div>

        </div>


        {{-- ========================================================
             SECTION 3 : PÉRIODE
        ======================================================== --}}

        <div class="form-section">

            <div class="section-heading">

                <div>

                    <h3>
                        Période de l'examen
                    </h3>

                    <p class="section-description">
                        Modifiez les dates et horaires associés à la convocation.
                    </p>

                </div>

            </div>


            <div class="form-grid">

                {{-- Date début --}}

                <div class="form-group">

                    <label for="date_debut">
                        Du
                    </label>

                    <input
                        class="form-control @error('date_debut') is-invalid @enderror"
                        id="date_debut"
                        name="date_debut"
                        type="date"
                        value="{{ old('date_debut', isset($convocation['date_debut']) ? \Illuminate\Support\Carbon::parse($convocation['date_debut'])->format('Y-m-d') : '') }}"
                    >

                    @error('date_debut')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Date fin --}}

                <div class="form-group">

                    <label for="date_fin">
                        Au
                    </label>

                    <input
                        class="form-control @error('date_fin') is-invalid @enderror"
                        id="date_fin"
                        name="date_fin"
                        type="date"
                        value="{{ old('date_fin', isset($convocation['date_fin']) ? \Illuminate\Support\Carbon::parse($convocation['date_fin'])->format('Y-m-d') : '') }}"
                    >

                    @error('date_fin')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Heure --}}

                <div class="form-group">

                    <label for="heure_debut">
                        Heure de début
                    </label>

                    <input
                        class="form-control @error('heure_debut') is-invalid @enderror"
                        id="heure_debut"
                        name="heure_debut"
                        type="time"
                        value="{{ old('heure_debut', $convocation['heure_debut'] ?? '08:00') }}"
                    >

                    @error('heure_debut')

                        <p class="field-error">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            </div>

        </div>


        {{-- ========================================================
             SECTION 4 : DOCUMENTS
        ======================================================== --}}

        <div class="form-section">

            <div class="section-heading">

                <div>

                    <h3>
                        Documents associés
                    </h3>

                    <p class="section-description">
                        Gérez les documents liés à cette convocation.
                    </p>

                </div>

            </div>


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
                            @checked(
                                old(
                                    'ordre_de_mission',
                                    $convocation['ordre_de_mission'] ?? false
                                )
                            )
                        >

                        <span>
                            Joindre un ordre de mission
                        </span>

                    </label>

                </div>

            </div>

        </div>


        {{-- ========================================================
             MESSAGE DE STATUT
        ======================================================== --}}

        <p
            class="form-status"
            data-form-status
            aria-live="polite"
        ></p>


        {{-- ========================================================
             ACTIONS
        ======================================================== --}}

        <div class="form-actions">

            <a
                class="btn-secondary"
                href="{{ route('indemnites.convocations.show', $id) }}"
            >

                <i
                    class="fa-solid fa-arrow-left"
                    aria-hidden="true"
                ></i>

                Annuler

            </a>


            <button
                class="btn-primary"
                type="submit"
            >

                <i
                    class="fa-solid fa-floppy-disk"
                    aria-hidden="true"
                ></i>

                Enregistrer les modifications

            </button>

        </div>

    </form>

</section>
```

</section>

@endsection

{{-- ================================================================
JAVASCRIPT
================================================================ --}}

@push('scripts')

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
        width: 100%;
        max-width: 1450px;
        margin: 0 auto 40px;
        padding: 0;
        overflow: hidden;
    }


    /* ============================================================
       FORMULAIRE
    ============================================================ */

    .convocation-card .convocation-form {
        width: 100%;
        padding: 0 36px 36px;
        box-sizing: border-box;
    }


    /* ============================================================
       SECTIONS
    ============================================================ */

    .convocation-card .form-section {
        width: 100%;
        margin-top: 28px;
        padding: 28px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        box-sizing: border-box;
    }


    .convocation-card .section-heading {
        margin-bottom: 24px;
    }


    .convocation-card .form-section h3 {
        margin: 0 0 6px;
    }


    .convocation-card .section-description {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }


    /* ============================================================
       GRILLE
    ============================================================ */

    .convocation-card .form-grid {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        align-items: start;
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


    /* ============================================================
       CHAMPS
    ============================================================ */

    .convocation-card .form-control {
        width: 100%;
        min-height: 44px;
        box-sizing: border-box;
    }


    /* ============================================================
       CHECKBOX
    ============================================================ */

    .convocation-card .checkbox-label {
        display: flex !important;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        margin: 0 !important;
        font-weight: 500 !important;
    }


    .convocation-card .checkbox-label input {
        width: 18px;
        height: 18px;
        margin: 0;
        flex-shrink: 0;
    }


    /* ============================================================
       ERREURS
    ============================================================ */

    .convocation-card .field-error {
        margin: 7px 0 0;
        font-size: 13px;
    }


    .convocation-card .form-errors {
        margin: 28px 0 0;
        padding: 16px 20px;
        border-radius: 10px;
    }


    /* ============================================================
       ACTIONS
    ============================================================ */

    .convocation-card .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 14px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }


    .convocation-card .form-actions a,
    .convocation-card .form-actions button {
        min-height: 44px;
        padding: 10px 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }


    /* ============================================================
       RESPONSIVE
    ============================================================ */

    @media (max-width: 900px) {

        .convocation-card .convocation-form {
            padding: 0 20px 24px;
        }

        .convocation-card .form-section {
            padding: 20px;
        }

        .convocation-card .form-grid {
            grid-template-columns: 1fr;
        }

        .convocation-card .form-group.full {
            grid-column: auto;
        }

        .convocation-card .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .convocation-card .form-actions a,
        .convocation-card .form-actions button {
            width: 100%;
        }

    }


    @media (min-width: 1600px) {

        .convocation-card {
            max-width: 1550px;
        }

    }

</style>

@endpush
