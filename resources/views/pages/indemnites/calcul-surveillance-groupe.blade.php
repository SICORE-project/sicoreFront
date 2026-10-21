@extends('layouts.app')

@section('title', 'SICORE - Calcul groupé des indemnités de surveillance')

@section('content')

<main class="main-content">

    <x-topbar
        title="Indemnités de surveillance — calcul groupé"
        subtitle="Indemnites > Indemnité de surveillance > Calcul groupé"
        icon="fa-solid fa-user-shield"
    />

    <section class="content-area">

        <div class="actions-row">
            <p class="breadcrumb">
                <a href="{{ route('indemnites.calcul-surveillance') }}">&larr; Retour à la liste</a>
            </p>
        </div>

        <section class="form-card convoc-summary-card">
            <div class="convoc-summary-meta">
                <h2>{{ $convocation['objet'] ?? '—' }}</h2>
                <p class="section-description">
                    Session {{ $convocation['session'] ?? '—' }}
                    @if (! empty($centreNom))
                        &middot; Centre : {{ $centreNom }}
                    @endif
                </p>
            </div>
        </section>

        <div class="stats-grid three">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Surveillants (ce centre)</p>
                    <p class="stat-value" id="statSurveillants">—</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Heures renseignées</p>
                    <p class="stat-value" id="statHeures">—</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Montant total</p>
                    <p class="stat-value" id="statMontant">—</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        <form method="POST" action="{{ route('indemnites.calcul-surveillance.groupe.store') }}" data-calcul-groupe-form>
            @csrf
            <input type="hidden" name="convocation_id" value="{{ $convocationId }}">
            <input type="hidden" name="centre_id" value="{{ $centreId }}">

            @php $index = 0; @endphp

            @forelse ($groupesMetier as $groupe)

                <section class="table-card metier-group" data-metier-group>

                    <div class="metier-header">
                        <div class="metier-header-text">
                            <span class="metier-dot"></span>
                            <div>
                                <strong>{{ $groupe['metier'] ?? '—' }}</strong>
                                <div><span>{{ count($groupe['surveillants']) }} surveillant(s)</span></div>
                            </div>
                        </div>
                        <div class="metier-rate-input-group">
                            <label>Tarif</label>
                            <input class="form-control" type="number" step="1" min="0" value="1000" data-metier-tarif>
                            <span class="unit">F / heure</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                @foreach ($groupe['surveillants'] as $surveillant)
                                    @php $index++; @endphp
                                    <tr
                                        data-row
                                        data-fiche-montant="{{ ! empty($surveillant['indemnite_surveillance_id']) ? ($surveillant['montant'] ?? 0) : '' }}"
                                    >
                                        <td class="checkbox-cell">
                                            @if (empty($surveillant['indemnite_surveillance_id']))
                                                <input type="checkbox" checked data-row-checkbox name="lignes[{{ $index }}][checked]" value="1">
                                                <input type="hidden" name="lignes[{{ $index }}][enseignant_id]" value="{{ $surveillant['id'] }}">
                                                <input type="hidden" name="lignes[{{ $index }}][convocation_centre_id]" value="{{ $surveillant['centre_id'] }}">
                                                <input type="hidden" name="lignes[{{ $index }}][metier]" value="{{ $groupe['metier'] }}">
                                            @endif
                                        </td>
                                        <td style="width:38%;">
                                            <div class="membre-cell">
                                                <div class="membre-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($surveillant['prenom'] ?? '', 0, 1).\Illuminate\Support\Str::substr($surveillant['nom'] ?? '', 0, 1)) }}</div>
                                                <div>
                                                    <div class="membre-name">{{ $surveillant['prenom'] ?? '—' }} {{ $surveillant['nom'] ?? '' }}</div>
                                                    <div class="membre-sub">{{ $surveillant['matricule'] ?? '—' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        @if (! empty($surveillant['indemnite_surveillance_id']))
                                            <td style="text-align:right;">{{ number_format($surveillant['nombre_heures'] ?? 0, 1, ',', ' ') }} h</td>
                                            <td style="text-align:right;">{{ number_format($surveillant['tarif_horaire'] ?? 0, 0, ',', ' ') }} F/h</td>
                                            <td class="montant-cell">
                                                <div class="montant-figure">{{ number_format($surveillant['montant'] ?? 0, 0, ',', ' ') }} F</div>
                                            </td>
                                        @else
                                            <td style="text-align:right;">
                                                <input class="form-control row-input" type="number" step="0.5" min="0" name="lignes[{{ $index }}][nombre_heures]" data-field="heures" aria-label="Heures surveillées">
                                                <span class="unit-hint">h</span>
                                            </td>
                                            <td style="text-align:right;">
                                                <input class="form-control row-input rate-row-input" type="number" step="1" min="0" value="1000" name="lignes[{{ $index }}][tarif_horaire]" data-field="tarif" aria-label="Tarif horaire">
                                                <span class="unit-hint">F/h</span>
                                            </td>
                                            <td class="montant-cell">
                                                <div class="montant-figure" data-montant></div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="metier-subtotal">
                        Sous-total {{ $groupe['metier'] ?? '' }} : <strong data-metier-subtotal>—</strong>
                    </div>

                </section>

            @empty

                <section class="table-card">
                    <p class="empty-message show">Aucun surveillant pour ce centre.</p>
                </section>

            @endforelse

            @if ($groupesMetier->isNotEmpty())

                <div class="total-bar">
                    <span class="total-label">Total estimé</span>
                    <span class="total-figure" id="totalGeneral">— F</span>
                </div>

                <div class="actions-group calcul-groupe-actions">
                    <button class="btn-primary" type="submit" data-submit-groupe>
                        Enregistrer les indemnités de surveillance
                    </button>
                </div>

            @endif

        </form>

    </section>

</main>

@push('styles')
<style>

    .convoc-summary-card {
        max-width: none;
        width: 100%;
        margin: 0 0 18px;
        display: flex;
        flex-direction: column;
        gap: 26px;
        padding: 30px 28px;
    }

    .stats-grid.three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-bottom: 18px;
    }

    .stats-grid.three .stat-value { color: #000; }

    .convoc-summary-meta h2 { margin: 0 0 8px; font-size: 21px; }
    .convoc-summary-meta .section-description { font-size: 14.5px; }

    .metier-group { margin-bottom: 18px; overflow: hidden; }

    .metier-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 18px 22px;
        background: #f7faf7;
        border-bottom: 1px solid var(--border-soft);
    }

    .metier-header-text { display: flex; align-items: center; gap: 10px; }

    .metier-header-text .metier-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--primary);
    }

    .metier-header-text strong { font-size: 18px; color: #000; }
    .metier-header-text span { font-size: 14px; color: #000; }

    .metier-rate-input-group { display: flex; align-items: center; gap: 8px; }

    .metier-rate-input-group label {
        font-size: 14.5px;
        font-weight: 700;
        color: #000;
        white-space: nowrap;
    }

    .metier-rate-input-group input { width: 120px; text-align: right; font-weight: 700; font-size: 17px; padding: 10px 12px; color: #000; }
    .metier-rate-input-group .unit { font-size: 14.5px; color: #000; }

    .metier-group table { margin: 0; }
    .metier-group table td { font-size: 16px; }

    .checkbox-cell { width: 36px; }
    .checkbox-cell input[type="checkbox"] { width: 18px; height: 18px; }

    .membre-cell { display: flex; align-items: center; gap: 12px; }

    .membre-avatar {
        flex: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e3efe6;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .membre-name { font-weight: 700; font-size: 16.5px; color: #000; }
    .membre-sub { font-size: 14px; color: #000; }

    .row-input { width: 110px; text-align: right; font-size: 16px; padding: 10px 12px; color: #000; }
    .row-input.rate-row-input { width: 120px; }
    .unit-hint { font-size: 14px; color: #000; margin-left: 6px; }

    .montant-cell { text-align: right; min-width: 140px; }
    .montant-figure { font-weight: 800; font-size: 18px; color: var(--primary); }

    .empty-hint {
        font-size: 14px;
        color: #b45309;
        font-weight: 700;
        background: #fbf0dc;
        padding: 4px 12px;
        border-radius: 999px;
        display: inline-block;
    }

    tr.is-empty-row td { opacity: 0.65; }

    .metier-subtotal {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 12px 22px;
        font-size: 15px;
        color: #000;
        background: #fafbfc;
    }

    .metier-subtotal strong { color: #000; font-size: 15.5px; }

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
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        opacity: 0.88;
    }

    .total-bar .total-figure { font-weight: 800; font-size: 26px; }

    .calcul-groupe-actions { margin-top: 14px; justify-content: flex-end; }

    @media (max-width: 900px) {
        .stats-grid.three { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 720px) {
        .stats-grid.three { grid-template-columns: minmax(0, 1fr); }
        .metier-header { flex-direction: column; align-items: flex-start; }
    }

</style>
@endpush

@push('scripts')
<script>
(function () {
    "use strict";

    function formatFCFA(n) {
        return new Intl.NumberFormat("fr-FR").format(Math.round(n)) + " F";
    }

    function recalculerLigne(row) {
        var heuresInput = row.querySelector("[data-field='heures']");
        var tarifInput = row.querySelector("[data-field='tarif']");
        if (!heuresInput || !tarifInput) return null;

        var heures = parseFloat(heuresInput.value) || 0;
        var tarif = parseFloat(tarifInput.value) || 0;
        var montant = heures * tarif;
        var figure = row.querySelector("[data-montant]");
        var cell = row.querySelector(".montant-cell");
        var hint = cell ? cell.querySelector(".empty-hint") : null;

        if (!heuresInput.value) {
            row.classList.add("is-empty-row");
            if (figure) figure.style.display = "none";
            if (!hint && cell) {
                hint = document.createElement("span");
                hint.className = "empty-hint";
                hint.textContent = "Heures non saisies";
                cell.appendChild(hint);
            }
            return { montant: 0, checked: row.querySelector("[data-row-checkbox]") };
        }

        row.classList.remove("is-empty-row");
        if (hint) hint.remove();
        if (figure) { figure.style.display = ""; figure.textContent = formatFCFA(montant); }

        return { montant: montant, checked: row.querySelector("[data-row-checkbox]") };
    }

    function recalculerTout() {
        var total = 0;
        var heuresTotal = 0;
        var surveillants = 0;

        document.querySelectorAll("[data-metier-group]").forEach(function (group) {
            var subtotal = 0;

            group.querySelectorAll("[data-row]").forEach(function (row) {
                surveillants++;

                if (!row.querySelector("[data-field='heures']")) {
                    var fiche = row.getAttribute("data-fiche-montant");
                    var ficheMontant = fiche ? (parseFloat(fiche) || 0) : 0;
                    total += ficheMontant;
                    subtotal += ficheMontant;
                    var heuresCell = row.children[2];
                    if (heuresCell) heuresTotal += parseFloat(heuresCell.textContent) || 0;
                    return;
                }

                var resultat = recalculerLigne(row);
                if (!resultat) return;

                var heuresInput = row.querySelector("[data-field='heures']");
                heuresTotal += parseFloat(heuresInput.value) || 0;

                if (!resultat.checked || resultat.checked.checked) {
                    total += resultat.montant;
                    subtotal += resultat.montant;
                }
            });

            var subtotalEl = group.querySelector("[data-metier-subtotal]");
            if (subtotalEl) subtotalEl.textContent = formatFCFA(subtotal);
        });

        var statSurveillants = document.getElementById("statSurveillants");
        var statHeures = document.getElementById("statHeures");
        var statMontant = document.getElementById("statMontant");
        var totalGeneral = document.getElementById("totalGeneral");

        if (statSurveillants) statSurveillants.textContent = surveillants;
        if (statHeures) statHeures.textContent = heuresTotal;
        if (statMontant) statMontant.textContent = formatFCFA(total);
        if (totalGeneral) totalGeneral.textContent = formatFCFA(total);
    }

    document.querySelectorAll("[data-field='heures'], [data-field='tarif']").forEach(function (input) {
        input.addEventListener("input", recalculerTout);
    });

    document.querySelectorAll("[data-row-checkbox]").forEach(function (checkbox) {
        checkbox.addEventListener("change", recalculerTout);
    });

    document.querySelectorAll("[data-metier-tarif]").forEach(function (tarifInput) {
        tarifInput.addEventListener("input", function () {
            var group = tarifInput.closest("[data-metier-group]");
            group.querySelectorAll("[data-row] [data-field='tarif']").forEach(function (input) {
                input.value = tarifInput.value;
            });
            recalculerTout();
        });
    });

    recalculerTout();

    var formulaireGroupe = document.querySelector("[data-calcul-groupe-form]");
    var boutonSubmit = document.querySelector("[data-submit-groupe]");

    function ligneSansHeures() {
        var manquantes = [];

        document.querySelectorAll("[data-row]").forEach(function (row) {
            var caseACocher = row.querySelector("[data-row-checkbox]");
            var heuresInput = row.querySelector("[data-field='heures']");

            if (!caseACocher || !caseACocher.checked || !heuresInput) {
                return;
            }

            if (!heuresInput.value || parseFloat(heuresInput.value) <= 0) {
                var nomEl = row.querySelector(".membre-name");
                manquantes.push(nomEl ? nomEl.textContent.trim() : "un surveillant");
            }
        });

        return manquantes;
    }

    if (formulaireGroupe && boutonSubmit) {
        formulaireGroupe.addEventListener("submit", function (event) {
            var manquantes = ligneSansHeures();

            if (manquantes.length > 0) {
                event.preventDefault();
                window.alert(
                    "Nombre d'heures surveillées manquant pour : " + manquantes.join(", ") +
                    ". Renseignez-le (ou décochez la ligne) avant d'enregistrer."
                );

                return;
            }

            boutonSubmit.disabled = true;
            boutonSubmit.setAttribute("aria-busy", "true");
            boutonSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enregistrement en cours…';
        });
    }
})();
</script>
@endpush

@endsection
