@php
  $analytics = $dashboard['analytics'] ?? ['history' => [], 'distributions' => []];
  $palette = ['#007451', '#e4c600', '#10dba1', '#ffa51c', '#748296', '#7b61a8', '#249db2'];
  // Couleur de l'icône des cartes selon le groupe (même design que les pages de paramétrage).
  $iconColors = ['Administration' => 'green', 'Personnel' => 'blue', 'Paie' => 'yellow', 'Crédits' => 'purple'];

  $updatedAt = \Carbon\Carbon::parse($dashboard['updated_at'])->locale('fr');
  $notices = $dashboard['notices'] ?? [];
  $actions = $dashboard['actions'] ?? [];
@endphp
<div class="sa-dashboard">

  {{-- ===== En-tête ===== --}}
  <header class="sa-hero">
    <div class="sa-hero-side">
      <span class="sa-updated" title="{{ $updatedAt->format('d/m/Y H:i:s') }}">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        Données actualisées {{ $updatedAt->diffForHumans() }}
      </span>
      <a class="sa-refresh" href="{{ route('dashboard') }}" data-sa-refresh>
        <i class="fa-solid fa-rotate-right" aria-hidden="true"></i><span>Actualiser</span>
      </a>
    </div>
  </header>

  {{-- ===== Indicateurs clés ===== --}}
  <section aria-label="Indicateurs clés">
    <div class="stats-grid four sa-kpis">
      @foreach ($dashboard['cards'] as $card)
        @php $color = $iconColors[$card['group'] ?? ''] ?? 'green'; @endphp
        <article class="stat-card sa-kpi sa-kpi--{{ $color }}">
          <div>
            <p class="stat-label">{{ $card['label'] }}</p>
            <p class="stat-value" data-sa-count="{{ (float) $card['value'] }}">{{ number_format((float) $card['value'], 0, ',', ' ') }}@if ($card['unit']) <small>{{ $card['unit'] }}</small>@endif</p>
            <p class="stat-note">{{ $card['group'] ?? 'Vue globale' }}</p>
          </div>
          <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $card['icon'] }}" aria-hidden="true"></i></span>
        </article>
      @endforeach
    </div>
  </section>

  {{-- ===== Graphiques ===== --}}
  <div class="sa-chart-grid">
    <section class="sa-panel sa-history">
      <div class="sa-panel-top">
        <div>
          <h2>Évolution des inscriptions</h2>
          <p>Nouveaux enseignants et comptes par mois · hors éléments supprimés</p>
        </div>
        <div class="sa-switch" role="group" aria-label="Période du graphique">
          <button type="button" data-months="6" aria-pressed="false">6 mois</button>
          <button type="button" data-months="12" aria-pressed="true">12 mois</button>
        </div>
      </div>

      <div class="sa-history-summary" aria-live="polite">
        <div class="sa-summary-item">
          <span class="sa-summary-swatch" style="background:#e4c600"></span>
          <div><strong data-sa-total="teachers">0</strong><small>Enseignants ajoutés</small></div>
        </div>
        <div class="sa-summary-item">
          <span class="sa-summary-swatch" style="background:#007451"></span>
          <div><strong data-sa-total="users">0</strong><small>Comptes créés</small></div>
        </div>
        <div class="sa-summary-item sa-summary-peak">
          <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
          <div><strong data-sa-peak>—</strong><small>Mois le plus actif</small></div>
        </div>
      </div>

      <div id="sa-history-chart" class="sa-history-chart"></div>
      <div class="sa-chart-tooltip" id="sa-chart-tooltip" role="status" hidden></div>
      <script type="application/json" id="sa-history-data">@json($analytics['history'])</script>

      <details class="sa-chart-data">
        <summary>Consulter les valeurs</summary>
        <div class="dashboard-table-scroll">
          <table class="dashboard-table">
            <thead><tr><th>Mois</th><th>Enseignants</th><th>Comptes utilisateurs</th></tr></thead>
            <tbody>
              @foreach ($analytics['history'] as $point)
                <tr><td>{{ $point['label'] }}</td><td>{{ $point['teachers'] }}</td><td>{{ $point['users'] }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </details>
    </section>

    @foreach ($analytics['distributions'] as $distribution)
      @php
        $total = array_sum(array_column($distribution['items'], 'value'));
        $offset = 0;
        $showPercentage = ! in_array($distribution['title'], ['Comptes enseignants par statut', 'Enseignants par statut', 'État des comptes'], true);
      @endphp
      <section class="sa-panel sa-distribution {{ $loop->first ? 'sa-accounts' : '' }}" data-sa-distribution>
        <h2>{{ $distribution['title'] }}</h2>
        <p>{{ $distribution['subtitle'] }}</p>
        @if ($total > 0)
          <div class="sa-distribution-body">
            <div class="sa-donut">
              <svg viewBox="0 0 120 120" role="img" aria-label="{{ $distribution['title'] }} : {{ $total }} au total">
                <circle cx="60" cy="60" r="45" fill="none" stroke="#f0f2f5" stroke-width="18"/>
                @foreach ($distribution['items'] as $item)
                  @php $percent = $item['value'] / $total * 100; @endphp
                  <circle class="sa-donut-segment" data-index="{{ $loop->index }}" cx="60" cy="60" r="45" fill="none"
                    stroke="{{ $palette[$loop->index % count($palette)] }}" stroke-width="18" pathLength="100"
                    stroke-dasharray="{{ $percent }} {{ 100 - $percent }}" stroke-dashoffset="{{ -$offset }}"
                    transform="rotate(-90 60 60)"><title>{{ $item['label'] }} : {{ $item['value'] }}</title></circle>
                  @php $offset += $percent; @endphp
                @endforeach
              </svg>
              <div><strong>{{ number_format($total, 0, ',', ' ') }}</strong><small>au total</small></div>
            </div>
            <ul class="sa-legend {{ $showPercentage ? '' : 'sa-legend-counts' }}">
              @foreach ($distribution['items'] as $item)
                <li data-index="{{ $loop->index }}" tabindex="0">
                  <span class="sa-dot" style="background:{{ $palette[$loop->index % count($palette)] }}"></span>
                  <span>{{ $item['label'] }}</span>
                  @if ($showPercentage)
                    <strong>{{ number_format($item['value'] / $total * 100, 1, ',', ' ') }} %</strong>
                    <small>{{ number_format($item['value'], 0, ',', ' ') }}</small>
                  @else
                    <strong>{{ number_format($item['value'], 0, ',', ' ') }}</strong>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        @else
          <div class="sa-empty"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Aucune donnée disponible</span></div>
        @endif
      </section>
    @endforeach
  </div>

  {{-- ===== Suivi, accès rapides et derniers éléments ===== --}}
  <div class="sa-bottom">
    <aside class="sa-side">
      <section class="sa-panel">
        <div class="sa-panel-title">
          <h2>Points de suivi</h2>
          @if (count($notices))
            <span class="sa-count-badge sa-count-badge--warn">{{ count($notices) }}</span>
          @endif
        </div>
        <p>Les informations à examiner</p>
        @if (count($notices))
          <ul class="sa-notices">
            @foreach ($notices as $notice)
              <li><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span>{{ $notice }}</span></li>
            @endforeach
          </ul>
        @else
          <div class="sa-all-good">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <div><strong>Tout est en ordre</strong><span>Aucun point de suivi signalé.</span></div>
          </div>
        @endif
      </section>

      @if (count($actions))
        <section class="sa-panel">
          <h2>Accès rapides</h2>
          <p>Vos outils d’administration</p>
          <div class="sa-shortcuts">
            @foreach ($actions as $action)
              <a href="{{ route($action['route']) }}">
                <span class="sa-shortcut-icon"><i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i></span>
                <span class="sa-shortcut-label">{{ $action['label'] }}</span>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
              </a>
            @endforeach
          </div>
        </section>
      @endif
    </aside>

    <div class="sa-tables">
      @foreach ($dashboard['sections'] as $section)
        <section class="sa-panel">
          <div class="sa-panel-title">
            <h2>{{ $section['title'] }}</h2>
            @if ($section['rows'])
              <span class="sa-count-badge">{{ count($section['rows']) }}</span>
            @endif
          </div>
          @if ($section['rows'])
            <div class="dashboard-table-scroll">
              <table class="dashboard-table">
                <thead><tr>@foreach ($section['columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
                  @foreach ($section['rows'] as $row)
                    <tr>@foreach ($row as $cell)<td data-label="{{ $section['columns'][$loop->index] ?? '' }}">{{ $cell ?? 'Non renseigné' }}</td>@endforeach</tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="sa-empty"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><span>Aucune donnée disponible.</span></div>
          @endif
        </section>
      @endforeach
    </div>
  </div>
</div>
