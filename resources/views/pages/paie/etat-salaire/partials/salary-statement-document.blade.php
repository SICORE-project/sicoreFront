@php
  $statementRows = collect($statement['rows'] ?? []);
  $statementColumns = collect($statement['columns'] ?? []);
  $scopeLabel = $statementRows->pluck('corps')->filter()->unique()->join(', ') ?: 'Tous les corps';
  $iaLabel = $statementRows->pluck('ia')->filter()->unique()->join(', ') ?: ($moduleData['scope_label'] ?? 'Toutes les IA');
  $iefLabel = $statementRows->pluck('ief')->filter()->unique()->join(', ') ?: 'Toutes les IEF';
@endphp

<article class="salary-statement-document">
  <header class="salary-statement-official-header">
    <div class="salary-statement-republic">
      <img src="{{ asset('assets/images/flag-senegal.svg') }}" alt="Drapeau du Sénégal">
      <div><strong>République du Sénégal</strong><span>Un Peuple · Un But · Une Foi</span></div>
    </div>
    <div class="salary-statement-title">
      <span>Ministère de l’Emploi et de la Formation Professionnelle et Technique</span>
      <h2>État des salaires</h2>
      <strong>{{ mb_strtoupper($statement['period_label'] ?? '') }}</strong>
    </div>
    <div class="salary-statement-edition-meta">
      <span>Année académique</span><strong>{{ $statement['academic_year'] ?? 'Non renseignée' }}</strong><small>Édité le {{ $statement['generated_at'] ?? '' }}</small>
    </div>
  </header>

  @if (! empty($statement['heading']))
    <h3 class="salary-statement-heading">{{ $statement['heading'] }}</h3>
  @endif

  <section class="salary-statement-scope" aria-label="Périmètre de l’état">
    <div><span>Corps</span><strong>{{ $scopeLabel }}</strong></div>
    <div><span>Inspection académique</span><strong>{{ $iaLabel }}</strong></div>
    <div><span>IEF</span><strong>{{ $iefLabel }}</strong></div>
    <div><span>Effectif</span><strong>{{ $statementRows->count() }} agent(s)</strong></div>
    <div><span>Unité</span><strong>Franc CFA (FCFA)</strong></div>
  </section>

  <div class="salary-statement-table-wrap">
    <table class="salary-statement-table">
      <thead><tr>
        @foreach ($statementColumns as $column)
          <th @class(['salary-statement-amount' => ! empty($column['amount'])])>{{ $column['label'] }}</th>
        @endforeach
      </tr></thead>
      <tbody>
        @forelse ($statementRows as $row)
          <tr>
            @foreach ($statementColumns as $column)
              @php
                $key = $column['key'];
                $value = ! empty($column['amount']) ? ($row[$key.'_display'] ?? '0') : ($row[$key] ?? '');
              @endphp
              <td @class(['salary-statement-amount' => ! empty($column['amount'])])>
                @if ($key === 'payment_status_label')
                  <span class="badge badge-{{ $row['payment_status_variant'] ?? 'pending' }}">{{ $value }}</span>
                @elseif ($key === 'signature')
                  <span class="salary-statement-signature-line" aria-label="Zone d’émargement"></span>
                @else
                  {{ $value }}
                @endif
              </td>
            @endforeach
          </tr>
        @empty
          <tr><td class="salary-statement-empty" colspan="{{ max(1, $statementColumns->count()) }}">Aucun bulletin ne correspond aux critères sélectionnés.</td></tr>
        @endforelse
      </tbody>
      @if ($statementRows->isNotEmpty())
        <tfoot><tr>
          @foreach ($statementColumns as $column)
            @php $key = $column['key']; @endphp
            <td @class(['salary-statement-amount' => ! empty($column['amount'])])>
              @if ($key === 'first_name') TOTAL GÉNÉRAL
              @elseif (! empty($column['amount'])) {{ data_get($statement, 'totals.'.$key, '0') }}
              @endif
            </td>
          @endforeach
        </tr></tfoot>
      @endif
    </table>
  </div>

  <footer class="salary-statement-footer">
    <div>
      @if (! empty($statement['service_done']))
        <strong>Certification du service fait</strong><span>Les éléments portés sur le présent état correspondent aux bulletins calculés pour la période.</span>
      @else
        <strong>État édité sans mention de service fait</strong><span>Option d’impression sélectionnée par l’utilisateur.</span>
      @endif
    </div>
    <div class="salary-statement-signatory"><span>Ordonnateur de crédit</span><strong data-ordonnateur-label>L’Inspecteur de l’Éducation et de la Formation (IEF)</strong></div>
    <div class="salary-statement-signatory"><span>Visa et signature</span><strong>{{ $statement['signatory'] ?? 'Le responsable habilité de la paie' }}</strong></div>
  </footer>
</article>
