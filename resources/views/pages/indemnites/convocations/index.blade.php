@extends('layouts.app')

@section('title', 'SICORE - Gestion des convocations')

@section('content')

<main class="main-content">

    <x-topbar
        title="Gestion des convocations"
        subtitle="Indemnites > Convocations"
        icon="fa-solid fa-envelope-open-text"
        search-id="convocationSearch"
        search-placeholder="Rechercher…"
        filter-target="#convocationsTable"
    />

    <section class="content-area">

        {{-- ============================================================
             STATISTIQUES
             NB: $stats est optionnel. S'il n'est pas envoyé par le
             controller, les valeurs retombent sur 0 / le total du
             paginator pour ne jamais planter la vue.
        ============================================================ --}}

        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Convocations</p>
                    <p class="stat-value">{{ $stats['total'] ?? $convocations->total() }}</p>
                    <p class="stat-note">Période active</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Envoyées</p>
                    <p class="stat-value">{{ $stats['envoyees'] ?? 0 }}</p>
                    <p class="stat-note">Statut final</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </span>
            </article>

           

            <article class="stat-card">
                <div>
                    <p class="stat-label">Brouillons</p>
                    <p class="stat-value">{{ $stats['brouillons'] ?? 0 }}</p>
                    <p class="stat-note">À finaliser</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Clôturées</p>
                    <p class="stat-value">{{ $stats['cloturees'] ?? 0 }}</p>
                    <p class="stat-note">Traitées</p>
                </div>
                <span class="stat-icon red">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        {{-- ============================================================
             ACTIONS
        ============================================================ --}}

        <div class="actions-row">
            <p class="breadcrumb">Gestion des indemnités &gt; Convocations</p>
            <div class="actions-group">
                <a class="btn-primary" href="{{ route('indemnites.convocations.create') }}">
                    Nouvelle convocation
                </a>

                <button class="btn-secondary" type="button" data-modal-open="import-convocations">
                    Importer
                </button>

                <button class="btn-secondary btn-bulk-delete" type="button" data-bulk-delete-button disabled>
                    Supprimer la sélection
                </button>
            </div>
        </div>

    

        <div class="modal-backdrop" id="import-convocations" data-modal hidden>
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="import-convocations-title">

                <div class="modal-header">
                    <h2 id="import-convocations-title">Importer une convocation</h2>
                    <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
                </div>

                <a class="btn-secondary modal-modele-link" href="{{ route('indemnites.convocations.modele-word') }}">
                    Télécharger le modèle Word
                </a>

                @if (! empty($importAvertissements))
                    <div class="form-errors" role="alert">
                        <p><strong>Points à vérifier sur le dernier import :</strong></p>
                        <ul>
                            @foreach ($importAvertissements as $avertissement)
                                <li>{{ $avertissement }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('indemnites.convocations.import') }}"
                    enctype="multipart/form-data"
                    class="import-panel-form"
                    data-import-form
                >
                    @csrf

                    <div class="form-group">
                        <label for="import-type-convocation">Type de convocation</label>
                        <select
                            class="form-control"
                            id="import-type-convocation"
                            name="type_convocation_id"
                            required
                        >
                            <option value="">Sélectionner</option>
                            @foreach ($typesConvocation ?? [] as $type)
                                <option value="{{ $type['id'] }}">{{ $type['libelle'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="import-fichier">Fichier (Word)</label>
                        <input
                            class="form-control"
                            id="import-fichier"
                            name="fichier"
                            type="file"
                            accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required
                        >
                    </div>

                    <div class="actions-group">
                        <button class="btn-primary" type="submit" data-import-submit>
                            Importer
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ============================================================
             FILTRES
        ============================================================ --}}

        <form
            id="convocationFilterForm"
            class="filter-panel"
            method="GET"
            action="{{ route('indemnites.convocations') }}"
            aria-label="Filtres de la page"
        >

            <div class="form-group">
                <label for="convocation-filter-date">Date</label>
                <input
                    class="form-control"
                    id="convocation-filter-date"
                    type="date"
                    name="date"
                    value="{{ request('date') }}"
                >
            </div>

            <div class="form-group">
                <label for="convocation-filter-objet">Objet</label>
                <input
                    class="form-control"
                    id="convocation-filter-objet"
                    type="text"
                    name="objet"
                    placeholder="Rechercher un objet"
                    value="{{ request('objet') }}"
                >
            </div>

            <div class="form-group">
                <label for="convocation-filter-metier">Métier</label>
                <input
                    class="form-control"
                    id="convocation-filter-metier"
                    type="text"
                    name="metier"
                    placeholder="Rechercher un métier"
                    value="{{ request('metier') }}"
                >
            </div>

            <div class="form-group">
                <label for="convocation-filter-centre">Centre</label>
                <input
                    class="form-control"
                    id="convocation-filter-centre"
                    type="text"
                    name="centre"
                    placeholder="Rechercher un centre"
                    value="{{ request('centre') }}"
                >
            </div>

            <div class="actions-group">
                <button class="btn-secondary" type="submit">
                    Filtrer
                </button>

                @if (request()->hasAny(['date', 'objet', 'metier', 'centre']))
                    <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">
                        Réinitialiser
                    </a>
                @endif
            </div>

        </form>

    

        <section class="table-card">

            <div class="table-responsive">
                <table class="table" id="convocationsTable">

                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" data-select-all aria-label="Tout sélectionner">
                            </th>
                            <th>Objet</th>
                            <th>Centre</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th class="actions-cell">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($centresLignes as $ligne)
                            <tr>
                                <td class="checkbox-cell">
                                    @if (! empty($ligne['convocation_id']))
                                        <input type="checkbox" data-row-checkbox value="{{ $ligne['convocation_id'] }}" aria-label="Sélectionner cette ligne">
                                    @endif
                                </td>
                                <td>{{ $ligne['objet'] ?? '—' }}</td>
                                <td>{{ $ligne['centre'] ?? '—' }}</td>
                                <td>{{ $ligne['date_debut'] ? \Illuminate\Support\Carbon::parse($ligne['date_debut'])->format('d/m/Y') : '—' }}</td>
                                <td>{{ $ligne['date_fin'] ? \Illuminate\Support\Carbon::parse($ligne['date_fin'])->format('d/m/Y') : '—' }}</td>
                                <td>
                                    @php
                                        $statutBadges = [
                                            'brouillon'    => ['badge-pending', 'Brouillon'],
                                            'emise'        => ['badge-primary', 'Émise'],
                                            'envoyee'      => ['badge-active', 'Envoyée'],
                                            'cloturee'     => ['badge-inactive', 'Clôturée'],
                                        ];
                                        [$badgeClass, $badgeLabel] = $statutBadges[$ligne['statut'] ?? null]
                                            ?? ['badge-pending', ucfirst($ligne['statut'] ?? '—')];
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </td>
                                <td class="actions-cell">
                                    <div class="table-actions-inline">
                                        @if (! empty($ligne['centre_id']))
                                            <a class="table-action" href="{{ route('indemnites.convocations.centres.show', [$ligne['convocation_id'], $ligne['centre_id']]) }}">
                                                Voir
                                            </a>
                                            <a class="table-action" href="{{ route('indemnites.convocations.centres.edit', [$ligne['convocation_id'], $ligne['centre_id']]) }}">
                                                Modifier
                                            </a>
                                        @else
                                            <a class="table-action" href="{{ route('indemnites.convocations.show', $ligne['convocation_id']) }}">
                                                Voir
                                            </a>
                                            <a class="table-action" href="{{ route('indemnites.convocations.edit', $ligne['convocation_id']) }}">
                                                Modifier
                                            </a>
                                        @endif
                                        @if (! empty($ligne['centre_id']))
                                            {{-- Une ligne = un centre : ne supprime QUE ce centre de CETTE
                                                 convocation, jamais les autres centres du meme objet — voir
                                                 ConvocationCentreController::destroy() cote back. Le message
                                                 nomme explicitement le centre ET la convocation concernes, et
                                                 previent si c'est le DERNIER centre : dans ce cas la
                                                 convocation entiere est supprimee avec (voir
                                                 'dernier_centre' dans construireLignesCentres()), pas de
                                                 fiche "fantome" sans centre laissee dans la liste. --}}
                                            @php
                                                $messageConfirmation = ! empty($ligne['dernier_centre'])
                                                    ? 'Supprimer le centre « '.($ligne['centre'] ?? '—').' » ? C\'est le dernier centre de la convocation « '.($ligne['objet'] ?? '—').' » : la convocation entière sera donc supprimée aussi.'
                                                    : 'Supprimer le centre « '.($ligne['centre'] ?? '—').' » de la convocation « '.($ligne['objet'] ?? '—').' » ? Les autres centres de cette convocation ne seront pas supprimés.';
                                            @endphp
                                            <form
                                                method="POST"
                                                action="{{ route('indemnites.convocations.centres.destroy', [$ligne['convocation_id'], $ligne['centre_id']]) }}"
                                                onsubmit="return confirm({{ \Illuminate\Support\Js::from($messageConfirmation) }});"
                                                style="display:inline;"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="table-action danger" type="submit">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @else
                                            @php
                                                $messageConfirmation = 'Supprimer définitivement la convocation « '.($ligne['objet'] ?? '—').' » (aucun centre n\'y est rattaché) ?';
                                            @endphp
                                            <form
                                                method="POST"
                                                action="{{ route('indemnites.convocations.destroy', $ligne['convocation_id']) }}"
                                                onsubmit="return confirm({{ \Illuminate\Support\Js::from($messageConfirmation) }});"
                                                style="display:inline;"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="table-action danger" type="submit">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>

                </table>
            </div>

            @if (empty($centresLignes))
                {{-- "show" necessaire : .empty-message est display:none par
                     defaut dans app.css, et n'est normalement bascule que
                     par le JS de recherche cote client (filterTable() dans
                     app.js) — jamais au chargement initial de la page. --}}
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

    </section>

</main>

@endsection

{{-- ================================================================
     STYLES — panneau d'import compact (voir bouton "Importer" dans
     .actions-row plus haut).
================================================================ --}}

