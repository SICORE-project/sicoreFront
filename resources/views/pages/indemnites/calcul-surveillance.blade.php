@extends('layouts.app')

@section('title', 'SICORE - Indemnité de surveillance')

@section('content')

<main class="main-content">

    <x-topbar
        title="Indemnités de surveillance"
        subtitle="Indemnites > Indemnité de surveillance"
        icon="fa-solid fa-user-shield"
    />

    <section class="content-area">

        <div class="stats-grid four" data-ajax-region="stats">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Surveillants éligibles</p>
                    <p class="stat-value">{{ $stats['surveillants'] ?? 0 }}</p>
                    <p class="stat-note">Fonction Surveillant</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
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
                    <p class="stat-label">Heures surveillées</p>
                    <p class="stat-value">{{ number_format($stats['heures_surveillees'] ?? 0, 1, ',', ' ') }}</p>
                    <p class="stat-note">Cumul des indemnités créées</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
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

        <form
            id="indemniteSurveillanceFilterForm"
            class="filter-panel"
            method="GET"
            action="{{ route('indemnites.calcul-surveillance') }}"
            aria-label="Filtres de la page"
            data-filtres-coherents="{{ route('indemnites.filtres-options') }}"
            data-filtres-instantanes
        >

            <div class="form-group">
                <label for="surveillance-filter-session">Session</label>
                <select class="form-control" id="surveillance-filter-session" name="session">
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['sessions'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('session') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="surveillance-filter-objet">Objet</label>
                <select class="form-control" id="surveillance-filter-objet" name="objet">
                    <option value="">Sélectionner</option>
                    @foreach ($optionsFiltres['objets'] ?? [] as $valeur)
                        <option value="{{ $valeur }}" @selected(request('objet') === $valeur)>{{ $valeur }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="surveillance-filter-centre">Centre</label>
                <select class="form-control" id="surveillance-filter-centre" name="centre">
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

                <a class="btn-secondary" href="{{ route('indemnites.calcul-surveillance') }}" data-ajax-lien>
                    Réinitialiser
                </a>
            </div>

        </form>

        {{-- data-ajax-region="corps" : bascule sans recharger la page
             (indemnites-ajax-resultats.js). --}}
        <div data-ajax-region="corps">

        @if (! $filtreActif)

            <section class="table-card">
                <p class="empty-message show">
                    Choisissez une session, un objet ou un centre d'examen ci-dessus pour afficher les surveillants éligibles.
                </p>
            </section>

        @else

            <section class="table-card">

                <div class="table-responsive">
                    <table class="table" id="indemniteSurveillanceTable">

                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Matricule</th>
                                <th>Métier surveillé</th>
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
                                        @if (! empty($ligne['indemnite_surveillance_id']))
                                            <span class="badge badge-active">Calculée</span>
                                        @else
                                            <span class="badge badge-pending">Pas encore créée</span>
                                        @endif
                                    </td>
                                    <td class="actions-cell">
                                        <a class="table-action" href="{{ route('indemnites.calcul-surveillance.groupe', ['convocation_id' => $ligne['convocation_id'], 'centre_id' => $ligne['centre_id']]) }}" title="Calculer les indemnités de surveillance de ce centre">
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
                    <p class="empty-message show">Aucun surveillant pour ce filtre.</p>
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

{{-- Les selects Session/Objet/Centre se mettent a jour entre eux en AJAX
     (indemnites-filtres-coherents.js), et le tableau de resultats se
     rafraichit lui aussi sans jamais recharger la page
     (indemnites-ajax-resultats.js). --}}
@push('scripts')
<script src="{{ asset('assets/js/indemnites-filtres-coherents.js') }}" defer></script>
<script src="{{ asset('assets/js/indemnites-ajax-resultats.js') }}" defer></script>
@endpush

@endsection
