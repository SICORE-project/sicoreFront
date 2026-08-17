@extends('layouts.app')

{{--
  PAGE PAIE SPÉCIFIQUE : bulletin individuel — URL /paie/bulletins/{id}.
  Route/contrôleur frontend : routes/web.php → PayrollController::payslip().
  Appel API : SicoreApi::payslip().
  Données backend : sicoreBack/PayrollPageController::payslip().
  Styles écran/impression : classes payslip-* dans style.css et responsive.css.
  Contrairement aux listes, cette page possède son propre HTML de bulletin.
--}}
@section('title', 'SICORE - Bulletin '.$data['reference'])

@php
  // Préparer uniquement des libellés d'affichage ; aucun calcul financier ici.
  $category = data_get($data, 'profile.category');
  $categoryLabel = $category ? ((int) $category === 1 ? '1re catégorie' : $category.'e catégorie') : 'Non applicable';
  $validity = mb_strtoupper(data_get($data, 'period.label', data_get($data, 'period.code', '')), 'UTF-8');
@endphp

@section('content')
<main class="main-content payslip-page">
  {{-- Actions d'écran non imprimées : retour à la liste et impression. --}}
  <x-topbar
    title="Bulletin de salaire"
    :subtitle="'Gestion de la paie > '.$data['reference']"
    icon="fa-solid fa-file-invoice-dollar"
  >
    <div class="actions-group payslip-screen-actions">
      <a class="btn-secondary" href="{{ route('paie.bulletins', ['period_id' => $data['period']['id']]) }}">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        Retour
      </a>
      <button class="btn-primary" type="button" onclick="window.print()">
        <i class="fa-solid fa-print" aria-hidden="true"></i>
        Imprimer le bulletin
      </button>
    </div>
  </x-topbar>

  <section class="content-area payslip-canvas">
    {{-- Document A4 complet envoyé à l'impression par window.print(). --}}
    <article class="payslip-document" aria-label="Bulletin de solde {{ $data['reference'] }}">
      <div class="payslip-color-rule" aria-hidden="true">
        <span></span><span></span><span></span>
      </div>

      {{-- En-tête officiel : République, service émetteur, matricule et période. --}}
      <header class="payslip-official-header">
        <section class="payslip-republic" aria-label="République du Sénégal">
          <img src="{{ asset('assets/images/flag-senegal.svg') }}" alt="Drapeau du Sénégal">
          <div>
            <p>République du Sénégal</p>
            <span>Un Peuple · Un But · Une Foi</span>
          </div>
        </section>

        <section class="payslip-document-title">
          <span>Direction des Ressources Humaines</span>
          <h2>Bulletin de solde</h2>
          <p>Bureau de la solde</p>
        </section>

        <section class="payslip-document-meta">
          <span>Matricule</span>
          <strong>{{ $data['teacher']['matricule'] ?: 'Non renseigné' }}</strong>
          <small>Validité : {{ $validity }}</small>
        </section>
      </header>

      {{-- Identité et situation administrative du bénéficiaire. --}}
      <section class="payslip-agent-card">
        <div class="payslip-agent-main">
          <span class="payslip-section-kicker">Agent bénéficiaire</span>
          <h3>{{ $data['teacher']['name'] ?: 'Identité non renseignée' }}</h3>
          <div class="payslip-agent-tags">
            <span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> {{ data_get($data, 'profile.engagement_label', 'Profil historique') }}</span>
            <span><i class="fa-solid fa-building-columns" aria-hidden="true"></i> {{ $data['teacher']['bank'] ?: 'Banque non renseignée' }}</span>
          </div>
        </div>

        <dl class="payslip-administrative-grid">
          <div>
            <dt>Inspection académique (IA)</dt>
            <dd>{{ $data['teacher']['academic_inspection'] ?: 'Non renseignée' }}</dd>
          </div>
          <div>
            <dt>IEF / IDEN</dt>
            <dd>{{ $data['teacher']['education_inspection'] ?: 'Non renseignée' }}</dd>
          </div>
          <div>
            <dt>École / Établissement</dt>
            <dd>{{ $data['teacher']['establishment'] ?: 'Non renseigné' }}</dd>
          </div>
          <div>
            <dt>Corps enseignant</dt>
            <dd>{{ $data['teacher']['corps'] ?: 'Non renseigné' }}</dd>
          </div>
          <div>
            <dt>Diplôme</dt>
            <dd>{{ data_get($data, 'profile.diploma') ?: 'Non applicable' }}</dd>
          </div>
          <div>
            <dt>Catégorie</dt>
            <dd>{{ $categoryLabel }}</dd>
          </div>
        </dl>
      </section>

      {{-- Références du bulletin, du compte masqué et du paiement. --}}
      <section class="payslip-reference-strip" aria-label="Références du bulletin">
        <div>
          <span>Référence bulletin</span>
          <strong>{{ $data['reference'] }}</strong>
        </div>
        <div>
          <span>Compte bancaire</span>
          <strong>{{ $data['teacher']['account_last_four'] ? '•••• '.$data['teacher']['account_last_four'] : 'Non renseigné' }}</strong>
        </div>
        <div>
          <span>Référence paiement</span>
          <strong>{{ $data['payment_reference'] ?: 'En attente de paiement' }}</strong>
        </div>
        <div class="payslip-payment-state">
          <span class="payslip-status-dot" aria-hidden="true"></span>
          {{ $data['payment_status'] === 'paid' ? 'Bulletin payé' : 'Paiement en attente' }}
        </div>
      </section>

      {{-- Rubriques déjà calculées par le backend : gains, retenues et employeur. --}}
      <div class="payslip-lines-wrap">
        <table class="payslip-lines">
          <thead>
            <tr>
              <th scope="col">Code et rubrique</th>
              <th scope="col" class="payslip-money-column">Gains</th>
              <th scope="col" class="payslip-money-column">Retenues</th>
              <th scope="col" class="payslip-money-column">Part employeur</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data['lines'] as $line)
              @php
                $isEarning = $line['category'] === 'earning';
                $isEmployer = $line['category'] === 'employer_contribution';
                $isDeduction = ! $isEarning && ! $isEmployer;
              @endphp
              <tr class="payslip-line-{{ $line['category'] }}">
                <td>
                  <span class="payslip-line-code">{{ $line['code'] }}</span>
                  <span class="payslip-line-label">
                    <strong>{{ $line['label'] }}</strong>
                    @if (! empty($line['is_augmentation']))
                      <small>Augmentation salariale tracée</small>
                    @endif
                  </span>
                </td>
                <td class="payslip-money-column {{ $isEarning ? 'is-gain' : '' }}">
                  {{ $isEarning ? format_fcfa($line['amount']) : '—' }}
                </td>
                <td class="payslip-money-column {{ $isDeduction ? 'is-deduction' : '' }}">
                  {{ $isDeduction ? format_fcfa($line['amount']) : '—' }}
                </td>
                <td class="payslip-money-column {{ $isEmployer ? 'is-employer' : '' }}">
                  {{ $isEmployer ? format_fcfa($line['amount']) : '—' }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Totaux fournis par PayrollPayslip ; ne jamais les recalculer dans Blade. --}}
      <section @class([
        'payslip-summary',
        'has-employer-contribution' => (float) $data['employer_contribution_amount'] > 0,
      ]) aria-label="Totaux du bulletin">
        <div class="payslip-summary-item">
          <span>Total des gains</span>
          <strong>{{ format_fcfa($data['gross_amount']) }}</strong>
        </div>
        <div class="payslip-summary-item is-deduction">
          <span>Total des retenues</span>
          <strong>{{ format_fcfa($data['deduction_amount']) }}</strong>
        </div>
        @if ((float) $data['employer_contribution_amount'] > 0)
          <div class="payslip-summary-item is-employer">
            <span>Part employeur · hors net</span>
            <strong>{{ format_fcfa($data['employer_contribution_amount']) }}</strong>
          </div>
        @endif
        <div class="payslip-net-card">
          <span>Net à percevoir</span>
          <strong>{{ format_fcfa($data['net_amount']) }}</strong>
        </div>
      </section>

      <section class="payslip-claim-note">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <p>Pour toute réclamation ou observation, mentionnez le matricule et la référence de ce bulletin auprès du Bureau de la solde.</p>
      </section>

      {{-- Pied officiel demandé : plateforme/ministère et date d'édition. --}}
      <footer class="payslip-official-footer">
        <div class="payslip-footer-brand">
          <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo SICORE">
          <div>
            <strong>SICORE - MEFPT</strong>
            <span>Plateforme intégrée de gestion</span>
          </div>
        </div>
        <div class="payslip-edition-date">
          <span>Document généré électroniquement</span>
          <strong>Édité le {{ $data['edited_on'] }}</strong>
        </div>
      </footer>
    </article>
  </section>
</main>
@endsection
