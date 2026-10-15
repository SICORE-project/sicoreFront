@extends('layouts.app')

@section('title', 'SICORE - États de paie')

@section('content')

<main class="main-content">

    <x-topbar
        title="États de paie"
        subtitle="Indemnites > États de paie"
        icon="fa-solid fa-file-invoice-dollar"
    />

    <section class="content-area">

        {{-- ============================================================
             STATISTIQUES
             A 0 pour l'instant : la generation des fiches etat de paie
             (back EtatPaieIndemnitesController) n'est pas encore implementee
             ("on continue" apres cette etape) — ces cartes seront reliees a
             de vrais compteurs des que la liste/generation existera.
        ============================================================ --}}

        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Fiches générées</p>
                    <p class="stat-value">{{ $stats['generees'] ?? 0 }}</p>
                    <p class="stat-note">Toutes périodes confondues</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Validées</p>
                    <p class="stat-value">{{ $stats['validees'] ?? 0 }}</p>
                    <p class="stat-note">Prêtes pour transmission</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">En attente</p>
                    <p class="stat-value">{{ $stats['en_attente'] ?? 0 }}</p>
                    <p class="stat-note">Brouillon ou générée</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Transmises</p>
                    <p class="stat-value">{{ $stats['transmises'] ?? 0 }}</p>
                    <p class="stat-note">Envoyées à la solde</p>
                </div>
                <span class="stat-icon purple">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        {{-- ============================================================
             FILTRES — type d'indemnite + objet + session, meme presentation
             que les autres pages du module (filter-panel). Choisir l'objet
             et/ou la session declenche la recherche des centres concernes,
             affiches ici sous forme de menu deroulant (demande utilisatrice).
        ============================================================ --}}

        <form
            id="etatsPaieFilterForm"
            class="filter-panel"
            aria-label="Filtres de la page"
            data-etat-paie-form
        >

            <div class="form-group">
                <label for="etat-paie-type">Type d'indemnité</label>
                <select class="form-control" id="etat-paie-type" name="type">
                    @foreach ($typesIndemnite as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected(($filtres['type'] ?? null) === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="etat-paie-objet">Objet</label>
                <select class="form-control" id="etat-paie-objet" name="objet" data-etat-paie-filtre>
                    <option value="">Tous les objets</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(($filtres['objet'] ?? null) === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="etat-paie-session">Session</label>
                <select class="form-control" id="etat-paie-session" name="session" data-etat-paie-filtre>
                    <option value="">Toutes les sessions</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(($filtres['session'] ?? null) === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="etat-paie-centre">Centre</label>
                <details class="multi-select" id="etat-paie-centre" data-etat-paie-centre>
                    <summary class="form-control" data-etat-paie-centre-resume>Chargement des centres…</summary>
                    <div class="multi-select__panel" data-etat-paie-centre-liste></div>
                </details>
            </div>

            <div class="form-group etat-paie-generer-group">
                <label>&nbsp;</label>
                <a class="etat-paie-btn-vert" href="#" target="_blank" rel="noopener" data-etat-paie-generer-pdf hidden>
                    <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                    Générer la fiche
                </a>
            </div>

        </form>

        {{-- ============================================================
             MEMBRES ASSOCIES — rempli en AJAX des que le type d'indemnite
             ET (l'objet ou la session) sont choisis (demande utilisatrice :
             "pour chaque type d'indemnite choisi on affiche la liste des
             membres associe avec le nom prenom categorie fonction et le
             montant"). Se rafraichit aussi si le centre est precise.
        ============================================================ --}}

        <section class="table-card">

            @if (\App\Support\SicoreAccess::can(request(), 'indemnites.manage'))
                <div class="etat-paie-groupe" data-etat-paie-groupe hidden>
                    <p class="etat-paie-groupe__resume" data-etat-paie-groupe-resume></p>

                    <form method="POST" action="{{ route('indemnites.etats-paie.generer') }}" data-etat-paie-groupe-form>
                        @csrf
                        <input type="hidden" name="type" data-etat-paie-groupe-champ="type">
                        <input type="hidden" name="objet" data-etat-paie-groupe-champ="objet">
                        <input type="hidden" name="session" data-etat-paie-groupe-champ="session">
                        <span data-etat-paie-groupe-centres></span>

                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i>
                            Générer l'état de paie
                        </button>
                    </form>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table" id="etatsPaieMembresTable">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Catégorie</th>
                            <th>Fonction</th>
                            <th>Centre</th>
                            <th style="text-align:right;">Montant</th>
                            <th class="actions-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody data-etat-paie-membres-body></tbody>
                </table>
            </div>

            <p class="empty-message show" data-etat-paie-membres-hint>
                Chargement des membres…
            </p>

            <div class="convocation-pagination" aria-label="Pagination" data-etat-paie-pagination hidden></div>

        </section>

        {{-- ============================================================
             ÉTATS DE PAIE GÉNÉRÉS — liste des états enregistrés (10 par page),
             consultation de l'état complet, PDF et suppression d'un brouillon.
             Suit les filtres type / objet / session de la page.
        ============================================================ --}}

        <section class="table-card" data-etat-paie-liste-section>

            <div class="etat-paie-liste-titre">
                <h3>États de paie générés</h3>
                <span data-etat-paie-liste-compteur></span>
            </div>

            <div class="table-responsive">
                <table class="table" id="etatsPaieListeTable">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Type</th>
                            <th>Session / objet</th>
                            <th>Période</th>
                            <th>Centre(s)</th>
                            <th style="text-align:right;">Bénéficiaires</th>
                            <th style="text-align:right;">Total</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody data-etat-paie-liste-body></tbody>
                </table>
            </div>

            <p class="empty-message show" data-etat-paie-liste-hint>Chargement des états de paie…</p>

            <div class="convocation-pagination" aria-label="Pagination des états" data-etat-paie-liste-pagination hidden></div>

        </section>

        <form method="POST" data-etat-paie-statut-form hidden>
            @csrf
            <input type="hidden" name="type">
            <input type="hidden" name="objet">
            <input type="hidden" name="session">
        </form>

        <form method="POST" data-etat-paie-supprimer-form hidden>
            @csrf
            @method('DELETE')
            <input type="hidden" name="type">
            <input type="hidden" name="objet">
            <input type="hidden" name="session">
        </form>

    </section>

</main>

{{-- ============================================================
     MODALE "Voir état" — etat(s) de paie deja crees pour le filtre courant
     (demande utilisatrice : "ajouter un bouton voir etat qui affiche les
     informations en modal").
============================================================ --}}

<x-module-indemnite type="modal" id="etatPaieVoirModal" title="État(s) de paie existants">

    <div data-etat-paie-voir-body></div>

</x-module-indemnite>

<x-module-indemnite type="modal" id="etatPaieConsulterModal" title="État de paie">

    <div data-etat-paie-consulter-body></div>

</x-module-indemnite>

@push('styles')
<style>

    .etat-paie-liste-titre {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px 8px;
    }
    .etat-paie-liste-titre h3 { margin: 0; font-size: 16px; }
    .etat-paie-liste-titre span { color: var(--text-muted); font-size: 13px; }

    #etatPaieConsulterModal .modal-dialog { max-width: 1000px; }
    .etat-paie-detail-infos { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px 20px; margin: 0 0 16px; }
    .etat-paie-detail-infos dt { font-size: 12px; color: var(--text-muted); }
    .etat-paie-detail-infos dd { margin: 2px 0 0; font-weight: 600; }
    .etat-paie-detail-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }

    .actions-cell { width: 90px; text-align: right; }

    .montant-defaut { color: #b45309; font-weight: 700; }

    /* Liste de centres à cocher (plusieurs centres possibles). */
    .multi-select { position: relative; }
    .multi-select > summary { cursor: pointer; list-style: none; }
    .multi-select > summary::-webkit-details-marker { display: none; }
    .multi-select[data-desactive] { pointer-events: none; opacity: 0.6; }
    .multi-select__panel {
        position: absolute;
        z-index: 20;
        top: calc(100% + 4px);
        left: 0;
        min-width: 100%;
        max-height: 260px;
        overflow-y: auto;
        padding: 8px 10px;
        background: #ffffff;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }
    .multi-select__option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 4px;
        font-size: 14px;
        cursor: pointer;
        white-space: nowrap;
    }

    .convocation-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 14px 18px;
        border-top: 1px solid var(--border-soft);
    }

    .etat-paie-groupe {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        background: #ecfdf5;
        border-bottom: 1px solid var(--border-soft);
    }
    .etat-paie-groupe__resume { margin: 0; font-weight: 600; }

    .etat-paie-btn-vert {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        border-radius: var(--radius-btn);
        padding: 11px 22px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        background: var(--primary);
        color: #ffffff;
        text-decoration: none;
        white-space: nowrap;
    }

    .etat-paie-btn-vert:hover { background: var(--primary-dark); color: #ffffff; }

    .etat-paie-generer-group label { visibility: hidden; }

    /* Bouton « Voir état » d'une ligne du tableau — un vrai bouton avec fond,
       pas juste un lien texte. */
    .etat-paie-btn-ligne {
        border: none;
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        background: var(--primary);
        color: #ffffff;
        white-space: nowrap;
    }

    .etat-paie-btn-ligne:hover { background: var(--primary-dark); }

    .etat-paie-btn-ligne-voir { background: var(--blue); }
    .etat-paie-btn-ligne-voir:hover { background: #1d4ed8; }

    /* ---------- Modale "Voir etat" ---------- */

    #etatPaieVoirModal .modal-dialog { max-width: 860px; }

    .etat-paie-voir-carte {
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .etat-paie-voir-carte:last-child { margin-bottom: 0; }

    .etat-paie-voir-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px;
        background: linear-gradient(135deg, #f0f7f2, #f7faf7);
        border-bottom: 1px solid var(--border-soft);
    }

    .etat-paie-voir-reference-row { display: flex; align-items: center; gap: 8px; }
    .etat-paie-voir-reference { margin: 0; font-size: 15px; font-weight: 800; letter-spacing: 0.01em; }
    .etat-paie-voir-periode { margin: 4px 0 0; font-size: 12px; color: var(--text-muted); }

    .etat-paie-voir-montant-wrap { text-align: right; }
    .etat-paie-voir-montant-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 2px;
    }
    .etat-paie-voir-montant { font-size: 19px; font-weight: 800; color: var(--primary); }

    .etat-paie-voir-statut {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 2px 9px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .etat-paie-voir-statut-brouillon { background: var(--yellow-bg, #fbf0dc); color: #b45309; }
    .etat-paie-voir-statut-genere { background: #e8f0fe; color: var(--blue); }
    .etat-paie-voir-statut-valide { background: #e3efe6; color: var(--primary); }
    .etat-paie-voir-statut-archive { background: #f1f2f4; color: #4b5563; }
    .etat-paie-voir-statut-transmis { background: #f2e9fb; color: #7c3aed; }

    .etat-paie-voir-table-wrap { overflow-x: auto; }

    .etat-paie-voir-table { width: 100%; border-collapse: collapse; white-space: nowrap; }

    .etat-paie-voir-table thead th {
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 10px 14px;
        background: #fafbfc;
        border-bottom: 1px solid var(--border-soft);
    }

    .etat-paie-voir-table tbody td {
        font-size: 13px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--border-soft);
        color: var(--text-main);
    }

    .etat-paie-voir-table tbody tr:last-child td { border-bottom: none; }
    .etat-paie-voir-table tbody tr:hover td { background: #f7faf7; }

    .etat-paie-voir-membre { display: flex; align-items: center; gap: 9px; }

    .etat-paie-voir-avatar {
        flex: none;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #e3efe6;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 10.5px;
    }

    .etat-paie-voir-table .cellule-code {
        font-family: "SFMono-Regular", Consolas, Menlo, monospace;
        font-size: 12px;
        color: var(--text-muted);
    }

    .etat-paie-voir-table .cellule-code.vide { color: #b8c0cc; }

    .etat-paie-voir-table .cellule-montant {
        text-align: right;
        font-weight: 700;
        color: var(--primary);
    }

</style>
@endpush

@push('scripts')
<script>
(function () {
    "use strict";

    var typeSelect = document.getElementById("etat-paie-type");
    var objetSelect = document.getElementById("etat-paie-objet");
    var sessionSelect = document.getElementById("etat-paie-session");
    var centreSelect = document.querySelector("[data-etat-paie-centre]");
    var centreResume = centreSelect ? centreSelect.querySelector("[data-etat-paie-centre-resume]") : null;
    var centreListe = centreSelect ? centreSelect.querySelector("[data-etat-paie-centre-liste]") : null;
    var placeholderCentres = "Chargement des centres…";
    var groupeBloc = document.querySelector("[data-etat-paie-groupe]");
    var groupeForm = document.querySelector("[data-etat-paie-groupe-form]");
    var groupeResume = document.querySelector("[data-etat-paie-groupe-resume]");
    var groupeCentres = document.querySelector("[data-etat-paie-groupe-centres]");
    var centresUrl = "{{ route('indemnites.etats-paie.centres') }}";
    var membresUrl = "{{ route('indemnites.etats-paie.membres') }}";
    var membresBody = document.querySelector("[data-etat-paie-membres-body]");
    var membresHint = document.querySelector("[data-etat-paie-membres-hint]");
    var existantsUrl = "{{ route('indemnites.etats-paie.existants') }}";
    var voirModal = document.getElementById("etatPaieVoirModal");
    var voirBody = document.querySelector("[data-etat-paie-voir-body]");
    var etatsExistants = [];
    var etatsExistantsCharges = false;
    var membresActuels = [];
    var pageMembres = 1;
    var MEMBRES_PAR_PAGE = 10;
    var paginationMembres = document.querySelector("[data-etat-paie-pagination]");
    var genererPdfUrl = "{{ route('indemnites.etats-paie.fiche-pdf') }}";
    var genererPdfLien = document.querySelector("[data-etat-paie-generer-pdf]");

    if (!typeSelect || !objetSelect || !sessionSelect || !centreSelect || !membresBody) {
        return;
    }

    // Centres cochés ([] = tous les centres).
    function valeursCentres() {
        if (!centreListe) return [];
        return Array.prototype.slice.call(centreListe.querySelectorAll("input[data-centre-option]:checked"))
            .map(function (c) { return c.value; });
    }

    function ajouterCentresAuxParametres(params) {
        valeursCentres().forEach(function (centre) { params.append("centre[]", centre); });
    }

    function resumeCentres() {
        var choisis = valeursCentres();
        var aDesOptions = centreListe && centreListe.children.length > 0;

        if (!aDesOptions) {
            centreResume.textContent = placeholderCentres;
        } else if (choisis.length === 0) {
            centreResume.textContent = "Tous les centres";
        } else if (choisis.length === 1) {
            centreResume.textContent = choisis[0];
        } else {
            centreResume.textContent = choisis.length + " centres sélectionnés";
        }
    }

    function creerOptionCentre(libelle, valeur, estTous) {
        var label = document.createElement("label");
        label.className = "multi-select__option";

        var champ = document.createElement("input");
        champ.type = "checkbox";
        champ.value = valeur;
        champ.setAttribute(estTous ? "data-centre-tous" : "data-centre-option", "");
        if (estTous) champ.checked = true;

        label.appendChild(champ);
        label.appendChild(document.createTextNode(libelle));
        return label;
    }

    // « Tous les centres » est coché tant qu'aucun centre précis ne l'est.
    if (centreListe) {
        centreListe.addEventListener("change", function (event) {
            var tous = centreListe.querySelector("input[data-centre-tous]");
            var cible = event.target;

            if (!tous) return;

            if (cible === tous) {
                centreListe.querySelectorAll("input[data-centre-option]").forEach(function (c) { c.checked = false; });
                tous.checked = true;
            } else {
                tous.checked = valeursCentres().length === 0;
            }

            resumeCentres();
        });

        // La liste se referme quand on clique ailleurs.
        document.addEventListener("click", function (event) {
            if (centreSelect.open && !centreSelect.contains(event.target)) {
                centreSelect.open = false;
            }
        });
    }

    function formatFCFA(n) {
        return new Intl.NumberFormat("fr-FR").format(Math.round(n)) + " F";
    }

    function afficherMembresHint(texte) {
        membresBody.innerHTML = "";
        membresHint.hidden = false;
        membresHint.textContent = texte;
    }

    function echapper(texte) {
        var div = document.createElement("div");
        div.textContent = texte == null ? "" : texte;
        return div.innerHTML;
    }

    // Matche par id d'enseignant si les deux le portent, sinon par
    // nom+prenom (repli pour les etats crees avant l'ajout de 'details[].id'
    // — sans ce repli, "Voir état" ne pouvait jamais apparaitre pour les
    // etats deja existants au moment de ce changement). Partagee entre
    // etatsDuMembre() (quel(s) etat(s) contiennent ce membre) et
    // afficherEtatsExistants() (quelle(s) ligne(s) de CET etat lui
    // appartiennent — un etat groupe plusieurs membres, "Voir état" ne
    // doit montrer que celui sur lequel on a cliqué).
    function detailCorrespondAuMembre(detail, membreId, nom, prenom) {
        if (detail.id != null && membreId) {
            return String(detail.id) === String(membreId);
        }

        var nomNormalise = (nom || "").trim().toLowerCase();
        var prenomNormalise = (prenom || "").trim().toLowerCase();

        if (!nomNormalise || !prenomNormalise) {
            return false;
        }

        return (detail.nom || "").trim().toLowerCase() === nomNormalise
            && (detail.prenom || "").trim().toLowerCase() === prenomNormalise;
    }

    // Etat(s) deja crees contenant ce membre (matche par id d'enseignant,
    // enregistre dans 'details[].id' depuis cette mise a jour — les etats
    // crees avant n'ont pas cette cle et ne matcheront donc jamais, ce qui
    // est correct : on ne peut pas les rattacher a coup sur a un membre).
    function etatsDuMembre(membreId, nom, prenom) {
        return etatsExistants.filter(function (etat) {
            return (etat.details || []).some(function (d) {
                return detailCorrespondAuMembre(d, membreId, nom, prenom);
            });
        });
    }

    // Tableau paginé par 10 lignes ; le résumé et le bouton « Générer » portent sur tous les membres.
    function afficherMembres(membres, conserverPage) {
        membresActuels = membres;
        membresBody.innerHTML = "";
        mettreAJourGroupe(membres);

        var nombrePages = Math.max(1, Math.ceil(membres.length / MEMBRES_PAR_PAGE));
        pageMembres = conserverPage ? Math.min(pageMembres, nombrePages) : 1;

        if (!membres.length) {
            afficherMembresHint("Aucun membre trouvé pour ce filtre.");
            if (paginationMembres) paginationMembres.hidden = true;
            return;
        }

        membresHint.hidden = true;

        var debut = (pageMembres - 1) * MEMBRES_PAR_PAGE;

        membres.slice(debut, debut + MEMBRES_PAR_PAGE).forEach(function (membre) {
            var tr = document.createElement("tr");
            tr.setAttribute("data-etat-paie-membre-row", "");
            tr.dataset.id = membre.id || "";
            tr.dataset.nom = membre.nom || "";
            tr.dataset.prenom = membre.prenom || "";
            tr.dataset.categoriePersonnel = membre.categorie_personnel || "";
            tr.dataset.fonction = membre.fonction || "";
            tr.dataset.centre = membre.centre || "";
            tr.dataset.montant = membre.montant || 0;

            var montantHtml = membre.montant_par_defaut
                ? "<span class=\"montant-defaut\" title=\"Montant par défaut pour ce rôle\">" + formatFCFA(membre.montant || 0) + "</span>"
                : formatFCFA(membre.montant || 0);

            // Deja dans un etat de paie (pour ce filtre) : "Voir état" ; sinon "Pas encore
            // payé". Tant que les etats existants ne sont pas charges, on affiche
            // "Pas encore payé" (re-rendu automatique des que chargerEtatsExistants()
            // termine, voir plus bas).
            var actionHtml = etatsDuMembre(membre.id, membre.nom, membre.prenom).length
                ? "<button type=\"button\" class=\"etat-paie-btn-ligne etat-paie-btn-ligne-voir\" data-etat-paie-membre-voir>Voir état</button>"
                : "<span class=\"badge badge-pending\">Pas encore payé</span>";

            tr.innerHTML =
                "<td>" + echapper(membre.nom) + "</td>" +
                "<td>" + echapper(membre.prenom) + "</td>" +
                "<td>" + (membre.categorie_personnel ? echapper(membre.categorie_personnel.charAt(0).toUpperCase() + membre.categorie_personnel.slice(1)) : "—") + "</td>" +
                "<td>" + echapper(membre.fonction || "—") + "</td>" +
                "<td>" + echapper(membre.centre || "—") + "</td>" +
                "<td style=\"text-align:right;\">" + montantHtml + "</td>" +
                "<td class=\"actions-cell\">" + actionHtml + "</td>";
            membresBody.appendChild(tr);
        });

        afficherPagination(nombrePages);
    }

    function afficherPagination(nombrePages) {
        if (!paginationMembres) return;

        paginationMembres.innerHTML = "";
        paginationMembres.hidden = nombrePages <= 1;

        if (nombrePages <= 1) return;

        function bouton(libelle, page, actif, desactive) {
            var b = document.createElement("button");
            b.type = "button";
            b.className = "page-btn" + (actif ? " active" : "");
            b.textContent = libelle;
            b.disabled = Boolean(desactive);
            b.setAttribute("data-etat-paie-page", String(page));
            paginationMembres.appendChild(b);
        }

        bouton("←", pageMembres - 1, false, pageMembres === 1);
        for (var page = 1; page <= nombrePages; page++) {
            bouton(String(page), page, page === pageMembres, false);
        }
        bouton("→", pageMembres + 1, false, pageMembres === nombrePages);
    }

    if (paginationMembres) {
        paginationMembres.addEventListener("click", function (evenement) {
            var cible = evenement.target.closest("[data-etat-paie-page]");
            if (!cible || cible.disabled) return;

            pageMembres = parseInt(cible.getAttribute("data-etat-paie-page"), 10) || 1;
            afficherMembres(membresActuels, true);
        });
    }

    // Résumé et champs du bouton « Générer l'état de paie » (un seul état pour tout le tableau).
    function mettreAJourGroupe(membres) {
        if (!groupeBloc || !groupeForm) return;

        var avecMontant = membres.filter(function (m) { return (Number(m.montant) || 0) > 0; });
        var total = avecMontant.reduce(function (somme, m) { return somme + (Number(m.montant) || 0); }, 0);

        var perimetreChoisi = Boolean(objetSelect.value || sessionSelect.value);
        var boutonGenerer = groupeForm.querySelector("button[type='submit']");

        groupeBloc.hidden = membres.length === 0;
        groupeResume.textContent = membres.length + " bénéficiaire(s) listé(s) — "
            + avecMontant.length + " avec un montant à payer, total " + formatFCFA(total)
            + (perimetreChoisi ? "" : " — choisissez une session ou un objet pour générer l'état de paie");

        if (boutonGenerer) {
            boutonGenerer.disabled = !perimetreChoisi;
            boutonGenerer.title = perimetreChoisi ? "" : "Choisissez une session ou un objet pour générer l'état de paie";
        }

        groupeForm.querySelector("[data-etat-paie-groupe-champ='type']").value = typeSelect.value;
        groupeForm.querySelector("[data-etat-paie-groupe-champ='objet']").value = objetSelect.value;
        groupeForm.querySelector("[data-etat-paie-groupe-champ='session']").value = sessionSelect.value;

        groupeCentres.innerHTML = "";
        valeursCentres().forEach(function (centre) {
            var champ = document.createElement("input");
            champ.type = "hidden";
            champ.name = "centre[]";
            champ.value = centre;
            groupeCentres.appendChild(champ);
        });
    }

    if (groupeForm) {
        groupeForm.addEventListener("submit", function (evenement) {
            var avecMontant = membresActuels.filter(function (m) { return (Number(m.montant) || 0) > 0; });
            var total = avecMontant.reduce(function (somme, m) { return somme + (Number(m.montant) || 0); }, 0);

            if (!window.confirm("Générer un état de paie pour " + avecMontant.length + " bénéficiaire(s) (total "
                + formatFCFA(total) + ") ?")) {
                evenement.preventDefault();
            }
        });
    }

    function chargerMembres() {
        var type = typeSelect.value;
        var objet = objetSelect.value;
        var session = sessionSelect.value;

        if (!type) {
            afficherMembresHint("Choisissez un type d'indemnité pour afficher les membres associés.");
            return;
        }

        afficherMembresHint("Recherche des membres…");

        var params = new URLSearchParams();
        params.set("type", type);
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);
        ajouterCentresAuxParametres(params);

        fetch(membresUrl + "?" + params.toString(), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then(function (reponse) { return reponse.json(); })
            .then(function (payload) {
                if (!payload.success) {
                    afficherMembresHint(payload.message || "Impossible de charger les membres.");
                    return;
                }

                afficherMembres(payload.data.membres || []);
            })
            .catch(function () {
                afficherMembresHint("Impossible de charger les membres.");
            });
    }

    [typeSelect, objetSelect, sessionSelect, centreSelect].forEach(function (select) {
        select.addEventListener("change", chargerMembres);
    });

    // "Générer la fiche" : lien visible des que type + (objet ou session)
    // sont choisis, pointe vers la fiche PDF du filtre courant — demande
    // utilisatrice : "je voudrais a chaque filtre generer un fiche d'etat
    // de paie".
    function mettreAJourLienGenererPdf() {
        if (!genererPdfLien) return;

        var type = typeSelect.value;
        var objet = objetSelect.value;
        var session = sessionSelect.value;

        if (!type || (!objet && !session)) {
            genererPdfLien.hidden = true;
            return;
        }

        var params = new URLSearchParams();
        params.set("type", type);
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);
        ajouterCentresAuxParametres(params);

        genererPdfLien.href = genererPdfUrl + "?" + params.toString();
        genererPdfLien.hidden = false;
    }

    [typeSelect, objetSelect, sessionSelect, centreSelect].forEach(function (select) {
        select.addEventListener("change", mettreAJourLienGenererPdf);
    });

    // Charge les etats de paie deja crees pour le filtre courant, pour
    // savoir (par membre) s'il faut afficher "Voir état" ou "Pas encore payé". Independant de chargerMembres() (2 appels paralleles) : on
    // re-rend le tableau des membres des que celui-ci arrive, avec les
    // bonnes actions par ligne.
    function chargerEtatsExistants() {
        etatsExistants = [];
        etatsExistantsCharges = false;

        var type = typeSelect.value;
        var objet = objetSelect.value;
        var session = sessionSelect.value;

        if (!type) {
            return;
        }

        var params = new URLSearchParams();
        params.set("type", type);
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);

        fetch(existantsUrl + "?" + params.toString(), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then(function (reponse) { return reponse.json(); })
            .then(function (payload) {
                etatsExistants = payload.success ? (payload.data.etats || []) : [];
                etatsExistantsCharges = true;

                if (membresActuels.length) {
                    afficherMembres(membresActuels, true);
                }
            })
            .catch(function () {
                etatsExistantsCharges = true;
            });
    }

    [typeSelect, objetSelect, sessionSelect, centreSelect].forEach(function (select) {
        select.addEventListener("change", chargerEtatsExistants);
    });

    function formatDate(valeur) {
        if (!valeur) return "—";
        var d = new Date(valeur);
        if (isNaN(d)) return "—";
        return d.toLocaleDateString("fr-FR");
    }

    function libelleStatut(statut) {
        return {
            brouillon: "Brouillon",
            genere: "Générée",
            valide: "Validée",
            archive: "Archivée",
            transmis: "Transmise",
        }[statut] || (statut || "—");
    }

    function celluleCode(valeur) {
        var vide = !valeur;
        return "<td class=\"cellule-code" + (vide ? " vide" : "") + "\">" + echapper(valeur || "—") + "</td>";
    }

    // $liste : sous-ensemble d'etatsExistants a afficher (celui/ceux
    // contenant le membre sur lequel "Voir état" a ete clique). Un etat
    // groupe plusieurs membres (voir "MEMBRE" en tete de tableau) — sans
    // filtrer $etat.details ici, "Voir état" sur UN membre affichait TOUT
    // le monde present dans le meme etat, pas seulement lui (demande
    // utilisatrice : "il devait juste afficher l'état de paie qui
    // conserve le membre uniquement"). Le total affiche est donc aussi
    // recalcule sur les lignes filtrees, pas etat.total_montant (qui
    // reste la somme de TOUS les membres de l'etat).
    function afficherEtatsExistants(liste, membreId, nom, prenom) {
        if (!voirBody) return;

        voirBody.innerHTML = "";

        liste.forEach(function (etat) {
            var detailsMembre = (etat.details || []).filter(function (d) {
                return detailCorrespondAuMembre(d, membreId, nom, prenom);
            });

            if (!detailsMembre.length) {
                return;
            }

            var totalMembre = detailsMembre.reduce(function (somme, d) {
                return somme + (Number(d.montant) || 0);
            }, 0);

            var carte = document.createElement("div");
            carte.className = "etat-paie-voir-carte";

            var header = document.createElement("div");
            header.className = "etat-paie-voir-header";
            header.innerHTML =
                "<div>" +
                    "<div class=\"etat-paie-voir-reference-row\">" +
                        "<p class=\"etat-paie-voir-reference\">" + echapper(etat.reference) + "</p>" +
                        "<span class=\"etat-paie-voir-statut etat-paie-voir-statut-" + echapper(etat.statut || "brouillon") + "\">" + echapper(libelleStatut(etat.statut)) + "</span>" +
                    "</div>" +
                    "<p class=\"etat-paie-voir-periode\">Du " + formatDate(etat.periode_debut) + " au " + formatDate(etat.periode_fin) + "</p>" +
                "</div>" +
                "<div class=\"etat-paie-voir-montant-wrap\">" +
                    "<span class=\"etat-paie-voir-montant-label\">Total</span>" +
                    "<span class=\"etat-paie-voir-montant\">" + formatFCFA(totalMembre) + "</span>" +
                "</div>";
            carte.appendChild(header);

            var tableWrap = document.createElement("div");
            tableWrap.className = "etat-paie-voir-table-wrap";

            var table = document.createElement("table");
            table.className = "etat-paie-voir-table";

            var lignes = detailsMembre.map(function (d) {
                var initiales = ((d.prenom || "").charAt(0) + (d.nom || "").charAt(0)).toUpperCase();

                return "<tr>" +
                    "<td><div class=\"etat-paie-voir-membre\">" +
                        "<span class=\"etat-paie-voir-avatar\">" + echapper(initiales) + "</span>" +
                        "<span>" + echapper(d.prenom) + " " + echapper(d.nom) + "</span>" +
                    "</div></td>" +
                    "<td>" + echapper(d.fonction || "—") + "</td>" +
                    celluleCode(d.code_banque) +
                    celluleCode(d.code_guichet) +
                    celluleCode(d.numero_compte_bancaire) +
                    celluleCode(d.cle_rib) +
                    "<td class=\"cellule-montant\">" + formatFCFA(d.montant || 0) + "</td>" +
                "</tr>";
            }).join("");

            table.innerHTML =
                "<thead><tr><th>Membre</th><th>Fonction</th><th>Code banque</th><th>Code guichet</th><th>Compte</th><th>Clé RIB</th><th style=\"text-align:right;\">Montant</th></tr></thead>" +
                "<tbody>" + lignes + "</tbody>";
            tableWrap.appendChild(table);
            carte.appendChild(tableWrap);

            voirBody.appendChild(carte);
        });
    }

    // "Voir état" (par ligne) : ouvre la modale sur le/les etat(s) de paie
    // contenant CE membre precis.
    membresBody.addEventListener("click", function (event) {
        var bouton = event.target.closest("[data-etat-paie-membre-voir]");
        if (!bouton || !voirModal) {
            return;
        }

        var ligne = bouton.closest("[data-etat-paie-membre-row]");
        if (!ligne) {
            return;
        }

        afficherEtatsExistants(
            etatsDuMembre(ligne.dataset.id, ligne.dataset.nom, ligne.dataset.prenom),
            ligne.dataset.id,
            ligne.dataset.nom,
            ligne.dataset.prenom
        );
        voirModal.hidden = false;
    });

    function remplirCentre(options, placeholder) {
        centreListe.innerHTML = "";
        placeholderCentres = placeholder;

        if (options.length) {
            centreListe.appendChild(creerOptionCentre("Tous les centres", "", true));
            options.forEach(function (centre) {
                centreListe.appendChild(creerOptionCentre(centre, centre, false));
            });
        } else {
            centreSelect.open = false;
        }

        if (options.length === 0) {
            centreSelect.setAttribute("data-desactive", "");
        } else {
            centreSelect.removeAttribute("data-desactive");
        }

        resumeCentres();
    }

    // $apresChargement (optionnel) : execute une fois les options du menu
    // Centre en place — sert au chargement initial (query string) pour
    // reselectionner le centre avant de charger membres/etats existants.
    function chargerCentres(apresChargement) {
        var objet = objetSelect.value;
        var session = sessionSelect.value;

        remplirCentre([], "Chargement des centres…");

        var params = new URLSearchParams();
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);

        fetch(centresUrl + "?" + params.toString(), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then(function (reponse) { return reponse.json(); })
            .then(function (payload) {
                if (!payload.success || !(payload.data.centres || []).length) {
                    remplirCentre([], "Aucun centre trouvé pour ce filtre");
                    if (apresChargement) apresChargement();
                    return;
                }

                remplirCentre(payload.data.centres, "Sélectionner");
                if (apresChargement) apresChargement();
            })
            .catch(function () {
                remplirCentre([], "Impossible de charger les centres");
                if (apresChargement) apresChargement();
            });
    }

    [objetSelect, sessionSelect].forEach(function (select) {
        select.addEventListener("change", function () { chargerCentres(); });
    });

    // Restaure le filtre apres redirection (demande utilisatrice : "je veux
    // fixer tableau filtré après ajout de l'état de paie") — type/objet/
    // session sont deja preselectionnes cote Blade (directive selected), il reste a
    // relancer le chargement des centres/membres/etats existants comme si
    // l'utilisatrice venait de les choisir elle-meme.
    var filtreInitialObjet = @json($filtres['objet'] ?? '');
    var filtreInitialSession = @json($filtres['session'] ?? '');
    var filtreInitialCentre = [].concat(@json($filtres['centre'] ?? []));
    var filtreInitialType = @json($filtres['type'] ?? '');

    // ------------------------------------------------------------------
    // États de paie générés : liste (10 par page), consultation, PDF, suppression
    // ------------------------------------------------------------------
    var listeUrl = "{{ route('indemnites.etats-paie.liste') }}";
    var pdfUrlTemplate = "{{ route('indemnites.etats-paie.pdf', ['id' => '__ID__']) }}";
    var supprimerUrlTemplate = "{{ route('indemnites.etats-paie.destroy', ['id' => '__ID__']) }}";
    var peutGererEtats = @json(\App\Support\SicoreAccess::can(request(), 'indemnites.manage'));
    var peutValiderEtats = @json(\App\Support\SicoreAccess::can(request(), 'indemnites.validate'));
    var statutUrlTemplate = "{{ route('indemnites.etats-paie.statut', ['id' => '__ID__', 'action' => '__ACTION__']) }}";
    var statutForm = document.querySelector("[data-etat-paie-statut-form]");
    // Circuit de la DAGE : brouillon → valider → transmettre → archiver.
    var ACTIONS_STATUT = { brouillon: ["valider", "Valider"], valide: ["transmettre", "Transmettre"], transmis: ["archiver", "Archiver"] };
    var listeBody = document.querySelector("[data-etat-paie-liste-body]");
    var listeHint = document.querySelector("[data-etat-paie-liste-hint]");
    var listePagination = document.querySelector("[data-etat-paie-liste-pagination]");
    var listeCompteur = document.querySelector("[data-etat-paie-liste-compteur]");
    var supprimerForm = document.querySelector("[data-etat-paie-supprimer-form]");
    var consulterModal = document.getElementById("etatPaieConsulterModal");
    var consulterBody = document.querySelector("[data-etat-paie-consulter-body]");
    var etatsListe = [];
    var pageEtats = 1;
    var ETATS_PAR_PAGE = 10;

    function chargerListeEtats() {
        if (!listeBody) return;

        var params = new URLSearchParams();
        if (typeSelect.value) params.set("type", typeSelect.value);
        if (objetSelect.value) params.set("objet", objetSelect.value);
        if (sessionSelect.value) params.set("session", sessionSelect.value);

        fetch(listeUrl + "?" + params.toString(), { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(function (reponse) { return reponse.json(); })
            .then(function (payload) {
                etatsListe = payload.success ? (payload.data.etats || []) : [];
                pageEtats = 1;
                afficherListeEtats();
                if (!payload.success) {
                    listeHint.textContent = payload.message || "Impossible de charger les états de paie.";
                }
            })
            .catch(function () {
                etatsListe = [];
                afficherListeEtats();
                listeHint.textContent = "Impossible de charger les états de paie.";
            });
    }

    function afficherListeEtats() {
        listeBody.innerHTML = "";

        var nombrePages = Math.max(1, Math.ceil(etatsListe.length / ETATS_PAR_PAGE));
        pageEtats = Math.min(Math.max(1, pageEtats), nombrePages);
        listeCompteur.textContent = etatsListe.length + " état(s)";

        if (!etatsListe.length) {
            listeHint.hidden = false;
            listeHint.textContent = "Aucun état de paie généré pour ce filtre.";
            listePagination.hidden = true;
            return;
        }

        listeHint.hidden = true;

        var debut = (pageEtats - 1) * ETATS_PAR_PAGE;

        etatsListe.slice(debut, debut + ETATS_PAR_PAGE).forEach(function (etat) {
            var tr = document.createElement("tr");
            var periode = etat.periode_debut ? formatDate(etat.periode_debut) + " → " + formatDate(etat.periode_fin) : "—";
            var perimetre = [etat.session, etat.objet].filter(Boolean).join(" · ") || "—";
            var pdf = pdfUrlTemplate.replace("__ID__", encodeURIComponent(etat.id));

            tr.innerHTML =
                "<td><strong>" + echapper(etat.reference) + "</strong></td>" +
                "<td>" + echapper(etat.type_libelle) + "</td>" +
                "<td>" + echapper(perimetre) + "</td>" +
                "<td>" + periode + "</td>" +
                "<td>" + echapper(etat.lieu_examen || "Tous les centres") + "</td>" +
                "<td style=\"text-align:right;\">" + (etat.details || []).length + "</td>" +
                "<td style=\"text-align:right;\">" + formatFCFA(etat.total_montant || 0) + "</td>" +
                "<td><span class=\"etat-paie-voir-statut etat-paie-voir-statut-" + echapper(etat.statut || "brouillon") + "\">" + echapper(libelleStatut(etat.statut)) + "</span></td>" +
                "<td class=\"actions-cell\"><div class=\"table-actions-inline\">" +
                    "<button type=\"button\" class=\"table-action\" data-etat-consulter=\"" + echapper(etat.id) + "\">Consulter</button>" +
                    "<a class=\"table-action\" href=\"" + pdf + "\" target=\"_blank\" rel=\"noopener\">PDF</a>" +
                    ((peutValiderEtats && ACTIONS_STATUT[etat.statut])
                        ? "<button type=\"button\" class=\"table-action\" data-etat-statut=\"" + echapper(etat.id) + "\" data-action=\"" + ACTIONS_STATUT[etat.statut][0] + "\">" + ACTIONS_STATUT[etat.statut][1] + "</button>"
                        : "") +
                    ((peutGererEtats && etat.statut === "brouillon")
                        ? "<button type=\"button\" class=\"table-action delete\" data-etat-supprimer=\"" + echapper(etat.id) + "\">Supprimer</button>"
                        : "") +
                "</div></td>";

            listeBody.appendChild(tr);
        });

        listePagination.innerHTML = "";
        listePagination.hidden = nombrePages <= 1;

        if (nombrePages > 1) {
            var ajouterBouton = function (libelle, page, actif, desactive) {
                var b = document.createElement("button");
                b.type = "button";
                b.className = "page-btn" + (actif ? " active" : "");
                b.textContent = libelle;
                b.disabled = Boolean(desactive);
                b.setAttribute("data-etat-liste-page", String(page));
                listePagination.appendChild(b);
            };

            ajouterBouton("←", pageEtats - 1, false, pageEtats === 1);
            for (var p = 1; p <= nombrePages; p++) { ajouterBouton(String(p), p, p === pageEtats, false); }
            ajouterBouton("→", pageEtats + 1, false, pageEtats === nombrePages);
        }
    }

    function consulterEtat(etat) {
        var details = etat.details || [];
        var total = details.reduce(function (somme, d) { return somme + (Number(d.montant) || 0); }, 0);
        var pdf = pdfUrlTemplate.replace("__ID__", encodeURIComponent(etat.id));

        var lignes = details.map(function (d, i) {
            var rib = [d.code_banque, d.code_guichet, d.numero_compte_bancaire, d.cle_rib].filter(Boolean).join(" ");
            return "<tr>" +
                "<td>" + (i + 1) + "</td>" +
                "<td>" + echapper(((d.prenom || "") + " " + (d.nom || "")).trim() || "—") + "</td>" +
                "<td>" + echapper(d.fonction || "—") + "</td>" +
                "<td>" + echapper(d.centre || "—") + "</td>" +
                "<td>" + echapper(rib || "—") + "</td>" +
                "<td style=\"text-align:right;\">" + formatFCFA(d.montant || 0) + "</td>" +
            "</tr>";
        }).join("");

        consulterBody.innerHTML =
            "<dl class=\"etat-paie-detail-infos\">" +
                "<div><dt>Référence</dt><dd>" + echapper(etat.reference) + "</dd></div>" +
                "<div><dt>Statut</dt><dd>" + echapper(libelleStatut(etat.statut)) + "</dd></div>" +
                "<div><dt>Type</dt><dd>" + echapper(etat.type_libelle) + "</dd></div>" +
                "<div><dt>Session</dt><dd>" + echapper(etat.session || "—") + "</dd></div>" +
                "<div><dt>Objet</dt><dd>" + echapper(etat.objet || "—") + "</dd></div>" +
                "<div><dt>Période</dt><dd>" + (etat.periode_debut ? formatDate(etat.periode_debut) + " au " + formatDate(etat.periode_fin) : "—") + "</dd></div>" +
                "<div><dt>Centre(s)</dt><dd>" + echapper(etat.lieu_examen || "Tous les centres") + "</dd></div>" +
                "<div><dt>Généré le</dt><dd>" + formatDate(etat.date_generation) + "</dd></div>" +
            "</dl>" +
            "<div class=\"table-responsive\"><table class=\"table\">" +
                "<thead><tr><th>N°</th><th>Nom et prénom</th><th>Fonction</th><th>Centre</th><th>Coordonnées bancaires</th><th style=\"text-align:right;\">Montant</th></tr></thead>" +
                "<tbody>" + lignes + "</tbody>" +
                "<tfoot><tr><th colspan=\"5\" style=\"text-align:right;\">Total (" + details.length + " bénéficiaire(s))</th><th style=\"text-align:right;\">" + formatFCFA(total) + "</th></tr></tfoot>" +
            "</table></div>" +
            "<div class=\"etat-paie-detail-actions\"><a class=\"btn-primary\" href=\"" + pdf + "\" target=\"_blank\" rel=\"noopener\">Télécharger le PDF</a></div>";

        consulterModal.hidden = false;
    }

    if (listeBody) {
        listeBody.addEventListener("click", function (evenement) {
            var consulter = evenement.target.closest("[data-etat-consulter]");
            var supprimer = evenement.target.closest("[data-etat-supprimer]");
            var statut = evenement.target.closest("[data-etat-statut]");

            if (statut && statutForm) {
                var etatCible = etatsListe.find(function (e) { return String(e.id) === statut.getAttribute("data-etat-statut"); });
                var action = statut.getAttribute("data-action");

                if (etatCible && window.confirm(statut.textContent + " l'état de paie " + etatCible.reference + " ?")) {
                    statutForm.action = statutUrlTemplate.replace("__ID__", encodeURIComponent(etatCible.id)).replace("__ACTION__", action);
                    statutForm.querySelector("[name='type']").value = typeSelect.value;
                    statutForm.querySelector("[name='objet']").value = objetSelect.value;
                    statutForm.querySelector("[name='session']").value = sessionSelect.value;
                    statutForm.submit();
                }
            } else if (consulter) {
                var etat = etatsListe.find(function (e) { return String(e.id) === consulter.getAttribute("data-etat-consulter"); });
                if (etat) consulterEtat(etat);
            } else if (supprimer && supprimerForm) {
                var cible = etatsListe.find(function (e) { return String(e.id) === supprimer.getAttribute("data-etat-supprimer"); });

                if (cible && window.confirm("Supprimer l'état de paie " + cible.reference + " ? Les bénéficiaires pourront de nouveau être inclus dans un autre état.")) {
                    supprimerForm.action = supprimerUrlTemplate.replace("__ID__", encodeURIComponent(cible.id));
                    supprimerForm.querySelector("[name='type']").value = typeSelect.value;
                    supprimerForm.querySelector("[name='objet']").value = objetSelect.value;
                    supprimerForm.querySelector("[name='session']").value = sessionSelect.value;
                    supprimerForm.submit();
                }
            }
        });
    }

    if (listePagination) {
        listePagination.addEventListener("click", function (evenement) {
            var cible = evenement.target.closest("[data-etat-liste-page]");
            if (!cible || cible.disabled) return;

            pageEtats = parseInt(cible.getAttribute("data-etat-liste-page"), 10) || 1;
            afficherListeEtats();
        });
    }

    [typeSelect, objetSelect, sessionSelect].forEach(function (select) {
        select.addEventListener("change", chargerListeEtats);
    });

    chargerCentres(function () {
        filtreInitialCentre.forEach(function (centre) {
            centreListe.querySelectorAll("input[data-centre-option]").forEach(function (champ) {
                if (champ.value === centre) champ.checked = true;
            });
        });

        var tousLesCentres = centreListe.querySelector("input[data-centre-tous]");
        if (tousLesCentres) tousLesCentres.checked = valeursCentres().length === 0;
        resumeCentres();

        // Liste par défaut : les membres s'affichent dès l'ouverture, sans filtre obligatoire.
        chargerMembres();
        chargerEtatsExistants();
        mettreAJourLienGenererPdf();
        chargerListeEtats();
    });
})();
</script>
@endpush

@endsection
