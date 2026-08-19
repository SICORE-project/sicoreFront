@extends('layouts.app')

@section('title', 'SICORE - Syndicats')

@section('content')
<main class="main-content">
  <x-topbar title="Syndicats" subtitle="Paramétrage > Syndicats > Consultation" icon="fa-solid fa-people-group" />

  <section class="content-area">
    <div class="stats-grid four">
      <article class="stat-card">
        <div><p class="stat-label">Total syndicats</p><p class="stat-value">{{ $syndicats->count() }}</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-people-group"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Syndicats actifs</p><p class="stat-value">{{ $syndicats->where('est_actif', true)->count() }}</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-check"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Syndicats inactifs</p><p class="stat-value">{{ $syndicats->where('est_actif', false)->count() }}</p></div>
        <span class="stat-icon yellow"><i class="fa-solid fa-pause"></i></span>
      </article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Syndicats</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" data-modal-open="create-syndicat-modal">
          <i class="fa-solid fa-plus" aria-hidden="true"></i> Nouveau syndicat
        </button>
      </div>
    </div>

    @if ($apiError)
      <div class="form-alert" role="alert">{{ $apiError }}</div>
    @endif

    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="syndicatsTable">
          <thead>
            <tr>
              <th>Code</th>
              <th>Libellé</th>
              <th>Check-off</th>
              <th>Œuvre sociale</th>
              <th>Statut</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($syndicats as $syndicat)
              <tr>
                <td>{{ $syndicat['code'] ?? '—' }}</td>
                <td>{{ $syndicat['libelle'] ?? '—' }}</td>
                <td>{{ isset($syndicat['montant_check_off']) ? number_format((float) $syndicat['montant_check_off'], 2, ',', ' ') . ' FCFA' : '—' }}</td>
                <td>{{ isset($syndicat['montant_oeuvre_sociale']) ? number_format((float) $syndicat['montant_oeuvre_sociale'], 2, ',', ' ') . ' FCFA' : '—' }}</td>
                <td>
                  <span class="badge {{ ! empty($syndicat['est_actif']) ? 'badge-active' : 'badge-inactive' }}">
                    {{ ! empty($syndicat['est_actif']) ? 'Actif' : 'Inactif' }}
                  </span>
                </td>
                <td class="actions-cell">
                  <button class="icon-action" type="button" title="Modifier" aria-label="Modifier {{ $syndicat['libelle'] ?? '' }}">&#9998;</button>
                  <button class="icon-action" type="button" title="Supprimer" aria-label="Supprimer {{ $syndicat['libelle'] ?? '' }}">&#128465;</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="empty-message">Aucun syndicat trouvé.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="create-syndicat-modal" title="Ajouter un syndicat" :open="$errors->any()">
  @include('pages.parametres.syndicats.create')
</x-module-indemnite>
@endsection

@push('styles')
<style>
  #create-syndicat-modal .modal-dialog { max-width: 760px; }
  #create-syndicat-modal .teacher-form { margin-top: 18px; }
  .form-alert { margin: 12px 0; padding: 12px 16px; border-radius: 8px; background: #fef2f2; color: #b91c1c; }
</style>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/syndicat-form.js') }}" defer></script>
@endpush
