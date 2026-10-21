@extends('layouts.app')

@section('title', 'SICORE - Fiche de déplacement')

@section('content')

<main class="main-content">

<x-topbar
    title="Détail de la fiche de déplacement"
    subtitle="Indemnites > Frais de déplacement > Détail"
    icon="fa-solid fa-route"
/>

<section class="content-area">

    {{--
        Demande utilisatrice : "gère-moi juste le show du fiche, fait le
        comme detail convocation" — mêmes classes/CSS que
        convocations/show.blade.php (.convocation-page /
        .convocation-page-header / .convocation-page-body), voir
        @push('styles') plus bas, copié depuis ce fichier-là.
    --}}

    <section class="convocation-page">

        <div class="convocation-page-header">
            <div>
                <h2>{{ $fiche['reference'] ?? '—' }}</h2>
                <p class="breadcrumb">
                    {{ trim(($fiche['beneficiaire']['prenom'] ?? '') . ' ' . ($fiche['beneficiaire']['nom'] ?? '')) ?: '—' }}
                    @if (! empty($fiche['convocation']['objet']))
                        &middot; {{ $fiche['convocation']['objet'] }}
                    @endif
                </p>
            </div>

            <x-module-indemnite type="statut-frais-deplacement" :statut="$fiche['statut'] ?? null" />
        </div>

        <div class="convocation-page-body">

            {{-- ============================================================
                 BENEFICIAIRE
            ============================================================ --}}

            <div class="form-section">

                <h3>Bénéficiaire</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Nom et prénoms</label>
                        <p>{{ trim(($fiche['beneficiaire']['prenom'] ?? '') . ' ' . ($fiche['beneficiaire']['nom'] ?? '')) ?: '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Matricule</label>
                        <p>{{ $fiche['beneficiaire']['matricule'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Type de bénéficiaire</label>
                        <p>{{ ucfirst($fiche['statut_agent'] ?? '—') }}</p>
                    </div>

                    <div class="form-group">
                        <label>Indice</label>
                        <p>{{ ($fiche['statut_agent'] ?? null) === 'fonctionnaire' ? ($fiche['indice_agent'] ?? '—') : 'Non applicable' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Grade et emploi</label>
                        <p>{{ $fiche['grade_emploi'] ?? '—' }}</p>
                    </div>

                </div>

            </div>

            {{-- ============================================================
                 TRAJET ET ORDRE DE MISSION
            ============================================================ --}}

            <div class="form-section">

                <h3>Trajet et ordre de mission</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Lieu de départ</label>
                        <p>{{ $fiche['lieu_depart'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Date de départ</label>
                        <p>{{ ! empty($fiche['date_depart']) ? \Illuminate\Support\Carbon::parse($fiche['date_depart'])->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Heure de départ</label>
                        <p>{{ $fiche['heure_depart'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Lieu de destination</label>
                        <p>{{ $fiche['lieu_destination'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Date de retour</label>
                        <p>{{ ! empty($fiche['date_retour']) ? \Illuminate\Support\Carbon::parse($fiche['date_retour'])->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Nature du déplacement</label>
                        <p>{{ $fiche['motif'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Moyen de transport</label>
                        <p>{{ $fiche['moyen_transport'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Distance (km)</label>
                        <p>{{ $fiche['distance_km'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Poids de bagages (kg)</label>
                        <p>{{ $fiche['poids_bagages_kg'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Ordre de service N°</label>
                        <p>
                            {{ $fiche['ordre_service_numero'] ?? '—' }}
                            @if (! empty($fiche['ordre_service_date']))
                                du {{ \Illuminate\Support\Carbon::parse($fiche['ordre_service_date'])->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Émis par</label>
                        <p>{{ $fiche['ordre_service_emetteur'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Accompagné de</label>
                        <p>{{ $fiche['accompagne_de'] ?? '—' }}</p>
                    </div>

                    <div class="form-group full">
                        <label>Itinéraire</label>
                        <p>{{ $fiche['itineraire'] ?? '—' }}</p>
                    </div>

                </div>

            </div>

            {{-- ============================================================
                 DECOMPTE DES AVANCES AU DEPART (saisi a la creation)
            ============================================================ --}}

            @php
                $lignesAvance = [
                    ['Frais de voyage et de transport', $fiche['avance_frais_transport_nombre'] ?? null, $fiche['avance_frais_transport_taux'] ?? null],
                    ['Indemnité journalière normale', $fiche['avance_indemnite_normale_nombre'] ?? null, $fiche['avance_indemnite_normale_taux'] ?? null],
                    ['Indemnité journalière réduite', $fiche['avance_indemnite_reduite_nombre'] ?? null, $fiche['avance_indemnite_reduite_taux'] ?? null],
                    ['Indemnité journalière partielle', $fiche['avance_indemnite_partielle_nombre'] ?? null, $fiche['avance_indemnite_partielle_taux'] ?? null],
                ];
            @endphp

            @if (! empty($fiche['avance_total']))

                <div class="form-section">

                    <h3>Décompte des avances au départ</h3>

                    <div class="table-responsive">
                        <table class="table">
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
                                @foreach ($lignesAvance as $index => $ligne)
                                    <tr>
                                        <td>{{ $ligne[0] }}</td>
                                        <td>{{ $ligne[1] ?? '—' }}</td>
                                        <td>{{ $ligne[2] ?? '—' }}</td>
                                        <td>{{ number_format(($ligne[1] ?? 0) * ($ligne[2] ?? 0), 0, ',', ' ') }}</td>
                                        @if ($index === 0)
                                            <td rowspan="{{ count($lignesAvance) }}">{{ $fiche['indication_requisitions'] ?? '—' }}</td>
                                            <td rowspan="{{ count($lignesAvance) }}">{{ $fiche['poids_bagages_mobilier'] ?? '—' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td><strong>{{ number_format($fiche['avance_total'], 0, ',', ' ') }}</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

            @endif

            {{-- ============================================================
                 VERSO — DETAIL DES VISAS ET PAIEMENT SUCCESSIFS EN COURS
                 DE ROUTE — demande utilisatrice : "tu peux faire le verso ?"
                 (reporté depuis "Verso plus tard" lors du RECTO). Affiché
                 seulement si au moins une donnée du verso a été saisie —
                 les fiches créées avant cette fonctionnalité n'en ont pas.
            ============================================================ --}}

            @php
                $visasRoute = $fiche['visas_route'] ?? [];
                $visaRempli = collect($visasRoute)->contains(function ($visa) {
                    return collect($visa ?? [])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                });
                $afficherVerso = $visaRempli
                    || ! empty($fiche['visa_avance_total'])
                    || ! empty($fiche['reglement_total'])
                    || ! empty($fiche['observations']);

                $lignesVisaAvance = [
                    ['Indemnité journalière normale', $fiche['visa_avance_indemnite_normale_nombre'] ?? null, $fiche['visa_avance_indemnite_normale_taux'] ?? null],
                    ['Indemnité journalière réduite', $fiche['visa_avance_indemnite_reduite_nombre'] ?? null, $fiche['visa_avance_indemnite_reduite_taux'] ?? null],
                    ['Indemnité journalière partielle', $fiche['visa_avance_indemnite_partielle_nombre'] ?? null, $fiche['visa_avance_indemnite_partielle_taux'] ?? null],
                ];

                $lignesReglement = [
                    ['Indemnité journalière normale', $fiche['reglement_indemnite_normale_nombre'] ?? null, $fiche['reglement_indemnite_normale_taux'] ?? null],
                    ['Indemnité journalière réduite', $fiche['reglement_indemnite_reduite_nombre'] ?? null, $fiche['reglement_indemnite_reduite_taux'] ?? null],
                    ['Indemnité journalière partielle', $fiche['reglement_indemnite_partielle_nombre'] ?? null, $fiche['reglement_indemnite_partielle_taux'] ?? null],
                ];
            @endphp

            @if ($afficherVerso)

                <div class="form-section">

                    <h3>Détail des visas et paiement successifs en cours de route (verso)</h3>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th rowspan="2"></th>
                                    <th colspan="3">À l'arrivée</th>
                                    <th colspan="3">Au départ</th>
                                    <th rowspan="2">Réquisitions délivrées en cours de route</th>
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
                                        <td>{{ $visa['arrivee_lieu'] ?? '—' }}</td>
                                        <td>{{ ! empty($visa['arrivee_date']) ? \Illuminate\Support\Carbon::parse($visa['arrivee_date'])->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $visa['arrivee_heure'] ?? '—' }}</td>
                                        <td>{{ $visa['depart_lieu'] ?? '—' }}</td>
                                        <td>{{ ! empty($visa['depart_date']) ? \Illuminate\Support\Carbon::parse($visa['depart_date'])->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $visa['depart_heure'] ?? '—' }}</td>
                                        <td>{{ $visa['requisitions'] ?? '—' }}</td>
                                        <td>{{ $visa['poids_bagages'] ?? '—' }}</td>
                                        <td>{{ $visa['logement_nourriture'] ?? '—' }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                </div>

                @if (! empty($fiche['visa_avance_total']))

                    <div class="form-section">

                        <h3>Avance ou compte perçus en route</h3>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Nombre</th>
                                        <th>Taux</th>
                                        <th>Décompte</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lignesVisaAvance as $ligne)
                                        <tr>
                                            <td>{{ $ligne[0] }}</td>
                                            <td>{{ $ligne[1] ?? '—' }}</td>
                                            <td>{{ $ligne[2] ?? '—' }}</td>
                                            <td>{{ number_format(($ligne[1] ?? 0) * ($ligne[2] ?? 0), 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>TOTAL</strong></td>
                                        <td><strong>{{ number_format($fiche['visa_avance_total'], 0, ',', ' ') }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="form-grid" style="margin-top: 16px;">

                            <div class="form-group full">
                                <label>Arrêté à payer la somme de</label>
                                <p>{{ $fiche['visa_avance_payer_somme'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Fait à</label>
                                <p>{{ $fiche['visa_avance_lieu'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Le</label>
                                <p>{{ ! empty($fiche['visa_avance_date']) ? \Illuminate\Support\Carbon::parse($fiche['visa_avance_date'])->format('d/m/Y') : '—' }}</p>
                            </div>

                        </div>

                    </div>

                @endif

                @if (! empty($fiche['reglement_total']))

                    <div class="form-section">

                        <h3>Règlement définitif</h3>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Nombre</th>
                                        <th>Taux</th>
                                        <th>Décompte</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lignesReglement as $ligne)
                                        <tr>
                                            <td>{{ $ligne[0] }}</td>
                                            <td>{{ $ligne[1] ?? '—' }}</td>
                                            <td>{{ $ligne[2] ?? '—' }}</td>
                                            <td>{{ number_format(($ligne[1] ?? 0) * ($ligne[2] ?? 0), 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>TOTAL</strong></td>
                                        <td><strong>{{ number_format($fiche['reglement_total'], 0, ',', ' ') }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="form-grid" style="margin-top: 16px;">

                            <div class="form-group">
                                <label>Montant des avances déjà perçues</label>
                                <p>{{ isset($fiche['reglement_montant_avances']) ? number_format($fiche['reglement_montant_avances'], 0, ',', ' ') : '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Reste à payer</label>
                                <p>{{ isset($fiche['reglement_reste_a_payer']) ? number_format($fiche['reglement_reste_a_payer'], 0, ',', ' ') : '—' }}</p>
                            </div>

                            <div class="form-group full">
                                <label>Arrêté à la somme de</label>
                                <p>{{ $fiche['reglement_arrete_somme'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Fait à</label>
                                <p>{{ $fiche['reglement_lieu'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Le</label>
                                <p>{{ ! empty($fiche['reglement_date']) ? \Illuminate\Support\Carbon::parse($fiche['reglement_date'])->format('d/m/Y') : '—' }}</p>
                            </div>

                        </div>

                    </div>

                @endif

                @if (! empty($fiche['observations']))

                    <div class="form-section">

                        <h3>Observations</h3>

                        <p>{{ $fiche['observations'] }}</p>

                    </div>

                @endif

            @endif

            {{-- ============================================================
                 PIECES JOINTES
            ============================================================ --}}

            <div class="form-section">

                <h3>Pièces jointes</h3>

                {{--
                    La feuille de déplacement papier est RECTO-VERSO : ses 2
                    faces sont déposées comme 2 pièces jointes distinctes
                    (voir FraisDeplacementController::store() côté back),
                    taguées par leur "commentaire" ("Recto"/"Verso") — on
                    les affiche donc chacune dans sa propre case. Demande
                    utilisatrice : pas de bouton (télécharger/modifier/
                    supprimer) ici, juste l'affichage — le module Calcul
                    déjà existant gère le reste.
                --}}
                @php
                    $justificatifsParCommentaire = collect($fiche['justificatifs'] ?? [])->groupBy(fn ($j) => $j['commentaire'] ?? '');
                    $recto = $justificatifsParCommentaire->get('Recto', collect())->first();
                    $verso = $justificatifsParCommentaire->get('Verso', collect())->first();
                    $autres = collect($fiche['justificatifs'] ?? [])->reject(fn ($j) => in_array($j['commentaire'] ?? null, ['Recto', 'Verso'], true));
                @endphp

                <div class="form-grid">

                    @foreach (['Recto' => $recto, 'Verso' => $verso] as $face => $justificatif)

                        <div class="form-group">
                            <label>{{ $face }}</label>

                            @if ($justificatif)
                                <p>
                                    <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                                    {{ $justificatif['nom_original'] ?? 'Fichier' }}
                                </p>
                                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement.justificatifs.telecharger', [$fiche['id'], $justificatif['id']]) }}">
                                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                                    Télécharger
                                </a>
                            @else
                                <p class="empty-message">Non déposé.</p>
                            @endif
                        </div>

                    @endforeach

                </div>

                @if ($autres->isNotEmpty())

                    <div class="table-responsive" style="margin-top: 16px;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fichier</th>
                                    <th>Commentaire</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($autres as $justificatif)
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                                            {{ $justificatif['nom_original'] ?? 'Fichier' }}
                                        </td>
                                        <td>{{ $justificatif['commentaire'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @endif

            </div>

            {{-- ============================================================
                 ACTIONS
            ============================================================ --}}

            <div class="form-actions">

                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement', ['objet' => $fiche['convocation']['objet'] ?? null]) }}">
                    Retour à la liste
                </a>

                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement.pdf', $fiche['id']) }}">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    Télécharger la fiche
                </a>

                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement.edit', $fiche['id']) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    Modifier
                </a>

                <form method="POST" action="{{ route('indemnites.frais-deplacement.destroy', $fiche['id']) }}" onsubmit="return confirm('Supprimer définitivement cette fiche de déplacement ?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-secondary" type="submit">
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        Supprimer
                    </button>
                </form>

            </div>

        </div>

    </section>

</section>

</main>

@endsection


@push('styles')
<style>

    /* Copié depuis convocations/show.blade.php (demande utilisatrice :
       "fait le comme detail convocation") — même habillage exact. */

    /* Le bouton "Supprimer" est dans un <form> (POST + @method('DELETE'))
       — display:inline-flex pour qu'il reste sur la même ligne que les
       autres actions (.form-actions est en display:flex, voir app.css). */
    .form-actions form {
        display: inline-flex;
    }

    .convocation-page {
        width: 100%;
        margin: 24px 0 40px;
        box-sizing: border-box;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .convocation-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .convocation-page-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .convocation-page-header .breadcrumb {
        margin: 4px 0 0;
    }

    .convocation-page-body {
        display: grid;
        gap: 22px;
        padding: 26px 30px 30px;
        box-sizing: border-box;
    }

    .convocation-page-body .form-section {
        padding: 22px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .convocation-page-body .form-section h3 {
        margin: 0 0 16px;
    }

    .convocation-page-body .table-responsive {
        overflow-x: auto;
    }

    /* Tableau de saisie des lignes de frais (étape Calcul) — même look que
       le tableau "avance" du formulaire de création. */
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

    .form-section label.required-label {
        font-weight: 800;
    }

    /* Actions (télécharger/supprimer) + formulaire ajouter-ou-remplacer
       d'une pièce jointe (recto/verso). */
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

    @media (max-width: 768px) {

        .convocation-page-header {
            padding: 16px 18px;
        }

        .convocation-page-body {
            padding: 18px;
        }

        .convocation-page-body .form-section {
            padding: 16px;
        }

    }

</style>
@endpush

@push('scripts')
<script>
    (function () {
        "use strict";

        // Calcule en direct chaque ligne (Quantité x Taux) et le TOTAL du
        // tableau "Calcul" — purement indicatif côté front, le montant
        // enregistré est toujours recalculé côté serveur (voir
        // FraisDeplacementController::calculer()).
        var table = document.getElementById("calculTable");

        if (! table) {
            return;
        }

        var lignes = table.querySelectorAll("tbody tr");
        var totalCell = document.getElementById("calculTotal");

        function nombre(valeur) {
            var n = parseFloat(valeur);
            return isNaN(n) ? 0 : n;
        }

        function recalculer() {
            var total = 0;

            lignes.forEach(function (ligne) {
                var champQuantite = ligne.querySelector("[data-ligne-quantite]");
                var champTaux = ligne.querySelector("[data-ligne-taux]");
                var celluleMontant = ligne.querySelector("[data-ligne-montant]");

                var montant = nombre(champQuantite.value) * nombre(champTaux.value);
                celluleMontant.textContent = montant.toLocaleString("fr-FR", { maximumFractionDigits: 2 });
                total += montant;
            });

            totalCell.innerHTML = "<strong>" + total.toLocaleString("fr-FR", { maximumFractionDigits: 2 }) + "</strong>";
        }

        table.querySelectorAll("[data-ligne-quantite], [data-ligne-taux]").forEach(function (champ) {
            champ.addEventListener("input", recalculer);
        });

        recalculer();
    })();
</script>
@endpush
