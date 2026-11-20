@extends('layouts.app')
@section('title', 'SICORE - Bulletin de solde '.$data['reference'])
@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/payroll-bulletin.css') }}?v={{ filemtime(public_path('assets/css/payroll-bulletin.css')) }}">
@endpush
@php
  $money = fn ($amount) => number_format((float) $amount, 0, ',', ' ');
  $monthLabel = data_get($data, 'period.month_label', data_get($data, 'period.label', ''));
  $monthUpper = mb_strtoupper(trim(preg_replace('/\s*\d{4}\s*/u', '', $monthLabel)), 'UTF-8');
  $year = data_get($data, 'period.year') ?: preg_replace('/.*(\d{4}).*/', '$1', $monthLabel);
  $category = data_get($data, 'profile.category');
  $categoryLabel = $category ? ($category == 1 ? '1ere catégorie' : $category.'e catégorie') : '';
  $categoryText = trim((data_get($data, 'profile.diploma') ?? '').' '.(data_get($data, 'profile.category_label') ?: $categoryLabel));
  $lines = collect($data['lines'] ?? []);
  $employerLines = $lines->where('category', 'employer_contribution');
  $salaryLines = $lines->where('category', '!=', 'employer_contribution');
  $bank = data_get($data, 'teacher.bank') ?: '—';
@endphp
@section('content')
<main class="main-content solde-page">
  <x-topbar title="Bulletin de solde" :subtitle="$monthLabel.' · '.$data['reference']" icon="fa-solid fa-file-invoice-dollar">
    <div class="actions-group">
      <a class="btn-secondary" href="{{ route('paie.bulletins', ['period_id' => $data['period']['id']]) }}">Retour aux bulletins</a>
      <button class="btn-primary" type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button>
    </div>
  </x-topbar>
  <section class="content-area solde-canvas">
    @include('pages.paie.partials.bulletin-document', ['data' => $data])
    <aside class="solde-screen-note" aria-label="Informations de consultation">
      <span>Référence : {{ $data['reference'] }}</span>
      <span>{{ ($data['payment_status'] ?? '') === 'paid' ? 'Bulletin payé' : 'Paiement en attente' }}</span>
      @if (data_get($data, 'display_settings.matched_rubric_count', 0) > 0)
        <span>Libellés et affichage issus de Paramétrage.
          {{ data_get($data, 'display_settings.origin') === 'calculation_snapshot' ? 'Réglages conservés à la date du calcul.' : 'Réglages actuels appliqués à ce bulletin antérieur.' }}
        </span>
      @endif
      @if ($employerLines->isNotEmpty())
        <details><summary>Part employeur · hors net : {{ $money($data['employer_contribution_amount']) }} FCFA</summary>
          @foreach ($employerLines as $line)<p>{{ $line['label'] }} : {{ $money($line['amount']) }} FCFA</p>@endforeach
        </details>
      @endif
    </aside>
  </section>
</main>
@endsection
