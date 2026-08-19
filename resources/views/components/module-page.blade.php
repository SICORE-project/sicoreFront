<main class="main-content">
  <x-topbar
    :title="$page['title']"
    :subtitle="$page['breadcrumb']"
    :icon="$pageIcon"
    search-id="moduleSearch"
    search-placeholder="Rechercher…"
    filter-target="#moduleTable"
  />

  <section class="content-area">
    @if (! empty($page['objectives']))
      <section class="objective-card {{ ! empty($page['sensitive']) ? 'sensitive-panel' : '' }}">
        <h2>Objectifs métier</h2>
        <ul class="objective-list">
          @foreach (($page['objectives'] ?? []) as $objective)
            <li>{{ $objective }}</li>
          @endforeach
        </ul>
      </section>
    @endif

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
            Fermer la période
          </button>
        @endif
      </div>
    </div>

    @if (! empty($page['filters']))
      <section class="filter-panel" aria-label="Filtres de la page">
        @foreach (($page['filters'] ?? []) as $index => $filter)
          @php
            $filterId = ($slug ?? 'module') . '-filter-' . $index;
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

    @if (! empty($page['chart']))
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
          @foreach (($page['chart'] ?? []) as $index => $label)
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

    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="moduleTable">
          <thead>
            <tr>
              @foreach (($page['columns'] ?? []) as $column)
                <th>{{ $column }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
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
