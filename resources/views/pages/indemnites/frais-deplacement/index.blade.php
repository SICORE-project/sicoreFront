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

        <div class="stats-grid four" data-ajax-region="stats">

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
            data-filtres-coherents="{{ route('indemnites.filtres-options') }}"
            data-filtres-instantanes
        >

            <div class="form-group">
                <label for="frais-filter-session">Session</label>
                <select class="form-control" id="frais-filter-session" name="session">
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('session') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="frais-filter-objet">Objet</label>
                <select class="form-control" id="frais-filter-objet" name="objet">
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('objet') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="frais-filter-centre">Centre</label>
                <select class="form-control" id="frais-filter-centre" name="centre">
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['centres'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('centre') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions-group">
                <button class="btn-secondary" type="submit">
                    Filtrer
                </button>

                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement') }}" data-ajax-lien>
                    Réinitialiser
                </a>
            </div>

        </form>

        {{-- ============================================================
             TABLEAU
             Cache tant qu'aucun filtre (session, objet ou centre) n'est
             choisi — meme comportement que Pieces justificatives. Une fois
             un filtre choisi : une ligne par bénéficiaire au dossier
             complet des convocations correspondantes.

             data-ajax-region="corps" : bascule sans recharger la page
             (indemnites-ajax-resultats.js) — demande utilisatrice : "je ne
             veux pas que les filtres rechargent la page".
        ============================================================ --}}

        <div data-ajax-region="corps">

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
                                        <div class="table-actions-inline">
                                            @if (! empty($ligne['fiche_deplacement_id']))
                                                <a class="table-action" href="{{ route('indemnites.frais-deplacement.show', $ligne['fiche_deplacement_id']) }}">
                                                    Voir la fiche
                                                </a>
                                            @else
                                                <a class="table-action" href="{{ route('indemnites.frais-deplacement.create', ['convocation_id' => $ligne['convocation_id'], 'beneficiaire_id' => $ligne['beneficiaire_id']]) }}">
                                                    Créer la fiche
                                                </a>
                                            @endif
                                            <a class="table-action" href="{{ route('indemnites.frais-deplacement.calcul-groupe', ['convocation_id' => $ligne['convocation_id']]) }}" title="Calculer pour tous les membres de cette convocation">
                                                Calcul groupé
                                            </a>
                                        </div>
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

        @endif

        </div>

    </section>

</main>

{{-- ================================================================
     SCRIPT — les selects Session/Objet/Centre se mettent a jour entre eux
     en AJAX (indemnites-filtres-coherents.js), et le tableau de resultats
     se rafraichit lui aussi sans jamais recharger la page
     (indemnites-ajax-resultats.js, voir @push('scripts') plus bas).
================================================================ --}}

{{-- ================================================================
     STYLE — .convocation-pagination/.page-btn (numeros de pagination du
     tableau) etaient utilises dans le HTML de cette page mais leur CSS
     n'avait jamais ete ajoutee ici (contrairement a convocations/index.blade.php
     et pieces-justificatives.blade.php, qui definissent chacune la meme
     regle dans leur propre @push('styles')) — consequence : les numeros de
     page s'affichaient sans mise en forme (pas d'alignement a droite, pas
     d'espacement, pas de bordure superieure). Demande utilisatrice : "les
     chiffres pour pagination dans l'index doivent etre comme index de
     convocation" — copie a l'identique depuis convocations/index.blade.php.
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
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/indemnites-filtres-coherents.js') }}" defer></script>
<script src="{{ asset('assets/js/indemnites-ajax-resultats.js') }}" defer></script>
@endpush

@endsection
