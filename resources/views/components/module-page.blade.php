@php
    $slug = $slug ?? 'modules';

    // ==========================================================
    // Fonctions utilitaires réutilisables (évitent la duplication)
    // ==========================================================

    // Génère un badge coloré <span> pour un statut booléen
    $badge = function (bool $isActive, string $labelTrue = 'Actif', string $labelFalse = 'Inactif') {
        $classes = $isActive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        $label = $isActive ? $labelTrue : $labelFalse;
        return '<span class="px-2 py-1 rounded text-xs font-medium ' . $classes . '">' . $label . '</span>';
    };

    // Génère un badge coloré selon le niveau d'un rôle
    $niveauBadge = function (?string $niveau) {
        $map = [
            'systeme' => 'bg-red-100 text-red-700',
            'admin_metier' => 'bg-purple-100 text-purple-700',
            'gestionnaire' => 'bg-blue-100 text-blue-700',
        ];
        $classes = $map[$niveau] ?? 'bg-gray-100 text-gray-700';
        return '<span class="px-2 py-1 rounded text-xs font-medium ' . $classes . '">' . ucfirst($niveau ?? '') . '</span>';
    };

    // Génère le bloc d'actions (modifier + supprimer) commun à toutes les lignes
    $actionButtons = function (string $editRoute, string $destroyRoute, string $confirmMessage, ?string $extraLink = null) {
        return '<div class="flex gap-2">'
            . ($extraLink ?? '')
            . '<a href="' . $editRoute . '" class="text-yellow-500 hover:text-yellow-700" title="Modifier">
                <i class="fas fa-edit"></i>
            </a>
            <form action="' . $destroyRoute . '" method="POST" class="inline">'
                . csrf_field()
                . method_field('DELETE')
                . '<button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm(\'' . $confirmMessage . '\')" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>';
    };

    $permissionsData = $permissions['data'] ?? [];
    $rolesData = $roles['data'] ?? [];

    $countByGroupe = collect($permissionsData)->countBy(fn ($p) => $p['groupe'] ?? 'autre');

    // ==========================================================
    // Configuration des pages
    // ==========================================================
    $configs = [
        'profils-roles' => [
            'title' => 'Profils / Rôles',
            'breadcrumb' => 'Gestion Utilisateur > Profils / Rôles',
            'icon' => 'fa-solid fa-users-cog',
            'objectives' => [
                'Décrire les responsabilités fonctionnelles.',
                'Associer les rôles aux modules SICORE.',
                'Limiter les accès aux besoins réels des services.',
            ],
            'stats' => [
                [
                    'label' => 'Profils',
                    'value' => count($rolesData),
                    'note' => 'Rôles actifs',
                    'color' => 'blue',
                    'icon' => 'fa-solid fa-users-cog',
                ],
                [
                    'label' => 'Modules couverts',
                    'value' => collect($rolesData)->pluck('module')->filter()->unique()->count(),
                    'note' => 'Navigation officielle',
                    'color' => 'green',
                    'icon' => 'fa-solid fa-th-large',
                ],
                [
                    'label' => 'En revue',
                    'value' => collect($rolesData)->where('est_actif', false)->count(),
                    'note' => 'Validation interne',
                    'color' => 'yellow',
                    'icon' => 'fa-solid fa-clock',
                ],
                [
                    'label' => 'Sensibles',
                    'value' => collect($rolesData)->where('niveau', 'systeme')->count(),
                    'note' => 'Accès renforcés',
                    'color' => 'red',
                    'icon' => 'fa-solid fa-shield-halved',
                ],
            ],
            'actions' => ['Nouveau profil', 'Exporter'],
            'filters' => ['Module', 'Niveau', 'Statut'],
            'columns' => ['Profil', 'Module principal', "Niveau d'accès", 'Statut', 'Actions'],
        ],
        'permissions' => [
            'title' => 'Permissions',
            'breadcrumb' => 'Gestion Utilisateur > Permissions',
            'icon' => 'fa-solid fa-lock',
            'objectives' => [
                'Visualiser les droits par module.',
                'Séparer les permissions de consultation, saisie, validation et administration.',
                'Préparer les paramètres sans inventer de backend.',
            ],
            'stats' => [
                [
                    'label' => 'Permissions',
                    'value' => count($permissionsData),
                    'note' => 'Droits définis',
                    'color' => 'blue',
                    'icon' => 'fa-solid fa-lock',
                ],
                [
                    'label' => 'Lecture',
                    'value' => $countByGroupe->get('lecture', 0),
                    'note' => 'Modules',
                    'color' => 'green',
                    'icon' => 'fa-solid fa-eye',
                ],
                [
                    'label' => 'Validation',
                    'value' => $countByGroupe->get('validation', 0),
                    'note' => 'Processus',
                    'color' => 'yellow',
                    'icon' => 'fa-solid fa-check-double',
                ],
                [
                    'label' => 'Administration',
                    'value' => $countByGroupe->get('administration', 0),
                    'note' => 'Accès critiques',
                    'color' => 'red',
                    'icon' => 'fa-solid fa-lock',
                ],
            ],
            'actions' => ['Nouvelle permission', 'Exporter'],
            'filters' => ['Module', 'Permission', 'Profil'],
            'columns' => ['Module', 'Permission', 'Profils concernés', 'Statut', 'Actions'],
        ],
    ];

    $page = $configs[$slug] ?? $configs['profils-roles'];
    $pageIcon = $page['icon'] ?? 'fa-solid fa-cog';

    // ==========================================================
    // Génération des lignes du tableau (plus de duplication)
    // ==========================================================
    if ($slug === 'profils-roles') {
        $rows = collect($rolesData)->map(function ($role) use ($niveauBadge, $badge, $actionButtons) {
            $permLink = '<a href="' . route('admin.roles.permissions', $role['id']) . '" class="text-blue-500 hover:text-blue-700" title="Permissions">
                <i class="fas fa-key"></i>
            </a>';

            return [
                $role['nom'] ?? '-',
                $role['module'] ?? 'Tous modules',
                $niveauBadge($role['niveau'] ?? null),
                $badge($role['est_actif'] ?? false),
                $actionButtons(
                    route('admin.roles.edit', $role['id']),
                    route('admin.roles.destroy', $role['id']),
                    'Supprimer ce rôle ?',
                    $permLink
                ),
            ];
        })->toArray();
    } elseif ($slug === 'permissions') {
        $rows = collect($permissionsData)->map(function ($permission) use ($badge, $actionButtons) {
            return [
                $permission['module'] ?? '-',
                $permission['nom'] ?? '-',
                $permission['groupe'] ?? '-',
                $badge($permission['est_actif'] ?? false),
                $actionButtons(
                    route('admin.permissions.edit', $permission['id']),
                    route('admin.permissions.destroy', $permission['id']),
                    'Supprimer cette permission ?'
                ),
            ];
        })->toArray();
    } else {
        $rows = [];
    }

    $page['rows'] = $rows;

    // ==========================================================
    // Options de filtre dynamiques (calculées depuis les vraies données)
    // ==========================================================
    if ($slug === 'profils-roles') {
        $filterOptions = [
            'Module' => collect($rolesData)->pluck('module')->filter()->unique()->values()->all(),
            'Niveau' => collect($rolesData)->pluck('niveau')->filter()->unique()->values()->all(),
            'Statut' => ['Actif', 'Inactif'],
        ];
    } elseif ($slug === 'permissions') {
        $filterOptions = [
            'Module' => collect($permissionsData)->pluck('module')->filter()->unique()->values()->all(),
            'Permission' => collect($permissionsData)->pluck('nom')->filter()->unique()->values()->all(),
            'Profil' => [],
        ];
    } else {
        $filterOptions = [];
    }
