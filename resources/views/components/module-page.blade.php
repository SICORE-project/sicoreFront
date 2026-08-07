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
            <i class="{{ str_contains((string) $stat['icon'], 'fa-') ? $stat['icon'] : ($statIconMap[$stat['icon']] ?? 'fa-solid fa-circle') }}" aria-hidden="true"></i>
          </span>
        </article>
      @endforeach
    </div>

    <div class="actions-row">
      <p class="breadcrumb">{{ $page['breadcrumb'] }}</p>
      <div class="actions-group">
        @foreach ($page['actions'] as $index => $label)
          <button
            class="{{ $index === 0 ? 'btn-primary' : 'btn-secondary' }}"
            type="button"
            @if (! empty($page['calculator']) && $label === 'Calculer') data-calculate-indemnity @endif
          >
            {{ $label }}
          </button>
        @endforeach

        @if (! empty($page['closePeriod']))
          <button
            class="btn-danger-soft"
            type="button"
            data-confirm="Êtes-vous sûr de vouloir fermer cette période de paie ? Cette action est sensible."
            data-success-message="Période de paie fermée."
          >
            Fermer la période
          </button>
        @endif
      </div>
    </div>

    <section class="filter-panel" aria-label="Filtres de la page">
      @foreach ($page['filters'] as $index => $filter)
        @php
          $filterId = $slug.'-filter-'.$index;
          $filterLower = mb_strtolower($filter);
          $colIndex = -1;
          foreach ($page['columns'] as $ci => $col) {
              $colLower = mb_strtolower(strip_tags($col));
              if ($colLower === $filterLower) { $colIndex = $ci; break; }
          }
          if ($colIndex === -1) {
              foreach ($page['columns'] as $ci => $col) {
                  $colLower = mb_strtolower(strip_tags($col));
                  if (str_contains($colLower, $filterLower) || str_contains($filterLower, $colLower)) { $colIndex = $ci; break; }
              }
          }
          $options = [];
          if ($colIndex >= 0) {
              foreach ($page['rows'] as $row) {
                  if (isset($row[$colIndex])) {
                      $text = trim(strip_tags($row[$colIndex]));
                      if ($text !== '' && !in_array($text, $options, true)) {
                          $options[] = $text;
                      }
                  }
              }
          }
        @endphp
        <div class="form-group">
          <label for="{{ $filterId }}">{{ $filter }}</label>
          <select class="form-control" id="{{ $filterId }}" data-filter-col="{{ $colIndex }}">
            <option value="">Tous</option>
            @foreach ($options as $opt)
              <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
          </select>
        </div>
      @endforeach
      <div class="actions-group">
        <button class="btn-secondary" type="button" data-apply-filters>Filtrer</button>
      </div>
    </section>

    @if (! empty($page['helpText']))
      <section class="help-card">
        <h2>{{ $page['helpTitle'] }}</h2>
        <p>{{ $page['helpText'] }}</p>
      </section>
    @endif

    @if (! empty($page['chart']))
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

    @if (! empty($page['calculator']))
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
            @foreach ($page['rows'] as $row)
              <tr>
                @foreach ($row as $cell)
                  <td @class(['actions-cell' => $loop->last])>{!! $cell !!}</td>
                @endforeach
              </tr>
            @endforeach
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

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.querySelector('[data-apply-filters]');
  if (!btn) return;

  function applyFilters() {
    var panel = btn.closest('.filter-panel');
    if (!panel) return;
    var selects = panel.querySelectorAll('select[data-filter-col]');
    var table = document.querySelector('#moduleTable');
    if (!table) return;

    var rows = table.querySelectorAll('tbody tr');
    var visible = 0;

    rows.forEach(function (row) {
      var cells = row.querySelectorAll('td');
      var show = true;
      selects.forEach(function (select) {
        var ci = parseInt(select.getAttribute('data-filter-col'), 10);
        if (ci < 0 || !select.value) return;
        if (ci < cells.length) {
          var cellText = (cells[ci].textContent || '').trim();
          if (cellText.indexOf(select.value) === -1) show = false;
        }
      });
      row.classList.toggle('is-hidden', !show);
      if (show) visible++;
    });

    var card = table.closest('.table-card');
    var empty = card ? card.querySelector('.empty-message') : null;
    if (empty) empty.classList.toggle('show', visible === 0);
  }

  btn.addEventListener('click', applyFilters);
  var panel = btn.closest('.filter-panel');
  if (panel) {
    panel.querySelectorAll('select[data-filter-col]').forEach(function (select) {
      select.addEventListener('change', applyFilters);
    });
  }
});
</script>
@endpush
@endonce
