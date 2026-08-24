@extends('layouts.app')

@section('title', 'SICORE - Nouvelle fiche de déplacement')

@section('content')

<main class="main-content">

<x-topbar
    title="Nouvelle fiche de déplacement"
    subtitle="Indemnites > Frais de déplacement > Nouvelle fiche"
    icon="fa-solid fa-route"
/>

<section class="content-area">

    {{-- ================================================================
         NOTA — tout en haut de page (demande utilisatrice), bandeau
         horizontal reprenant l'encadre "NOTA" + les notes de bas de page
         (1) a (5) de la feuille de deplacement papier.
    ================================================================ --}}

    <section class="sidebar-box nota-bandeau">

        <h3>NOTA</h3>

        <p>
            Le titulaire de la présente feuille doit s'assurer que toutes les
            indications nécessaires à la constatation de ses droits ont été
            apposées par chaque fonctionnaire ou agent compétent.
        </p>

        <ol class="sidebar-definitions">
            <li><strong>(1)</strong> Nom et Prénoms</li>
            <li><strong>(2)</strong> Grade et emploi</li>
            <li><strong>(3)</strong> Catégorie</li>
            <li><strong>(4)</strong> Nom et garde de l'autorité qui délivre la feuille de déplacement</li>
            <li><strong>(5)</strong> Lorsque le fonctionnaire utilise un véhicule dont l'exploitation relève de la règle des Chemins de fer</li>
        </ol>

    </section>

    {{-- ================================================================
         FORMULAIRE EN 3 ETAPES — meme mecanique que "Nouvelle convocation"
         (wizard-progress / wizard-panel / data-wizard-next/prev/submit,
         voir resources/views/pages/indemnites/convocations/create.blade.php
         et public/assets/js/indemnites/frais-deplacement-wizard.js, une
         version generique/allegee de convocation-wizard.js sans tout ce
         qui est specifique aux convocations).

         Les champs suivent le RECTO de la feuille de deplacement papier
         (Ministere des Finances et du Budget, Direction du Materiel et du
         Transit Administratif), repartis en 3 etapes :
           1. Beneficiaire (identite, categorie, montant/indice)
           2. Trajet et ordre de mission
           3. Decompte des avances au depart + pieces jointes (recto/verso)

         Tous les champs obligatoires ont leur libelle en gras (classe
         "required-label") — demande utilisatrice.
    ================================================================ --}}

    <section class="form-card wizard-card convocation-card">

        <div class="form-card-header">
            <div>
                <p class="breadcrumb"><a href="{{ route('indemnites.frais-deplacement', ['objet' => $convocation['objet'] ?? null]) }}">&larr; Retour à la liste</a></p>
                <h2>Feuille de déplacement</h2>
                <p class="breadcrumb">
                    {{ $convocation['objet'] ?? '—' }}
                    &middot; {{ $convocation['session'] ?? '—' }}
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('indemnites.frais-deplacement.store') }}"
            enctype="multipart/form-data"
            class="convocation-form"
            id="ficheDeplacementForm"
            data-frais-deplacement-wizard
            novalidate
        >
            @csrf

            <input type="hidden" name="convocation_id" value="{{ $convocationId }}">
            <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire['id'] }}">

            {{-- ============================================================
                 PROGRESSION
            ============================================================ --}}

            <div class="wizard-progress" aria-label="Progression du formulaire">
                <button class="wizard-step active" type="button" data-step-indicator="1">
                    <span class="wizard-step-number">1</span>
                    <span>Bénéficiaire</span>
                </button>
                <button class="wizard-step" type="button" data-step-indicator="2">
                    <span class="wizard-step-number">2</span>
                    <span>Trajet et ordre de mission</span>
                </button>
                <button class="wizard-step" type="button" data-step-indicator="3">
                    <span class="wizard-step-number">3</span>
                    <span>Décompte des avances</span>
                </button>
                <button class="wizard-step" type="button" data-step-indicator="4">
                    <span class="wizard-step-number">4</span>
                    <span>Verso — Visas et paiements</span>
                </button>
                <button class="wizard-step" type="button" data-step-indicator="5">
                    <span class="wizard-step-number">5</span>
                    <span>Pièces jointes</span>
                </button>
            </div>

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
                 ETAPE 1 — BENEFICIAIRE
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="1">

                <div class="form-section">

                    <h3>Bénéficiaire</h3>

                    <div class="form-grid">

                        <div class="form-group full">
                            <label>Délivré à M. (1)</label>
                            <p>
                                {{ trim(($beneficiaire['prenom'] ?? '') . ' ' . ($beneficiaire['nom'] ?? '')) ?: '—' }}
                                &middot; Matricule {{ $beneficiaire['matricule'] ?? '—' }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label>Type de bénéficiaire</label>
                            <p>{{ ucfirst($beneficiaire['categorie_personnel'] ?? '—') }}</p>
                        </div>

                        {{--
                            Demande utilisatrice : "si c'est fonctionnaire le
                            champs indice s'affiche tout juste après type
                            fonctionnaire, si c'est vacataire champ avec
                            montant fixe à 150 000 et contractuelle champ
                            libre" — ce champ (indice/montant selon la
                            categorie) est directement a cote de "Type de
                            beneficiaire", pas plus loin dans le formulaire.
                        --}}
                        @if (($beneficiaire['categorie_personnel'] ?? null) === 'vacataire')

                            <div class="form-group">
                                <label>Montant</label>
                                <p><strong>150 000 FCFA</strong> (montant fixe vacataire)</p>
                            </div>

                        @elseif (($beneficiaire['categorie_personnel'] ?? null) === 'fonctionnaire')

                            <div class="form-group">
                                <label for="indice_agent" @class(['required-label' => empty($beneficiaire['indice'])])>
                                    Indice
                                    @if (empty($beneficiaire['indice']))<span class="required">*</span>@endif
                                </label>
                                @if (! empty($beneficiaire['indice']))
                                    <p>{{ $beneficiaire['indice'] }}</p>
                                    <input type="hidden" name="indice_agent" value="{{ $beneficiaire['indice'] }}">
                                @else
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control @error('indice_agent') is-invalid @enderror"
                                        id="indice_agent"
                                        name="indice_agent"
                                        value="{{ old('indice_agent') }}"
                                        placeholder="Ex : 849"
                                        required
                                    >
                                    <small class="form-hint">Pas encore renseigné sur la fiche de cet agent — sera mémorisé pour la prochaine fois.</small>
                                @endif
                            </div>

                        @elseif (($beneficiaire['categorie_personnel'] ?? null) === 'contractuel')

                            <div class="form-group">
                                <label for="montant_saisi" class="required-label">Montant <span class="required">*</span></label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="form-control @error('montant_saisi') is-invalid @enderror"
                                    id="montant_saisi"
                                    name="montant_saisi"
                                    value="{{ old('montant_saisi') }}"
                                    placeholder="Montant en FCFA"
                                    required
                                >
                            </div>

                        @else

                            <div class="form-group">
                                <label>Montant</label>
                                <p class="empty-message">Type de personnel non renseigné sur la fiche de cet agent — impossible de déterminer le montant.</p>
                            </div>

                        @endif

                        <div class="form-group">
                            <label for="grade_emploi">(2) Grade et emploi</label>
                            <input
                                type="text"
                                class="form-control @error('grade_emploi') is-invalid @enderror"
                                id="grade_emploi"
                                name="grade_emploi"
                                value="{{ old('grade_emploi') }}"
                            >
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ETAPE 2 — TRAJET ET ORDRE DE MISSION
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="2" hidden>

                <div class="form-section">

                    <h3>Trajet et ordre de mission</h3>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="lieu_depart" class="required-label">Partant de <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('lieu_depart') is-invalid @enderror"
                                id="lieu_depart"
                                name="lieu_depart"
                                value="{{ old('lieu_depart', 'Dakar') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="date_depart" class="required-label">le <span class="required">*</span></label>
                            <input
                                type="date"
                                class="form-control @error('date_depart') is-invalid @enderror"
                                id="date_depart"
                                name="date_depart"
                                value="{{ old('date_depart') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="heure_depart">à (heure)</label>
                            <input
                                type="time"
                                class="form-control @error('heure_depart') is-invalid @enderror"
                                id="heure_depart"
                                name="heure_depart"
                                value="{{ old('heure_depart') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="lieu_destination" class="required-label">Pour se rendre à <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('lieu_destination') is-invalid @enderror"
                                id="lieu_destination"
                                name="lieu_destination"
                                value="{{ old('lieu_destination', $convocation['lieu_affectation'] ?? '') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="ordre_service_numero">Suivant ordre de service N°</label>
                            <input
                                type="text"
                                class="form-control @error('ordre_service_numero') is-invalid @enderror"
                                id="ordre_service_numero"
                                name="ordre_service_numero"
                                value="{{ old('ordre_service_numero') }}"
                                placeholder="Ex : 00107/MEFPT"
                            >
                        </div>

                        <div class="form-group">
                            <label for="ordre_service_date">en date du</label>
                            <input
                                type="date"
                                class="form-control @error('ordre_service_date') is-invalid @enderror"
                                id="ordre_service_date"
                                name="ordre_service_date"
                                value="{{ old('ordre_service_date') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="ordre_service_emetteur">de</label>
                            <input
                                type="text"
                                class="form-control @error('ordre_service_emetteur') is-invalid @enderror"
                                id="ordre_service_emetteur"
                                name="ordre_service_emetteur"
                                value="{{ old('ordre_service_emetteur') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="accompagne_de">Accompagné de</label>
                            <input
                                type="text"
                                class="form-control @error('accompagne_de') is-invalid @enderror"
                                id="accompagne_de"
                                name="accompagne_de"
                                value="{{ old('accompagne_de') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="groupe">groupe (3)</label>
                            <input
                                type="text"
                                class="form-control @error('groupe') is-invalid @enderror"
                                id="groupe"
                                name="groupe"
                                value="{{ old('groupe') }}"
                                placeholder="Ex : III"
                            >
                        </div>

                        <div class="form-group full">
                            <label for="itineraire">Itinéraire à suivre, avance à faire, le cas échéant</label>
                            <textarea
                                class="form-control @error('itineraire') is-invalid @enderror"
                                id="itineraire"
                                name="itineraire"
                                rows="2"
                            >{{ old('itineraire') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="date_retour" class="required-label">Date de retour <span class="required">*</span></label>
                            <input
                                type="date"
                                class="form-control @error('date_retour') is-invalid @enderror"
                                id="date_retour"
                                name="date_retour"
                                value="{{ old('date_retour') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="motif">Nature du déplacement (temporaire ou définitif)</label>
                            <input
                                type="text"
                                class="form-control @error('motif') is-invalid @enderror"
                                id="motif"
                                name="motif"
                                value="{{ old('motif', 'Mission') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="moyen_transport">Moyen de transport</label>
                            <input
                                type="text"
                                class="form-control @error('moyen_transport') is-invalid @enderror"
                                id="moyen_transport"
                                name="moyen_transport"
                                value="{{ old('moyen_transport') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="distance_km">Distance (km)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('distance_km') is-invalid @enderror"
                                id="distance_km"
                                name="distance_km"
                                value="{{ old('distance_km') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="poids_bagages_kg">Poids de bagages dont le transport est autorisé (kgs)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('poids_bagages_kg') is-invalid @enderror"
                                id="poids_bagages_kg"
                                name="poids_bagages_kg"
                                value="{{ old('poids_bagages_kg') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="delivre_par">Délivré par nous (4)</label>
                            <input
                                type="text"
                                class="form-control @error('delivre_par') is-invalid @enderror"
                                id="delivre_par"
                                name="delivre_par"
                                value="{{ old('delivre_par') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="date_emission_fiche">Dakar, le</label>
                            <input
                                type="date"
                                class="form-control @error('date_emission_fiche') is-invalid @enderror"
                                id="date_emission_fiche"
                                name="date_emission_fiche"
                                value="{{ old('date_emission_fiche', date('Y-m-d')) }}"
                            >
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ETAPE 3 — DECOMPTE DES AVANCES

                 Demande utilisatrice : "pièces jointes devrait être l'étape
                 5" — déplacé dans sa propre étape (voir plus bas), après le
                 VERSO, pour ne pas mélanger saisie de décompte et dépôt de
                 fichiers.
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="3" hidden>

                <div class="form-section">

                    <h3>Décompte des avances au départ</h3>

                    {{--
                        Demande utilisatrice : "avoir les tableaux
                        exactement comme les exemples" — le tableau papier
                        a 2 colonnes en plus de Nombre/Taux/Décompte,
                        chacune une remarque libre qui couvre tout le
                        tableau (rowspan), pas une valeur par ligne :
                        "INDICATION des Réquisitions délivrées au départ"
                        et "Poids des bagages et du mobilier constaté,
                        indiquer si la nourriture et le logement sont
                        assurés".
                    --}}
                    <div class="table-responsive">
                        <table class="table avance-table" id="avanceTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nombre</th>
                                    <th>Taux</th>
                                    <th>Décompte</th>
                                    <th>Indication des réquisitions délivrées au départ</th>
                                    <th>Poids des bagages et du mobilier constaté (nourriture et logement assurés ?)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Frais de voyage et de transport (5)</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_frais_transport_nombre" value="{{ old('avance_frais_transport_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_frais_transport_taux" value="{{ old('avance_frais_transport_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                    <td rowspan="4">
                                        <textarea class="form-control" name="indication_requisitions" rows="4">{{ old('indication_requisitions') }}</textarea>
                                    </td>
                                    <td rowspan="4">
                                        <textarea class="form-control" name="poids_bagages_mobilier" rows="4">{{ old('poids_bagages_mobilier') }}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Indemnité journalière normale</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_normale_nombre" value="{{ old('avance_indemnite_normale_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_normale_taux" value="{{ old('avance_indemnite_normale_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>réduite</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_reduite_nombre" value="{{ old('avance_indemnite_reduite_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_reduite_taux" value="{{ old('avance_indemnite_reduite_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>partielle</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_partielle_nombre" value="{{ old('avance_indemnite_partielle_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_partielle_taux" value="{{ old('avance_indemnite_partielle_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td class="avance-decompte" id="avanceTotal"><strong>0</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-grid" style="margin-top: 16px;">

                        <div class="form-group full">
                            <label for="arrete_somme">ARRETÉ à la somme de</label>
                            <input
                                type="text"
                                class="form-control @error('arrete_somme') is-invalid @enderror"
                                id="arrete_somme"
                                name="arrete_somme"
                                value="{{ old('arrete_somme') }}"
                                placeholder="Ex : cent cinquante mille francs"
                            >
                        </div>

                        <div class="form-group">
                            <label for="avance_versee">Payé à titre d'avance</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('avance_versee') is-invalid @enderror"
                                id="avance_versee"
                                name="avance_versee"
                                value="{{ old('avance_versee') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="date_fait_avance">Fait à Dakar, le</label>
                            <input
                                type="date"
                                class="form-control @error('date_fait_avance') is-invalid @enderror"
                                id="date_fait_avance"
                                name="date_fait_avance"
                                value="{{ old('date_fait_avance') }}"
                            >
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ETAPE 4 — VERSO : DETAIL DES VISAS ET PAIEMENT SUCCESSIFS
                 EN COURS DE ROUTE

                 Demande utilisatrice : "tu peux faire le verso ?" (reporté
                 depuis "Verso plus tard" lors du RECTO). Reprend "exactement
                 comme les exemples" (photo du VERSO) : 4 lignes fixes de
                 visas (à l'arrivée / au départ, réquisitions, poids des
                 bagages, logement et nourriture), puis les 2 mini-tableaux
                 "Avance ou compte perçus en route" et "Règlement définitif"
                 (même mécanique Nombre x Taux que le tableau RECTO), et une
                 zone Observations.
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="4" hidden>

                <div class="form-section">

                    <h3>Détail des visas et paiement successifs en cours de route</h3>

                    <div class="table-responsive">
                        <table class="table avance-table" id="visasTable">
                            <thead>
                                <tr>
                                    <th rowspan="2"></th>
                                    <th colspan="3">À l'arrivée</th>
                                    <th colspan="3">Au départ</th>
                                    <th rowspan="2">Indication des réquisitions délivrées en cours de route</th>
                                    <th rowspan="2">Poids des bagages et du mobilier constaté</th>
                                    <th rowspan="2">Logement et nourriture assurés</th>
                                </tr>
                                <tr>
                                    <th>Lieu</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Lieu</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 4; $i++)
                                    <tr>
                                        <td>Visa {{ $i + 1 }}</td>
                                        <td><input type="text" class="form-control" name="visa_arrivee_lieu[]" value="{{ old('visa_arrivee_lieu.' . $i) }}"></td>
                                        <td><input type="date" class="form-control" name="visa_arrivee_date[]" value="{{ old('visa_arrivee_date.' . $i) }}"></td>
                                        <td><input type="time" class="form-control" name="visa_arrivee_heure[]" value="{{ old('visa_arrivee_heure.' . $i) }}"></td>
                                        <td><input type="text" class="form-control" name="visa_depart_lieu[]" value="{{ old('visa_depart_lieu.' . $i) }}"></td>
                                        <td><input type="date" class="form-control" name="visa_depart_date[]" value="{{ old('visa_depart_date.' . $i) }}"></td>
                                        <td><input type="time" class="form-control" name="visa_depart_heure[]" value="{{ old('visa_depart_heure.' . $i) }}"></td>
                                        <td><input type="text" class="form-control" name="visa_requisitions[]" value="{{ old('visa_requisitions.' . $i) }}"></td>
                                        <td><input type="text" class="form-control" name="visa_poids_bagages[]" value="{{ old('visa_poids_bagages.' . $i) }}"></td>
                                        <td><input type="text" class="form-control" name="visa_logement_nourriture[]" value="{{ old('visa_logement_nourriture.' . $i) }}"></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="form-section">

                    <h3>Avance ou compte perçus en route</h3>

                    <div class="table-responsive">
                        <table class="table avance-table" id="visaAvanceTable" data-avance-total-target="visaAvanceTotal">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nombre</th>
                                    <th>Taux</th>
                                    <th>Décompte</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Indemnité journalière normale</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_normale_nombre" value="{{ old('visa_avance_indemnite_normale_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_normale_taux" value="{{ old('visa_avance_indemnite_normale_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>réduite</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_reduite_nombre" value="{{ old('visa_avance_indemnite_reduite_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_reduite_taux" value="{{ old('visa_avance_indemnite_reduite_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>partielle</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_partielle_nombre" value="{{ old('visa_avance_indemnite_partielle_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_partielle_taux" value="{{ old('visa_avance_indemnite_partielle_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td class="avance-decompte" id="visaAvanceTotal"><strong>0</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-grid" style="margin-top: 16px;">

                        <div class="form-group full">
                            <label for="visa_avance_payer_somme">ARRETÉ à payer la somme de</label>
                            <input
                                type="text"
                                class="form-control @error('visa_avance_payer_somme') is-invalid @enderror"
                                id="visa_avance_payer_somme"
                                name="visa_avance_payer_somme"
                                value="{{ old('visa_avance_payer_somme') }}"
                                placeholder="Ex : cinquante mille francs"
                            >
                        </div>

                        <div class="form-group">
                            <label for="visa_avance_lieu">Fait à</label>
                            <input
                                type="text"
                                class="form-control @error('visa_avance_lieu') is-invalid @enderror"
                                id="visa_avance_lieu"
                                name="visa_avance_lieu"
                                value="{{ old('visa_avance_lieu') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="visa_avance_date">le</label>
                            <input
                                type="date"
                                class="form-control @error('visa_avance_date') is-invalid @enderror"
                                id="visa_avance_date"
                                name="visa_avance_date"
                                value="{{ old('visa_avance_date') }}"
                            >
                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>Règlement définitif</h3>

                    <div class="table-responsive">
                        <table class="table avance-table" id="reglementTable" data-avance-total-target="reglementTotalCell">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nombre</th>
                                    <th>Taux</th>
                                    <th>Décompte</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Indemnité journalière normale</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_normale_nombre" value="{{ old('reglement_indemnite_normale_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_normale_taux" value="{{ old('reglement_indemnite_normale_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>réduite</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_reduite_nombre" value="{{ old('reglement_indemnite_reduite_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_reduite_taux" value="{{ old('reglement_indemnite_reduite_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>partielle</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_partielle_nombre" value="{{ old('reglement_indemnite_partielle_nombre') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_partielle_taux" value="{{ old('reglement_indemnite_partielle_taux') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td class="avance-decompte" id="reglementTotalCell"><strong>0</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-grid" style="margin-top: 16px;">

                        <div class="form-group">
                            <label for="reglement_montant_avances">Montant des avances déjà perçues</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('reglement_montant_avances') is-invalid @enderror"
                                id="reglement_montant_avances"
                                name="reglement_montant_avances"
                                value="{{ old('reglement_montant_avances') }}"
                            >
                            <small class="form-hint">Le reste à payer est recalculé automatiquement (Total − Avances déjà perçues).</small>
                        </div>

                        <div class="form-group full">
                            <label for="reglement_arrete_somme">ARRETÉ à la somme de</label>
                            <input
                                type="text"
                                class="form-control @error('reglement_arrete_somme') is-invalid @enderror"
                                id="reglement_arrete_somme"
                                name="reglement_arrete_somme"
                                value="{{ old('reglement_arrete_somme') }}"
                                placeholder="Ex : cent cinquante mille francs"
                            >
                        </div>

                        <div class="form-group">
                            <label for="reglement_lieu">Fait à</label>
                            <input
                                type="text"
                                class="form-control @error('reglement_lieu') is-invalid @enderror"
                                id="reglement_lieu"
                                name="reglement_lieu"
                                value="{{ old('reglement_lieu') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="reglement_date">le</label>
                            <input
                                type="date"
                                class="form-control @error('reglement_date') is-invalid @enderror"
                                id="reglement_date"
                                name="reglement_date"
                                value="{{ old('reglement_date') }}"
                            >
                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>Observations</h3>

                    <div class="form-grid">

                        <div class="form-group full">
                            <label for="observations">Observations</label>
                            <textarea
                                class="form-control @error('observations') is-invalid @enderror"
                                id="observations"
                                name="observations"
                                rows="3"
                            >{{ old('observations') }}</textarea>
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ETAPE 5 — PIECES JOINTES (RECTO/VERSO)

                 Demande utilisatrice : "pièces jointes devrait être l'étape
                 5" — anciennement mêlé à l'étape 3 ("Décompte et pièces
                 jointes"), maintenant sa propre étape, dernière du wizard.
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="5" hidden>

                <div class="form-section">

                    {{--
                        Demande utilisatrice : "fiche de déplacement c'est
                        recto verso donc faut prendre en compte de ça à
                        l'upload" — 2 champs fichier distincts (une face
                        chacun) plutôt qu'un seul comme avant.
                    --}}
                    <h3>Feuille de déplacement (fichier — recto et verso)</h3>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="fichier_recto">Recto (scan ou photo)</label>
                            <input
                                type="file"
                                class="form-control @error('fichier_recto') is-invalid @enderror"
                                id="fichier_recto"
                                name="fichier_recto"
                                accept=".pdf,.jpg,.jpeg,.png"
                            >
                            <small class="form-hint">PDF, JPG ou PNG, 5 Mo maximum. Optionnel à cette étape.</small>
                        </div>

                        <div class="form-group">
                            <label for="fichier_verso">Verso (scan ou photo)</label>
                            <input
                                type="file"
                                class="form-control @error('fichier_verso') is-invalid @enderror"
                                id="fichier_verso"
                                name="fichier_verso"
                                accept=".pdf,.jpg,.jpeg,.png"
                            >
                            <small class="form-hint">PDF, JPG ou PNG, 5 Mo maximum. Optionnel à cette étape, peut être ajouté après coup.</small>
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ACTIONS
            ============================================================ --}}

            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement', ['objet' => $convocation['objet'] ?? null]) }}" data-wizard-cancel>
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
                    Créer la fiche de déplacement
                </button>
            </div>

        </form>

    </section>

</section>

</main>

@push('styles')
{{-- Feuille de style partagee du wizard "convocations" — voir l'entete de
     ce fichier CSS : c'est de la ou viennent .convocation-card/.form-card-header/
     .form-section/.form-grid/.convocation-form/.wizard-progress/.wizard-panel,
     exactement le meme habillage que "Nouvelle convocation". --}}
<link rel="stylesheet" href="{{ asset('assets/css/indemnites/convocation-wizard.css') }}">
<style>
    /* Formulaire plus large que le max-width partage de .convocation-card
       (1500px, meme valeur que "Nouvelle convocation") — demande
       utilisatrice "elargi un peu le formulaire", puis ré-élargi ("y'a
       des infos qui ne s'affichent pas parce que c'est étroit") pour
       laisser respirer les tableaux VERSO (9 colonnes) et le tableau des
       avances (6 colonnes). */
    .convocation-card {
        max-width: 2200px;
    }

    /* NOTA en bandeau horizontal — demande utilisatrice : tout en haut de
       page, forme horizontale (les 5 definitions cote a cote plutot
       qu'empilees). */
    .nota-bandeau {
        width: calc(100% - 40px);
        max-width: 2200px;
        margin: 24px auto 20px;
        box-sizing: border-box;
    }

    .sidebar-box {
        background: #ffffff;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: var(--shadow-card, 0 1px 3px rgba(0, 0, 0, 0.06));
    }

    .sidebar-box h3 {
        margin: 0 0 10px;
        color: var(--primary);
        font-size: 14px;
        font-weight: 900;
        letter-spacing: 0.02em;
    }

    .sidebar-box > p {
        margin: 0 0 14px;
        font-size: 13px;
        line-height: 1.5;
        color: #475569;
    }

    .sidebar-definitions {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 10px 28px;
    }

    .sidebar-definitions li {
        font-size: 13px;
        line-height: 1.4;
        color: #334155;
        flex: 1 1 220px;
    }

    .sidebar-definitions strong {
        color: var(--primary);
        margin-right: 4px;
    }

    /* Champs obligatoires en gras — demande utilisatrice. */
    .form-group label.required-label {
        font-weight: 800;
    }

    .avance-table th,
    .avance-table td {
        text-align: left;
        vertical-align: middle;
    }

    .avance-table input {
        min-width: 100px;
    }

    .avance-decompte {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/indemnites/frais-deplacement-wizard.js') }}"></script>
<script>
    (function () {
        "use strict";

        // Calcule en direct chaque ligne (Nombre x Taux) et le TOTAL de
        // CHAQUE tableau "Nombre x Taux" de la fiche — purement indicatif
        // cote front, le montant enregistre est toujours recalcule cote
        // serveur (voir FraisDeplacementController::store()/update()).
        // 3 tableaux au total : "Décompte des avances au départ" (RECTO,
        // id="avanceTable"/"avanceTotal") + les 2 mini-tableaux du VERSO
        // ("Avance ou compte perçus en route" et "Règlement définitif",
        // chacun via son attribut data-avance-total-target).
        function nombre(valeur) {
            var n = parseFloat(valeur);
            return isNaN(n) ? 0 : n;
        }

        function brancherTableauAvance(table, totalCell) {
            if (! table || ! totalCell) {
                return;
            }

            var lignes = table.querySelectorAll("tbody tr");

            function recalculer() {
                var total = 0;

                lignes.forEach(function (ligne) {
                    var champNombre = ligne.querySelector("[data-avance-nombre]");
                    var champTaux = ligne.querySelector("[data-avance-taux]");
                    var celluleDecompte = ligne.querySelector("[data-avance-decompte]");

                    if (! champNombre || ! champTaux || ! celluleDecompte) {
                        return;
                    }

                    var decompte = nombre(champNombre.value) * nombre(champTaux.value);
                    celluleDecompte.textContent = decompte.toLocaleString("fr-FR", { maximumFractionDigits: 2 });
                    total += decompte;
                });

                totalCell.innerHTML = "<strong>" + total.toLocaleString("fr-FR", { maximumFractionDigits: 2 }) + "</strong>";
            }

            table.querySelectorAll("[data-avance-nombre], [data-avance-taux]").forEach(function (champ) {
                champ.addEventListener("input", recalculer);
            });

            recalculer();
        }

        brancherTableauAvance(document.getElementById("avanceTable"), document.getElementById("avanceTotal"));

        document.querySelectorAll("table[data-avance-total-target]").forEach(function (table) {
            brancherTableauAvance(table, document.getElementById(table.getAttribute("data-avance-total-target")));
        });
     })();
</script>
@endpush

@endsection
