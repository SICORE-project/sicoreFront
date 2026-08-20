{{--
  COMPOSANT VISUEL PARTAGÉ PAR LES MODULES SICORE

  Appelé depuis : resources/views/pages/*/*.blade.php avec <x-module-page>.
  Préparé par : app/View/Components/ModulePage.php.
  Textes statiques : config/module-pages.php.
  Formulaires Paie : config/payroll-forms.php.
  Données Paie : sicoreBack/app/Services/PayrollPageService.php via SicoreApi.php.
  Interactions Paie : public/assets/js/payroll.js.

  Modifier ici pour changer toutes les pages. Pour les colonnes ou données
  d'une page Paie, modifier sa méthode dans PayrollPageService.php.
--}}
<main class="main-content" @if($connected) data-payroll-module="{{ $slug }}" @endif>
  {{-- En-tête commun : titre, fil d'Ariane et recherche générale. --}}
  <x-topbar
    :title="$page['title']"
    :subtitle="$page['breadcrumb']"
    :icon="$pageIcon"
    search-id="moduleSearch"
    search-placeholder="Rechercher…"
    filter-target="#moduleTable"
  />

  <section class="content-area">
<<<<<<< HEAD
=======
    {{-- État de la communication avec le backend. --}}
    @if ($error)
      <section class="connection-banner connection-banner-error" role="alert">
        <i class="fa-solid fa-plug-circle-xmark" aria-hidden="true"></i>
        <div>
          <strong>Connexion au backend indisponible</strong>
          <p>{{ $error }}</p>
        </div>
      </section>
    @elseif ($connected)
      <section class="connection-banner" role="status">
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        <div>
          <strong>Données de paie sécurisées et synchronisées</strong>
          <p>{{ $moduleData['notice'] ?? 'Les données affichées proviennent du backend SICORE.' }}</p>
        </div>
      </section>
    @endif

    {{-- Objectifs définis dans config/module-pages.php. --}}
>>>>>>> origin/module-paie
    @if (! empty($page['objectives']))
      <section class="objective-card {{ ! empty($page['sensitive']) ? 'sensitive-panel' : '' }}">
        <h2>Objectifs métier</h2>
        <ul class="objective-list">
<<<<<<< HEAD
          @foreach (($page['objectives'] ?? []) as $objective)
=======
          @foreach ($page['objectives'] as $objective)
>>>>>>> origin/module-paie
            <li>{{ $objective }}</li>
          @endforeach
        </ul>
      </section>
    @endif

<<<<<<< HEAD
    @if (! empty($page['stats']))
      <div class="stats-grid four">
        @foreach (($page['stats'] ?? []) as $stat)
          <article class="stat-card">
            <div>
              <p class="stat-label">{{ $stat['label'] }}</p>
              <p class="stat-value">{{ $stat['value'] }}</p>
              <p class="stat-note">{{ $stat['note'] }}</p>
            </div>
            <span class="stat-icon {{ $stat['color'] }}">
              <i class="{{ str_contains((string) $stat['icon'], 'fa-') ? $stat['icon'] : ($statIconMap[$stat['icon']] ?? 'fa-solid fa-circle') }}" aria-hidden="true"></i>
            </span>
          </article>
        @endforeach
      </div>
    @endif

    <div class="actions-row">
      <p class="breadcrumb">{{ $page['breadcrumb'] }}</p>
      <div class="actions-group">
        @foreach (($page['actions'] ?? []) as $index => $label)
          @php
            $actionText = is_array($label) ? ($label['label'] ?? '') : $label;
            $actionUrl = is_array($label) ? ($label['url'] ?? null) : null;
            $actionClass = $index === 0 ? 'btn-primary' : 'btn-secondary';
            $isNewUserButton = ($slug ?? '') === 'utilisateurs' && trim((string) $actionText) === 'Nouvel utilisateur';
          @endphp

          @if ($isNewUserButton)
            <button class="{{ $actionClass }}" type="button" data-modal-open="create-user-modal">{{ $actionText }}</button>
          @elseif ($actionUrl)
            <a class="{{ $actionClass }}" href="{{ $actionUrl }}">{{ $actionText }}</a>
          @else
            <button class="{{ $actionClass }}" type="button">{{ $actionText }}</button>
          @endif
        @endforeach

        @if (! empty($page['closePeriod']))
          <button class="btn-danger-soft" type="button" data-confirm="Êtes-vous sûr de vouloir fermer cette période de paie ? Cette action est sensible." data-success-message="Période de paie fermée.">
=======
    {{-- Cartes statistiques : données API en Paie, configuration sinon. --}}
    <div class="stats-grid four">
      @foreach ($page['stats'] as $stat)
        <article class="stat-card">
          <div>
            <p class="stat-label">{{ $stat['label'] }}</p>
            <p class="stat-value">{{ $stat['value'] }}</p>
            <p class="stat-note">{{ $stat['note'] }}</p>
          </div>
          <span class="stat-icon {{ $stat['color'] }}">
            <i class="{{ str_contains((string) $stat['icon'], 'fa-') ? $stat['icon'] : ($statIconMap[$stat['icon']] ?? 'fa-solid fa-circle') }}" aria-hidden="true"></i>
          </span>
        </article>
      @endforeach
    </div>

    {{-- Boutons globaux : export ou commandes métier. --}}
    <div class="actions-row">
      <p class="breadcrumb">{{ $page['breadcrumb'] }}</p>
      <div class="actions-group">
        @if ($connected)
          @foreach ($page['actions'] as $action)
            @if (($action['code'] ?? '') === 'export')
              <a
                class="btn-secondary"
                href="{{ route('paie.export', array_filter([
                  'slug' => $slug,
                  'period_id' => data_get($moduleData, 'period.id'),
                ])) }}"
              >
                <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                {{ $action['label'] }}
              </a>
            @else
              <button
                class="{{ ($action['style'] ?? '') === 'primary' ? 'btn-primary' : (($action['style'] ?? '') === 'danger' ? 'btn-danger-soft' : 'btn-secondary') }}"
                type="button"
                data-payroll-action="{{ $action['code'] }}"
                data-action-defaults="{{ json_encode($action['defaults'] ?? [], JSON_UNESCAPED_UNICODE) }}"
              >
                {{ $action['label'] }}
              </button>
            @endif
          @endforeach
        @else
          @foreach ($page['actions'] as $index => $label)
            <button
              class="{{ $index === 0 ? 'btn-primary' : 'btn-secondary' }}"
              type="button"
              @if (! empty($page['calculator']) && $label === 'Calculer') data-calculate-indemnity @endif
            >
              {{ $label }}
            </button>
          @endforeach
        @endif

        @if (! $connected && ! empty($page['closePeriod']))
          <button
            class="btn-danger-soft"
            type="button"
            data-confirm="Êtes-vous sûr de vouloir fermer cette période de paie ? Cette action est sensible."
            data-success-message="Période de paie fermée."
          >
>>>>>>> origin/module-paie
            Fermer la période
          </button>
        @endif
      </div>
    </div>

<<<<<<< HEAD
    @if (! empty($page['filters']))
      <section class="filter-panel" aria-label="Filtres de la page">
        @foreach (($page['filters'] ?? []) as $index => $filter)
          @php
            $filterId = ($slug ?? 'module') . '-filter-' . $index;
=======
    {{-- Filtre de période transmis par GET au contrôleur. --}}
    @if ($connected)
      <form class="filter-panel" method="GET" aria-label="Filtres de la page">
        @foreach ($page['filters'] as $index => $filter)
          @php
            $filterId = $slug.'-filter-'.$index;
          @endphp
          <div class="form-group">
            <label for="{{ $filterId }}">{{ $filter['label'] }}</label>
            <select class="form-control" id="{{ $filterId }}" name="{{ $filter['name'] }}">
              @foreach ($filter['options'] as $option)
                <option
                  value="{{ $option['value'] }}"
                  @selected((string) $option['value'] === (string) ($filter['value'] ?? ''))
                >
                  {{ $option['label'] }}
                </option>
              @endforeach
            </select>
          </div>
        @endforeach
        <div class="actions-group">
          <button class="btn-secondary" type="submit">
            <i class="fa-solid fa-filter" aria-hidden="true"></i>
            Appliquer
          </button>
        </div>
      </form>

      {{-- Recherche instantanée IA → IEF → matricule, sans rechargement. --}}
      @if (! empty($moduleData['supports_hierarchy_filter']))
        <section class="payroll-live-filter" data-payroll-live-filter aria-labelledby="payrollLiveFilterTitle">
          <header class="payroll-live-filter-header">
            <span class="payroll-live-filter-icon" aria-hidden="true">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <div>
              <h2 id="payrollLiveFilterTitle">Recherche administrative instantanée</h2>
              <p>Filtrez les résultats sans recharger la page.</p>
            </div>
          </header>

          <div class="payroll-live-filter-grid">
            <div class="form-group">
              <label for="payrollLiveIa">Inspection académique (IA)</label>
              <select class="form-control" id="payrollLiveIa" data-payroll-live-ia>
                <option value="">Toutes les IA</option>
                @foreach (($moduleData['academic_inspections'] ?? []) as $inspection)
                  <option value="{{ $inspection['id'] }}">{{ $inspection['label'] }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="payrollLiveIef">Inspection de l’Éducation et de la Formation (IEF)</label>
              <select class="form-control" id="payrollLiveIef" data-payroll-live-ief disabled>
                <option value="">Choisissez d’abord une IA</option>
              </select>
            </div>

            <div class="form-group">
              <label for="payrollLiveMatricule">Matricule</label>
              <input
                class="form-control"
                id="payrollLiveMatricule"
                type="search"
                list="payrollLiveMatriculeSuggestions"
                placeholder="Saisir un matricule"
                autocomplete="off"
                autocapitalize="characters"
                spellcheck="false"
                data-payroll-live-matricule
                disabled
              >
              <datalist id="payrollLiveMatriculeSuggestions" data-payroll-live-suggestions></datalist>
            </div>

            <button class="payroll-live-reset" type="button" data-payroll-live-reset disabled>
              <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
              Réinitialiser
            </button>
          </div>

          <p class="payroll-live-results" aria-live="polite">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
            <span data-payroll-live-results>Affichage de tous les résultats.</span>
          </p>
        </section>
      @endif
    @else
      <section class="filter-panel" aria-label="Filtres de la page">
        @foreach ($page['filters'] as $index => $filter)
          @php
            $filterId = $slug.'-filter-'.$index;
>>>>>>> origin/module-paie
          @endphp
          <div class="form-group">
            <label for="{{ $filterId }}">{{ $filter }}</label>
            <select class="form-control" id="{{ $filterId }}">
              <option value="">Tous</option>
              <option>Juin 2026</option>
              <option>IA Dakar</option>
              <option>Validé</option>
            </select>
          </div>
        @endforeach
        <div class="actions-group">
          <button class="btn-secondary" type="button">Filtrer</button>
        </div>
      </section>
    @endif

    @if (! empty($page['helpText']))
      <section class="help-card">
        <h2>{{ $page['helpTitle'] }}</h2>
        <p>{{ $page['helpText'] }}</p>
      </section>
    @endif

<<<<<<< HEAD
    @if (! empty($page['chart']))
=======
    @if (! $connected && ! empty($page['chart']))
>>>>>>> origin/module-paie
      @php
        $heights = [58, 74, 48, 86, 66, 96];
      @endphp
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Graphique mensuel</h2>
            <p>Vue synthétique de la période</p>
          </div>
        </div>
        <div class="mini-chart">
<<<<<<< HEAD
          @foreach (($page['chart'] ?? []) as $index => $label)
=======
          @foreach ($page['chart'] as $index => $label)
>>>>>>> origin/module-paie
            <div class="mini-bar">
              <span style="height: {{ $heights[$index % count($heights)] }}px"></span>
              {{ $label }}
            </div>
          @endforeach
        </div>
      </section>
    @endif

    @if (! empty($page['calculator']))
      <section class="result-card" data-indemnity-result hidden></section>
    @endif

<<<<<<< HEAD
    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="moduleTable">
          <thead>
            <tr>
              @foreach (($page['columns'] ?? []) as $column)
                <th>{{ $column }}</th>
=======
    {{-- Tableau générique construit avec columns et rows. --}}
    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="moduleTable" data-paginated-table>
          <thead>
            <tr>
              @foreach ($page['columns'] as $column)
                <th @class(['actions-cell' => $loop->last])>{{ $column }}</th>
>>>>>>> origin/module-paie
              @endforeach
            </tr>
          </thead>
          <tbody>
<<<<<<< HEAD
            @foreach (($page['rows'] ?? []) as $row)
              <tr>
                @foreach (($row ?? []) as $cell)
                  <td>{!! $cell !!}</td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <p class="empty-message">Aucune donnée trouvée.</p>

      @php
        $pagination = $page['pagination'] ?? ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 10];
        $currentPage = max(1, (int) ($pagination['current_page'] ?? 1));
        $lastPage = max(1, (int) ($pagination['last_page'] ?? 1));
        $previousPage = max(1, $currentPage - 1);
        $nextPage = min($lastPage, $currentPage + 1);
      @endphp

      <div class="pagination" aria-label="Pagination">
        @if ($currentPage > 1)
          <a class="page-btn" href="{{ request()->fullUrlWithQuery(['page' => $previousPage]) }}" aria-label="Page précédente">←</a>
        @else
          <button class="page-btn" type="button" aria-label="Page précédente" disabled>←</button>
        @endif

        @php
          $pages = range(1, $lastPage);
        @endphp

        @foreach ($pages as $pageNumber)
          <a
            class="page-btn {{ $pageNumber === $currentPage ? 'active' : '' }}"
            href="{{ request()->fullUrlWithQuery(['page' => $pageNumber]) }}"
            data-page-number
          >
            {{ $pageNumber }}
          </a>
        @endforeach

        @if ($currentPage < $lastPage)
          <a class="page-btn" href="{{ request()->fullUrlWithQuery(['page' => $nextPage]) }}" aria-label="Page suivante">→</a>
        @else
          <button class="page-btn" type="button" aria-label="Page suivante" disabled>→</button>
        @endif
      </div>
    </section>
  </section>
</main>
=======
            @forelse ($page['rows'] as $row)
              @php
                $rowIndex = $loop->index;
                $rowFilter = data_get($moduleData, 'row_filters.'.$rowIndex, []);
              @endphp
              <tr
                data-table-row
                @if ($connected)
                  data-payroll-row
                  data-payroll-ia="{{ data_get($rowFilter, 'ia_id') }}"
                  data-payroll-ief="{{ data_get($rowFilter, 'ief_id') }}"
                  data-payroll-matricule="{{ data_get($rowFilter, 'matricule') }}"
                @endif
              >
                @foreach ($row as $cell)
                  <td @class(['actions-cell' => $loop->last])>
                    {{-- Une cellule peut être un badge, des actions ou du texte. --}}
                    @if ($connected && is_array($cell) && isset($cell['badge']))
                      <span class="badge badge-{{ $cell['badge'] }}">{{ $cell['value'] }}</span>
                    @elseif ($connected && is_array($cell) && isset($cell['actions']))
                      @foreach ($cell['actions'] as $cellAction)
                        @if ($cellAction['code'] === 'view-payslip')
                          <a
                            class="btn-table-action"
                            href="{{ route('paie.payslip', ['payslip' => data_get($cellAction, 'payload.payroll_payslip_id')]) }}"
                          >
                            {{ $cellAction['label'] }}
                          </a>
                        @else
                          <button
                            class="btn-table-action"
                            type="button"
                            data-payroll-action="{{ $cellAction['code'] }}"
                            data-action-defaults="{{ json_encode($cellAction['payload'] ?? [], JSON_UNESCAPED_UNICODE) }}"
                          >
                            {{ $cellAction['label'] }}
                          </button>
                        @endif
                      @endforeach
                    @elseif ($connected)
                      {{ $cell }}
                    @else
                      {!! $cell !!}
                    @endif
                  </td>
                @endforeach
              </tr>
            @empty
              <tr class="table-empty-row">
                <td colspan="{{ max(1, count($page['columns'])) }}" class="table-empty-cell">
                  Aucune donnée disponible pour cette sélection.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <p class="empty-message">Aucune donnée trouvée.</p>
      {{--
        Pagination locale commune à toutes les pages. Le script
        public/assets/js/app.js affiche uniquement les lignes de la page
        courante et recalcule les pages après une recherche IA/IEF/matricule.
      --}}
      <nav
        class="pagination"
        data-table-pagination
        data-table-target="#moduleTable"
        data-current-page="1"
        aria-label="Pagination du tableau"
      >
        <p class="pagination-summary" data-pagination-summary aria-live="polite"></p>
        <div class="pagination-controls">
          <button class="page-btn page-btn-direction" type="button" data-page-action="first" aria-label="Première page">
            <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
          </button>
          <button class="page-btn page-btn-direction" type="button" data-page-action="previous" aria-label="Page précédente">
            <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
          </button>
          <button class="page-btn page-number active" type="button" data-current-page-number aria-current="page" tabindex="-1">1</button>
          <button class="page-btn page-btn-direction" type="button" data-page-action="next" aria-label="Page suivante">
            <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
          </button>
          <button class="page-btn page-btn-direction" type="button" data-page-action="last" aria-label="Dernière page">
            <i class="fa-solid fa-angles-right" aria-hidden="true"></i>
          </button>
          <label class="visually-hidden" for="{{ $slug }}-page-size">Nombre de lignes par page</label>
          <select class="page-size-select" id="{{ $slug }}-page-size" data-page-size aria-label="Nombre de lignes par page">
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
        </div>
      </nav>
    </section>
  </section>
</main>

@if ($connected)
  {{--
    Modale unique des actions Paie. payroll.js crée les champs dans
    data-payroll-fields depuis la configuration JavaScript située plus bas.
  --}}
  <div class="payroll-modal-backdrop" data-payroll-modal hidden>
    <section class="payroll-modal" role="dialog" aria-modal="true" aria-labelledby="payrollModalTitle">
      <form data-payroll-form novalidate>
        <header class="payroll-modal-header">
          <div>
            <span class="auth-kicker">Opération sécurisée</span>
            <h2 id="payrollModalTitle" data-payroll-modal-title>Gestion de la paie</h2>
          </div>
          <button class="payroll-modal-close" type="button" data-payroll-modal-close aria-label="Fermer">&times;</button>
        </header>
        <p class="payroll-confirmation" data-payroll-confirmation hidden></p>
        <div class="payroll-form-grid" data-payroll-fields></div>
        <div class="form-status" data-payroll-status role="alert"></div>
        <footer class="payroll-modal-actions">
          <button class="btn-secondary" type="button" data-payroll-modal-close>Annuler</button>
          <button class="btn-primary" type="submit" data-payroll-submit>
            <i class="fa-solid fa-check" aria-hidden="true"></i>
            Confirmer
          </button>
        </footer>
      </form>
    </section>
  </div>

  @push('scripts')
    {{-- Transmettre proprement les données PHP au script payroll.js. --}}
    <script>
      window.SICOREPayrollPage = {{ Illuminate\Support\Js::from([
        'slug' => $slug,
        'data' => $moduleData,
        'forms' => $payrollForms,
        'actionUrl' => route('paie.action', ['action' => '__ACTION__']),
      ]) }};
    </script>
    <script src="{{ asset('assets/js/payroll.js') }}" defer></script>
  @endpush
@endif
>>>>>>> origin/module-paie
