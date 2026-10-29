@php
  $statementRows = collect($statement['rows'] ?? []);
  $selectedAcademicYear = request('academic_year_id');
  if (! $selectedAcademicYear) {
    $selectedAcademicYear = collect($moduleData['academic_years'] ?? [])
      ->firstWhere('label', $statement['academic_year'] ?? null)['id'] ?? null;
  }
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
        <select class="form-control" id="salaryStatementCorps" name="statut_enseignant_id">
          <option value="">Tous les statuts</option>
          @foreach (data_get($statement, 'filter_options.statuts', []) as $option)
            <option value="{{ $option['id'] }}" @selected((string) $option['id'] === (string) request('statut_enseignant_id'))>{{ $option['label'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="salaryStatementIa">Inspection académique (IA)</label>
        @if (! empty($moduleData['scope_ia_id']))
          <input class="form-control" value="{{ $moduleData['scope_label'] }}" readonly aria-label="IA de rattachement">
          <input type="hidden" id="salaryStatementIa" value="{{ $moduleData['scope_ia_id'] }}" data-salary-statement-ia>
        @else
        <select class="form-control" id="salaryStatementIa" name="ia_id" data-salary-statement-ia>
          <option value="">Toutes les IA</option>
          @foreach (data_get($statement, 'filter_options.ias', []) as $option)
            <option value="{{ $option['id'] }}" @selected((string) $option['id'] === (string) request('ia_id'))>{{ $option['label'] }}</option>
          @endforeach
        </select>
        @endif
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
        <select class="form-control" id="salaryStatementTrainingCenter" name="training_center_id" data-salary-statement-center>
          <option value="">Tous les centres de formation</option>
          @foreach (data_get($statement, 'filter_options.training_centers', []) as $option)
            <option value="{{ $option['id'] }}" data-ia-id="{{ $option['ia_id'] ?? '' }}" data-ief-id="{{ $option['ief_id'] ?? '' }}" @selected((string) $option['id'] === (string) request('training_center_id'))>{{ $option['label'] }}</option>
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
      <button class="btn-secondary" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Appliquer les filtres</button>
      <button class="btn-primary" type="submit" name="document" value="1" formtarget="_blank"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Générer l’état</button>
    </div>
  </form>

</section>

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const ia = document.querySelector('[data-salary-statement-ia]');
      const ief = document.querySelector('[data-salary-statement-ief]');
      const center = document.querySelector('[data-salary-statement-center]');
      if (!ia || !ief) return;
      const refreshCenters = () => {
        if (!center) return;
        Array.from(center.options).forEach((option, index) => {
          if (index > 0) option.hidden = (ia.value !== '' && option.dataset.iaId !== ia.value)
            || (ief.value !== '' && option.dataset.iefId !== ief.value);
        });
        if (center.options[center.selectedIndex]?.hidden) center.value = '';
      };
      const refreshIefs = () => {
        Array.from(ief.options).forEach((option, index) => {
          if (index > 0) option.hidden = ia.value !== '' && option.dataset.iaId !== ia.value;
        });
        if (ief.options[ief.selectedIndex]?.hidden) ief.value = '';
        refreshCenters();
      };
      ia.addEventListener('change', refreshIefs);
      ief.addEventListener('change', refreshCenters);
      refreshIefs();
    });
  </script>
@endpush
