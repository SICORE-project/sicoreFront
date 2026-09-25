@php
  $profile = collect($dashboard['sections'])->firstWhere('title', 'Mon dossier');
  $history = collect($dashboard['sections'])->firstWhere('title', 'Mes derniers bulletins');
  $payments = $dashboard['charts']['payments'] ?? [];
  $total = array_sum(array_column($payments, 'value'));
  $paid = $payments[0]['value'] ?? 0;
  $rate = $total ? (int) round($paid / $total * 100) : 0;
@endphp
<div class="teacher-dashboard">
  <header class="teacher-hero">
    <div><span class="teacher-eyebrow">MON ESPACE · ENSEIGNANT</span><h1>Bonjour, {{ $dashboard['name'] }}</h1><p>Votre dossier et votre paie, en un coup d’œil.</p></div>
    <div class="teacher-hero-meta"><span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Espace personnel</span><small>Mis à jour le {{ \Carbon\Carbon::parse($dashboard['updated_at'])->format('d/m/Y à H:i') }}</small></div>
  </header>
  @foreach ($dashboard['notices'] as $notice)
    <p class="dashboard-notice" role="status">{{ $notice }}</p>
  @endforeach
  @if ($dashboard['cards'])
    <section class="teacher-kpis" aria-label="Mes indicateurs de paie">
      @foreach ($dashboard['cards'] as $card)
        <article class="teacher-kpi"><span class="teacher-kpi-icon"><i class="fa-solid {{ $card['icon'] }}" aria-hidden="true"></i></span><div><p>{{ $card['label'] }}</p><strong>{{ $card['value'] === null ? '—' : number_format((float) $card['value'], 0, ',', ' ') }} @if ($card['unit'])<small>{{ $card['unit'] }}</small>@endif</strong></div></article>
      @endforeach
    </section>
  @endif
  @if ($dashboard['charts'] ?? null)
    <div class="teacher-charts">
      <section class="teacher-panel teacher-evolution">
        <header class="teacher-panel-header"><div><span class="teacher-eyebrow">MA RÉMUNÉRATION</span><h2>Évolution du montant net</h2><p>Montants des bulletins validés · FCFA</p></div><div class="teacher-range" role="group" aria-label="Nombre de périodes affichées"><button type="button" data-teacher-range="6" aria-pressed="true">6 périodes</button><button type="button" data-teacher-range="12" aria-pressed="false">12 périodes</button></div></header>
        <div id="teacher-net-chart" class="teacher-net-chart" data-history='@json($dashboard["charts"]["net_history"])'></div>
        @if (empty($dashboard['charts']['net_history']))
          <div class="teacher-chart-empty"><i class="fa-solid fa-chart-line" aria-hidden="true"></i><strong>Votre historique apparaîtra ici</strong><p>Dès qu’un bulletin sera validé, vous pourrez suivre son montant net.</p></div>
        @else
          <p class="teacher-chart-caption" id="teacher-chart-caption">Dernières périodes disponibles. Survolez un point pour voir le montant.</p>
          <details class="teacher-chart-data"><summary>Consulter les montants</summary><table class="dashboard-table"><thead><tr><th>Période</th><th>Net (FCFA)</th></tr></thead><tbody>@foreach ($dashboard['charts']['net_history'] as $point)<tr><td>{{ $point['period'] }}</td><td>{{ number_format($point['amount'], 0, ',', ' ') }}</td></tr>@endforeach</tbody></table></details>
        @endif
      </section>
      <section class="teacher-panel">
        <header class="teacher-panel-header"><div><span class="teacher-eyebrow">MES VERSEMENTS</span><h2>Situation des paiements</h2><p>Ensemble de mes bulletins validés</p></div></header>
        @if ($total)
          <div class="teacher-donut" role="img" aria-label="{{ $paid }} bulletins payés sur {{ $total }}">
            <svg viewBox="0 0 180 180" aria-hidden="true"><circle cx="90" cy="90" r="70" fill="none" stroke="#eef2f6" stroke-width="17" />
              @php $offset = 0; @endphp
              @foreach ($payments as $payment)
                @php $length = $payment['value'] / $total * 100; @endphp
                @if ($length > 0)<circle cx="90" cy="90" r="70" fill="none" stroke="{{ $payment['color'] }}" stroke-width="17" pathLength="100" stroke-dasharray="{{ $length }} {{ 100 - $length }}" stroke-dashoffset="{{ -$offset }}" transform="rotate(-90 90 90)" />@endif
                @php $offset += $length; @endphp
              @endforeach
            </svg><div><strong>{{ $rate }}<small>%</small></strong><span>bulletins payés</span></div>
          </div>
          <ul class="teacher-chart-legend">@foreach ($payments as $payment)<li><span><i style="background: {{ $payment['color'] }}" aria-hidden="true"></i>{{ $payment['label'] }}</span><strong>{{ $payment['value'] }}</strong></li>@endforeach</ul>
        @else
          <div class="teacher-chart-empty"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><strong>Aucun paiement à afficher</strong><p>La répartition sera disponible avec vos premiers bulletins validés.</p></div>
        @endif
      </section>
    </div>
  @endif
  <div class="teacher-details-grid">
    @if ($history)
      <section class="teacher-panel"><header class="teacher-panel-header"><div><h2>Mes derniers bulletins</h2><p>Les cinq dernières périodes disponibles</p></div><span class="teacher-panel-symbol"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i></span></header>
        @if ($history['rows'])
          <div class="dashboard-table-scroll"><table class="dashboard-table"><thead><tr>@foreach ($history['columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead><tbody>@foreach ($history['rows'] as $row)<tr>@foreach ($row as $cell)<td>@if ($loop->last)<span class="teacher-payment-badge {{ $cell === 'Payé' ? 'is-paid' : ($cell === 'Rejeté' ? 'is-rejected' : 'is-pending') }}">{{ $cell }}</span>@else{{ $cell ?? '—' }}@endif</td>@endforeach</tr>@endforeach</tbody></table></div>
        @else<p class="dashboard-muted">Aucun bulletin validé pour le moment.</p>@endif
      </section>
    @endif
    @if ($profile)
      <section class="teacher-panel teacher-profile"><header class="teacher-panel-header"><div><h2>Mon dossier</h2><p>Mes informations de référence</p></div><span class="teacher-panel-symbol"><i class="fa-solid fa-id-card" aria-hidden="true"></i></span></header><dl>@foreach ($profile['rows'] as $row)<div><dt>{{ $row[0] }}</dt><dd>{{ $row[1] ?? 'Non renseigné' }}</dd></div>@endforeach</dl></section>
    @endif
  </div>
</div>
