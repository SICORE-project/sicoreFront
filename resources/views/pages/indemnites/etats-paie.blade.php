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
                    <option value="">Sélectionner</option>
                    @foreach ($typesIndemnite as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected(($filtres['type'] ?? null) === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="etat-paie-objet">Objet</label>
                <select class="form-control" id="etat-paie-objet" name="objet" data-etat-paie-filtre>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(($filtres['objet'] ?? null) === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="etat-paie-session">Session</label>
                <select class="form-control" id="etat-paie-session" name="session" data-etat-paie-filtre>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(($filtres['session'] ?? null) === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="etat-paie-centre">Centre</label>
                <select class="form-control" id="etat-paie-centre" name="centre" data-etat-paie-centre disabled>
                    <option value="">Choisissez d'abord un objet ou une session</option>
                </select>
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

            <div class="table-responsive">
                <table class="table" id="etatsPaieMembresTable">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Catégorie</th>
                            <th>Fonction</th>
                            <th style="text-align:right;">Montant</th>
                            <th class="actions-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody data-etat-paie-membres-body></tbody>
                </table>
            </div>

            <p class="empty-message show" data-etat-paie-membres-hint>
                Choisissez un type d'indemnité, puis un objet ou une session, pour afficher les membres associés.
            </p>

        </section>

    </section>

</main>

{{-- ============================================================
     MODALE "Ajouter état de paie" — un membre a la fois (chaque membre a
     ses propres coordonnees bancaires, un formulaire groupe n'aurait pas de
     sens). Ouverte manuellement en JS (le bouton "Ajouter" de chaque ligne
     est cree dynamiquement, donc apres le cablage automatique de
     x-module-indemnite) ; la fermeture (fond/Echap/croix) reste geree par
     ce composant.
============================================================ --}}

<x-module-indemnite type="modal" id="etatPaieAjouterModal" title="Ajouter un état de paie">

    <form method="POST" action="{{ route('indemnites.etats-paie.store') }}" data-etat-paie-modal-form>
        @csrf

        <input type="hidden" name="type" data-etat-paie-modal-field="type">
        <input type="hidden" name="objet" data-etat-paie-modal-field="objet">
        <input type="hidden" name="session" data-etat-paie-modal-field="session">
        <input type="hidden" name="centre" data-etat-paie-modal-field="centre">

        <input type="hidden" name="details[0][id]" data-etat-paie-modal-field="id">
        <input type="hidden" name="details[0][nom]" data-etat-paie-modal-field="nom">
        <input type="hidden" name="details[0][prenom]" data-etat-paie-modal-field="prenom">
        <input type="hidden" name="details[0][categorie_personnel]" data-etat-paie-modal-field="categorie_personnel">
        <input type="hidden" name="details[0][fonction]" data-etat-paie-modal-field="fonction">

        <div class="etat-paie-modal-membre">
            <span class="etat-paie-modal-avatar" data-etat-paie-modal-avatar></span>
            <div>
                <p class="etat-paie-modal-nom" data-etat-paie-modal-nom></p>
                <p class="etat-paie-modal-sub" data-etat-paie-modal-sub></p>
            </div>
        </div>

        <div class="etat-paie-modal-montant-block">
            <label for="etat-paie-modal-montant">Montant</label>
            <div class="etat-paie-modal-montant-row">
                <input class="etat-paie-modal-montant-input" type="text" inputmode="numeric" id="etat-paie-modal-montant" name="details[0][montant]" data-etat-paie-modal-field="montant" readonly>
                <span class="etat-paie-modal-montant-unit">F</span>
            </div>
        </div>

        <div class="etat-paie-modal-section">
            <h3>Coordonnées bancaires</h3>

            <div class="form-grid">

                <div class="form-group">
                    <label for="etat-paie-modal-code-banque">Code banque</label>
                    <input class="form-control" type="text" maxlength="5" placeholder="Ex : SN012" id="etat-paie-modal-code-banque" name="details[0][code_banque]" data-etat-paie-modal-field="code_banque">
                </div>

                <div class="form-group">
                    <label for="etat-paie-modal-code-guichet">Code guichet</label>
                    <input class="form-control" type="text" maxlength="5" placeholder="Ex : 01234" id="etat-paie-modal-code-guichet" name="details[0][code_guichet]" data-etat-paie-modal-field="code_guichet">
                </div>

                <div class="form-group">
                    <label for="etat-paie-modal-numero-compte">Numéro de compte</label>
                    <input class="form-control" type="text" maxlength="34" placeholder="Ex : 00123456789" id="etat-paie-modal-numero-compte" name="details[0][numero_compte_bancaire]" data-etat-paie-modal-field="numero_compte_bancaire">
                </div>

                <div class="form-group">
                    <label for="etat-paie-modal-cle-rib">Clé RIB</label>
                    <input class="form-control" type="text" maxlength="2" placeholder="Ex : 45" id="etat-paie-modal-cle-rib" name="details[0][cle_rib]" data-etat-paie-modal-field="cle_rib">
                </div>

            </div>

            <p class="etat-paie-modal-rib-hint" data-etat-paie-modal-rib-hint hidden>Recherche des coordonnées bancaires…</p>
        </div>

        <div class="form-actions">
            <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
            <button class="etat-paie-btn-vert" type="submit">Ajouter</button>
        </div>

    </form>

</x-module-indemnite>

{{-- ============================================================
     MODALE "Voir état" — etat(s) de paie deja crees pour le filtre courant
     (demande utilisatrice : "ajouter un bouton voir etat qui affiche les
     informations en modal").
============================================================ --}}

<x-module-indemnite type="modal" id="etatPaieVoirModal" title="État(s) de paie existants">

    <div data-etat-paie-voir-body></div>

</x-module-indemnite>

@push('styles')
<style>

    .actions-cell { width: 90px; text-align: right; }

    .montant-defaut { color: #b45309; font-weight: 700; }

    .etat-paie-modal-membre {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-soft);
    }

    .etat-paie-modal-avatar {
        flex: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e3efe6;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
    }

    .etat-paie-modal-nom {
        margin: 0;
        font-size: 15.5px;
        font-weight: 700;
    }

    .etat-paie-modal-sub {
        margin: 2px 0 0;
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .etat-paie-modal-montant-block {
        display: grid;
        gap: 6px;
        margin-bottom: 20px;
        padding: 14px 16px;
        border-radius: 10px;
        background: #f0f7f2;
        border: 1px solid #d7e9dc;
    }

    .etat-paie-modal-montant-block label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--primary);
    }

    .etat-paie-modal-montant-row {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }

    .etat-paie-modal-montant-input {
        border: none;
        background: transparent;
        padding: 0;
        font-size: 22px;
        font-weight: 800;
        color: var(--primary);
        width: auto;
        max-width: 220px;
        cursor: default;
    }

    .etat-paie-modal-montant-input:focus { outline: none; }

    .etat-paie-modal-montant-unit {
        font-size: 15px;
        font-weight: 700;
        color: var(--primary);
    }

    .etat-paie-modal-section h3 {
        margin: 0 0 12px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .etat-paie-modal-rib-hint {
        margin: 10px 0 0;
        font-size: 12.5px;
        color: var(--text-muted);
    }

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

    /* Boutons d'action par ligne du tableau (Ajouter / Voir état) — un vrai
       bouton avec fond, pas juste un lien texte (demande utilisatrice :
       "tu n'a pas mis une couleur de fond sur le bouton ajouter"). */
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
    var centresUrl = "{{ route('indemnites.etats-paie.centres') }}";
    var membresUrl = "{{ route('indemnites.etats-paie.membres') }}";
    var membresBody = document.querySelector("[data-etat-paie-membres-body]");
    var membresHint = document.querySelector("[data-etat-paie-membres-hint]");
    var ribUrlTemplate = "{{ route('indemnites.etats-paie.membres.rib', ['id' => '__ID__']) }}";
    var modal = document.getElementById("etatPaieAjouterModal");
    var modalRibHint = document.querySelector("[data-etat-paie-modal-rib-hint]");
    var modalNom = document.querySelector("[data-etat-paie-modal-nom]");
    var modalSub = document.querySelector("[data-etat-paie-modal-sub]");
    var modalAvatar = document.querySelector("[data-etat-paie-modal-avatar]");
    var existantsUrl = "{{ route('indemnites.etats-paie.existants') }}";
    var voirModal = document.getElementById("etatPaieVoirModal");
    var voirBody = document.querySelector("[data-etat-paie-voir-body]");
    var etatsExistants = [];
    var etatsExistantsCharges = false;
    var membresActuels = [];
    var genererPdfUrl = "{{ route('indemnites.etats-paie.fiche-pdf') }}";
    var genererPdfLien = document.querySelector("[data-etat-paie-generer-pdf]");

    if (!typeSelect || !objetSelect || !sessionSelect || !centreSelect || !membresBody || !modal) {
        return;
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

    function afficherMembres(membres) {
        membresActuels = membres;
        membresBody.innerHTML = "";

        if (!membres.length) {
            afficherMembresHint("Aucun membre trouvé pour ce filtre.");
            return;
        }

        membresHint.hidden = true;

        membres.forEach(function (membre) {
            var tr = document.createElement("tr");
            tr.setAttribute("data-etat-paie-membre-row", "");
            tr.dataset.id = membre.id || "";
            tr.dataset.nom = membre.nom || "";
            tr.dataset.prenom = membre.prenom || "";
            tr.dataset.categoriePersonnel = membre.categorie_personnel || "";
            tr.dataset.fonction = membre.fonction || "";
            tr.dataset.montant = membre.montant || 0;

            var montantHtml = membre.montant_par_defaut
                ? "<span class=\"montant-defaut\" title=\"Montant par défaut pour ce rôle\">" + formatFCFA(membre.montant || 0) + "</span>"
                : formatFCFA(membre.montant || 0);

            // Deja dans un etat de paie (pour ce filtre) : "Voir état" a la
            // place de "Ajouter" — demande utilisatrice : "voir etat dois
            // etre dans chaque ligne de membre". Tant que les etats
            // existants ne sont pas encore charges, on affiche "Ajouter"
            // par defaut (re-rendu automatique des que chargerEtatsExistants()
            // termine, voir plus bas).
            var actionHtml = etatsDuMembre(membre.id, membre.nom, membre.prenom).length
                ? "<button type=\"button\" class=\"etat-paie-btn-ligne etat-paie-btn-ligne-voir\" data-etat-paie-membre-voir>Voir état</button>"
                : "<button type=\"button\" class=\"etat-paie-btn-ligne\" data-etat-paie-membre-ajouter>Ajouter</button>";

            tr.innerHTML =
                "<td>" + echapper(membre.nom) + "</td>" +
                "<td>" + echapper(membre.prenom) + "</td>" +
                "<td>" + (membre.categorie_personnel ? echapper(membre.categorie_personnel.charAt(0).toUpperCase() + membre.categorie_personnel.slice(1)) : "—") + "</td>" +
                "<td>" + echapper(membre.fonction || "—") + "</td>" +
                "<td style=\"text-align:right;\">" + montantHtml + "</td>" +
                "<td class=\"actions-cell\">" + actionHtml + "</td>";
            membresBody.appendChild(tr);
        });
    }

    function chargerMembres() {
        var type = typeSelect.value;
        var objet = objetSelect.value;
        var session = sessionSelect.value;
        var centre = centreSelect.value;

        if (!type || (!objet && !session)) {
            afficherMembresHint("Choisissez un type d'indemnité, puis un objet ou une session, pour afficher les membres associés.");
            return;
        }

        afficherMembresHint("Recherche des membres…");

        var params = new URLSearchParams();
        params.set("type", type);
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);
        if (centre) params.set("centre", centre);

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
        var centre = centreSelect.value;

        if (!type || (!objet && !session)) {
            genererPdfLien.hidden = true;
            return;
        }

        var params = new URLSearchParams();
        params.set("type", type);
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);
        if (centre) params.set("centre", centre);

        genererPdfLien.href = genererPdfUrl + "?" + params.toString();
        genererPdfLien.hidden = false;
    }

    [typeSelect, objetSelect, sessionSelect, centreSelect].forEach(function (select) {
        select.addEventListener("change", mettreAJourLienGenererPdf);
    });

    // Charge les etats de paie deja crees pour le filtre courant, pour
    // savoir (par membre) s'il faut afficher "Voir état" ou "Ajouter" —
    // demande utilisatrice : "voir etat dois etre dans chaque ligne de
    // membre". Independant de chargerMembres() (2 appels paralleles) : on
    // re-rend le tableau des membres des que celui-ci arrive, avec les
    // bonnes actions par ligne.
    function chargerEtatsExistants() {
        etatsExistants = [];
        etatsExistantsCharges = false;

        var type = typeSelect.value;
        var objet = objetSelect.value;
        var session = sessionSelect.value;
        var centre = centreSelect.value;

        if (!type || (!objet && !session)) {
            return;
        }

        var params = new URLSearchParams();
        params.set("type", type);
        if (objet) params.set("objet", objet);
        if (session) params.set("session", session);
        if (centre) params.set("centre", centre);

        fetch(existantsUrl + "?" + params.toString(), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then(function (reponse) { return reponse.json(); })
            .then(function (payload) {
                etatsExistants = payload.success ? (payload.data.etats || []) : [];
                etatsExistantsCharges = true;

                if (membresActuels.length) {
                    afficherMembres(membresActuels);
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
        centreSelect.innerHTML = "";

        var option = document.createElement("option");
        option.value = "";
        option.textContent = placeholder;
        centreSelect.appendChild(option);

        options.forEach(function (centre) {
            var opt = document.createElement("option");
            opt.value = centre;
            opt.textContent = centre;
            centreSelect.appendChild(opt);
        });

        centreSelect.disabled = options.length === 0;
    }

    // $apresChargement (optionnel) : execute une fois les options du menu
    // Centre en place — sert au chargement initial (query string) pour
    // reselectionner le centre avant de charger membres/etats existants.
    function chargerCentres(apresChargement) {
        var objet = objetSelect.value;
        var session = sessionSelect.value;

        if (!objet && !session) {
            remplirCentre([], "Choisissez d'abord un objet ou une session");
            return;
        }

        remplirCentre([], "Recherche des centres…");

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
            });
    }

    [objetSelect, sessionSelect].forEach(function (select) {
        select.addEventListener("change", function () { chargerCentres(); });
    });

    function champModal(nom) {
        return modal.querySelector("[data-etat-paie-modal-field='" + nom + "']");
    }

    function remplirChampModal(nom, valeur) {
        var champ = champModal(nom);
        if (champ) champ.value = valeur == null ? "" : valeur;
    }

    // "Ajouter" (par ligne) : ouvre la modale d'enregistrement de CE membre
    // (demande utilisatrice : "un modal d'enregistrement du membre concerné
    // s'affiche") — pré-remplit type/objet/session/centre (filtres actuels)
    // + nom/prenom/categorie/fonction/montant (donnees de la ligne), puis va
    // chercher les coordonnees bancaires deja enregistrees sur la fiche de
    // l'enseignant le cas echeant.
    membresBody.addEventListener("click", function (event) {
        var bouton = event.target.closest("[data-etat-paie-membre-ajouter]");
        if (!bouton) {
            return;
        }

        var ligne = bouton.closest("[data-etat-paie-membre-row]");
        if (!ligne) {
            return;
        }

        remplirChampModal("type", typeSelect.value);
        remplirChampModal("objet", objetSelect.value);
        remplirChampModal("session", sessionSelect.value);
        remplirChampModal("centre", centreSelect.value);

        remplirChampModal("id", ligne.dataset.id);
        remplirChampModal("nom", ligne.dataset.nom);
        remplirChampModal("prenom", ligne.dataset.prenom);
        remplirChampModal("categorie_personnel", ligne.dataset.categoriePersonnel);
        remplirChampModal("fonction", ligne.dataset.fonction);
        // Valeur brute (pas de separateur de milliers) : ce champ readonly
        // est aussi celui soumis au formulaire, un espace dedans casserait
        // le parsing numerique cote back (collect($details)->sum('montant')).
        remplirChampModal("montant", Math.round(parseFloat(ligne.dataset.montant) || 0));

        remplirChampModal("code_banque", "");
        remplirChampModal("code_guichet", "");
        remplirChampModal("numero_compte_bancaire", "");
        remplirChampModal("cle_rib", "");

        var nomComplet = (ligne.dataset.prenom + " " + ligne.dataset.nom).trim();

        if (modalNom) {
            modalNom.textContent = nomComplet;
        }

        if (modalSub) {
            modalSub.textContent = [ligne.dataset.categoriePersonnel, ligne.dataset.fonction]
                .filter(Boolean)
                .map(function (mot) { return mot.charAt(0).toUpperCase() + mot.slice(1); })
                .join(" · ");
        }

        if (modalAvatar) {
            modalAvatar.textContent = (
                (ligne.dataset.prenom || "").charAt(0) + (ligne.dataset.nom || "").charAt(0)
            ).toUpperCase();
        }

        modal.hidden = false;

        var enseignantId = ligne.dataset.id;
        if (enseignantId && modalRibHint) {
            modalRibHint.hidden = false;
            modalRibHint.textContent = "Recherche des coordonnées bancaires…";

            fetch(ribUrlTemplate.replace("__ID__", enseignantId), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            })
                .then(function (reponse) { return reponse.json(); })
                .then(function (payload) {
                    modalRibHint.hidden = true;

                    if (!payload.success) {
                        return;
                    }

                    remplirChampModal("code_banque", payload.data.code_banque);
                    remplirChampModal("code_guichet", payload.data.code_guichet);
                    remplirChampModal("numero_compte_bancaire", payload.data.numero_compte_bancaire);
                    remplirChampModal("cle_rib", payload.data.cle_rib);
                })
                .catch(function () {
                    modalRibHint.hidden = true;
                });
        }
    });

    // Restaure le filtre apres redirection (demande utilisatrice : "je veux
    // fixer tableau filtré après ajout de l'état de paie") — type/objet/
    // session sont deja preselectionnes cote Blade (directive selected), il reste a
    // relancer le chargement des centres/membres/etats existants comme si
    // l'utilisatrice venait de les choisir elle-meme.
    var filtreInitialObjet = @json($filtres['objet'] ?? '');
    var filtreInitialSession = @json($filtres['session'] ?? '');
    var filtreInitialCentre = @json($filtres['centre'] ?? '');
    var filtreInitialType = @json($filtres['type'] ?? '');

    if (filtreInitialObjet || filtreInitialSession) {
        chargerCentres(function () {
            if (filtreInitialCentre) {
                centreSelect.value = filtreInitialCentre;
            }

            if (filtreInitialType) {
                chargerMembres();
                chargerEtatsExistants();
            }

            mettreAJourLienGenererPdf();
        });
    } else if (filtreInitialType) {
        chargerMembres();
        chargerEtatsExistants();
        mettreAJourLienGenererPdf();
    }
})();
</script>
@endpush

@endsection
