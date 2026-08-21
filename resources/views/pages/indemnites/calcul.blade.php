@extends('layouts.app')

@section('title', 'SICORE - Calcul des indemnités')

@section('content')

<main class="main-content">

    <x-topbar
        title="Indemnités de correction"
        subtitle="Indemnites > Calcul"
        icon="fa-solid fa-pen-nib"
    />

    <section class="content-area">

        {{-- ============================================================
             STATISTIQUES
             Calculees sur la page courante (convocations filtrees), meme
             limitation deja acceptee sur Frais de deplacement/Pieces
             justificatives : l'API ne fournit pas d'agregat dedie.
        ============================================================ --}}

        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Correcteurs éligibles</p>
                    <p class="stat-value">{{ $stats['correcteurs'] ?? 0 }}</p>
                    <p class="stat-note">Fonction Correction</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Indemnités créées</p>
                    <p class="stat-value">{{ $stats['fiches_creees'] ?? 0 }}</p>
                    <p class="stat-note">Toutes indemnités confondues</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Copies corrigées</p>
                    <p class="stat-value">{{ number_format($stats['copies_corrigees'] ?? 0, 0, ',', ' ') }}</p>
                    <p class="stat-note">Cumul des indemnités créées</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Montant total</p>
                    <p class="stat-value">{{ number_format($stats['montant_total'] ?? 0, 0, ',', ' ') }} F</p>
                    <p class="stat-note">Cumul des indemnités créées</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        {{-- ============================================================
             FILTRES
             Memes menus/memes options que Frais de deplacement/Pieces
             justificatives (ConvocationService::optionsFiltres()).
        ============================================================ --}}

        <form
            id="indemniteCorrectionFilterForm"
            class="filter-panel"
            method="GET"
            action="{{ route('indemnites.calcul') }}"
            aria-label="Filtres de la page"
        >

            <div class="form-group">
                <label for="correction-filter-session">Session</label>
                <select class="form-control" id="correction-filter-session" name="session" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('session') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="correction-filter-objet">Objet</label>
                <select class="form-control" id="correction-filter-objet" name="objet" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('objet') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="correction-filter-centre">Centre</label>
                <select class="form-control" id="correction-filter-centre" name="centre" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['centres'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('centre') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions-group">
                @if ($filtreActif)
                    <a class="btn-secondary" href="{{ route('indemnites.calcul') }}">
                        Réinitialiser
                    </a>
                @endif
            </div>

        </form>

        {{-- ============================================================
             TABLEAU
             Cache tant qu'aucun filtre (session, objet ou centre) n'est
             choisi — meme comportement que Frais de deplacement. Une ligne
             par correcteur x metier, "Calcul groupé" scope toujours a UN
             centre d'UNE convocation (demande utilisatrice).
        ============================================================ --}}

        @if (! $filtreActif)

            <section class="table-card">
                <p class="empty-message show">
                    Choisissez une session, un objet ou un centre d'examen ci-dessus pour afficher les correcteurs éligibles.
                </p>
            </section>

        @else

            <section class="table-card">

                <div class="table-responsive">
                    <table class="table" id="indemniteCorrectionTable">

                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Matricule</th>
                                <th>Métier corrigé</th>
                                <th>Centre</th>
                                <th>Objet</th>
                                <th>Session</th>
                                <th>Statut</th>
                                <th class="actions-cell">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($lignes as $ligne)
                                <tr>
                                    <td>{{ $ligne['nom'] ?? '—' }}</td>
                                    <td>{{ $ligne['prenom'] ?? '—' }}</td>
                                    <td>{{ $ligne['matricule'] ?? '—' }}</td>
                                    <td>{{ $ligne['metier'] ?? '—' }}</td>
                                    <td>{{ $ligne['centre'] ?? '—' }}</td>
                                    <td>{{ $ligne['objet'] ?? '—' }}</td>
                                    <td>{{ $ligne['session'] ?? '—' }}</td>
                                    <td>
                                        @if (! empty($ligne['indemnite_correction_id']))
                                            <span class="badge badge-active">Calculée</span>
                                        @else
                                            <span class="badge badge-pending">Pas encore créée</span>
                                        @endif
                                    </td>
                                    <td class="actions-cell">
                                        <a class="table-action" href="{{ route('indemnites.calcul.groupe', ['convocation_id' => $ligne['convocation_id'], 'centre_id' => $ligne['centre_id']]) }}" title="Calculer les indemnités de correction de ce centre">
                                            Calcul groupé du centre
                                        </a>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>

                    </table>
                </div>

                @if (empty($lignes))
                    <p class="empty-message show">Aucun correcteur pour ce filtre.</p>
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

{{-- .convocation-pagination n'est pas une classe globale (voir
     frais-deplacement/index.blade.php pour le meme constat) — chaque page
     qui l'utilise doit definir sa propre regle. --}}
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

{{-- Soumet le formulaire de filtres des qu'une valeur est choisie dans un
     des menus deroulants — sans ce script, [data-filter-auto-submit] est un
     attribut sans effet (meme bug deja rencontre et corrige sur
     frais-deplacement/index.blade.php). --}}
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
