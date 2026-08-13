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

                <button class="btn-secondary" type="button" data-import-toggle>
                    Importer
                </button>

                <button class="btn-secondary" type="button">
                    Exporter
                </button>
            </div>
        </div>

        {{-- ============================================================
             OPTION A — IMPORT D'UN FICHIER DE CONVOCATIONS (DECPC)
             Panneau compact, masqué par défaut, ouvert par le bouton
             "Importer" ci-dessus (aligné avec Exporter / Nouvelle
             convocation, plus de détails superflus sur le format CSV
             attendu — voir GUIDE-IMPORT-CONVOCATIONS.md si besoin).
        ============================================================ --}}

        <div id="import-convocations" class="import-panel" data-import-panel hidden>

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
            >
                @csrf

                <div class="form-group">
                    <label for="import-fichier">Fichier (CSV)</label>
                    <input
                        class="form-control"
                        id="import-fichier"
                        name="fichier"
                        type="file"
                        accept=".csv,text/csv,text/plain"
                        required
                    >
                </div>

                <div class="actions-group">
                    <button class="btn-primary" type="submit">
                        Importer
                    </button>
                </div>
            </form>

        </div>

        {{-- ============================================================
             FILTRES
        ============================================================ --}}

        <section class="filter-panel" aria-label="Filtres de la page">

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
                <label for="convocation-filter-statut">Statut</label>
                <select class="form-control" id="convocation-filter-statut" name="statut">
                    <option value="">Tous</option>
                    <option value="brouillon" @selected(request('statut') === 'brouillon')>Brouillon</option>
                    <option value="emise" @selected(request('statut') === 'emise')>Émise</option>
                    <option value="envoyee" @selected(request('statut') === 'envoyee')>Envoyée</option>
                    <option value="cloturee" @selected(request('statut') === 'cloturee')>Clôturée</option>
                </select>
            </div>

            <div class="actions-group">
                <button class="btn-secondary" type="submit" form="convocationFilterForm">
                    Filtrer
                </button>
            </div>

        </section>

        {{-- ============================================================
             TABLEAU — liste DAGE (point 3 du cahier des charges
             "Transmission des convocations à la DAGE") : une ligne par
             agent convoqué (Agent/Type/Session/Centre/Rôle/Dates/Lieu de
             service/Lieu d'examen/Statut). $lignes est calculé par
             ConvocationsController::construireLignes() à partir des
             convocations de la page courante ($convocations) : une
             convocation à plusieurs bénéficiaires produit donc plusieurs
             lignes, ce qui explique que leur nombre puisse dépasser la
             taille de page ci-dessous.
        ============================================================ --}}

        <section class="table-card">

            <div class="table-responsive">
                <table class="table" id="convocationsTable">

                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Type</th>
                            <th>Session</th>
                            <th>Centre</th>
                            <th>Rôle</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Lieu de service</th>
                            <th>Lieu d'examen</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lignes as $ligne)
                            <tr>
                                <td>{{ $ligne['agent'] ?? '—' }}</td>
                                <td>{{ $ligne['type'] ?? '—' }}</td>
                                <td>{{ $ligne['session'] ?? '—' }}</td>
                                <td>{{ $ligne['centre'] ?? '—' }}</td>
                                <td>{{ $ligne['role'] ?? '—' }}</td>
                                <td>{{ $ligne['date_debut'] ? \Illuminate\Support\Carbon::parse($ligne['date_debut'])->format('d/m/Y') : '—' }}</td>
                                <td>{{ $ligne['date_fin'] ? \Illuminate\Support\Carbon::parse($ligne['date_fin'])->format('d/m/Y') : '—' }}</td>
                                <td>{{ $ligne['lieu_service'] ?? '—' }}</td>
                                <td>{{ $ligne['lieu_examen'] ?? '—' }}</td>
                                <td>
                                    @php
                                        $statutBadges = [
                                            'brouillon'    => ['badge-pending', 'Brouillon'],
                                            'a_completer'  => ['badge-pending', 'À compléter'],
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
                                        <a class="table-action" href="{{ route('indemnites.convocations.show', $ligne['convocation_id']) }}">
                                            Voir
                                        </a>
                                        <a class="table-action" href="{{ route('indemnites.convocations.edit', $ligne['convocation_id']) }}">
                                            Compléter
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('indemnites.convocations.destroy', $ligne['convocation_id']) }}"
                                            onsubmit="return confirm('Supprimer définitivement cette convocation ?');"
                                            style="display:inline;"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button class="table-action danger" type="submit">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>

                </table>
            </div>

            @if (empty($lignes))
                <p class="empty-message">Aucune donnée trouvée.</p>
            @endif

            <div class="pagination" aria-label="Pagination">

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

    .import-panel {
        margin: 0 0 20px;
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .import-panel-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
    }

    .import-panel-form .form-group {
        margin: 0;
        min-width: 240px;
    }

    .import-panel-form .actions-group {
        margin: 0;
    }

</style>
@endpush

{{-- ================================================================
     SCRIPT — bascule l'affichage du panneau d'import au clic sur le
     bouton "Importer".
================================================================ --}}

@push('scripts')
<script>
    (function () {
        "use strict";

        var toggleButton = document.querySelector("[data-import-toggle]");
        var panel = document.querySelector("[data-import-panel]");

        if (!toggleButton || !panel) {
            return;
        }

        toggleButton.addEventListener("click", function () {
            panel.hidden = !panel.hidden;

            if (!panel.hidden) {
                panel.scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        });
    })();
</script>
@endpush