@push('styles')
<style>

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
        max-width: 520px;
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

    .modal-modele-link {
        display: block;
        width: 100%;
        text-align: center;
        margin-bottom: 18px;
        color: #ffffff;
        background: var(--blue, #2563eb);
        border-color: var(--blue, #2563eb);
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

    .import-panel-form .actions-group {
        margin: 0;
    }

    .import-panel-form .actions-group .btn-primary {
        width: 100%;
    }

    .checkbox-cell {
        width: 40px;
        text-align: center;
    }

    /* Filtrer / Reinitialiser : sur leur propre ligne (span sur les 4
       colonnes de .filter-panel, cf. app.css), alignes en bas a droite du
       panneau plutot qu'a gauche sous les champs, avec des boutons plus
       compacts que le style par defaut (min-height 40px / padding 10px 18px). */
    #convocationFilterForm .actions-group {
        grid-column: 1 / -1;
        justify-content: flex-end;
        margin-top: 2px;
    }

    #convocationFilterForm .actions-group .btn-secondary {
        min-height: 30px;
        padding: 5px 12px;
        font-size: 12.5px;
    }

    /* Meme rendu que .pagination (app.css), sous un nom de classe
       different pour ne pas etre capturee par setupPagination() dans
       app.js (voir commentaire au-dessus du bloc pagination). */
    .convocation-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 14px 18px;
        border-top: 1px solid var(--border-soft);
    }

    .btn-bulk-delete {
        color: #b91c1c;
        border-color: #fca5a5;
    }

    .btn-bulk-delete:disabled {
        color: inherit;
        border-color: inherit;
        opacity: 0.5;
        cursor: not-allowed;
    }

