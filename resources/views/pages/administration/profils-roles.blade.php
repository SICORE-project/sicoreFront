@extends('layouts.app')

@section('title', 'SICORE - Profils / Rôles')

@section('content')
    <main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
        <x-topbar title="Profils / Rôles" subtitle="Gestion Utilisateur > Profils / Rôles" icon="fa-solid fa-users-cog" />

        <section class="content-area">
            <!-- Objectifs métier -->
            <section class="objective-card">
                <h2>Objectifs métier</h2>
                <ul class="objective-list">
                    <li>Décrire les responsabilités fonctionnelles.</li>
                    <li>Associer les rôles aux modules SICORE.</li>
                    <li>Limiter les accès aux besoins réels des services.</li>
                </ul>
            </section>

            <!-- Statistiques -->
            <div class="stats-grid four">
                <article class="stat-card">
                    <div>
                        <p class="stat-label">Profils</p>
                        <p class="stat-value">{{ isset($roles['data']) ? count($roles['data']) : 0 }}</p>
                        <p class="stat-note">Rôles actifs</p>
                    </div>
                    <span class="stat-icon blue">
                        <i class="fa-solid fa-users-cog"></i>
                    </span>
                </article>
                <article class="stat-card">
                    <div>
                        <p class="stat-label">Niveaux distincts</p>
                        <p class="stat-value">{{ isset($roles['data']) ? collect($roles['data'])->pluck('niveau')->filter()->unique()->count() : 0 }}</p>
                        <p class="stat-note">Hiérarchie d'accès</p>
                    </div>
                    <span class="stat-icon green">
                        <i class="fa-solid fa-th-large"></i>
                    </span>
                </article>
                <article class="stat-card">
                    <div>
                        <p class="stat-label">En revue</p>
                        <p class="stat-value">{{ isset($roles['data']) ? collect($roles['data'])->where('est_actif', false)->count() : 0 }}</p>
                        <p class="stat-note">Validation interne</p>
                    </div>
                    <span class="stat-icon yellow">
                        <i class="fa-solid fa-clock"></i>
                    </span>
                </article>
                <article class="stat-card">
                    <div>
                        <p class="stat-label">Sensibles</p>
                        <p class="stat-value">{{ isset($roles['data']) ? collect($roles['data'])->where('niveau', 'systeme')->count() : 0 }}</p>
                        <p class="stat-note">Accès renforcés</p>
                    </div>
                    <span class="stat-icon red">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </article>
            </div>

            <!-- Actions -->
            <div class="actions-row">
                <p class="breadcrumb">Gestion Utilisateur > Profils / Rôles</p>
                <div class="actions-group">
                    <a href="{{ route('admin.roles.create') }}" class="btn-primary">
                        <i class="fas fa-plus"></i> Nouveau profil
                    </a>
                    <button class="btn-secondary" type="button">Exporter</button>
                </div>
            </div>

            <!-- Filtres -->
            <section class="filter-panel" aria-label="Filtres">
                <div class="form-group">
                    <label for="filter-niveau">Niveau</label>
                    <select class="form-control" id="filter-niveau">
                        <option value="">Tous</option>
                        @foreach (collect($roles['data'] ?? [])->pluck('niveau')->filter()->unique() as $niveau)
                            <option value="{{ $niveau }}">{{ ucfirst($niveau) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-statut">Statut</label>
                    <select class="form-control" id="filter-statut">
                        <option value="">Tous</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
                <div class="actions-group">
                    <button class="btn-secondary" type="button" id="btn-filtrer">Filtrer</button>
                    <button class="btn-secondary" type="button" id="btn-reset-filtres">Réinitialiser</button>
                </div>
            </section>

            <!-- Tableau -->
            <section class="table-card">
                <div class="table-responsive">
                    <table class="table" id="moduleTable">
                        <thead>
                            <tr>
                                <th>Profil</th>
                                <th>Description</th>
                                <th>Niveau d'accès</th>
                                <th>Statut</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($roles['data'] ?? []) as $role)
                                <tr data-niveau="{{ $role['niveau'] ?? '' }}" data-statut="{{ ($role['est_actif'] ?? false) ? '1' : '0' }}">
                                    <td><strong>{{ $role['nom'] ?? '-' }}</strong></td>
                                    <td>{{ $role['description'] ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge 
                                    @if ($role['niveau'] == 'systeme') badge-danger
                                    @elseif($role['niveau'] == 'admin_metier') badge-purple
                                    @elseif($role['niveau'] == 'gestionnaire') badge-info
                                    @else badge-secondary @endif">
                                            {{ ucfirst($role['niveau'] ?? '') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $role['est_actif'] ?? false ? 'badge-success' : 'badge-danger' }}">
                                            {{ $role['est_actif'] ?? false ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.roles.permissions', $role['id']) }}" class="action-btn"
                                                title="Permissions">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <a href="{{ route('admin.roles.edit', $role['id']) }}" class="action-btn"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.roles.destroy', $role['id']) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete"
                                                    onclick="return confirm('Supprimer ce rôle ?')" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-inbox"
                                            style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                        Aucun rôle trouvé
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="empty-message" id="empty-message-filtre" style="display: none;">Aucun résultat pour ce filtre.</p>
                <p class="empty-message">Aucune donnée trouvée.</p>
                <div class="pagination" aria-label="Pagination">
                    @if (!empty($roles['links']))
                        @foreach ($roles['links'] as $link)
                            @if ($link['url'])
                                <a href="{{ $link['url'] }}" class="page-btn {{ $link['active'] ? 'active' : '' }}">
                                    {!! $link['label'] !!}
                                </a>
                            @else
                                <span class="page-btn disabled">{!! $link['label'] !!}</span>
                            @endif
                        @endforeach
                    @else
                        <button class="page-btn" type="button">←</button>
                        <button class="page-btn active" type="button">1</button>
                        <button class="page-btn" type="button">2</button>
                        <button class="page-btn" type="button">→</button>
                    @endif
                </div>
            </section>
        </section>
    </main>

    @push('scripts')
    <script>
        (function () {
            const btnFiltrer = document.getElementById('btn-filtrer');
            const btnReset = document.getElementById('btn-reset-filtres');
            const selectNiveau = document.getElementById('filter-niveau');
            const selectStatut = document.getElementById('filter-statut');
            const rows = document.querySelectorAll('#moduleTable tbody tr[data-niveau]');
            const emptyMessage = document.getElementById('empty-message-filtre');

            function applyFilters() {
                const niveau = selectNiveau.value;
                const statut = selectStatut.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchNiveau = !niveau || row.dataset.niveau === niveau;
                    const matchStatut = !statut || row.dataset.statut === statut;
                    const visible = matchNiveau && matchStatut;

                    row.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            if (btnFiltrer) {
                btnFiltrer.addEventListener('click', applyFilters);
            }

            if (btnReset) {
                btnReset.addEventListener('click', function () {
                    selectNiveau.value = '';
                    selectStatut.value = '';
                    applyFilters();
                });
            }
        })();
    </script>
    @endpush
@endsection