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
         Reprend le meme habillage que "Nouvelle convocation" (.form-card /
         .convocation-card / .form-card-header / .form-section / .form-grid
         — voir resources/views/pages/indemnites/convocations/create.blade.php
         et convocation-wizard.css).

         Les champs suivent EXACTEMENT, dans le meme ordre et avec les
         memes intitules, le RECTO de la feuille de deplacement papier
         fournie par l'utilisatrice (Ministere des Finances et du Budget,
         Direction du Materiel et du Transit Administratif) — demande
         explicite ("prend exactement ce qui est dans le document"), pas de
         regroupement thematique invente : une seule section "Feuille de
         deplacement" qui reprend la feuille ligne par ligne, puis le
         tableau "Decompte des avances au depart" tel quel juste en dessous
         (meme decoupage visuel que le papier). Seuls les 2 champs
         "Type de beneficiaire" / "Montant" n'ont pas d'equivalent
         imprime sur la feuille : ils pilotent le calcul du montant
         (vacataire/fonctionnaire/contractuel), deja en place avant cette
         demande et necessaires au reste de l'application.

         Le VERSO (visas/paiements successifs en cours de route + reglement
         definitif) reste pour une etape ulterieure sur la fiche deja creee
         (voir show.blade.php), puisqu'il se remplit progressivement
         pendant/apres la mission plutot qu'a la creation — voir la
         reponse de l'utilisatrice a ce sujet.
    ================================================================ --}}

    {{-- ================================================================
         NOTA — deplace tout en bas de page, en bandeau horizontal (pas de
         barre laterale : ca "derangeait" le formulaire) — voir bloc NOTA
         apres le </section> du formulaire, plus bas.
    ================================================================ --}}

    <section class="form-card convocation-card">

        <div class="form-card-header">
            <div>
                <h2>Feuille de déplacement</h2>
                <p class="breadcrumb">
                    {{ $convocation['objet'] ?? '—' }}
                    &middot; {{ $convocation['session'] ?? '—' }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('indemnites.frais-deplacement.store') }}" enctype="multipart/form-data" class="convocation-form" id="ficheDeplacementForm">
            @csrf

            <input type="hidden" name="convocation_id" value="{{ $convocationId }}">
            <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire['id'] }}">

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

            {{-- ============================================================
                 FEUILLE DE DEPLACEMENT — reprise ligne par ligne du RECTO
                 papier, dans l'ordre exact du document.
            ============================================================ --}}

            <div class="form-section">

                <h3>Feuille de déplacement</h3>

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

                    @if (($beneficiaire['categorie_personnel'] ?? null) === 'vacataire')

                        <div class="form-group">
                            <label>Montant</label>
                            <p><strong>150 000 FCFA</strong> (montant fixe vacataire)</p>
                        </div>

                    @elseif (($beneficiaire['categorie_personnel'] ?? null) === 'contractuel')

                        <div class="form-group">
                            <label for="montant_saisi">Montant</label>
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

                    @elseif (($beneficiaire['categorie_personnel'] ?? null) !== 'fonctionnaire')

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

                    <div class="form-group">
                        <label for="lieu_depart">Partant de <span class="required">*</span></label>
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
                        <label for="date_depart">le <span class="required">*</span></label>
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
                        <label for="lieu_destination">Pour se rendre à <span class="required">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('lieu_destination') is-invalid @enderror"
                            id="lieu_destination"
                            name="lieu_destination"
                            value="{{ old('lieu_destination', $convocation['lieu_examen'] ?? '') }}"
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

                    <div class="form-group">
                        <label for="indice_agent">indice</label>
                        {{--
                            Demande utilisatrice : "tenir compte du champ indice
                            si c'est fonctionnaire, contractuelle a remplir et
                            fix pour vacataire" — l'indice ne s'applique QUE
                            pour un agent fonctionnaire (sert au calcul par
                            barème). Pour vacataire/contractuel, ce champ n'a
                            pas de sens : on l'indique clairement au lieu de
                            laisser un champ vide sans explication (le montant
                            de ces deux categories est deja gere plus haut,
                            dans "Montant" : fixe pour vacataire, a remplir
                            pour contractuel).
                        --}}
                        @if (($beneficiaire['categorie_personnel'] ?? null) === 'fonctionnaire')
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
                        @else
                            <p class="empty-message">Non applicable (uniquement pour un agent fonctionnaire)</p>
                        @endif
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
                        <label for="date_retour">Date de retour <span class="required">*</span></label>
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

            {{-- ============================================================
                 DECOMPTE DES AVANCES AU DEPART — meme tableau que le
                 papier (Nombre / Taux / Décompte par ligne). Le Décompte de
                 chaque ligne et le TOTAL sont calcules en direct par JS
                 (purement indicatif) puis re-verifies/enregistres cote
                 serveur — voir FraisDeplacementController::store().
            ============================================================ --}}

            <div class="form-section">

                <h3>Décompte des avances au départ</h3>

                <div class="table-responsive">
                    <table class="table avance-table" id="avanceTable">
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
                                <td>Frais de voyage et de transport (5)</td>
                                <td><input type="number" step="0.01" min="0" class="form-control" name="avance_frais_transport_nombre" value="{{ old('avance_frais_transport_nombre') }}" data-avance-nombre></td>
                                <td><input type="number" step="0.01" min="0" class="form-control" name="avance_frais_transport_taux" value="{{ old('avance_frais_transport_taux') }}" data-avance-taux></td>
                                <td class="avance-decompte" data-avance-decompte>0</td>
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

            <div class="form-section">

                <h3>Feuille de déplacement (fichier)</h3>

                <div class="form-group">
                    <label for="fichier">Feuille de déplacement remplie (scan ou photo)</label>
                    <input
                        type="file"
                        class="form-control @error('fichier') is-invalid @enderror"
                        id="fichier"
                        name="fichier"
                        accept=".pdf,.jpg,.jpeg,.png"
                    >
                    <small class="form-hint">PDF, JPG ou PNG, 5 Mo maximum. Optionnel à cette étape, peut être ajouté après coup.</small>
                </div>

            </div>

            <div class="form-actions">

                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement', ['objet' => $convocation['objet'] ?? null]) }}">
                    Annuler
                </a>

                <button class="btn-primary" type="submit">
                    Créer la fiche de déplacement
                </button>

            </div>

        </form>

    </section>

    {{-- ============================================================
         NOTA — tout en bas de page, bandeau horizontal (pas de barre
         laterale) reprenant l'encadre "NOTA" + les notes de bas de page
         (1) a (5) de la feuille papier.
    ============================================================ --}}

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

