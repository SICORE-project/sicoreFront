@extends('layouts.standalone')

{{--
  ÉTAT DES SALAIRES — DOCUMENT À IMPRIMER, ouvert dans un nouvel onglet par
  le bouton « Générer l’état » (/paie/etat-salaires?document=1&…).
  Vue choisie par PayrollController::viewFor() ; mêmes données que la page de filtres.
--}}
@section('title', 'SICORE - État des salaires')
@section('content')
  <link rel="stylesheet" href="{{ asset('assets/css/paie/etat-salaire.css') }}?v={{ filemtime(public_path('assets/css/paie/etat-salaire.css')) }}">
  @php
    // « FICHIER SICA » : même export CSV que la page, avec les filtres de l'état affiché.
    $exportFilters = array_filter(array_merge(
      ['slug' => 'paie-etat-salaires', 'period_id' => data_get($moduleData, 'period.id', request('period_id'))],
      request()->only([
        'academic_year_id', 'statut_enseignant_id', 'ia_id', 'ief_id', 'matricule',
        'payment_place_id', 'training_center_id', 'tabaski_only',
        'with_signature', 'without_service_done', 'dage_signatory',
      ])
    ), fn ($value) => $value !== null && $value !== '');
    $exportUrl = app(\App\Services\Organisation\InterfaceAccess::class)->isIa()
      ? route('ia.payroll.export', $exportFilters)
      : route('paie.export', $exportFilters);
  @endphp
  <main class="salary-statement-page salary-statement-standalone">
    @if (! empty($moduleData['salary_statement']))
      @include('pages.paie.etat-salaire.partials.salary-statement-document', [
        'statement' => $moduleData['salary_statement'],
      ])
    @else
      <section class="connection-banner connection-banner-error" role="alert">
        <i class="fa-solid fa-plug-circle-xmark" aria-hidden="true"></i>
        <div>
          <strong>{{ is_array($apiError ?? null) ? ($apiError['title'] ?? 'État indisponible') : 'État indisponible' }}</strong>
          <p>{{ is_array($apiError ?? null) ? ($apiError['message'] ?? '') : ($apiError ?: 'Aucun état ne peut être produit pour ces critères.') }}</p>
        </div>
      </section>
    @endif

    <div class="salary-statement-standalone-actions">
      <div class="salary-statement-ordonnateur" role="radiogroup" aria-labelledby="ordonnateurLabel">
        <span id="ordonnateurLabel">Ordonnateur de crédit</span>
        <div class="salary-statement-segmented">
          <label><input type="radio" name="ordonnateur" value="IEF" checked data-ordonnateur-choice><span>IEF</span></label>
          <label><input type="radio" name="ordonnateur" value="IA" data-ordonnateur-choice><span>IA</span></label>
        </div>
      </div>
      <div class="salary-statement-standalone-buttons">
        <button class="btn-secondary" type="button" onclick="window.close()"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Fermer</button>
        <a class="btn-secondary" href="{{ $exportUrl }}"><i class="fa-solid fa-file-csv" aria-hidden="true"></i> Fichier SICA</a>
        <button class="btn-primary" type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button>
      </div>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const target = document.querySelector('[data-ordonnateur-label]');
      const labels = {
        IEF: 'L’Inspecteur de l’Éducation et de la Formation (IEF)',
        IA: 'L’Inspecteur d’Académie (IA)',
      };
      document.querySelectorAll('[data-ordonnateur-choice]').forEach((radio) => {
        radio.addEventListener('change', () => {
          if (target && radio.checked) target.textContent = labels[radio.value];
        });
      });
    });
  </script>
@endpush