</style>
@endpush

{{-- ================================================================
     SCRIPT — ouverture/fermeture de la modal d'import (bouton
     "Importer", bouton de fermeture, clic sur l'overlay, touche Échap).
================================================================ --}}

@push('scripts')
<script>
    (function () {
        "use strict";

        function ouvrirModal(modal) {
            modal.hidden = false;
        }

        function fermerModal(modal) {
            modal.hidden = true;
        }

        document.querySelectorAll("[data-modal-open]").forEach(function (bouton) {
            var modal = document.getElementById(bouton.getAttribute("data-modal-open"));

            if (!modal) {
                return;
            }

            bouton.addEventListener("click", function () {
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

        @if (! empty($importAvertissements))
            // Rouvre automatiquement la modal apres un import : sinon les
            // avertissements ("agent non reconnu", ...) restent invisibles
            // dans la modal fermee et l'utilisateur croit l'import parfait.
            var modaleImport = document.getElementById("import-convocations");
            if (modaleImport) {
                ouvrirModal(modaleImport);
            }
        @endif
    })();
</script>

{{-- ================================================================
     SCRIPT — etat "chargement" du bouton "Importer" : le fichier Word
     peut prendre quelques secondes a etre lu et traite cote back (voir
     ConvocationImportController), et sans retour visuel un double-clic
     pendant ce temps soumettait le formulaire une seconde fois - meme
     souci que le bouton "Enregistrer" du wizard (convocation-wizard.js).
================================================================ --}}

<script>
    (function () {
        "use strict";

        var formulaireImport = document.querySelector("[data-import-form]");
        var boutonImport = document.querySelector("[data-import-submit]");

        if (!formulaireImport || !boutonImport) {
            return;
        }

        // Verrou explicite (pas seulement "disabled" sur le bouton) :
        // bloque tout second envoi meme si le style "disabled" n'a pas eu
        // le temps de s'afficher (double-clic tres rapide, navigateur lent
        // a repeindre) - le premier "submit" pose le verrou, tout "submit"
        // suivant tant qu'il est pose est annule avant meme de partir.
        var envoiEnCours = false;

        formulaireImport.addEventListener("submit", function (event) {
            if (envoiEnCours) {
                event.preventDefault();

                return;
            }

            envoiEnCours = true;

            boutonImport.disabled = true;
            boutonImport.setAttribute("aria-busy", "true");
            boutonImport.dataset.labelOriginal = boutonImport.innerHTML;
            boutonImport.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Import en cours…';
        });
    })();
</script>

{{-- ================================================================
     SCRIPT — selection multiple (cases a cocher) + suppression
     groupee. Une meme convocation peut apparaitre sur plusieurs lignes
     (une par centre) : on deduplique cote back (voir
     destroyMultiple()), donc pas besoin de s'en soucier ici.
================================================================ --}}

<script>
    (function () {
        "use strict";

        var selectAll = document.querySelector("[data-select-all]");
        var deleteButton = document.querySelector("[data-bulk-delete-button]");

        function getRowCheckboxes() {
            return Array.prototype.slice.call(
                document.querySelectorAll("[data-row-checkbox]"),
            );
        }

        function updateState() {
            var boxes = getRowCheckboxes();
            var checked = boxes.filter(function (box) {
                return box.checked;
            });

            if (deleteButton) {
                deleteButton.disabled = checked.length === 0;
                deleteButton.textContent =
                    checked.length > 0
                        ? "Supprimer la sélection (" + checked.length + ")"
                        : "Supprimer la sélection";
            }

            if (selectAll) {
                selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener("change", function () {
                getRowCheckboxes().forEach(function (box) {
                    box.checked = selectAll.checked;
                });

                updateState();
            });
        }

        document.addEventListener("change", function (event) {
            if (event.target.matches && event.target.matches("[data-row-checkbox]")) {
                updateState();
            }
        });

        if (deleteButton) {
            deleteButton.addEventListener("click", function () {
                var ids = getRowCheckboxes()
                    .filter(function (box) {
                        return box.checked;
                    })
                    .map(function (box) {
                        return box.value;
                    });

                if (ids.length === 0) {
                    return;
                }

                var confirmation =
                    ids.length === 1
                        ? "Supprimer définitivement cette convocation ?"
                        : "Supprimer définitivement ces " + ids.length + " convocations ?";

                if (!window.confirm(confirmation)) {
                    return;
                }

                var form = document.createElement("form");
                form.method = "POST";
                form.action = "{{ route('indemnites.convocations.destroy-multiple') }}";
                form.style.display = "none";

                var csrf = document.createElement("input");
                csrf.type = "hidden";
                csrf.name = "_token";
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                var methode = document.createElement("input");
                methode.type = "hidden";
                methode.name = "_method";
                methode.value = "DELETE";
                form.appendChild(methode);

                ids.forEach(function (id) {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "ids[]";
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        }

        updateState();
    })();
</script>
@endpush