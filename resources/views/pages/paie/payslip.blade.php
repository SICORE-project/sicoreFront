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
    <article class="solde-document" aria-label="Bulletin de solde {{ $data['reference'] }}">
      <header class="solde-header">
        <div class="solde-institution">
          <strong>République du Sénégal</strong>
          <span>Un peuple Un But Une Foi</span>
          <span>Ministère de l'Emploi de la Formation Professionnelle Et Technique</span>
        </div>
        <div><h2>BULLETIN DE SOLDE</h2><strong>VALIDITE : MOIS DE {{ $monthUpper }}</strong></div>
      </header>
      <section class="solde-identity">
        <div class="solde-agent">
          <strong>{{ mb_strtoupper(data_get($data, 'teacher.name', ''), 'UTF-8') }}</strong>
          <dl>
            <div><dt>MATRICULE :</dt><dd>{{ data_get($data, 'teacher.matricule') ?: '—' }}</dd></div>
            <div><dt>IA:</dt><dd>{{ data_get($data, 'teacher.academic_inspection') ?: '—' }}</dd></div>
            <div><dt>IDEN:</dt><dd>{{ data_get($data, 'teacher.education_inspection') ?: '—' }}</dd></div>
            <div><dt>Ecole/Etablissement :</dt><dd>{{ data_get($data, 'teacher.establishment') ?: '—' }}</dd></div>
            <div><dt>Corps Enseignant :</dt><dd>{{ data_get($data, 'teacher.corps') ?: data_get($data, 'profile.engagement_label', '—') }}</dd></div>
            <div><dt>Catégorie :</dt><dd>{{ $categoryText ?: '—' }}</dd></div>
          </dl>
        </div>
        <aside class="solde-claim">Joindre le present timbre<br>à l'appui de toute réclamation ou<br>observation .Adresser la correspondance à<br>la DIRECTION DES RESSOURCES<br>HUMAINES<br>BUREAU DE SOLDE</aside>
      </section>
      @if (data_get($data, 'display_settings.hidden_line_count', 0) > 0)
        <p class="solde-display-note">{{ data_get($data, 'display_settings.hidden_line_count') }} ligne(s) non affichée(s) selon le paramétrage ; leurs montants restent inclus dans les totaux.</p>
      @endif
      <div class="solde-table-scroll">
        <table class="solde-lines">
          <colgroup><col class="solde-date-col"><col class="solde-date-col"><col><col class="solde-amount-col"><col class="solde-amount-col"></colgroup>
          <thead>
            <tr><th colspan="2" scope="colgroup">Dates</th><th rowspan="2" scope="col">Rubriques</th><th rowspan="2" scope="col">Gains</th><th rowspan="2" scope="col">Retenues</th></tr>
            <tr><th scope="col">Origine</th><th scope="col">Echéances</th></tr>
          </thead>
          <tbody>
            @forelse ($salaryLines as $line)
              <tr>
                <td>{{ $line['origin_date'] ?? '' }}</td><td>{{ $line['due_date'] ?? '' }}</td>
                <td class="solde-rubric"><span class="solde-code">{{ $line['display_code'] ?? $line['code'] }}</span> {{ $line['label'] }}</td>
                <td class="solde-amount solde-gain">{{ $line['category'] === 'earning' ? $money($line['amount']) : '' }}</td>
                <td class="solde-amount solde-deduction">{{ $line['category'] !== 'earning' ? $money($line['amount']) : '' }}</td>
              </tr>
            @empty
              <tr><td colspan="5">Aucune rubrique à afficher pour ce bulletin.</td></tr>
            @endforelse
          </tbody>
          <tfoot><tr><td></td><td></td><th scope="row">TOTAL</th><td class="solde-amount">{{ $money($data['gross_amount']) }}</td><td class="solde-amount">{{ $money($data['deduction_amount']) }}</td></tr></tfoot>
        </table>
        <table class="solde-settlement">
          <thead><tr><th>N° Emission</th><th>N° Ordre</th><th>Date</th><th>Imputation</th><th>Bulletin Négatif</th><th>NET A PERCEVOIR</th></tr></thead>
          <tbody><tr>
            <td>{{ $data['issue_number'] ?? '—' }}</td><td>{{ $data['order_number'] ?? '—' }}</td>
            <td>{{ $monthUpper }} {{ $year }}</td><td>{{ $data['allocation'] ?? '—' }}</td>
            <td>{{ (float) $data['net_amount'] < 0 ? 'Oui' : '' }}</td><td class="solde-net">{{ $money($data['net_amount']) }}</td>
          </tr></tbody>
        </table>
        <section class="solde-stub" aria-label="Talon du bulletin">
          <div class="solde-stub-agent">
            <strong class="solde-stub-country">REPUBLIQUE DU SENEGAL</strong>
            <strong>{{ trim(data_get($data, 'teacher.first_name', '').' '.mb_strtoupper(data_get($data, 'teacher.last_name', ''), 'UTF-8')) ?: data_get($data, 'teacher.name', '') }}</strong>
            <strong>GESTION {{ $year }}</strong>
          </div>
          <div class="solde-stamp"><span>Visa</span><span>Timbre à date</span></div>
          <div class="solde-stub-payment">
            <table><thead><tr><th>Matricule</th><th>N° Emission</th><th>N° Ordre</th><th>Date</th></tr></thead>
              <tbody><tr><td>{{ data_get($data, 'teacher.matricule') ?: '—' }}</td><td>{{ $data['issue_number'] ?? '—' }}</td><td>{{ $data['order_number'] ?? '—' }}</td><td>{{ $data['payment_date'] ?? '—' }}</td></tr></tbody>
            </table>
            <div class="solde-stub-net"><span>{{ $bank }}</span><strong>{{ $money($data['net_amount']) }}</strong></div>
          </div>
        </section>
      </div>
      <footer class="solde-footer"><strong>SICORE - MEFPT</strong><span>Edité le {{ $data['edited_at'] ?? $data['edited_on'] }}</span></footer>
    </article>
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
