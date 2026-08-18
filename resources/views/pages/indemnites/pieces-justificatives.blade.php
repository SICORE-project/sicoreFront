@extends('layouts.app')

@section('title', 'SICORE - Pièces justificatives')

@section('content')

<main class="main-content">

    <x-topbar
        title="Pièces justificatives"
        subtitle="Indemnites > Pièces justificatives"
        icon="fa-solid fa-folder-open"
        search-id="pieceJustificativeSearch"
        search-placeholder="Rechercher…"
        filter-target="#piecesJustificativesTable"
    />

    <section class="content-area">

        {{-- ============================================================
             STATISTIQUES
             Une carte = un aspect du suivi des dossiers (convocation x
             centre x session). "total_dossiers" reprend le nombre de
             lignes de la page courante (une convocation sans centre
             compte pour une ligne, comme sur la liste des convocations) ;
             le reste est calcule sur cette meme page, pas sur l'ensemble
             des dossiers — meme limite deja acceptee sur
             ConvocationsController::index() (l'API ne fournit pas
             d'agregat dedie).
        ============================================================ --}}

        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Dossiers</p>
                    <p class="stat-value">{{ $stats['total_dossiers'] ?? 0 }}</p>
                    <p class="stat-note">Convocation × centre d'examen</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Avec pièces déposées</p>
                    <p class="stat-value">{{ $stats['sessions_avec_pieces'] ?? 0 }}</p>
                    <p class="stat-note">Dossiers avec au moins une pièce</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Sans aucune pièce</p>
                    <p class="stat-value">{{ $stats['sessions_sans_pieces'] ?? 0 }}</p>
                    <p class="stat-note">Dossiers à compléter</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Pièces rejetées</p>
                    <p class="stat-value">{{ $stats['pieces_rejetees'] ?? 0 }}</p>
                    <p class="stat-note">À redéposer</p>
                </div>
                <span class="stat-icon red">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        {{-- ============================================================
             FILTRES
             Menus deroulants (pas de saisie libre) : uniquement des
             valeurs de session/objet/centre qui appartiennent reellement a
             au moins une convocation existante — voir
             ConvocationsController::filtres() cote back.
        ============================================================ --}}

        <form
            id="pieceJustificativeFilterForm"
            class="filter-panel"
            method="GET"
            action="{{ route('indemnites.pieces-justificatives') }}"
            aria-label="Filtres de la page"
        >

            <div class="form-group">
                <label for="piece-filter-session">Session</label>
                <select class="form-control" id="piece-filter-session" name="session" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('session') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="piece-filter-objet">Objet</label>
                <select class="form-control" id="piece-filter-objet" name="objet" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('objet') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="piece-filter-centre">Centre</label>
                <select class="form-control" id="piece-filter-centre" name="centre" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['centres'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('centre') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions-group">
                @if ($filtreActif)
                    <a class="btn-secondary" href="{{ route('indemnites.pieces-justificatives') }}">
                        Réinitialiser
                    </a>
                @endif
            </div>

        </form>

        {{-- ============================================================
             TABLEAU DES MEMBRES
             Cache tant qu'aucun filtre (session, objet ou centre) n'est
             choisi. Une fois un filtre choisi : une ligne par membre du
             jury rattache au centre/a l'objet/a la session selectionnes —
             voir PiecesJustificativesController::construireMembres().
        ============================================================ --}}

        @if (! $filtreActif)

            <section class="table-card">
                {{-- "show" necessaire : .empty-message est display:none par
                     defaut, seul le JS de recherche cote client le
                     basculerait sinon (jamais au chargement initial). --}}
                <p class="empty-message show">
                    Choisissez une session, un objet ou un centre d'examen ci-dessus pour afficher les membres concernés.
                </p>
            </section>

        @else

            <section class="table-card">

                <div class="table-responsive">
                    <table class="table" id="piecesJustificativesTable">

                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénoms</th>
                                <th>Fonction</th>
                                <th>Centre d'examen</th>
                                <th>Jury</th>
                                <th>Objet</th>
                                <th>Type de convocation</th>
                                <th>Session</th>
                                <th>Provenance</th>
                                <th>Statut du dossier</th>
                                <th class="actions-cell">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($membres as $membre)
                                <tr>
                                    <td>{{ $membre['nom'] ?? '—' }}</td>
                                    <td>{{ $membre['prenom'] ?? '—' }}</td>
                                    <td>{{ $membre['fonction'] ?? '—' }}</td>
                                    <td>{{ $membre['centre'] ?? '—' }}</td>
                                    <td>{{ $membre['jury'] ?? '—' }}</td>
                                    <td>{{ $membre['objet'] ?? '—' }}</td>
                                    <td>{{ $membre['type_convocation'] ?? '—' }}</td>
                                    <td>{{ $membre['session'] ?? '—' }}</td>
                                    <td>{{ $membre['provenance'] ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ ($membre['dossier_complet'] ?? false) ? 'badge-active' : 'badge-pending' }}">
                                            {{ $membre['dossier_resume'] ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="table-actions-inline">
                                            <button
                                                class="table-action"
                                                type="button"
                                                data-modal-open="modal-voir-dossier"
                                                data-dossier-label="{{ trim(($membre['prenom'] ?? '').' '.($membre['nom'] ?? '')) }} — {{ $membre['centre'] ?? '—' }}"
                                                data-dossier-json="{{ json_encode($membre['dossier'] ?? []) }}"
                                            >
                                                Voir le dossier
                                            </button>
                                            <button
                                                class="table-action"
                                                type="button"
                                                data-modal-open="modal-piece-justificative"
                                                data-piece-convocation-id="{{ $membre['convocation_id'] }}"
                                                data-piece-enseignant-id="{{ $membre['enseignant_id'] }}"
                                                data-piece-centre-id="{{ $membre['centre_id'] }}"
                                                data-piece-label="{{ trim(($membre['prenom'] ?? '').' '.($membre['nom'] ?? '')) }} — {{ $membre['centre'] ?? '—' }}"
                                            >
                                                Ajouter une pièce
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>

                    </table>
                </div>

                @if (empty($membres))
                    {{-- La classe "show" est necessaire ici : .empty-message
                         est display:none par defaut dans app.css, et n'est
                         normalement basculee que par le JS de recherche
                         cote client (filterTable() dans app.js) — jamais au
                         chargement initial de la page, ce qui laissait ce
                         message invisible meme quand le serveur savait deja
                         qu'il n'y avait aucune donnee. --}}
                    <p class="empty-message show">Aucune donnée trouvée.</p>
                @endif

                <div class="convocation-pagination" aria-label="Pagination">

                    @if ($convocations->onFirstPage())
                        <span class="page-btn" aria-disabled="true">←</span>
                    @else
                        <a class="page-btn" href="{{ $convocations->previousPageUrl() }}" aria-label="Page précédente">←</a>
                    @endif

                    @for ($page = 1; $page <= $convocations->lastPage(); $page++)
                        <a
                            class="page-btn {{ $page === $convocations->currentPage() ? 'active' : '' }}"
                            href="{{ $convocations->url($page) }}"
                            data-page-number
                        >
                            {{ $page }}
                        </a>
                    @endfor

                    @if ($convocations->hasMorePages())
                        <a class="page-btn" href="{{ $convocations->nextPageUrl() }}" aria-label="Page suivante">→</a>
                    @else
                        <span class="page-btn" aria-disabled="true">→</span>
                    @endif

                </div>

            </section>

        @endif

        {{-- ============================================================
             MODALE "Ajouter une pièce justificative"
             Partagee par tous les boutons "Ajouter une pièce" du tableau
             (une par membre) : le JS ci-dessous remplit ses champs caches
             (convocation_id/enseignant_id/centre_id) et son libelle a
             partir des attributs data-piece-* du bouton cliqué. Le
             "Dossier de convocation" (5e type) n'a pas de champ fichier :
             il est toujours rattache automatiquement cote back, a partir
             du PDF deja genere pour la convocation — voir
             PieceJustificativesController::attacherDossierConvocation().
        ============================================================ --}}

        <div class="modal-backdrop" id="modal-piece-justificative" data-modal hidden>
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-piece-justificative-title">

                <div class="modal-header">
                    <h2 id="modal-piece-justificative-title">Ajouter une pièce justificative</h2>
                    <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
                </div>

                <p class="modal-piece-membre" data-piece-membre-label></p>

                <form
                    method="POST"
                    action="{{ route('indemnites.pieces-justificatives.deposer') }}"
                    enctype="multipart/form-data"
                    class="import-panel-form"
                    data-piece-form
                >
                    @csrf

                    <input type="hidden" name="convocation_id" data-piece-field="convocation_id">
                    <input type="hidden" name="enseignant_id" data-piece-field="enseignant_id">
                    <input type="hidden" name="centre_id" data-piece-field="centre_id">

                    <div class="import-panel-form-grid">

                        <div class="form-group">
                            <label for="piece-service-fait">Service fait <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-service-fait" type="file" name="service_fait" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="service_fait">
                        </div>

                        <div class="form-group">
                            <label for="piece-ordre-mission">Ordre de mission <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-ordre-mission" type="file" name="ordre_mission" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="ordre_mission">
                        </div>

                        <div class="form-group">
                            <label for="piece-rapport-mission">Rapport de mission <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-rapport-mission" type="file" name="rapport_mission" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="rapport_mission">
                        </div>

                        <div class="form-group">
                            <label for="piece-bulletin-salaire">Bulletin de salaire <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-bulletin-salaire" type="file" name="bulletin_salaire" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="bulletin_salaire">
                        </div>

                        <div class="form-group">
                            <label for="piece-accuse-reception">Accusé de réception <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-accuse-reception" type="file" name="accuse_reception" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="accuse_reception">
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Dossier de convocation <span class="form-group-hint">(joint automatiquement)</span></label>
                        <p class="form-hint">PDF déjà généré pour cette convocation — rien à téléverser ici.</p>
                    </div>

                    {{-- ========================================================
                         RECAP — met a jour en direct (JS) le nombre de
                         fichiers prets sur les 6 attendus : les 5 champs
                         manuels + le dossier de convocation, toujours pret
                         puisqu'il est automatique. Les 5 champs sont
                         "required" : le navigateur bloque deja l'envoi tant
                         qu'ils ne sont pas tous remplis, ce recap est la
                         pour le rendre visible avant meme de cliquer sur
                         "Déposer".
                    ======================================================== --}}

                    <div class="piece-recap" data-piece-recap>
                        <p class="piece-recap-title">
                            Récapitulatif — <span data-piece-recap-count>1</span>/6 fichiers prêts
                        </p>
                        <ul class="piece-recap-list">
                            <li data-piece-recap-item="service_fait">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Service fait
                            </li>
                            <li data-piece-recap-item="ordre_mission">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Ordre de mission
                            </li>
                            <li data-piece-recap-item="rapport_mission">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Rapport de mission
                            </li>
                            <li data-piece-recap-item="bulletin_salaire">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Bulletin de salaire
                            </li>
                            <li data-piece-recap-item="accuse_reception">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Accusé de réception
                            </li>
                            <li class="is-ready">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Dossier de convocation (automatique)
                            </li>
                        </ul>
                    </div>

                    <div class="actions-group">
                        <button class="btn-primary" type="submit" data-piece-submit>
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            Déposer
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ============================================================
             MODALE "Voir le dossier"
             Consultation seule (statut + date + telechargement) — pas de
             validation/rejet ici pour l'instant. Partagee par tous les
             boutons "Voir le dossier" : le JS lit le JSON encode dans
             data-dossier-json du bouton clique et construit les 6 lignes
             (les 5 manuelles + le dossier de convocation auto-rattache,
             une par type attendu meme si non deposee) a la volee.
        ============================================================ --}}

        <div
            class="modal-backdrop"
            id="modal-voir-dossier"
            data-modal
            hidden
            data-dossier-download-url-template="{{ route('indemnites.pieces-justificatives.telecharger', ':id') }}"
        >
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-voir-dossier-title">

                <div class="modal-header">
                    <h2 id="modal-voir-dossier-title">Dossier de pièces justificatives</h2>
                    <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
                </div>

                <p class="modal-piece-membre" data-dossier-membre-label></p>

                <ul class="piece-dossier-list" data-dossier-liste></ul>

            </div>
        </div>

    </section>

</main>

@endsection

{{-- ================================================================
     STYLES — memes classes que index.blade.php de Convocations
     (.filter-panel .actions-group deja stylees globalement dans app.css),
     juste le necessaire propre a cette page.
================================================================ --}}

@push('styles')
<style>

    .convocation-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 14px 18px;
        border-top: 1px solid var(--border-soft);
    }

    /* Modale "Ajouter une pièce justificative" — memes classes que la
       modale d'import de convocations/index.blade.php (pas de style
       partage globalement dans app.css pour les modales, il faut les
       redefinir sur chaque page qui en a besoin). */

    .modal-backdrop {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.5);
        z-index: 1000;
    }

    .modal-backdrop[hidden] {
        display: none;
    }

    .modal-dialog {
        width: 100%;
        /* Plus large que la modale d'import de convocations (520px) : les
           4 champs fichiers sont affiches sur 2 colonnes ici, avec un
           libelle assez long (nom + format/taille entre parentheses). */
        max-width: 640px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        padding: 20px 22px;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.1rem;
    }

    .modal-close {
        border: 0;
        background: transparent;
        font-size: 1.4rem;
        line-height: 1;
        cursor: pointer;
        color: inherit;
    }

    .modal-piece-membre {
        margin: 0 0 16px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .import-panel-form {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .import-panel-form .form-group {
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .import-panel-form .form-control {
        width: 100%;
    }

    /* Les 4 champs fichiers (service fait / ordre de mission / rapport de
       mission / bulletin de salaire) sur 2 colonnes plutot qu'empiles. */
    .import-panel-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    @media (max-width: 560px) {
        .import-panel-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .form-group-hint {
        font-weight: 400;
        font-size: 12px;
        color: var(--text-muted);
    }

    .import-panel-form .form-hint {
        margin: 0;
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .import-panel-form .actions-group {
        margin: 0;
    }

    .import-panel-form .actions-group .btn-primary {
        width: 100%;
    }

    .piece-recap {
        padding: 12px 14px;
        border: 1px solid var(--border-soft);
        border-radius: 8px;
        background: #f8fafc;
    }

    .piece-recap-title {
        margin: 0 0 8px;
        font-size: 13px;
        font-weight: 800;
    }

    .piece-recap-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 13px;
    }

    .piece-recap-list li {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
    }

    .piece-recap-list li i {
        color: #cbd5e1;
    }

    .piece-recap-list li.is-ready {
        color: inherit;
        font-weight: 600;
    }

    .piece-recap-list li.is-ready i {
        color: var(--green, #16a34a);
    }

    .piece-dossier-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .piece-dossier-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid var(--border-soft);
        border-radius: 8px;
    }

    .piece-dossier-item-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .piece-dossier-item-label {
        font-weight: 700;
        font-size: 13px;
    }

    .piece-dossier-item-date {
        font-size: 12px;
        color: var(--text-muted);
    }

    .piece-dossier-item-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

</style>
@endpush

{{-- ================================================================
     SCRIPT — soumet le formulaire de filtres des qu'une valeur est
     choisie dans un des menus deroulants (session/objet/centre) : pas
     besoin de cliquer sur un bouton "Filtrer" separe.
================================================================ --}}

@push('scripts')
<script>
    (function () {
        "use strict";

        document.querySelectorAll("[data-filter-auto-submit]").forEach(function (champ) {
            champ.addEventListener("change", function () {
                champ.form.submit();
            });
        });
    })();
</script>

{{-- ================================================================
     SCRIPT — modale "Ajouter une pièce justificative" : ouverture/
     fermeture (meme mecanique que la modale d'import de
     convocations/index.blade.php, redefinie ici puisque chaque page gere
     ses propres scripts), pre-remplissage des champs caches a partir du
     bouton "Ajouter une pièce" clique, et etat "en cours" sur le bouton
     "Déposer" (meme principe que le bouton "Importer" / "Enregistrer" —
     verrou explicite + spinner, voir index.blade.php de Convocations et
     convocation-wizard.js).
================================================================ --}}

<script>
    (function () {
        "use strict";

        function ouvrirModal(modal) {
            modal.hidden = false;
        }

        function fermerModal(modal) {
            modal.hidden = true;
        }

        var formulairePiece = document.querySelector("[data-piece-form]");
        var recapPiece = document.querySelector("[data-piece-recap]");
        var compteurRecap = recapPiece ? recapPiece.querySelector("[data-piece-recap-count]") : null;

        // 100 Ko max par fichier (voir demande utilisateur) — verifie des
        // la selection, cote client, plutot que d'attendre le retour du
        // serveur apres l'envoi : memes classes (.field-error/.is-invalid)
        // que le wizard de convocation (convocation-wizard.js), pour rester
        // coherent avec le reste de l'appli.
        var TAILLE_MAX_OCTETS = 100 * 1024;

        function formaterKo(octets) {
            return Math.round(octets / 1024) + " Ko";
        }

        function afficherErreurChamp(champ, message) {
            var groupe = champ.closest(".form-group");

            if (!groupe) {
                return;
            }

            var erreur = groupe.querySelector(".field-error");

            if (!erreur) {
                erreur = document.createElement("p");
                erreur.className = "field-error";
                groupe.appendChild(erreur);
            }

            champ.classList.add("is-invalid");
            champ.setAttribute("aria-invalid", "true");
            erreur.textContent = message;
        }

        function effacerErreurChamp(champ) {
            var groupe = champ.closest(".form-group");
            var erreur = groupe ? groupe.querySelector(".field-error") : null;

            champ.classList.remove("is-invalid");
            champ.setAttribute("aria-invalid", "false");

            if (erreur) {
                erreur.textContent = "";
            }
        }

        // Renvoie false et vide le champ si le fichier depasse 100 Ko —
        // ainsi mettreAJourRecap() ne le compte jamais comme "pret" et le
        // formulaire ne peut pas etre soumis avec ce fichier.
        function verifierTailleFichier(champ) {
            var fichier = champ.files && champ.files[0];

            if (!fichier) {
                effacerErreurChamp(champ);

                return true;
            }

            if (fichier.size > TAILLE_MAX_OCTETS) {
                afficherErreurChamp(
                    champ,
                    "Fichier trop volumineux (" + formaterKo(fichier.size) + ") — diminuez sa taille à 100 Ko maximum."
                );
                champ.value = "";

                return false;
            }

            effacerErreurChamp(champ);

            return true;
        }

        function mettreAJourRecap() {
            if (!formulairePiece || !recapPiece) {
                return;
            }

            var prets = 1; // le dossier de convocation compte toujours

            formulairePiece.querySelectorAll("[data-piece-fichier]").forEach(function (champ) {
                var estPret = champ.files && champ.files.length > 0;
                var item = recapPiece.querySelector(
                    '[data-piece-recap-item="' + champ.getAttribute("data-piece-fichier") + '"]'
                );

                if (estPret) {
                    prets += 1;
                }

                if (!item) {
                    return;
                }

                item.classList.toggle("is-ready", estPret);

                var icone = item.querySelector("i");
                if (icone) {
                    icone.className = estPret ? "fa-solid fa-circle-check" : "fa-regular fa-circle";
                }
            });

            if (compteurRecap) {
                compteurRecap.textContent = String(prets);
            }
        }

        if (formulairePiece) {
            formulairePiece.querySelectorAll("[data-piece-fichier]").forEach(function (champ) {
                champ.addEventListener("change", function () {
                    verifierTailleFichier(champ);
                    mettreAJourRecap();
                });
            });
        }

        // Statuts possibles d'une piece (voir piece_justificatives.statut
        // cote back) : "null" = pas encore deposee du tout.
        var LIBELLES_STATUT_DOSSIER = {
            depose: { label: "Déposé", classe: "badge-primary" },
            valide: { label: "Validé", classe: "badge-active" },
            rejete: { label: "Rejeté", classe: "badge-inactive" },
        };

        function formaterDateDepot(date) {
            if (!date) {
                return "";
            }

            var parties = date.substring(0, 10).split("-");

            return parties.length === 3 ? parties.reverse().join("/") : date;
        }

        function remplirDossier(modal, bouton) {
            var libelle = modal.querySelector("[data-dossier-membre-label]");
            if (libelle) {
                libelle.textContent = bouton.getAttribute("data-dossier-label") || "";
            }

            var liste = modal.querySelector("[data-dossier-liste]");
            if (!liste) {
                return;
            }

            liste.innerHTML = "";

            var dossier = [];
            try {
                dossier = JSON.parse(bouton.getAttribute("data-dossier-json") || "[]");
            } catch (erreur) {
                dossier = [];
            }

            var urlTemplate = modal.getAttribute("data-dossier-download-url-template") || "";

            dossier.forEach(function (piece) {
                var item = document.createElement("li");
                item.className = "piece-dossier-item";

                var infos = document.createElement("div");
                infos.className = "piece-dossier-item-info";

                var label = document.createElement("span");
                label.className = "piece-dossier-item-label";
                label.textContent = piece.label;
                infos.appendChild(label);

                if (piece.date_depot) {
                    var date = document.createElement("span");
                    date.className = "piece-dossier-item-date";
                    date.textContent = "Déposé le " + formaterDateDepot(piece.date_depot);
                    infos.appendChild(date);
                }

                item.appendChild(infos);

                var actions = document.createElement("div");
                actions.className = "piece-dossier-item-actions";

                var infoStatut = LIBELLES_STATUT_DOSSIER[piece.statut] || { label: "Non déposé", classe: "badge-pending" };
                var badge = document.createElement("span");
                badge.className = "badge " + infoStatut.classe;
                badge.textContent = infoStatut.label;
                actions.appendChild(badge);

                if (piece.id && urlTemplate) {
                    // Ouvre dans un nouvel onglet : le fichier s'affiche
                    // (visionneuse du navigateur) plutot que de forcer un
                    // telechargement immediat — l'utilisateur peut ensuite
                    // le telecharger lui-meme depuis cette visionneuse s'il
                    // le souhaite, sans quitter la page/la modale.
                    var lien = document.createElement("a");
                    lien.className = "table-action";
                    lien.href = urlTemplate.replace(":id", piece.id);
                    lien.target = "_blank";
                    lien.rel = "noopener";
                    lien.textContent = "Voir / Télécharger";
                    actions.appendChild(lien);
                }

                item.appendChild(actions);
                liste.appendChild(item);
            });
        }

        document.querySelectorAll("[data-modal-open]").forEach(function (bouton) {
            var modal = document.getElementById(bouton.getAttribute("data-modal-open"));

            if (!modal) {
                return;
            }

            bouton.addEventListener("click", function () {
                if (bouton.hasAttribute("data-dossier-json")) {
                    remplirDossier(modal, bouton);
                    ouvrirModal(modal);

                    return;
                }

                // Pre-remplit les champs caches (convocation/enseignant/
                // centre) et le libelle du membre a partir des attributs
                // data-piece-* du bouton clique — un seul formulaire/modale
                // partage par toutes les lignes du tableau.
                var champsParAttribut = {
                    "convocation_id": "pieceConvocationId",
                    "enseignant_id": "pieceEnseignantId",
                    "centre_id": "pieceCentreId",
                };

                modal.querySelectorAll("[data-piece-field]").forEach(function (champ) {
                    var cle = champsParAttribut[champ.getAttribute("data-piece-field")];
                    champ.value = (cle && bouton.dataset[cle]) || "";
                });

                var libelle = modal.querySelector("[data-piece-membre-label]");
                if (libelle) {
                    libelle.textContent = bouton.getAttribute("data-piece-label") || "";
                }

                // Repart de zero a chaque ouverture : sinon un fichier
                // choisi (ou une erreur de taille affichee) pour un membre
                // resterait present si on ouvre la modale pour un AUTRE
                // membre juste apres.
                if (formulairePiece) {
                    formulairePiece.reset();
                    formulairePiece.querySelectorAll("[data-piece-fichier]").forEach(effacerErreurChamp);
                    mettreAJourRecap();
                }

                ouvrirModal(modal);
            });
        });

        document.querySelectorAll("[data-modal]").forEach(function (modal) {
            modal.addEventListener("click", function (event) {
                if (event.target === modal) {
                    fermerModal(modal);
                }
            });

            modal.querySelectorAll("[data-modal-close]").forEach(function (bouton) {
                bouton.addEventListener("click", function () {
                    fermerModal(modal);
                });
            });
        });

        document.addEventListener("keydown", function (event) {
            if (event.key !== "Escape") {
                return;
            }

            document.querySelectorAll("[data-modal]:not([hidden])").forEach(fermerModal);
        });

        var boutonPiece = document.querySelector("[data-piece-submit]");
        var envoiPieceEnCours = false;

        if (formulairePiece && boutonPiece) {
            formulairePiece.addEventListener("submit", function (event) {
                if (envoiPieceEnCours) {
                    event.preventDefault();

                    return;
                }

                envoiPieceEnCours = true;

                boutonPiece.disabled = true;
                boutonPiece.setAttribute("aria-busy", "true");
                boutonPiece.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Dépôt en cours…';
            });
        }
    })();
</script>
@endpush