</section>

</main>

@push('styles')
{{-- Feuille de style partagee du wizard "convocations" — voir l'entete de
     ce fichier CSS : c'est de la ou viennent .convocation-card/.form-card-header/
     .form-section/.form-grid/.convocation-form, exactement le meme habillage
     que "Nouvelle convocation". --}}
<link rel="stylesheet" href="{{ asset('assets/css/indemnites/convocation-wizard.css') }}">
<style>
    /* NOTA en bandeau horizontal tout en bas de page (plus de barre
       laterale — ca "derangeait" le formulaire). Meme largeur/centrage que
       .convocation-card pour rester aligne avec le formulaire au-dessus. */
    .nota-bandeau {
        width: calc(100% - 40px);
        max-width: 1500px;
        margin: 20px auto 40px;
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

    /* Forme horizontale demandee : les 5 definitions en ligne, cote a
       cote, qui reviennent a la ligne (wrap) si la fenetre est trop
       etroite, plutot qu'empilees verticalement. */
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
<script>
    (function () {
        "use strict";

        // Calcule en direct chaque ligne (Nombre x Taux) et le TOTAL du
        // tableau "Décompte des avances au départ" — purement indicatif
        // cote front, le montant enregistre est toujours recalcule cote
        // serveur (voir FraisDeplacementController::store()).
        var table = document.getElementById("avanceTable");

        if (! table) {
            return;
        }

        var lignes = table.querySelectorAll("tbody tr");
        var totalCell = document.getElementById("avanceTotal");

        function nombre(valeur) {
            var n = parseFloat(valeur);
            return isNaN(n) ? 0 : n;
        }

        function recalculer() {
            var total = 0;

            lignes.forEach(function (ligne) {
                var champNombre = ligne.querySelector("[data-avance-nombre]");
                var champTaux = ligne.querySelector("[data-avance-taux]");
                var celluleDecompte = ligne.querySelector("[data-avance-decompte]");

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
    })();
</script>
@endpush

@endsection