@endphp

<main class="main-content">
    <x-topbar :title="$page['title']" :subtitle="$page['breadcrumb']" :icon="$pageIcon" search-id="moduleSearch"
        search-placeholder="Rechercher…" filter-target="#moduleTable" />

    <section class="content-area">
        @if (!empty($page['objectives']))
            <section class="objective-card {{ !empty($page['sensitive']) ? 'sensitive-panel' : '' }}">
                <h2>Objectifs métier</h2>
                <ul class="objective-list">
                    @foreach ($page['objectives'] as $objective)
                        <li>{{ $objective }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="stats-grid four">
            @foreach ($page['stats'] as $stat)
                <article class="stat-card">
                    <div>
                        <p class="stat-label">{{ $stat['label'] }}</p>
                        <p class="stat-value">{{ $stat['value'] }}</p>
                        <p class="stat-note">{{ $stat['note'] }}</p>
                    </div>
                    <span class="stat-icon {{ $stat['color'] }}">
                        <i class="{{ str_contains((string) $stat['icon'], 'fa-') ? $stat['icon'] : $statIconMap[$stat['icon']] ?? 'fa-solid fa-circle' }}"
                            aria-hidden="true"></i>
                    </span>
                </article>
            @endforeach
        </div>

        <div class="actions-row">
            <p class="breadcrumb">{{ $page['breadcrumb'] }}</p>
            <div class="actions-group">
                @foreach ($page['actions'] as $index => $label)
                    @php
                        $isNouveau =
                            ($slug === 'profils-roles' && $index === 0) || ($slug === 'permissions' && $index === 0);
                    @endphp
                    @if ($isNouveau)
                        @php
                            $route =
                                $slug === 'profils-roles'
                                    ? route('admin.roles.create')
                                    : route('admin.permissions.create');
                        @endphp
                        <a href="{{ $route }}" class="btn-primary">
                            <i class="fas fa-plus"></i> {{ $label }}
                        </a>
                    @else
                        <button class="{{ $index === 0 ? 'btn-primary' : 'btn-secondary' }}" type="button"
                            @if (!empty($page['calculator']) && $label === 'Calculer') data-calculate-indemnity @endif>
                            {{ $label }}
                        </button>
                    @endif
                @endforeach

                @if (!empty($page['closePeriod']))
                    <button class="btn-danger-soft" type="button"
                        data-confirm="Êtes-vous sûr de vouloir fermer cette période de paie ? Cette action est sensible."
                        data-success-message="Période de paie fermée.">
                        Fermer la période
                    </button>
                @endif
            </div>
        </div>

        <section class="filter-panel" aria-label="Filtres de la page">
            @foreach ($page['filters'] as $index => $filter)
                @php($filterId = $slug . '-filter-' . $index)
                @php($options = $filterOptions[$filter] ?? [])
                <div class="form-group">
                    <label for="{{ $filterId }}">{{ $filter }}</label>
                    <select class="form-control" id="{{ $filterId }}">
                        <option value="">Tous</option>
                        @foreach ($options as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="actions-group">
                <button class="btn-secondary" type="button">Filtrer</button>
            </div>
        </section>

        @if (!empty($page['helpText']))
            <section class="help-card">
                <h2>{{ $page['helpTitle'] }}</h2>
                <p>{{ $page['helpText'] }}</p>
            </section>
        @endif

        @if (!empty($page['chart']))
            @php($heights = [58, 74, 48, 86, 66, 96])
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h2>Graphique mensuel</h2>
                        <p>Vue synthétique de la période</p>
                    </div>
                </div>
                <div class="mini-chart">
                    @foreach ($page['chart'] as $index => $label)
                        <div class="mini-bar">
                            <span style="height: {{ $heights[$index % count($heights)] }}px"></span>
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if (!empty($page['calculator']))
            <section class="result-card" data-indemnity-result hidden></section>
        @endif

        <section class="table-card">
            <div class="table-responsive">
                <table class="table" id="moduleTable">
                    <thead>
                        <tr>
                            @foreach ($page['columns'] as $column)
                                <th @class(['actions-cell' => $loop->last])>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($page['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td @class(['actions-cell' => $loop->last])>{!! $cell !!}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($page['columns']) }}" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-inbox text-4xl block mb-2"></i>
                                    Aucune donnée trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="empty-message">Aucune donnée trouvée.</p>
            <div class="pagination" aria-label="Pagination">
                <button class="page-btn" type="button" aria-label="Page précédente">←</button>
                <button class="page-btn active" type="button" data-page-number>1</button>
                <button class="page-btn" type="button" data-page-number>2</button>
                <button class="page-btn" type="button" aria-label="Page suivante">→</button>
            </div>
        </section>
    </section>
</main>