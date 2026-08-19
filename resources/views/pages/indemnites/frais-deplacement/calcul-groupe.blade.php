@extends('layouts.app')

@section('title', 'SICORE - Calcul groupé des frais de déplacement')

@section('content')

<main class="main-content">

    <x-topbar
        title="Calcul des frais — tous les membres"
        subtitle="Indemnites > Frais de déplacement > Calcul groupé"
        icon="fa-solid fa-route"
    />

    <section class="content-area">

        <div class="actions-row">
            <p class="breadcrumb">
                <a href="{{ route('indemnites.frais-deplacement') }}">&larr; Retour aux fiches de déplacement</a>
            </p>
        </div>

        {{-- ============================================================
             CONVOCATION — donnees partagees (lecture seule : le back
             calcule toujours a partir des valeurs reelles de la
             convocation, pas de ce qui serait tape ici).
        ============================================================ --}}

        <section class="form-card convoc-summary-card">
            <div class="convoc-summary-meta">
                <h2>{{ $convocation['objet'] ?? '—' }}</h2>
                <p class="section-description">
                    Session {{ $convocation['session'] ?? '—' }}
                    @if (! empty($convocation['centres'][0]['centre']))
                        &middot; {{ $convocation['centres'][0]['centre'] }}
                    @endif
                </p>
            </div>
            <div class="convoc-summary-fields">
                <div class="convoc-summary-field">
                    <span class="label">Lieu d'examen</span>
                    <span class="value">{{ $convocation['lieu_examen'] ?? '—' }}</span>
                </div>
                <div class="convoc-summary-field">
                    <span class="label">Période d'examen</span>
                    <span class="value">
                        @if (! empty($convocation['date_debut']) && ! empty($convocation['date_fin']))
                            Du {{ \Illuminate\Support\Carbon::parse($convocation['date_debut'])->format('d/m/Y') }}
                            au {{ \Illuminate\Support\Carbon::parse($convocation['date_fin'])->format('d/m/Y') }}
                        @else
                            <span class="text-danger">Non définie — le calcul est impossible tant que la convocation n'a pas de dates.</span>
                        @endif
                    </span>
                </div>
                <div class="convoc-summary-field">
                    <span class="label">Jours</span>
                    <span class="value" id="joursAffiche">—</span>
                </div>
            </div>
        </section>

        {{-- ============================================================
             BAREME DE REFERENCE (repliable)
        ============================================================ --}}

        <details class="bareme-details">
            <summary>
                Barème de référence
                <span class="bareme-preview">
                    <span class="bareme-chip">I · 25 000 F</span>
                    <span class="bareme-chip">II · 20 000 F</span>
                    <span class="bareme-chip">III · 15 000 F</span>
                </span>
                <i class="fa-solid fa-chevron-right chev" aria-hidden="true"></i>
            </summary>
            <div class="bareme-grid" id="baremeGrid">
                <div class="bareme-row" data-groupe="I">
                    <div class="bareme-row-top"><strong>Groupe I</strong><span>25 000 F</span></div>
                    <span class="bareme-detail">Indice ≥ 2296</span>
                    <span class="bareme-detail">Salaire annuel ≥ 24 294 500</span>
                </div>
                <div class="bareme-row" data-groupe="II">
                    <div class="bareme-row-top"><strong>Groupe II</strong><span>20 000 F</span></div>
                    <span class="bareme-detail">Indice entre 1728 et 2295</span>
                    <span class="bareme-detail">Salaire annuel entre 2 109 610 et 24 294 500</span>
                </div>
                <div class="bareme-row" data-groupe="III">
                    <div class="bareme-row-top"><strong>Groupe III</strong><span>15 000 F</span></div>
                    <span class="bareme-detail">Indice ≤ 1728</span>
                    <span class="bareme-detail">Salaire annuel ≤ 2 109 610</span>
                </div>
            </div>
            <p class="section-description bareme-note">
                Contractuel/vacataire : le montant mensuel saisi est multiplié par 12 pour obtenir le salaire annuel comparé au barème.
                <strong>Lieu d'affectation</strong> = le centre où la personne est envoyée pour cet examen (déduit automatiquement, non modifiable).
                <strong>Provenance</strong> = où elle exerce habituellement (modifiable ci-dessous). Les deux identiques ⇒ taux ÷ 4 (pas de vrai déplacement).
                Le montant final officiel reste celui recalculé par le serveur à la création de chaque fiche.
            </p>
        </details>

        {{-- ============================================================
             MEMBRES ELIGIBLES
        ============================================================ --}}

        <form method="POST" action="{{ route('indemnites.frais-deplacement.calcul-groupe.store') }}" data-calcul-groupe-form>
            @csrf
            <input type="hidden" name="convocation_id" value="{{ $convocationId }}">
            <input type="hidden" name="lieu_examen" id="lieuExamenInput" value="{{ $convocation['lieu_examen'] ?? '' }}">
            <input type="hidden" name="date_debut" value="{{ $convocation['date_debut'] ?? '' }}">
            <input type="hidden" name="date_fin" value="{{ $convocation['date_fin'] ?? '' }}">

            <section class="table-card">

                <div class="table-responsive">
                    <table class="table" id="membresGroupeTable">
                        <thead>
                            <tr>
                                <th class="checkbox-cell"><i class="fa-solid fa-check" aria-hidden="true"></i></th>
                                <th>Membre</th>
                                <th>Catégorie</th>
                                <th>Indice / Montant mensuel</th>
                                <th>Lieu d'affectation</th>
                                <th>Provenance</th>
                                <th>Groupe · Trajet</th>
                                <th style="text-align:right;">Montant estimé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($membres as $membre)
                                <tr data-membre-row data-categorie="{{ $membre['categorie_personnel'] ?? '' }}">
                                    <td class="checkbox-cell">
                                        @if (empty($membre['fiche_deplacement_id']))
                                            <input type="checkbox" name="beneficiaire_id[]" value="{{ $membre['id'] }}" checked data-membre-checkbox>
                                            <input type="hidden" name="nom.{{ $membre['id'] }}" value="{{ trim(($membre['prenom'] ?? '').' '.($membre['nom'] ?? '')) }}">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="membre-cell">
                                            <div class="membre-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($membre['prenom'] ?? '', 0, 1).\Illuminate\Support\Str::substr($membre['nom'] ?? '', 0, 1)) }}</div>
                                            <div>
                                                <div class="membre-name">{{ $membre['prenom'] ?? '—' }} {{ $membre['nom'] ?? '' }}</div>
                                                <div class="membre-sub">{{ $membre['matricule'] ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="cat-badge cat-{{ $membre['categorie_personnel'] ?? '' }}">
                                            {{ ucfirst($membre['categorie_personnel'] ?? '—') }}
                                        </span>
                                    </td>

                                    @if (! empty($membre['fiche_deplacement_id']))
                                        @php
                                            // Indice/salaire annuel : figes au moment de la creation
                                            // de la fiche (non redecouvrables autrement pour un
                                            // contractuel/vacataire). Lieu d'affectation/provenance :
                                            // toujours recalcules a partir de la structure actuelle de
                                            // la convocation (memes valeurs que pour une ligne pas
                                            // encore creee). Memes seuils que determinerGroupe() cote
                                            // back (FraisDeplacementController).
                                            $ficheCategorie = $membre['categorie_personnel'] ?? null;
                                            $ficheDonneesDisponibles = $ficheCategorie === 'fonctionnaire'
                                                ? ($membre['fiche_indice_agent'] ?? null) !== null
                                                : ($membre['fiche_salaire_annuel'] ?? null) !== null;
                                            $ficheMontantManquant = false;

                                            if ($ficheDonneesDisponibles) {
                                                if ($ficheCategorie === 'fonctionnaire') {
                                                    $ficheIndice = (float) $membre['fiche_indice_agent'];
                                                    $ficheGroupe = $ficheIndice >= 2296 ? 'I' : ($ficheIndice >= 1728 ? 'II' : 'III');
                                                    $ficheSeuilTexte = 'Indice '.rtrim(rtrim(number_format($ficheIndice, 2, ',', ' '), '0'), ',');
                                                } else {
                                                    // Seul le salaire ANNUALISE est stocke (voir
                                                    // FraisDeplacementController::update(), commentaire
                                                    // "desannualise ... / 12") : le montant MENSUEL
                                                    // reellement saisi se retrouve en divisant par 12,
                                                    // pour l'afficher clairement plutot que le seul "SG"
                                                    // (peu parlant, et masquait un montant jamais saisi
                                                    // du tout : SG 0 F).
                                                    $ficheSg = (float) $membre['fiche_salaire_annuel'];
                                                    $ficheMontantMensuel = $ficheSg / 12;
                                                    $ficheMontantManquant = $ficheSg <= 0;
                                                    $ficheGroupe = $ficheSg >= 24294500 ? 'I' : ($ficheSg >= 2109610 ? 'II' : 'III');
                                                    $ficheSeuilTexte = $ficheMontantManquant
                                                        ? 'Montant mensuel non saisi'
                                                        : number_format($ficheMontantMensuel, 0, ',', ' ').' F/mois (SG '.number_format($ficheSg, 0, ',', ' ').' F)';
                                                }
                                                $ficheTauxBase = ['I' => 25000, 'II' => 20000, 'III' => 15000][$ficheGroupe];
                                                $ficheLieuAffectation = $membre['lieu_affectation'] ?? null;
                                                $ficheProvenance = $membre['provenance'] ?? null;
                                                $ficheMemeLieu = $ficheLieuAffectation && $ficheProvenance
                                                    && \Illuminate\Support\Str::lower(trim($ficheLieuAffectation)) === \Illuminate\Support\Str::lower(trim($ficheProvenance));
                                            }
                                        @endphp
                                        <td>
                                            <span class="seuil-detail" style="{{ $ficheMontantManquant ? 'color:#b45309;font-weight:700;' : '' }}">{{ $ficheDonneesDisponibles ? $ficheSeuilTexte : '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="seuil-detail">{{ $membre['lieu_affectation'] ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="seuil-detail">{{ $membre['provenance'] ?? '—' }}</span>
                                        </td>
                                        @if ($ficheDonneesDisponibles)
                                            <td>
                                                <div class="stack">
                                                    <div class="stack-item">
                                                        <span class="mini-badge accent">Groupe {{ $ficheGroupe }} · {{ number_format($ficheTauxBase, 0, ',', ' ') }} F/j</span>
                                                    </div>
                                                    <div class="stack-item">
                                                        <span class="mini-badge {{ $ficheMemeLieu ? 'warn' : 'info' }}">{{ $ficheMemeLieu ? '÷4 même lieu' : 'Taux plein' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        @else
                                            <td>
                                                <span class="seuil-detail">Détail indisponible — fiche créée avant la mise en place du barème.</span>
                                            </td>
                                        @endif
                                        <td class="montant-cell">
                                            <div class="montant-figure">{{ number_format($membre['fiche_montant'] ?? 0, 0, ',', ' ') }} F</div>
                                            <a class="table-action" href="{{ route('indemnites.frais-deplacement.show', $membre['fiche_deplacement_id']) }}">Voir la fiche</a>
                                        </td>
                                    @else
                                        <td>
                                            <input
                                                class="form-control row-input"
                                                type="number"
                                                step="0.01"
                                                name="{{ ($membre['categorie_personnel'] ?? '') === 'fonctionnaire' ? 'indice' : 'montant_mensuel' }}.{{ $membre['id'] }}"
                                                value="{{ ($membre['categorie_personnel'] ?? '') === 'fonctionnaire' ? ($membre['indice'] ?? '') : '' }}"
                                                data-field="valeur"
                                                data-id="{{ $membre['id'] }}"
                                                aria-label="{{ ($membre['categorie_personnel'] ?? '') === 'fonctionnaire' ? 'Indice' : 'Montant mensuel' }}"
                                            >
                                        </td>
                                        <td>
                                            <span class="seuil-detail">{{ $membre['lieu_affectation'] ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <input
                                                class="form-control row-input"
                                                type="text"
                                                name="provenance.{{ $membre['id'] }}"
                                                value="{{ $membre['provenance'] ?? '' }}"
                                                data-field="provenance"
                                                data-id="{{ $membre['id'] }}"
                                                data-lieu-affectation-valeur="{{ $membre['lieu_affectation'] ?? '' }}"
                                                placeholder="Ex : Dakar"
                                            >
                                        </td>
                                        <td>
                                            <div class="stack" data-detail="{{ $membre['id'] }}"></div>
                                        </td>
                                        <td class="montant-cell">
                                            <div class="montant-calc" data-calc="{{ $membre['id'] }}"></div>
                                            <div class="montant-figure" data-montant="{{ $membre['id'] }}"></div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (empty($membres))
                    <p class="empty-message show">Aucun membre au dossier complet pour cette convocation.</p>
                @endif

            </section>

            @if (! empty($membres))
                <div class="total-bar">
                    <span class="total-label">Total estimé pour les fiches sélectionnées</span>
                    <span class="total-figure" id="totalGeneral">— F</span>
                </div>

                <div class="actions-group calcul-groupe-actions">
                    <button class="btn-primary" type="submit" data-submit-groupe>
                        Créer les fiches sélectionnées
                    </button>
                </div>
            @endif

        </form>

    </section>

</main>

@push('styles')
<style>

    .convoc-summary-card {
        display: flex;
        flex-wrap: wrap;
        gap: 22px;
        align-items: flex-end;
        justify-content: space-between;
        padding: 18px 22px;
    }

    .convoc-summary-meta h2 { margin: 0 0 4px; font-size: 18px; }

    .convoc-summary-fields {
        display: flex;
        gap: 26px;
        flex-wrap: wrap;
    }

    .convoc-summary-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 150px;
    }

    .convoc-summary-field .label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .convoc-summary-field .value {
        font-size: 13.5px;
        font-weight: 600;
    }

    .text-danger { color: #b91c1c; font-weight: 600; }

    /* ---------- Bareme (repliable) ---------- */

    details.bareme-details {
        background: #ffffff;
        border: 1px solid var(--border-soft);
        border-radius: 10px;
        margin: 16px 0;
        overflow: hidden;
    }

    details.bareme-details summary {
        list-style: none;
        cursor: pointer;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        font-size: 13px;
        color: var(--text-muted);
    }

    details.bareme-details summary::-webkit-details-marker { display: none; }

    .bareme-preview { display: flex; gap: 6px; flex-wrap: wrap; }

    .bareme-chip {
        font-size: 11px;
        font-weight: 700;
        background: #f4f6f3;
        border: 1px solid var(--border-soft);
        border-radius: 999px;
        padding: 2px 8px;
        color: #334;
    }

    details.bareme-details summary .chev {
        margin-left: auto;
        font-size: 10px;
        color: var(--text-muted);
        transition: transform 0.15s ease;
    }

    details.bareme-details[open] summary .chev { transform: rotate(90deg); }

    .bareme-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        padding: 0 18px 6px;
    }

    @media (max-width: 700px) {
        .bareme-grid { grid-template-columns: minmax(0, 1fr); }
    }

    .bareme-row {
        border: 1px solid var(--border-soft);
        border-radius: 8px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 3px;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }

    .bareme-row.is-active {
        border-color: var(--primary);
        background: #f0f7f2;
    }

    .bareme-row-top {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }

    .bareme-detail { font-size: 11px; color: var(--text-muted); }

    .bareme-note { padding: 0 18px 14px; margin: 0; font-size: 12px; }

    /* ---------- Table ---------- */

    .membre-cell { display: flex; align-items: center; gap: 10px; }

    .membre-avatar {
        flex: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e3efe6;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
    }

    .membre-name { font-weight: 700; }
    .membre-sub { font-size: 11.5px; color: var(--text-muted); }

    .cat-badge {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 999px;
        background: #f4f6f3;
        color: #334;
        white-space: nowrap;
    }

    .cat-badge.cat-fonctionnaire { background: #e3efe6; color: var(--primary); }
    .cat-badge.cat-contractuel { background: #e8f0fe; color: var(--blue, #2563eb); }
    .cat-badge.cat-vacataire { background: #fbf0dc; color: #b45309; }

    .row-input { min-width: 110px; }

    .stack { display: flex; flex-direction: column; gap: 6px; min-width: 190px; }
    .stack-item { display: flex; flex-direction: column; gap: 2px; }

    .mini-badge {
        display: inline-flex;
        width: fit-content;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .mini-badge.accent { background: #e3efe6; color: var(--primary); }
    .mini-badge.warn { background: #fbf0dc; color: #b45309; }
    .mini-badge.info { background: #e8f0fe; color: var(--blue, #2563eb); }

    .seuil-detail { font-size: 11px; color: var(--text-muted); }
    .seuil-detail b { color: #334; font-weight: 700; }

    .montant-cell { text-align: right; min-width: 170px; }

    .montant-calc {
        font-size: 11.5px;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .montant-figure {
        font-weight: 800;
        font-size: 15px;
        color: var(--primary);
        margin-top: 2px;
    }

    /* ---------- Total + actions ---------- */

    .total-bar {
        margin-top: 14px;
        background: linear-gradient(135deg, #114f29, var(--primary));
        color: #fff;
        border-radius: 10px;
        padding: 14px 20px;
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .total-bar .total-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        opacity: 0.88;
    }

    .total-bar .total-figure {
        font-weight: 800;
        font-size: 22px;
    }

    .calcul-groupe-actions { margin-top: 14px; justify-content: flex-end; }

</style>
@endpush

@push('scripts')
<script>
(function () {
    "use strict";

    var TAUX = { I: 25000, II: 20000, III: 15000 };
    var SEUIL_INDICE_I = 2296;
    var SEUIL_INDICE_II = 1728;
    var SEUIL_SG_I = 24294500;
    var SEUIL_SG_II = 2109610;

    var dateDebut = "{{ $convocation['date_debut'] ?? '' }}";
    var dateFin = "{{ $convocation['date_fin'] ?? '' }}";
    var baremeRows = document.querySelectorAll("#baremeGrid .bareme-row");
    var joursAffiche = document.getElementById("joursAffiche");

    function formatFCFA(n) {
        return new Intl.NumberFormat("fr-FR").format(Math.round(n)) + " F";
    }

    function joursInclusifs(debut, fin) {
        var d1 = new Date(debut);
        var d2 = new Date(fin);
        if (isNaN(d1) || isNaN(d2) || d2 < d1) return 0;
        return Math.round((d2 - d1) / 86400000) + 1;
    }

    function groupeParIndice(indice) {
        if (indice >= SEUIL_INDICE_I) return "I";
        if (indice >= SEUIL_INDICE_II) return "II";
        return "III";
    }

    function groupeParSalaire(sg) {
        if (sg >= SEUIL_SG_I) return "I";
        if (sg >= SEUIL_SG_II) return "II";
        return "III";
    }

    var jours = joursInclusifs(dateDebut, dateFin);
    joursAffiche.textContent = jours > 0 ? (jours + (jours > 1 ? " jours" : " jour")) : "—";

    function recalculerLigne(row) {
        var id = row.querySelector("[data-field='valeur']");
        if (!id) return;

        var index = id.getAttribute("data-id");
        var estFonctionnaire = row.getAttribute("data-categorie") === "fonctionnaire";
        var valeur = parseFloat(id.value) || 0;
        var provenanceInput = row.querySelector("[data-field='provenance']");
        var provenanceMembre = provenanceInput ? provenanceInput.value.trim() : "";
        var lieuAffectation = provenanceInput ? (provenanceInput.getAttribute("data-lieu-affectation-valeur") || "") : "";

        var groupe, sgAffiche = null, seuilTexte;

        if (estFonctionnaire) {
            groupe = groupeParIndice(valeur);
            seuilTexte = "Indice <b>" + valeur + "</b>";
        } else {
            sgAffiche = valeur * 12;
            groupe = groupeParSalaire(sgAffiche);
            seuilTexte = "SG <b>" + formatFCFA(sgAffiche) + "</b> (" + valeur.toLocaleString("fr-FR") + " F/mois × 12)";
        }

        var tauxBase = TAUX[groupe];
        var memeLieu = provenanceMembre !== "" && lieuAffectation !== ""
            && provenanceMembre.toLowerCase() === lieuAffectation.toLowerCase();
        var tauxAjuste = memeLieu ? tauxBase / 4 : tauxBase;
        var montant = tauxAjuste * jours;

        var detail = document.querySelector('[data-detail="' + index + '"]');
        if (detail) {
            detail.innerHTML =
                '<div class="stack-item"><span class="mini-badge accent">Groupe ' + groupe + ' · ' + formatFCFA(tauxBase) + '/j</span><span class="seuil-detail">' + seuilTexte + '</span></div>' +
                '<div class="stack-item"><span class="mini-badge ' + (memeLieu ? "warn" : "info") + '">' + (memeLieu ? "÷4 même lieu" : "Taux plein") + '</span><span class="seuil-detail">' + (provenanceMembre || "—") + ' ' + (memeLieu ? "=" : "≠") + ' ' + (lieuAffectation || "—") + '</span></div>';
        }

        var calc = document.querySelector('[data-calc="' + index + '"]');
        if (calc) calc.textContent = jours > 0 ? (formatFCFA(tauxAjuste) + "/j × " + jours + " j") : "période non définie";

        var figure = document.querySelector('[data-montant="' + index + '"]');
        if (figure) figure.textContent = jours > 0 ? formatFCFA(montant) : "—";

        return { groupe: groupe, montant: jours > 0 ? montant : 0, checked: row.querySelector("[data-membre-checkbox]") };
    }

    function recalculerTout() {
        var total = 0;
        var groupesUtilises = {};

        document.querySelectorAll("[data-membre-row]").forEach(function (row) {
            if (!row.querySelector("[data-field='valeur']")) return;

            var resultat = recalculerLigne(row);
            groupesUtilises[resultat.groupe] = true;

            var caseACocher = row.querySelector("[data-membre-checkbox]");
            if (!caseACocher || caseACocher.checked) {
                total += resultat.montant;
            }
        });

        baremeRows.forEach(function (r) {
            r.classList.toggle("is-active", !!groupesUtilises[r.getAttribute("data-groupe")]);
        });

        var totalEl = document.getElementById("totalGeneral");
        if (totalEl) totalEl.textContent = formatFCFA(total);
    }

    document.querySelectorAll("[data-field='valeur'], [data-field='provenance']").forEach(function (input) {
        input.addEventListener("input", recalculerTout);
    });

    document.querySelectorAll("[data-membre-checkbox]").forEach(function (checkbox) {
        checkbox.addEventListener("change", recalculerTout);
    });

    recalculerTout();

    var formulaireGroupe = document.querySelector("[data-calcul-groupe-form]");
    var boutonSubmit = document.querySelector("[data-submit-groupe]");

    /*
      Sans ce garde-fou, une ligne cochee dont l'Indice/Montant mensuel est
      reste vide partait quand meme (valeur = 0 cote back), tombait donc
      toujours en Groupe III et affichait "SG 0 F"/"Indice 0" sans que
      personne ne s'en aperçoive avant d'aller voir la fiche creee.
    */
    function ligneSansMontant() {
        var manquantes = [];

        document.querySelectorAll("[data-membre-row]").forEach(function (row) {
            var caseACocher = row.querySelector("[data-membre-checkbox]");
            var valeurInput = row.querySelector("[data-field='valeur']");

            if (!caseACocher || !caseACocher.checked || !valeurInput) {
                return;
            }

            if (!valeurInput.value || parseFloat(valeurInput.value) <= 0) {
                var nomEl = row.querySelector(".membre-name");
                manquantes.push(nomEl ? nomEl.textContent.trim() : "un membre");
            }
        });

        return manquantes;
    }

    if (formulaireGroupe && boutonSubmit) {
        formulaireGroupe.addEventListener("submit", function (event) {
            var manquantes = ligneSansMontant();

            if (manquantes.length > 0) {
                event.preventDefault();
                window.alert(
                    "Indice / Montant mensuel manquant pour : " + manquantes.join(", ") +
                    ". Renseignez-le (ou décochez la ligne) avant de créer les fiches."
                );

                return;
            }

            boutonSubmit.disabled = true;
            boutonSubmit.setAttribute("aria-busy", "true");
            boutonSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Création en cours…';
        });
    }
})();
</script>
@endpush

@endsection
