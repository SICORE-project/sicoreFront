@push('scripts')
<script src="{{ asset('assets/js/payroll-bulletin-consultation.js') }}?v={{ filemtime(public_path('assets/js/payroll-bulletin-consultation.js')) }}" defer></script>
@endpush
@php
  $options = data_get($moduleData, 'salary_statement.filter_options', []);
  $fields = [
    'statut_enseignant_id' => ["Corps d’enseignant", $moduleData['teaching_statuses'] ?? [], true],
    'academic_year_id' => ['Année académique', $moduleData['academic_years'] ?? [], true],
    'period_id' => ['Période de paie', $moduleData['periods'] ?? [], true],
    'ia_id' => ['IA', $moduleData['academic_inspections'] ?? [], false],
    'ief_id' => ['IEF', $moduleData['education_inspections'] ?? [], false],
    'matricule' => ['Matricule du', [], false],
    'payment_place_id' => ['Lieu de payement', $options['payment_places'] ?? [], false],
  ];
  $placeholders = ['ia_id' => 'Toutes les IA', 'ief_id' => 'Toutes les IEF', 'payment_place_id' => 'Tous les lieux de payement'];
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/paie/etat-salaire.css') }}?v={{ filemtime(public_path('assets/css/paie/etat-salaire.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/payroll-bulletin-consultation.css') }}?v={{ filemtime(public_path('assets/css/payroll-bulletin-consultation.css')) }}">
<main class="main-content">
  <x-topbar title="Bulletins des salaires" subtitle="Gestion de la paie · Consultation" icon="fa-solid fa-file-invoice-dollar" />
  <section class="content-area bulletin-consultation">
    <form method="get" action="{{ route('paie.bulletins') }}" class="salary-statement-filter bulletin-filter" data-bulletin-form>
      <header class="salary-statement-filter-header"><div><h2>Période comptable</h2></div><span class="salary-statement-filter-count"><i class="fa-solid fa-users" aria-hidden="true"></i> {{ count(data_get($moduleData, 'salary_statement.rows', [])) }} agent(s)</span></header>
      @if (! empty($apiError))<p role="alert" class="bulletin-error">{{ $apiError }}</p>@endif
      @if ($errors->any())<div class="bulletin-error" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
      <div class="salary-statement-filter-grid">
        @foreach ($fields as $name => [$label, $choices, $required])
        @if ($name === 'matricule')
        <div class="form-group bulletin-matricule"><label for="bulletinMatricule">{{ $label }}</label>
          <div id="bulletinSuggestions" class="bulletin-suggestions" role="listbox" aria-label="Enseignants disponibles" hidden></div>
          <input id="bulletinMatricule" name="matricule" value="{{ old('matricule', $filters['matricule'] ?? '') }}" class="form-control" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="bulletinSuggestions" aria-expanded="false" placeholder="Commencer à saisir le matricule…">
          <span class="bulletin-suggestion-status" role="status" aria-live="polite"></span>
        </div>
        @else
        <div class="form-group bulletin-field-{{ $name }}"><label for="bulletin-{{ $name }}">{{ $label }}</label>
          <select id="bulletin-{{ $name }}" name="{{ $name }}" @required($required) class="form-control" @disabled($name === 'ia_id' && ! empty($moduleData['scope_ia_id']))>
            <option value="">{{ $placeholders[$name] ?? 'Sélectionner…' }}</option>
            @foreach ($choices as $option)
            <option value="{{ $option['id'] }}" data-ia="{{ $option['ia_id'] ?? '' }}" data-ief="{{ $option['ief_id'] ?? '' }}" data-start="{{ $option['start_date'] ?? '' }}" data-end="{{ $option['end_date'] ?? '' }}" data-code="{{ $option['code'] ?? '' }}" @selected((string) $option['id'] === (string) old($name, $filters[$name] ?? ($name === 'ia_id' ? ($moduleData['scope_ia_id'] ?? '') : '')))>{{ $option['label'] }}{{ $name === 'period_id' && !empty($option['status_label']) ? ' — '.$option['status_label'] : '' }}</option>
            @endforeach
          </select></div>
        @endif
        @endforeach
      </div>
      <fieldset class="salary-statement-options"><label><input type="checkbox" name="group" value="1" @checked(old('group', $filters['group'] ?? false))> Édition bulletin avec le groupe</label></fieldset>
      <p class="bulletin-help">Pour le groupe, laissez le matricule vide. Tous les bulletins correspondant aux filtres s’ouvriront dans une seule page.</p>
      <div class="salary-statement-filter-actions"><a class="btn-secondary" href="{{ route('paie.bulletins') }}"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Réinitialiser</a><button class="btn-primary" type="submit" name="consult" value="1"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Consulter</button></div>
    </form>
  </section>
</main>
