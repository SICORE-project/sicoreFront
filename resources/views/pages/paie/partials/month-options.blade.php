@php
  $monthNames = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
  $allPeriods = collect($moduleData['periods'] ?? []);
  $selectedPeriod = $allPeriods->firstWhere('id', (int) $monthSelected);
  $yearId = request('academic_year_id', request('annee_academique_id'));
  $academicYears = collect($moduleData['academic_years'] ?? []);
  $year = $academicYears->firstWhere('id', (int) $yearId);
  if (! $year && $selectedPeriod) {
      $code = $selectedPeriod['code'] ?? '';
      $year = $academicYears->first(fn ($y) => !empty($y['start_date']) && $code >= substr($y['start_date'], 0, 7) && $code <= substr($y['end_date'], 0, 7));
  }
  $visiblePeriods = $allPeriods->filter(fn ($p) => !$year || (($p['code'] ?? '') >= substr($year['start_date'], 0, 7) && ($p['code'] ?? '') <= substr($year['end_date'], 0, 7)))
    ->sortByDesc(fn ($p) => (string) ($p['id'] ?? '') === (string) $monthSelected)
    ->unique(fn ($p) => (int) substr($p['code'] ?? '', 5, 2))
    ->sortBy(fn ($p) => (int) substr($p['code'] ?? '', 5, 2));
@endphp
@foreach ($visiblePeriods as $monthPeriod)
  <option value="{{ $monthPeriod['id'] }}" @selected((string) $monthPeriod['id'] === (string) $monthSelected)>{{ $monthNames[(int) substr($monthPeriod['code'] ?? '', 5, 2)] ?? $monthPeriod['label'] }}</option>
@endforeach
@once
  @push('scripts')
    <script src="{{ asset('assets/js/payroll-month-filters.js') }}?v={{ filemtime(public_path('assets/js/payroll-month-filters.js')) }}" defer></script>
  @endpush
@endonce
