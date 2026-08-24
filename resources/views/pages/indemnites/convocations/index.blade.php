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


        <div class="stats-grid four" data-ajax-region="stats">

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

    

        <x-module-indemnite type="modal" id="import-convocations" title="Importer une convocation" :open="! empty($importAvertissements)">

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
                    <label for="import-fichier">Fichier (Word)</label>
                    <div class="dropzone" data-dropzone>
                        <div class="dropzone-visual">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                            <span class="dropzone-text" data-dropzone-text>Cliquez pour joindre un fichier</span>
                        </div>
                        <input
                            class="dropzone-input"
                            id="import-fichier"
                            name="fichier"
                            type="file"
                            accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required
                        >
                    </div>
                </div>

                <div class="actions-group">
                    <button class="btn-primary" type="submit" data-import-submit>
                        Importer
                    </button>
                </div>
            </form>

        </x-module-indemnite>

        {{-- ============================================================
             FILTRES
        ============================================================ --}}

      
        <form
            id="convocationFilterForm"
            class="filter-panel"
            method="GET"
            action="{{ route('indemnites.convocations') }}"
            aria-label="Filtres de la page"
            data-filtres-coherents="{{ route('indemnites.filtres-options') }}"
            data-filtres-instantanes
        >

            <div class="form-group">
                <label for="convocation-filter-date">Date</label>
                <select class="form-control" id="convocation-filter-date" name="date">
                    <option value="">Toutes les dates</option>
                    @foreach ($filtreOptions['dates'] ?? [] as $dateOption)
                        <option value="{{ $dateOption }}" @selected(request('date') === $dateOption)>
                            {{ \Illuminate\Support\Carbon::parse($dateOption)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="convocation-filter-objet">Objet</label>
                <select class="form-control" id="convocation-filter-objet" name="objet">
                    <option value="">Tous les objets</option>
                    @foreach ($filtreOptions['objets'] ?? [] as $objetOption)
                        <option value="{{ $objetOption }}" @selected(request('objet') === $objetOption)>
                            {{ $objetOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="convocation-filter-metier">Métier</label>
                <select class="form-control" id="convocation-filter-metier" name="metier">
                    <option value="">Tous les métiers</option>
                    @foreach ($filtreOptions['metiers'] ?? [] as $metierOption)
                        <option value="{{ $metierOption }}" @selected(request('metier') === $metierOption)>
                            {{ $metierOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="convocation-filter-centre">Centre</label>
                <select class="form-control" id="convocation-filter-centre" name="centre">
                    <option value="">Tous les centres</option>
                    @foreach ($filtreOptions['centres'] ?? [] as $centreOption)
                        <option value="{{ $centreOption }}" @selected(request('centre') === $centreOption)>
                            {{ $centreOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="actions-group">
                <button class="btn-secondary" type="submit">
                    Filtrer
                </button>

                <a class="btn-secondary" href="{{ route('indemnites.convocations') }}" data-ajax-lien>
                    Réinitialiser
                </a>
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

                    {{-- data-ajax-region="lignes" : seul le <tbody> est
                         remplace au changement de filtre/page
                         (indemnites-ajax-resultats.js) — le <thead> (case
                         "tout selectionner") reste statique pour ne pas
                         perdre son ecouteur JS. --}}
                    <tbody data-ajax-region="lignes">
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
                                    <x-module-indemnite type="statut-convocation" :statut="$ligne['statut'] ?? null" />
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

            {{-- "show" necessaire : .empty-message est display:none par
                 defaut dans app.css, et n'est normalement bascule que par
                 le JS de recherche cote client (filterTable() dans
                 app.js) — jamais au chargement initial de la page.
                 Toujours rendu (pas de condition Blade) : la visibilite
                 bascule via la classe "show", pour que data-ajax-region
                 puisse retrouver cet element meme quand il n'y a aucune
                 ligne (une condition qui retire l'element du DOM le
                 rendrait impossible a cibler pour le remplacement AJAX).
                 Demande utilisatrice : les convocations s'affichent par
                 defaut (aucun filtre requis) — ce message n'apparait donc
                 que s'il n'y a vraiment aucune convocation en base, jamais
                 juste parce qu'aucun filtre n'est choisi. --}}
            <p class="empty-message {{ empty($centresLignes) ? 'show' : '' }}" data-ajax-region="empty-message">Aucune donnée trouvée.</p>

            <div class="convocation-pagination" aria-label="Pagination" data-ajax-region="pagination">

                @if ($convocations->onFirstPage())
                    <span class="page-btn" aria-disabled="true">←</span>
                @else
                    <a class="page-btn" href="{{ $convocations->previousPageUrl() }}" aria-label="Page précédente" data-ajax-lien>←</a>
                @endif

                @for ($page = 1; $page <= $convocations->lastPage(); $page++)
                    <a
                        class="page-btn {{ $page === $convocations->currentPage() ? 'active' : '' }}"
                        href="{{ $convocations->url($page) }}"
                        data-page-number
                        data-ajax-lien
                    >
                        {{ $page }}
                    </a>
                @endfor

                @if ($convocations->hasMorePages())
                    <a class="page-btn" href="{{ $convocations->nextPageUrl() }}" aria-label="Page suivante" data-ajax-lien>→</a>
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

    /* Zone de depot façon "dropzone" pour le fichier Word — remplace le
       <input type="file"> brut du navigateur. L'input reste en place, en
       pleine largeur/hauteur mais invisible (opacity, pas display:none qui
       exclurait le champ "required" de la validation native dans certains
       navigateurs) : il capte le clic sur toute la zone, pas besoin de JS
       pour ouvrir le selecteur de fichier. */
    .dropzone {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 12px;
        border: 1.5px dashed #cbd5e1;
        border-radius: 10px;
        background: #fafbfc;
        text-align: center;
        transition: border-color .15s ease, background-color .15s ease;
    }

    .dropzone:hover {
        border-color: var(--blue, #2563eb);
        background: #f4f7ff;
    }

    .dropzone-visual {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        pointer-events: none;
    }

    .dropzone-visual i {
        font-size: 22px;
        color: #94a3b8;
    }

    .dropzone-text {
        font-size: 13px;
        color: var(--text-muted);
    }

    .dropzone-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .dropzone.has-file {
        border-style: solid;
        border-color: var(--blue, #2563eb);
        background: #eff4ff;
    }

    .dropzone.has-file .dropzone-visual i {
        color: var(--blue, #2563eb);
    }

    .dropzone.has-file .dropzone-text {
        color: #1e293b;
        font-weight: 600;
    }

    .checkbox-cell {
        width: 40px;
        text-align: center;
    }

  
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

        document.querySelectorAll("[data-dropzone] [data-piece-fichier], [data-dropzone] input[type=\"file\"]").forEach(function (champ) {
            var zone = champ.closest("[data-dropzone]");
            var texte = zone ? zone.querySelector("[data-dropzone-text]") : null;

            champ.addEventListener("change", function () {
                var fichier = champ.files && champ.files[0];

                if (zone) {
                    zone.classList.toggle("has-file", !!fichier);
                }

                if (texte) {
                    texte.textContent = fichier ? fichier.name : "Cliquez pour joindre un fichier";
                }
            });
        });
    })();
</script>

<script src="{{ asset('assets/js/indemnites-filtres-coherents.js') }}" defer></script>
<script src="{{ asset('assets/js/indemnites-ajax-resultats.js') }}" defer></script>


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

.


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

        // Le tbody (data-ajax-region="lignes") est remplace sans recharger
        // la page a chaque filtre/page (indemnites-ajax-resultats.js) : les
        // nouvelles lignes arrivent avec des cases toutes decochees, il
        // faut donc reevaluer l'etat de "tout selectionner"/"Supprimer la
        // selection" (la delegation sur "change" ci-dessus gere deja les
        // cases elles-memes, sans re-binding necessaire).
        document.addEventListener("sicore:ajax-regions-mises-a-jour", updateState);
    })();
</script>
@endpush