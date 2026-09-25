@php
  $analytics = $dashboard['analytics'] ?? ['history' => [], 'distributions' => []];
  $palette = ['#007451', '#e4c600', '#10dba1', '#ffa51c', '#748296', '#7b61a8', '#249db2'];
@endphp
<div class="sa-dashboard">
  <header class="sa-heading"><div><span class="sa-eyebrow">ADMINISTRATION CENTRALE</span><h1>{{ $dashboard['title'] }}</h1><p>Bonjour {{ $dashboard['name'] }}. Une vue d’ensemble de votre plateforme.</p></div><span class="sa-updated"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Actualisé le {{ \Carbon\Carbon::parse($dashboard['updated_at'])->format('d/m à H:i') }}</span></header>
  <div class="sa-metrics">
    @foreach ($dashboard['cards'] as $card)
      <article class="sa-metric"><div><span>{{ $card['label'] }}</span><i class="fa-solid {{ $card['icon'] }}" aria-hidden="true"></i></div><strong>{{ number_format((float) $card['value'], 0, ',', ' ') }} <small>{{ $card['unit'] }}</small></strong><p><span class="sa-dot"></span>{{ $card['group'] ?? 'Vue globale' }}</p></article>
    @endforeach
  </div>
  <div class="sa-chart-grid">
    <section class="sa-panel sa-history"><div class="sa-panel-top"><div><h2>Évolution des inscriptions</h2><p>Nouveaux enseignants et comptes par mois · hors éléments supprimés</p></div><div class="sa-switch" aria-label="Période du graphique"><button type="button" data-months="6" aria-pressed="false">6 mois</button><button type="button" data-months="12" aria-pressed="true">12 mois</button></div></div><div class="sa-chart-legend"><span><i style="background:#e4c600"></i>Enseignants</span><span><i style="background:#007451"></i>Comptes utilisateurs</span></div><div id="sa-history-chart" class="sa-history-chart"></div><script type="application/json" id="sa-history-data">@json($analytics['history'])</script><details class="sa-chart-data"><summary>Consulter les valeurs</summary><div class="dashboard-table-scroll"><table class="dashboard-table"><thead><tr><th>Mois</th><th>Enseignants</th><th>Comptes utilisateurs</th></tr></thead><tbody>@foreach ($analytics['history'] as $point)<tr><td>{{ $point['label'] }}</td><td>{{ $point['teachers'] }}</td><td>{{ $point['users'] }}</td></tr>@endforeach</tbody></table></div></details></section>
    @foreach ($analytics['distributions'] as $distribution)
      @php
        $total = array_sum(array_column($distribution['items'], 'value'));
        $offset = 0;
        $showPercentage = !in_array($distribution['title'], ['Comptes enseignants par corps', 'Enseignants par corps', 'État des comptes'], true);
      @endphp
      <section class="sa-panel sa-distribution {{ $loop->first ? 'sa-accounts' : '' }}"><h2>{{ $distribution['title'] }}</h2><p>{{ $distribution['subtitle'] }}</p>
        @if ($total > 0)
        <div class="sa-distribution-body"><div class="sa-donut"><svg viewBox="0 0 120 120" role="img" aria-label="{{ $distribution['title'] }} : {{ $total }} au total"><circle cx="60" cy="60" r="45" fill="none" stroke="#f0f2f5" stroke-width="18"/>
          @foreach ($distribution['items'] as $item)
            @php $percent = $item['value'] / $total * 100; @endphp
            <circle cx="60" cy="60" r="45" fill="none" stroke="{{ $palette[$loop->index % count($palette)] }}" stroke-width="18" pathLength="100" stroke-dasharray="{{ $percent }} {{ 100 - $percent }}" stroke-dashoffset="{{ -$offset }}" transform="rotate(-90 60 60)"><title>{{ $item['label'] }} : {{ $item['value'] }}</title></circle>
            @php $offset += $percent; @endphp
          @endforeach
        </svg><div><strong>{{ number_format($total, 0, ',', ' ') }}</strong><small>au total</small></div></div><ul class="sa-legend {{ $showPercentage ? '' : 'sa-legend-counts' }}">@foreach ($distribution['items'] as $item)<li><span class="sa-dot" style="background:{{ $palette[$loop->index % count($palette)] }}"></span><span>{{ $item['label'] }}</span>@if ($showPercentage)<strong>{{ number_format($item['value'] / $total * 100, 1, ',', ' ') }} %</strong><small>{{ number_format($item['value'], 0, ',', ' ') }}</small>@else<strong>{{ number_format($item['value'], 0, ',', ' ') }}</strong>@endif</li>@endforeach</ul></div>
        @else <div class="sa-empty"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Aucune donnée disponible</span></div> @endif
      </section>
    @endforeach
  </div>
  <div class="sa-bottom"><aside class="sa-panel"><h2>Points de suivi</h2><p>Les informations à examiner</p><ol class="sa-notices">@forelse ($dashboard['notices'] as $notice)<li>{{ $notice }}</li>@empty<li>Aucun point de suivi signalé.</li>@endforelse</ol><h2 class="sa-shortcut-title">Accès rapides</h2><div class="sa-shortcuts">@foreach ($dashboard['actions'] as $action)<a href="{{ route($action['route']) }}"><i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i>{{ $action['label'] }}<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>@endforeach</div></aside><div class="sa-tables">@foreach ($dashboard['sections'] as $section)<section class="sa-panel"><h2>{{ $section['title'] }}</h2>@if ($section['rows'])<div class="dashboard-table-scroll"><table class="dashboard-table"><thead><tr>@foreach ($section['columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead><tbody>@foreach ($section['rows'] as $row)<tr>@foreach ($row as $cell)<td>{{ $cell ?? 'Non renseigné' }}</td>@endforeach</tr>@endforeach</tbody></table></div>@else<p class="sa-empty">Aucune donnée disponible.</p>@endif</section>@endforeach</div></div>
</div>
