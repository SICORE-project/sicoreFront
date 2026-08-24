@extends('layouts.app')

@section('title', 'SICORE - Modifier la fiche de déplacement')

@section('content')

<main class="main-content">

<x-topbar
    title="Modifier la fiche de déplacement"
    subtitle="Indemnites > Frais de déplacement > Modifier"
    icon="fa-solid fa-route"
/>

<section class="content-area">

    {{-- ================================================================
         "EDIT DOIT ETRE COMPLET, BASE TOI SUR L'EDIT DE CONVOCATION" —
         même principe que convocations/edit.blade.php ("IL FAUT QUE EDIT
         SOIT EXACTEMENT COMME CREATE MAIS PREREMPLI") : ce formulaire est
         structurellement IDENTIQUE à create.blade.php (même wizard en 3
         étapes, mêmes champs, même JS frais-deplacement-wizard.js) — seules
         différences : method PUT, tout préreempli depuis $fiche, et pas de
         champs fichier recto/verso (gérés depuis le détail de la fiche,
         pas ce formulaire).
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

    <section class="form-card wizard-card convocation-card">

        <div class="form-card-header">
            <div>
                <p class="breadcrumb"><a href="{{ route('indemnites.frais-deplacement', ['objet' => $fiche['convocation']['objet'] ?? null]) }}">&larr; Retour à la liste</a></p>
                <h2>Modifier la fiche de déplacement</h2>
                <p class="breadcrumb">
                    {{ $fiche['reference'] ?? '—' }}
                    &middot; {{ trim(($fiche['beneficiaire']['prenom'] ?? '') . ' ' . ($fiche['beneficiaire']['nom'] ?? '')) ?: '—' }}
                </p>
            </div>

            <x-module-indemnite type="statut-frais-deplacement" :statut="$fiche['statut'] ?? null" />
        </div>

        {{--
            Demande utilisatrice : "Pièces jointes (recto/verso) doit être
            étape 5, juste au lieu de le mettre tout le temps en bas" — le
            data-frais-deplacement-wizard (et donc la portée des
            data-wizard-panel/data-step-indicator du JS) est maintenant
            porté par ce <div> englobant, PAS par le <form> lui-même : ça
            permet à l'étape 5 (pièces jointes, ses propres <form> par
            face) et à la barre d'actions de vivre HORS du <form>
            principal (un <form> ne peut pas en contenir un autre), tout en
            restant une étape du même wizard. Le bouton "Enregistrer les
            modifications" référence le <form> via son attribut form="…"
            (HTML5), puisqu'il n'en est plus un descendant direct.
        --}}

        <div class="convocation-form" data-frais-deplacement-wizard>

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
                    <span>Décompte</span>
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

            <form
                method="POST"
                action="{{ route('indemnites.frais-deplacement.update', $fiche['id']) }}"
                id="ficheDeplacementForm"
                novalidate
            >
                @csrf
                @method('PUT')

            {{-- ============================================================
                 ETAPE 1 — BENEFICIAIRE
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="1">

                <div class="form-section">

                    <h3>Bénéficiaire</h3>

                    <div class="form-grid">

                        {{--
                            Demande utilisatrice : même traitement que
                            create.blade.php — champs récupérés (identité,
                            statut) affichés en désactivé + fond coloré
                            (.field-prefilled) plutôt qu'un simple texte.
                            Rien à soumettre pour ces deux champs (pas de
                            name) : ils n'en avaient pas avant non plus.
                        --}}
                        <div class="form-group full">
                            <label for="delivre_a_affiche">Délivré à M. (1)</label>
                            <input
                                type="text"
                                class="form-control field-prefilled"
                                id="delivre_a_affiche"
                                value="{{ trim(($fiche['beneficiaire']['prenom'] ?? '') . ' ' . ($fiche['beneficiaire']['nom'] ?? '')) ?: '—' }} · Matricule {{ $fiche['beneficiaire']['matricule'] ?? '—' }}"
                                disabled
                            >
                            <small class="form-hint">Déjà connu depuis la fiche de l'agent — non modifiable ici.</small>
                        </div>

                        <div class="form-group">
                            <label for="statut_agent_affiche">Type de bénéficiaire</label>
                            <input
                                type="text"
                                class="form-control field-prefilled"
                                id="statut_agent_affiche"
                                value="{{ ucfirst($fiche['statut_agent'] ?? '—') }}"
                                disabled
                            >
                            <small class="form-hint">Déjà connu depuis la fiche de l'agent — non modifiable ici.</small>
                        </div>

                        {{--
                            Catégorie non modifiable ici (fixée à la
                            création) — seul le champ correspondant est
                            éditable : indice pour fonctionnaire, montant
                            pour contractuel, rien pour vacataire (montant
                            fixe).
                        --}}
                        @if (($fiche['statut_agent'] ?? null) === 'vacataire')

                            <div class="form-group">
                                <label for="montant_vacataire_affiche">Montant</label>
                                <input
                                    type="text"
                                    class="form-control field-prefilled"
                                    id="montant_vacataire_affiche"
                                    value="150 000 FCFA (montant fixe vacataire)"
                                    disabled
                                >
                                <small class="form-hint">Montant fixe, déjà déterminé — non modifiable ici.</small>
                            </div>

                        @elseif (($fiche['statut_agent'] ?? null) === 'fonctionnaire')

                            <div class="form-group">
                                <label for="indice_agent" class="required-label">Indice <span class="required">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control @error('indice_agent') is-invalid @enderror"
                                    id="indice_agent"
                                    name="indice_agent"
                                    value="{{ old('indice_agent', $fiche['indice_agent'] ?? '') }}"
                                    placeholder="Ex : 849"
                                    required
                                >
                            </div>

                        @elseif (($fiche['statut_agent'] ?? null) === 'contractuel')

                            <div class="form-group">
                                <label for="montant_saisi" class="required-label">Montant <span class="required">*</span></label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="form-control @error('montant_saisi') is-invalid @enderror"
                                    id="montant_saisi"
                                    name="montant_saisi"
                                    value="{{ old('montant_saisi', $fiche['montant_calcule'] ?? '') }}"
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
                                value="{{ old('grade_emploi', $fiche['grade_emploi'] ?? '') }}"
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
                                value="{{ old('lieu_depart', $fiche['lieu_depart'] ?? '') }}"
                                required
                            >
                        </div>

                        {{--
                            Demande utilisatrice : date_depart/date_retour
                            désactivés (comme create.blade.php), avec un
                            hidden portant la valeur réelle — un input
                            disabled n'est jamais envoyé par le navigateur.
                            lieu_depart/lieu_destination (ci-dessus/plus bas)
                            restent volontairement modifiables.
                        --}}
                        <div class="form-group">
                            <label for="date_depart_affiche" class="required-label">le <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control field-prefilled"
                                id="date_depart_affiche"
                                value="{{ ! empty($fiche['date_depart']) ? \Illuminate\Support\Carbon::parse($fiche['date_depart'])->format('d/m/Y') : '—' }}"
                                disabled
                            >
                            <input type="hidden" name="date_depart" value="{{ old('date_depart', ! empty($fiche['date_depart']) ? \Illuminate\Support\Carbon::parse($fiche['date_depart'])->format('Y-m-d') : '') }}">
                            <small class="form-hint">Déjà enregistrée sur la fiche — non modifiable ici.</small>
                        </div>

                        <div class="form-group">
                            <label for="heure_depart">à (heure)</label>
                            <input
                                type="time"
                                class="form-control @error('heure_depart') is-invalid @enderror"
                                id="heure_depart"
                                name="heure_depart"
                                value="{{ old('heure_depart', $fiche['heure_depart'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="lieu_destination" class="required-label">Pour se rendre à <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('lieu_destination') is-invalid @enderror"
                                id="lieu_destination"
                                name="lieu_destination"
                                value="{{ old('lieu_destination', $fiche['lieu_destination'] ?? '') }}"
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
                                value="{{ old('ordre_service_numero', $fiche['ordre_service_numero'] ?? '') }}"
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
                                value="{{ old('ordre_service_date', ! empty($fiche['ordre_service_date']) ? \Illuminate\Support\Carbon::parse($fiche['ordre_service_date'])->format('Y-m-d') : '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="ordre_service_emetteur">de</label>
                            <input
                                type="text"
                                class="form-control @error('ordre_service_emetteur') is-invalid @enderror"
                                id="ordre_service_emetteur"
                                name="ordre_service_emetteur"
                                value="{{ old('ordre_service_emetteur', $fiche['ordre_service_emetteur'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="accompagne_de">Accompagné de</label>
                            <input
                                type="text"
                                class="form-control @error('accompagne_de') is-invalid @enderror"
                                id="accompagne_de"
                                name="accompagne_de"
                                value="{{ old('accompagne_de', $fiche['accompagne_de'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="groupe">groupe (3)</label>
                            <input
                                type="text"
                                class="form-control @error('groupe') is-invalid @enderror"
                                id="groupe"
                                name="groupe"
                                value="{{ old('groupe', $fiche['groupe'] ?? '') }}"
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
                            >{{ old('itineraire', $fiche['itineraire'] ?? '') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="date_retour_affiche" class="required-label">Date de retour <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control field-prefilled"
                                id="date_retour_affiche"
                                value="{{ ! empty($fiche['date_retour']) ? \Illuminate\Support\Carbon::parse($fiche['date_retour'])->format('d/m/Y') : '—' }}"
                                disabled
                            >
                            <input type="hidden" name="date_retour" value="{{ old('date_retour', ! empty($fiche['date_retour']) ? \Illuminate\Support\Carbon::parse($fiche['date_retour'])->format('Y-m-d') : '') }}">
                            <small class="form-hint">Déjà enregistrée sur la fiche — non modifiable ici.</small>
                        </div>

                        <div class="form-group">
                            <label for="motif">Nature du déplacement (temporaire ou définitif)</label>
                            <input
                                type="text"
                                class="form-control @error('motif') is-invalid @enderror"
                                id="motif"
                                name="motif"
                                value="{{ old('motif', $fiche['motif'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="moyen_transport">Moyen de transport</label>
                            <input
                                type="text"
                                class="form-control @error('moyen_transport') is-invalid @enderror"
                                id="moyen_transport"
                                name="moyen_transport"
                                value="{{ old('moyen_transport', $fiche['moyen_transport'] ?? '') }}"
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
                                value="{{ old('distance_km', $fiche['distance_km'] ?? '') }}"
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
                                value="{{ old('poids_bagages_kg', $fiche['poids_bagages_kg'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="delivre_par">Délivré par nous (4)</label>
                            <input
                                type="text"
                                class="form-control @error('delivre_par') is-invalid @enderror"
                                id="delivre_par"
                                name="delivre_par"
                                value="{{ old('delivre_par', $fiche['delivre_par'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="date_emission_fiche">Dakar, le</label>
                            <input
                                type="date"
                                class="form-control @error('date_emission_fiche') is-invalid @enderror"
                                id="date_emission_fiche"
                                name="date_emission_fiche"
                                value="{{ old('date_emission_fiche', ! empty($fiche['date_emission_fiche']) ? \Illuminate\Support\Carbon::parse($fiche['date_emission_fiche'])->format('Y-m-d') : '') }}"
                            >
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ETAPE 3 — DECOMPTE DES AVANCES
            ============================================================ --}}

            <section class="wizard-panel" data-wizard-panel="3" hidden>

                <div class="form-section">

                    <h3>Décompte des avances au départ</h3>

                    {{--
                        Demande utilisatrice : "avoir les tableaux
                        exactement comme les exemples" — voir
                        create.blade.php pour le détail des 2 colonnes
                        ajoutées (rowspan, remarques libres).
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
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_frais_transport_nombre" value="{{ old('avance_frais_transport_nombre', $fiche['avance_frais_transport_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_frais_transport_taux" value="{{ old('avance_frais_transport_taux', $fiche['avance_frais_transport_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                    <td rowspan="4">
                                        <textarea class="form-control" name="indication_requisitions" rows="4">{{ old('indication_requisitions', $fiche['indication_requisitions'] ?? '') }}</textarea>
                                    </td>
                                    <td rowspan="4">
                                        <textarea class="form-control" name="poids_bagages_mobilier" rows="4">{{ old('poids_bagages_mobilier', $fiche['poids_bagages_mobilier'] ?? '') }}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Indemnité journalière normale</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_normale_nombre" value="{{ old('avance_indemnite_normale_nombre', $fiche['avance_indemnite_normale_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_normale_taux" value="{{ old('avance_indemnite_normale_taux', $fiche['avance_indemnite_normale_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>réduite</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_reduite_nombre" value="{{ old('avance_indemnite_reduite_nombre', $fiche['avance_indemnite_reduite_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_reduite_taux" value="{{ old('avance_indemnite_reduite_taux', $fiche['avance_indemnite_reduite_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>partielle</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_partielle_nombre" value="{{ old('avance_indemnite_partielle_nombre', $fiche['avance_indemnite_partielle_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="avance_indemnite_partielle_taux" value="{{ old('avance_indemnite_partielle_taux', $fiche['avance_indemnite_partielle_taux'] ?? '') }}" data-avance-taux></td>
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
                                value="{{ old('arrete_somme', $fiche['arrete_somme'] ?? '') }}"
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
                                value="{{ old('avance_versee', $fiche['avance_versee'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="date_fait_avance">Fait à Dakar, le</label>
                            <input
                                type="date"
                                class="form-control @error('date_fait_avance') is-invalid @enderror"
                                id="date_fait_avance"
                                name="date_fait_avance"
                                value="{{ old('date_fait_avance', ! empty($fiche['date_fait_avance']) ? \Illuminate\Support\Carbon::parse($fiche['date_fait_avance'])->format('Y-m-d') : '') }}"
                            >
                        </div>

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ETAPE 4 — VERSO : DETAIL DES VISAS ET PAIEMENT SUCCESSIFS
                 EN COURS DE ROUTE — voir create.blade.php pour le détail,
                 préreempli ici depuis $fiche (visas_route / visa_avance_* /
                 reglement_* / observations).
            ============================================================ --}}

            @php
                $visasRoute = $fiche['visas_route'] ?? [];
            @endphp

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
                                    @php
                                        $visa = $visasRoute[$i] ?? [];
                                    @endphp
                                    <tr>
                                        <td>Visa {{ $i + 1 }}</td>
                                        <td><input type="text" class="form-control" name="visa_arrivee_lieu[]" value="{{ old('visa_arrivee_lieu.' . $i, $visa['arrivee_lieu'] ?? '') }}"></td>
                                        <td><input type="date" class="form-control" name="visa_arrivee_date[]" value="{{ old('visa_arrivee_date.' . $i, ! empty($visa['arrivee_date']) ? \Illuminate\Support\Carbon::parse($visa['arrivee_date'])->format('Y-m-d') : '') }}"></td>
                                        <td><input type="time" class="form-control" name="visa_arrivee_heure[]" value="{{ old('visa_arrivee_heure.' . $i, $visa['arrivee_heure'] ?? '') }}"></td>
                                        <td><input type="text" class="form-control" name="visa_depart_lieu[]" value="{{ old('visa_depart_lieu.' . $i, $visa['depart_lieu'] ?? '') }}"></td>
                                        <td><input type="date" class="form-control" name="visa_depart_date[]" value="{{ old('visa_depart_date.' . $i, ! empty($visa['depart_date']) ? \Illuminate\Support\Carbon::parse($visa['depart_date'])->format('Y-m-d') : '') }}"></td>
                                        <td><input type="time" class="form-control" name="visa_depart_heure[]" value="{{ old('visa_depart_heure.' . $i, $visa['depart_heure'] ?? '') }}"></td>
                                        <td><input type="text" class="form-control" name="visa_requisitions[]" value="{{ old('visa_requisitions.' . $i, $visa['requisitions'] ?? '') }}"></td>
                                        <td><input type="text" class="form-control" name="visa_poids_bagages[]" value="{{ old('visa_poids_bagages.' . $i, $visa['poids_bagages'] ?? '') }}"></td>
                                        <td><input type="text" class="form-control" name="visa_logement_nourriture[]" value="{{ old('visa_logement_nourriture.' . $i, $visa['logement_nourriture'] ?? '') }}"></td>
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
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_normale_nombre" value="{{ old('visa_avance_indemnite_normale_nombre', $fiche['visa_avance_indemnite_normale_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_normale_taux" value="{{ old('visa_avance_indemnite_normale_taux', $fiche['visa_avance_indemnite_normale_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>réduite</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_reduite_nombre" value="{{ old('visa_avance_indemnite_reduite_nombre', $fiche['visa_avance_indemnite_reduite_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_reduite_taux" value="{{ old('visa_avance_indemnite_reduite_taux', $fiche['visa_avance_indemnite_reduite_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>partielle</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_partielle_nombre" value="{{ old('visa_avance_indemnite_partielle_nombre', $fiche['visa_avance_indemnite_partielle_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="visa_avance_indemnite_partielle_taux" value="{{ old('visa_avance_indemnite_partielle_taux', $fiche['visa_avance_indemnite_partielle_taux'] ?? '') }}" data-avance-taux></td>
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
                                value="{{ old('visa_avance_payer_somme', $fiche['visa_avance_payer_somme'] ?? '') }}"
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
                                value="{{ old('visa_avance_lieu', $fiche['visa_avance_lieu'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="visa_avance_date">le</label>
                            <input
                                type="date"
                                class="form-control @error('visa_avance_date') is-invalid @enderror"
                                id="visa_avance_date"
                                name="visa_avance_date"
                                value="{{ old('visa_avance_date', ! empty($fiche['visa_avance_date']) ? \Illuminate\Support\Carbon::parse($fiche['visa_avance_date'])->format('Y-m-d') : '') }}"
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
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_normale_nombre" value="{{ old('reglement_indemnite_normale_nombre', $fiche['reglement_indemnite_normale_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_normale_taux" value="{{ old('reglement_indemnite_normale_taux', $fiche['reglement_indemnite_normale_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>réduite</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_reduite_nombre" value="{{ old('reglement_indemnite_reduite_nombre', $fiche['reglement_indemnite_reduite_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_reduite_taux" value="{{ old('reglement_indemnite_reduite_taux', $fiche['reglement_indemnite_reduite_taux'] ?? '') }}" data-avance-taux></td>
                                    <td class="avance-decompte" data-avance-decompte>0</td>
                                </tr>
                                <tr>
                                    <td>partielle</td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_partielle_nombre" value="{{ old('reglement_indemnite_partielle_nombre', $fiche['reglement_indemnite_partielle_nombre'] ?? '') }}" data-avance-nombre></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control" name="reglement_indemnite_partielle_taux" value="{{ old('reglement_indemnite_partielle_taux', $fiche['reglement_indemnite_partielle_taux'] ?? '') }}" data-avance-taux></td>
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
                                value="{{ old('reglement_montant_avances', $fiche['reglement_montant_avances'] ?? '') }}"
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
                                value="{{ old('reglement_arrete_somme', $fiche['reglement_arrete_somme'] ?? '') }}"
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
                                value="{{ old('reglement_lieu', $fiche['reglement_lieu'] ?? '') }}"
                            >
                        </div>

                        <div class="form-group">
                            <label for="reglement_date">le</label>
                            <input
                                type="date"
                                class="form-control @error('reglement_date') is-invalid @enderror"
                                id="reglement_date"
                                name="reglement_date"
                                value="{{ old('reglement_date', ! empty($fiche['reglement_date']) ? \Illuminate\Support\Carbon::parse($fiche['reglement_date'])->format('Y-m-d') : '') }}"
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
                            >{{ old('observations', $fiche['observations'] ?? '') }}</textarea>
                        </div>

                    </div>

                </div>

            </section>

            </form>

            {{-- ============================================================
                 ETAPE 5 — PIECES JOINTES (RECTO/VERSO)

                 Demande utilisatrice : "Pièces jointes (recto/verso) doit
                 être étape 5, juste au lieu de le mettre tout le temps en
                 bas" — chaque face a son propre <form> multipart, posté
                 directement vers FraisDeplacementController::
                 remplacerJustificatif() (front), HORS du <form> principal
                 de la fiche (un <form> ne peut pas en contenir un autre).
                 Mêmes routes que celles déjà utilisées auparavant sur le
                 show (telecharger/remplacer/destroy).
            ============================================================ --}}

            @php
                $justificatifsParCommentaire = collect($fiche['justificatifs'] ?? [])->groupBy(fn ($j) => $j['commentaire'] ?? '');
                $recto = $justificatifsParCommentaire->get('Recto', collect())->first();
                $verso = $justificatifsParCommentaire->get('Verso', collect())->first();
            @endphp

            <section class="wizard-panel" data-wizard-panel="5" hidden>

                <div class="form-section">

                    <h3>Feuille de déplacement (fichier — recto et verso)</h3>

                    <div class="form-grid">

                        @foreach (['Recto' => $recto, 'Verso' => $verso] as $face => $justificatif)

                            <div class="form-group">
                                <label>{{ $face }}</label>

                                @if ($justificatif)

                                    <p>
                                        <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                                        {{ $justificatif['nom_original'] ?? 'Fichier' }}
                                    </p>

                                    <div class="piece-actions">
                                        <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement.justificatifs.telecharger', [$fiche['id'], $justificatif['id']]) }}">
                                            <i class="fa-solid fa-download" aria-hidden="true"></i>
                                            Télécharger
                                        </a>
                                        <form method="POST" action="{{ route('indemnites.frais-deplacement.justificatifs.destroy', [$fiche['id'], $justificatif['id']]) }}" onsubmit="return confirm('Supprimer ce fichier ({{ $face }}) ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-secondary" type="submit">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>

                                @else

                                    <p class="empty-message">Non déposé.</p>

                                @endif

                                <form method="POST" action="{{ route('indemnites.frais-deplacement.justificatifs.remplacer', $fiche['id']) }}" enctype="multipart/form-data" class="piece-form">
                                    @csrf
                                    <input type="hidden" name="commentaire" value="{{ $face }}">
                                    @if ($justificatif)
                                        <input type="hidden" name="ancien_id" value="{{ $justificatif['id'] }}">
                                    @endif
                                    <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <button class="btn-secondary" type="submit">
                                        {{ $justificatif ? 'Remplacer' : 'Déposer' }}
                                    </button>
                                </form>

                            </div>

                        @endforeach

                    </div>

                </div>

            </section>

            {{-- ============================================================
                 ACTIONS

                 Hors du <form> principal désormais (voir plus haut) — le
                 bouton d'enregistrement lui est rattaché via l'attribut
                 HTML5 form="ficheDeplacementForm".
            ============================================================ --}}

            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement.show', $fiche['id']) }}" data-wizard-cancel>
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
                <button class="btn-primary" type="submit" form="ficheDeplacementForm" data-wizard-submit hidden>
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Enregistrer les modifications
                </button>
            </div>

        </div>

    </section>

</section>

</main>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/indemnites/convocation-wizard.css') }}">
<style>
    /* Demande utilisatrice : "il faut élargir le formulaire, y'a des infos
       qui ne s'affichent pas parce que c'est étroit" — les tableaux VERSO
       (9 colonnes) et le tableau des avances (6 colonnes) ont besoin de
       plus de place que le max-width partagé (1500px, voir
       convocation-wizard.css) ; élargi ici comme pour "Nouvelle fiche". */
    .convocation-card {
        max-width: 2200px;
    }

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

    /* Section "Pièces jointes (recto/verso)" ajoutée sous le wizard —
       mêmes classes que show.blade.php (piece-actions/piece-form). */
    .piece-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .piece-actions form {
        display: inline;
    }

    .piece-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
    }

    .piece-form input[type="file"] {
        max-width: 220px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/indemnites/frais-deplacement-wizard.js') }}"></script>
<script>
    (function () {
        "use strict";

        // 3 tableaux "Nombre x Taux" sur cette page : "Décompte des avances
        // au départ" (RECTO, id="avanceTable"/"avanceTotal") + les 2
        // mini-tableaux du VERSO ("Avance ou compte perçus en route" et
        // "Règlement définitif", via data-avance-total-target) — voir
        // create.blade.php, même mécanique.
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
