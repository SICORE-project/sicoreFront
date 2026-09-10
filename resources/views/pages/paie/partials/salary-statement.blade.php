@php
  $statementRows = collect($statement['rows'] ?? []);
  $statementColumns = collect($statement['columns'] ?? []);
  $selectedAcademicYear = request('academic_year_id');
  if (! $selectedAcademicYear) {
    $selectedAcademicYear = collect($moduleData['academic_years'] ?? [])
      ->firstWhere('label', $statement['academic_year'] ?? null)['id'] ?? null;
  }
  $scopeLabel = $statementRows->pluck('corps')->filter()->unique()->join(', ') ?: 'Tous les corps';
  $iaLabel = $statementRows->pluck('ia')->filter()->unique()->join(', ') ?: 'Toutes les IA';
  $iefLabel = $statementRows->pluck('ief')->filter()->unique()->join(', ') ?: 'Toutes les IEF';
@endphp

<section class="salary-statement-workspace" aria-labelledby="salaryStatementFiltersTitle">
  <form class="salary-statement-filter" method="GET">
    <header class="salary-statement-filter-header">
      <div>
        <span class="auth-kicker">Paramètres de l’édition</span>
        <h2 id="salaryStatementFiltersTitle">Préparer l’état mensuel des salaires</h2>
        <p>Les critères s’appliquent à l’écran, à l’impression et à l’export CSV.</p>
      </div>
      <span class="salary-statement-filter-count">
        <i class="fa-solid fa-users" aria-hidden="true"></i>
        {{ $statementRows->count() }} agent(s)
      </span>
    </header>

    <div class="salary-statement-filter-grid">
      <div class="form-group">
        <label for="salaryStatementPeriod">Période de paie</label>
        <select class="form-control" id="salaryStatementPeriod" name="period_id" required>
          @foreach (($moduleData['periods'] ?? []) as $periodOption)
            <option value="{{ $periodOption['id'] }}" @selected((string) $periodOption['id'] === (string) data_get($moduleData, 'period.id'))>
              {{ $periodOption['label'] }} — {{ $periodOption['status_label'] }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementAcademicYear">Année académique</label>
        <select class="form-control" id="salaryStatementAcademicYear" name="academic_year_id">
          <option value="">Année correspondant à la période</option>
          @foreach (($moduleData['academic_years'] ?? []) as $year)
            <option value="{{ $year['id'] }}" @selected((string) $year['id'] === (string) $selectedAcademicYear)>{{ $year['label'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementCorps">Corps d’enseignement</label>
        <select class="form-control" id="salaryStatementCorps" name="corps_id">
          <option value="">Tous les corps</option>
          @foreach (data_get($statement, 'filter_options.corps', []) as $option)
            <option value="{{ $option['id'] }}" @selected((string) $option['id'] === (string) request('corps_id'))>{{ $option['label'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementIa">Inspection académique (IA)</label>
        <select class="form-control" id="salaryStatementIa" name="ia_id" data-salary-statement-ia>
          <option value="">Toutes les IA</option>
          @foreach (data_get($statement, 'filter_options.ias', []) as $option)
            <option value="{{ $option['id'] }}" @selected((string) $option['id'] === (string) request('ia_id'))>{{ $option['label'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementIef">Inspection de l’Éducation et de la Formation (IEF)</label>
        <select class="form-control" id="salaryStatementIef" name="ief_id" data-salary-statement-ief>
          <option value="">Toutes les IEF</option>
          @foreach (data_get($statement, 'filter_options.iefs', []) as $option)
            <option value="{{ $option['id'] }}" data-ia-id="{{ $option['ia_id'] }}" @selected((string) $option['id'] === (string) request('ief_id'))>
              {{ $option['label'] }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementMatricule">Matricule</label>
        <input class="form-control" id="salaryStatementMatricule" name="matricule" type="search" value="{{ request('matricule') }}" list="salaryStatementMatricules" placeholder="Ex. PC-TEST-001" autocomplete="off">
        <datalist id="salaryStatementMatricules">
          @foreach (data_get($statement, 'filter_options.matricules', []) as $teacher)
            <option value="{{ $teacher['value'] }}">{{ $teacher['label'] }}</option>
          @endforeach
        </datalist>
      </div>

      <div class="form-group">
        <label for="salaryStatementPaymentPlace">Lieu de paiement</label>
        <select class="form-control" id="salaryStatementPaymentPlace" name="payment_place_id">
          <option value="">Tous les lieux de paiement</option>
          @foreach (data_get($statement, 'filter_options.payment_places', []) as $option)
            <option value="{{ $option['id'] }}" @selected((string) $option['id'] === (string) request('payment_place_id'))>{{ $option['label'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementTrainingCenter">Centre de formation</label>
        <select class="form-control" id="salaryStatementTrainingCenter" name="training_center_id">
          <option value="">Tous les centres de formation</option>
          @foreach (data_get($statement, 'filter_options.training_centers', []) as $option)
            <option value="{{ $option['id'] }}" @selected((string) $option['id'] === (string) request('training_center_id'))>{{ $option['label'] }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <fieldset class="salary-statement-options">
      <legend>Options de l’état</legend>
      <label><input type="checkbox" name="tabaski_only" value="1" @checked(request()->boolean('tabaski_only'))> Éditer uniquement les agents avec une avance Tabaski</label>
      <label><input type="checkbox" name="with_signature" value="1" @checked(request()->boolean('with_signature'))> Ajouter une colonne d’émargement</label>
      <label><input type="checkbox" name="without_service_done" value="1" @checked(request()->boolean('without_service_done'))> Imprimer sans la mention « service fait »</label>
      <label><input type="checkbox" name="dage_signatory" value="1" @checked(request()->boolean('dage_signatory'))> Utiliser le signataire DAGE</label>
    </fieldset>

    <div class="salary-statement-filter-actions">
      <a class="btn-secondary" href="{{ route('paie.etat-salaires') }}"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Réinitialiser</a>
      <button class="btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Consulter l’état</button>
      <button class="btn-secondary" type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button>
    </div>
  </form>

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
      <div class="salary-statement-signatory"><span>Visa et signature</span><strong>{{ $statement['signatory'] ?? 'Le responsable habilité de la paie' }}</strong></div>
    </footer>
  </article>
</section>

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const ia = document.querySelector('[data-salary-statement-ia]');
      const ief = document.querySelector('[data-salary-statement-ief]');
      if (!ia || !ief) return;
      const refreshIefs = () => {
        Array.from(ief.options).forEach((option, index) => {
          if (index > 0) option.hidden = ia.value !== '' && option.dataset.iaId !== ia.value;
        });
        if (ief.options[ief.selectedIndex]?.hidden) ief.value = '';
      };
      ia.addEventListener('change', refreshIefs);
      refreshIefs();
    });
  </script>
@endpush
