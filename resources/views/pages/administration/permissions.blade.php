@extends('layouts.app')

@section('title', 'SICORE - Permissions')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <x-topbar title="Permissions" subtitle="Gestion Utilisateur > Permissions" icon="fa-solid fa-lock" />

    @php
        $permissionsData = $permissions['data'] ?? [];

        // Comptage réel par groupe, sans supposer les noms à l'avance
        $countByGroupe = collect($permissionsData)
            ->countBy(fn ($p) => $p['groupe'] ?? 'Non classé')
            ->sortDesc();

        // On prend les 3 groupes les plus fréquents pour les 3 cartes secondaires
        $topGroupes = $countByGroupe->take(3);

        $iconsCycle = ['fa-solid fa-eye', 'fa-solid fa-check-double', 'fa-solid fa-shield-halved'];
        $colorsCycle = ['green', 'yellow', 'red'];
    @endphp

    <section class="content-area">
        @if (!empty($permissionsError))
            <div class="alert alert-danger">{{ $permissionsError }}</div>
        @endif
        <!-- Objectifs métier -->
        <section class="objective-card">
            <h2>Objectifs métier</h2>
            <ul class="objective-list">
                <li>Visualiser les droits par module.</li>
                <li>Séparer les permissions de consultation, saisie, validation et administration.</li>
                <li>Préparer les paramètres sans inventer de backend.</li>
            </ul>
        </section>

        <!-- Statistiques -->
        <div class="stats-grid four">
            <article class="stat-card">
                <div>
                    <p class="stat-label">Permissions</p>
                    <p class="stat-value">{{ count($permissionsData) }}</p>
                    <p class="stat-note">Droits définis</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-lock"></i>
                </span>
            </article>

            @foreach ($topGroupes as $groupeNom => $groupeCount)
                <article class="stat-card">
                    <div>
                        <p class="stat-label">{{ ucfirst($groupeNom) }}</p>
                        <p class="stat-value">{{ $groupeCount }}</p>
                        <p class="stat-note">Permissions</p>
                    </div>
                    <span class="stat-icon {{ $colorsCycle[$loop->index] ?? 'blue' }}">
                        <i class="{{ $iconsCycle[$loop->index] ?? 'fa-solid fa-circle' }}"></i>
                    </span>
                </article>
            @endforeach
        </div>

        <!-- Actions -->
        <div class="actions-row">
            <p class="breadcrumb">Gestion Utilisateur > Permissions</p>
            <div class="actions-group">
                <a href="{{ route('admin.permissions.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle permission
                </a>
                <button class="btn-secondary" type="button">Exporter</button>
                <a href="{{ route('admin.permissions.sync') }}" class="btn-warning" style="background: #f59e0b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-sync"></i> Synchroniser
                </a>
            </div>
        </div>

        <!-- Filtres -->
        <section class="filter-panel" aria-label="Filtres">
            <div class="form-group">
                <label for="filter-module">Module</label>
                <select class="form-control" id="filter-module">
                    <option value="">Tous</option>
                    @foreach (collect($permissionsData)->pluck('module')->filter()->unique() as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="filter-permission">Permission</label>
                <select class="form-control" id="filter-permission">
                    <option value="">Tous</option>
                    @foreach (collect($permissionsData)->pluck('nom')->filter()->unique() as $nom)
                        <option value="{{ $nom }}">{{ $nom }}</option>
                    @endforeach
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
                            <th>Module</th>
                            <th>Permission</th>
                            <th>Groupe</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissionsData as $permission)
                        <tr data-module="{{ $permission['module'] ?? '' }}" data-permission="{{ $permission['nom'] ?? '' }}">
                            <td>{{ $permission['module'] ?? '-' }}</td>
                            <td><strong>{{ $permission['nom'] ?? '-' }}</strong></td>
                            <td>{{ $permission['groupe'] ?? '-' }}</td>
                            <td>
                                <span class="badge {{ ($permission['est_actif'] ?? false) ? 'badge-success' : 'badge-danger' }}">
                                    {{ ($permission['est_actif'] ?? false) ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="table-actions-inline">
                                    <a href="{{ route('admin.permissions.show', $permission['id']) }}" class="table-action">Voir</a>
                                    <a href="{{ route('admin.permissions.edit', $permission['id']) }}" class="table-action">Modifier</a>
                                    <form action="{{ route('admin.permissions.destroy', $permission['id']) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="table-action delete"
                                                onclick="return confirm('Supprimer cette permission ?')">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                <i class="fas fa-inbox" style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                Aucune permission trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="empty-message" id="empty-message-filtre" style="display: none;">Aucun résultat pour ce filtre.</p>
            <p class="empty-message">Aucune donnée trouvée.</p>
            <div class="pagination" aria-label="Pagination">
                @if (!empty($permissions['links']))
                    @foreach ($permissions['links'] as $link)
                        @if ($link['url'])
                            <a href="{{ $link['url'] }}" class="page-btn {{ $link['active'] ? 'active' : '' }}">
                                {{ $loop->first ? '←' : ($loop->last ? '→' : $link['label']) }}
                            </a>
                        @else
                            <span class="page-btn disabled">{{ $loop->first ? '←' : ($loop->last ? '→' : $link['label']) }}</span>
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

    @push('scripts')
    <script>
        (function () {
            const btnFiltrer = document.getElementById('btn-filtrer');
            const btnReset = document.getElementById('btn-reset-filtres');
            const selectModule = document.getElementById('filter-module');
            const selectPermission = document.getElementById('filter-permission');
            const rows = document.querySelectorAll('#moduleTable tbody tr[data-module]');
            const emptyMessage = document.getElementById('empty-message-filtre');

            function applyFilters() {
                const module = selectModule.value;
                const permission = selectPermission.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchModule = !module || row.dataset.module === module;
                    const matchPermission = !permission || row.dataset.permission === permission;
                    const visible = matchModule && matchPermission;

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
                    selectModule.value = '';
                    selectPermission.value = '';
                    applyFilters();
                });
            }
        })();
    </script>
    @endpush
</main>
@endsection
