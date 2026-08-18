@extends('layouts.app')

@section('title', 'SICORE - Frais de déplacement')

@section('content')

<main class="main-content">

    <x-topbar
        title="Fiches de déplacement"
        subtitle="Indemnites > Frais de déplacement"
        icon="fa-solid fa-route"
        search-id="fraisDeplacementSearch"
        search-placeholder="Rechercher…"
        filter-target="#fraisDeplacementTable"
    />

    <section class="content-area">

        {{-- ============================================================
             STATISTIQUES
             Calculees sur la page courante (convocations filtrees), meme
             limitation deja acceptee sur Pieces justificatives : l'API ne
             fournit pas d'agregat dedie.
        ============================================================ --}}

        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Bénéficiaires éligibles</p>
                    <p class="stat-value">{{ $stats['total_eligibles'] ?? 0 }}</p>
                    <p class="stat-note">Dossier de pièces complet</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Fiches créées</p>
                    <p class="stat-value">{{ $stats['fiches_creees'] ?? 0 }}</p>
                    <p class="stat-note">Toutes fiches confondues</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-route" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">En attente</p>
                    <p class="stat-value">{{ $stats['en_attente'] ?? 0 }}</p>
                    <p class="stat-note">Brouillon ou calculée</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Fiches rejetées</p>
                    <p class="stat-value">{{ $stats['fiches_rejetees'] ?? 0 }}</p>
                    <p class="stat-note">À corriger</p>
                </div>
                <span class="stat-icon red">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        {{-- ============================================================
             FILTRES
             Memes menus/memes options que la page "Pieces justificatives"
             (ConvocationService::optionsFiltres()), pour une navigation
             coherente entre les deux pages du module.
        ============================================================ --}}

        <form
            id="fraisDeplacementFilterForm"
            class="filter-panel"
            method="GET"
            action="{{ route('indemnites.frais-deplacement') }}"
            aria-label="Filtres de la page"
        >

            <div class="form-group">
                <label for="frais-filter-session">Session</label>
                <select class="form-control" id="frais-filter-session" name="session" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('session') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="frais-filter-objet">Objet</label>
                <select class="form-control" id="frais-filter-objet" name="objet" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('objet') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="frais-filter-centre">Centre</label>
                <select class="form-control" id="frais-filter-centre" name="centre" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['centres'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('centre') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions-group">
                @if ($filtreActif)
                    <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement') }}">
                        Réinitialiser
                    </a>
                @endif
            </div>

        </form>

        {{-- ============================================================
             TABLEAU
             Cache tant qu'aucun filtre (session, objet ou centre) n'est
             choisi — meme comportement que Pieces justificatives. Une fois
             un filtre choisi : une ligne par bénéficiaire au dossier
             complet des convocations correspondantes.
        ============================================================ --}}

        @if (! $filtreActif)

            <section class="table-card">
                <p class="empty-message show">
                    Choisissez une session, un objet ou un centre d'examen ci-dessus pour afficher les bénéficiaires éligibles.
                </p>
            </section>

        @else

            <section class="table-card">

                <div class="table-responsive">
                    <table class="table" id="fraisDeplacementTable">

                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Matricule</th>
                                <th>Type</th>
                                <th>Objet</th>
                                <th>Session</th>
                                <th>Statut fiche</th>
                                <th class="actions-cell">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($lignes as $ligne)
                                <tr>
                                    <td>{{ $ligne['nom'] ?? '—' }}</td>
                                    <td>{{ $ligne['prenom'] ?? '—' }}</td>
                                    <td>{{ $ligne['matricule'] ?? '—' }}</td>
                                    <td>{{ ucfirst($ligne['categorie_personnel'] ?? '—') }}</td>
                                    <td>{{ $ligne['objet'] ?? '—' }}</td>
                                    <td>{{ $ligne['session'] ?? '—' }}</td>
                                    <td>
                                        @if (! empty($ligne['fiche_deplacement_id']))
                                            <x-module-indemnite type="statut-frais-deplacement" :statut="$ligne['fiche_statut'] ?? null" />
                                        @else
                                            <span class="badge badge-pending">Pas encore créée</span>
                                        @endif
                                    </td>
                                    <td class="actions-cell">
                                        @if (! empty($ligne['fiche_deplacement_id']))
                                            <a class="table-action" href="{{ route('indemnites.frais-deplacement.show', $ligne['fiche_deplacement_id']) }}">
                                                Voir la fiche
                                            </a>
                                        @else
                                            <a class="table-action" href="{{ route('indemnites.frais-deplacement.create', ['convocation_id' => $ligne['convocation_id'], 'beneficiaire_id' => $ligne['beneficiaire_id']]) }}">
                                                Créer la fiche
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>

                    </table>
                </div>

                @if (empty($lignes))
                    <p class="empty-message show">Aucun bénéficiaire au dossier complet pour ce filtre.</p>
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

    </section>

</main>

{{-- ================================================================
     SCRIPT — soumet le formulaire de filtres des qu'une valeur est
     choisie dans un des menus deroulants (session/objet/centre) : pas
     besoin de cliquer sur un bouton "Filtrer" separe.

     NB : ce bloc existait deja sur pieces-justificatives.blade.php mais
     avait ete oublie ici lors de la reorganisation de cette page sur le
     meme modele — consequence concrete : l'attribut data-filter-auto-submit
     etait present sur les <select> mais sans aucun JS pour l'exploiter,
     donc choisir une session/objet/centre ne soumettait jamais le
     formulaire (l'URL restait sans parametre de filtre, la page
     n'affichait donc jamais les benefeciaires eligibles malgre un choix
     visible dans le menu deroulant).
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
@endpush

@endsection
