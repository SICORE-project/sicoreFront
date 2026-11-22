{{--
  Édition IA : état de la validation du mois et modifications de fiches faites
  depuis la dernière validation. Les fiches se modifient dans Gestion du
  personnel > Enseignants ; la validation se fait avec le bouton « Valider
  l'Édition » en bas du tableau.
  Données : sicoreBack PayrollEditionService::pageData() (clé « edition »).
  Envoi : route ia.edition.validate (POST).
--}}
@php
  $statusLabels = [
    'jamais' => ['Non validée', 'badge-pending', 'Aucune validation pour ce mois. L’État des salaires de l’IA reste bloqué tant que l’Édition n’est pas validée. La validation est définitive.'],
    'validee' => ['Validée', 'badge-active', 'L’Édition de ce mois est validée définitivement : les bulletins de l’IA sont arrêtés et l’État des salaires est disponible. Les mises à jour faites après cette validation seront prises en compte le mois suivant.'],
  ];
  [$statusText, $statusBadge, $statusHelp] = $statusLabels[$edition['status']] ?? $statusLabels['jamais'];
@endphp

<section class="table-card edition-workflow" aria-labelledby="editionWorkflowTitle">
  <h2 id="editionWorkflowTitle">Validation de l’Édition — {{ $edition['period_label'] ?? '' }}
    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
  </h2>
  <p class="table-card-subtitle">{{ $statusHelp }}</p>
  @if (!empty($edition['validated_at']))
    <p class="table-card-subtitle">Dernière validation le {{ \Illuminate\Support\Carbon::parse($edition['validated_at'])->format('d/m/Y à H:i') }}@if (!empty($edition['validated_by'])) par {{ $edition['validated_by'] }}@endif — {{ $edition['payslip_count'] }} bulletin(s), net {{ number_format((float) $edition['total_net'], 0, ',', ' ') }} FCFA.</p>
  @endif
  <p class="table-card-subtitle">Les fiches des enseignants se mettent à jour dans Gestion du personnel &gt; Enseignants.</p>
  <div class="form-status" data-edition-status role="status" aria-live="polite"></div>

  @if (!empty($edition['changes']))
    <details class="edition-changes" open>
      <summary>{{ $edition['status'] === 'validee' ? 'Mises à jour reçues après la validation — prises en compte le mois suivant' : 'Mises à jour à prendre en compte à la validation de ce mois' }} ({{ count($edition['changes']) }})</summary>
      <ul>
        @foreach ($edition['changes'] as $change)
          <li>
            <strong>{{ $change['matricule'] }} — {{ $change['name'] }}</strong>
            <span class="edition-change-date">{{ \Illuminate\Support\Carbon::parse($change['at'])->format('d/m/Y H:i') }}</span>
            <ul>
              @foreach ($change['changes'] as $field)
                <li>{{ $field['label'] }} : <s>{{ $field['avant'] ?? '—' }}</s> → <strong>{{ $field['apres'] ?? '—' }}</strong></li>
              @endforeach
            </ul>
          </li>
        @endforeach
      </ul>
    </details>
  @endif
</section>

@push('styles')
<style>
  .edition-workflow { padding: 16px 18px; margin-bottom: 16px; }
  .edition-workflow h2 { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 0 0 4px; font-size: 1.05rem; }
  .edition-changes { margin-top: 12px; }
  .edition-changes summary { cursor: pointer; font-weight: 600; }
  .edition-changes ul { margin: 8px 0 0 18px; padding: 0; }
  .edition-change-date { color: #64748b; font-size: .85em; margin-left: 6px; }
  @media print { .edition-workflow { display: none !important; } }
</style>
@endpush

@if (!empty($edition['can_validate']))
@push('scripts')
<script>
  (function () {
    var edition = @json(['period_id' => $edition['period_id'], 'period_label' => $edition['period_label']], JSON_UNESCAPED_UNICODE);
    var csrf = @json(csrf_token());
    var validateUrl = @json(route('ia.edition.validate'));
    var status = document.querySelector('[data-edition-status]');

    document.querySelectorAll('[data-edition-validate]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!window.confirm('Valider l’Édition de ' + (edition.period_label || 'ce mois') + ' ? Les bulletins de votre IA seront recalculés avec les fiches à jour. Cette validation est définitive : les mises à jour ultérieures iront au mois suivant.')) return;
        button.disabled = true;
        status.textContent = 'Validation et recalcul des bulletins en cours…';
        status.scrollIntoView({ block: 'center' });
        fetch(validateUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'Idempotency-Key': 'edition-' + Date.now() + '-' + Math.random().toString(36).slice(2) },
          body: JSON.stringify({ payroll_period_id: edition.period_id })
        }).then(function (r) {
          return r.json().catch(function () { return {}; }).then(function (json) {
            if (!r.ok) {
              var errs = json.errors || {};
              var list = Object.keys(errs).map(function (k) { return [].concat(errs[k]).join(' '); });
              status.textContent = (json.message || 'Validation impossible.') + (list.length ? ' ' + list.join(' ') : '');
              button.disabled = false;
              return;
            }
            status.textContent = json.message || 'Édition validée.';
            window.location.reload();
          });
        }).catch(function () { status.textContent = 'Le service est indisponible.'; button.disabled = false; });
      });
    });
  }());
</script>
@endpush
@endif
