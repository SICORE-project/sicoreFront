@extends('layouts.app')

@section('title', 'SICORE - Syndicats')

@section('content')
<main class="main-content">
  <x-topbar title="Syndicats" subtitle="Paramétrage > Syndicats > Consultation" icon="fa-solid fa-people-group" />

  <section class="content-area">
    <div class="stats-grid four">
      <article class="stat-card">
        <div><p class="stat-label">Total syndicats</p><p class="stat-value">{{ $stats['total'] }}</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-people-group"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Syndicats actifs</p><p class="stat-value">{{ $stats['actifs'] }}</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-check"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Syndicats inactifs</p><p class="stat-value">{{ $stats['inactifs'] }}</p></div>
        <span class="stat-icon yellow"><i class="fa-solid fa-pause"></i></span>
      </article>
    </div>

    <form class="filter-panel syndicats-filters" method="GET" action="{{ route('parametres.syndicats.index') }}" data-syndicats-filters>
      <div class="form-group">
        <label for="syndicatSearch">Rechercher</label>
        <input class="form-control" id="syndicatSearch" name="search" type="search" value="{{ $search }}" placeholder="Code ou libellé…">
      </div>
      <div class="form-group">
        <label for="syndicatStatus">Statut</label>
        <select class="form-control" id="syndicatStatus" name="est_actif">
          <option value="" @selected($statut === '')>Tous les statuts</option>
          <option value="1" @selected($statut === '1')>Actifs</option>
          <option value="0" @selected($statut === '0')>Inactifs</option>
        </select>
      </div>
      <div class="actions-group syndicats-filter-actions">
        @if ($search !== '' || $statut !== '')
          <a class="btn-secondary" href="{{ route('parametres.syndicats.index') }}">Réinitialiser</a>
        @endif
      </div>
    </form>

    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Syndicats</p>
      @if ($canManage)<div class="actions-group">
        <button class="btn-primary" type="button" data-modal-open="create-syndicat-modal">
          <i class="fa-solid fa-plus" aria-hidden="true"></i> Nouveau syndicat
        </button>
      </div>@endif
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
            @if ($apiError)
              <tr><td colspan="6" class="empty-message">Les données n’ont pas pu être chargées. Veuillez réessayer.</td></tr>
            @else
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
                  <button class="icon-action" type="button" data-modal-open="show-syndicat-{{ $syndicat['id'] }}" title="Consulter" aria-label="Consulter {{ $syndicat['libelle'] ?? '' }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                  @if ($canManage)
                    <button class="icon-action" type="button" data-modal-open="edit-syndicat-{{ $syndicat['id'] }}" title="Modifier" aria-label="Modifier {{ $syndicat['libelle'] ?? '' }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                    <form class="inline-action-form" action="{{ route('parametres.syndicats.destroy', $syndicat['id']) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ $syndicat['libelle'] ?? '' }}" data-confirm="Voulez-vous vraiment supprimer le syndicat « {{ $syndicat['libelle'] ?? '' }} » ?" data-confirm-submit><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                    </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="empty-message">Aucun syndicat trouvé.</td></tr>
            @endforelse
            @endif
          </tbody>
        </table>
      </div>
      @if ($syndicats->hasPages())
        <nav class="pagination" aria-label="Pagination des syndicats">
          @if ($syndicats->onFirstPage())
            <span class="page-btn" aria-disabled="true">&#8592;</span>
          @else
            <a class="page-btn" href="{{ $syndicats->previousPageUrl() }}" aria-label="Page précédente">&#8592;</a>
          @endif

          @foreach ($syndicats->getUrlRange(1, $syndicats->lastPage()) as $page => $url)
            <a class="page-btn {{ $page === $syndicats->currentPage() ? 'active' : '' }}" href="{{ $url }}" @if ($page === $syndicats->currentPage()) aria-current="page" @endif>{{ $page }}</a>
          @endforeach

          @if ($syndicats->hasMorePages())
            <a class="page-btn" href="{{ $syndicats->nextPageUrl() }}" aria-label="Page suivante">&#8594;</a>
          @else
            <span class="page-btn" aria-disabled="true">&#8594;</span>
          @endif
        </nav>
      @endif
    </section>
  </section>
</main>

@if ($canManage)
  <x-module-indemnite type="modal" id="create-syndicat-modal" title="Ajouter un syndicat" :open="$errors->any() && old('_editing_id') === null">
    @include('pages.parametres.syndicats.create')
  </x-module-indemnite>
@endif

@foreach ($syndicats as $syndicat)
  @php
    $nombreAdherents = $syndicat['enseignants_count']
      ?? (isset($syndicat['enseignants']) && is_array($syndicat['enseignants']) ? count($syndicat['enseignants']) : null);
    $isEditing = (string) old('_editing_id') === (string) $syndicat['id'];
  @endphp

  <x-module-indemnite type="modal" id="show-syndicat-{{ $syndicat['id'] }}" title="Détail du syndicat">
    <div class="syndicat-detail-grid">
      <div><i class="fa-solid fa-hashtag" aria-hidden="true"></i><span>Code</span><strong>{{ $syndicat['code'] ?? '—' }}</strong></div>
      <div><i class="fa-solid fa-people-group" aria-hidden="true"></i><span>Libellé</span><strong>{{ $syndicat['libelle'] ?? '—' }}</strong></div>
      <div><i class="fa-solid fa-money-check-dollar" aria-hidden="true"></i><span>Check-off</span><strong>{{ isset($syndicat['montant_check_off']) ? number_format((float) $syndicat['montant_check_off'], 2, ',', ' ').' FCFA' : '—' }}</strong></div>
      <div><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i><span>Œuvre sociale</span><strong>{{ isset($syndicat['montant_oeuvre_sociale']) ? number_format((float) $syndicat['montant_oeuvre_sociale'], 2, ',', ' ').' FCFA' : '—' }}</strong></div>
      <div><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Statut</span><strong><span class="badge {{ ! empty($syndicat['est_actif']) ? 'badge-active' : 'badge-inactive' }}">{{ ! empty($syndicat['est_actif']) ? 'Actif' : 'Inactif' }}</span></strong></div>
      <div><i class="fa-solid fa-user-group" aria-hidden="true"></i><span>Enseignants adhérents</span><strong>{{ $nombreAdherents ?? '—' }}</strong></div>
    </div>
    @if ($nombreAdherents === null)<p class="detail-hint">Le nombre d’adhérents n’est pas encore fourni par l’API.</p>@endif
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
  </x-module-indemnite>

  @if ($canManage)<x-module-indemnite type="modal" id="edit-syndicat-{{ $syndicat['id'] }}" title="Modifier le syndicat" :open="$isEditing && $errors->any()">
    @if ($isEditing && $errors->has('api'))<div class="form-alert" role="alert">{{ $errors->first('api') }}</div>@endif
    <form class="teacher-form" action="{{ route('parametres.syndicats.update', $syndicat['id']) }}" method="POST" data-edit-syndicat-form>
      @csrf
      @method('PUT')
      <input type="hidden" name="_editing_id" value="{{ $syndicat['id'] }}">
      <div class="form-grid form-grid--balanced">
        <div class="form-group">
          <label for="edit-code-{{ $syndicat['id'] }}">Code *</label>
          <input class="form-control {{ $isEditing && $errors->has('code') ? 'is-invalid' : '' }}" id="edit-code-{{ $syndicat['id'] }}" name="code" value="{{ $isEditing ? old('code') : $syndicat['code'] }}" maxlength="20" required>
          @if ($isEditing)@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
        </div>
        <div class="form-group">
          <label for="edit-libelle-{{ $syndicat['id'] }}">Libellé *</label>
          <input class="form-control {{ $isEditing && $errors->has('libelle') ? 'is-invalid' : '' }}" id="edit-libelle-{{ $syndicat['id'] }}" name="libelle" value="{{ $isEditing ? old('libelle') : $syndicat['libelle'] }}" maxlength="100" required>
          @if ($isEditing)@error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
        </div>
        <div class="form-group">
          <label for="edit-check-off-{{ $syndicat['id'] }}">Montant check-off</label>
          <input class="form-control {{ $isEditing && $errors->has('montant_check_off') ? 'is-invalid' : '' }}" id="edit-check-off-{{ $syndicat['id'] }}" name="montant_check_off" type="number" min="0" max="9999999999.99" step="0.01" value="{{ $isEditing ? old('montant_check_off') : ($syndicat['montant_check_off'] ?? '') }}">
          @if ($isEditing)@error('montant_check_off')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
        </div>
        <div class="form-group">
          <label for="edit-oeuvre-{{ $syndicat['id'] }}">Montant œuvre sociale</label>
          <input class="form-control {{ $isEditing && $errors->has('montant_oeuvre_sociale') ? 'is-invalid' : '' }}" id="edit-oeuvre-{{ $syndicat['id'] }}" name="montant_oeuvre_sociale" type="number" min="0" max="9999999999.99" step="0.01" value="{{ $isEditing ? old('montant_oeuvre_sociale') : ($syndicat['montant_oeuvre_sociale'] ?? '') }}">
          @if ($isEditing)@error('montant_oeuvre_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
        </div>
        <div class="form-group syndicat-status-field">
          <label for="edit-status-{{ $syndicat['id'] }}">Statut *</label>
          <select class="form-control {{ $isEditing && $errors->has('est_actif') ? 'is-invalid' : '' }}" id="edit-status-{{ $syndicat['id'] }}" name="est_actif" required>
            <option value="1" @selected((string) ($isEditing ? old('est_actif') : (int) $syndicat['est_actif']) === '1')>Actif</option>
            <option value="0" @selected((string) ($isEditing ? old('est_actif') : (int) $syndicat['est_actif']) === '0')>Inactif</option>
          </select>
          @if ($isEditing)@error('est_actif')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
      </div>
    </form>
  </x-module-indemnite>@endif
@endforeach
@endsection

@push('styles')
<style>
  #create-syndicat-modal .modal-dialog,
  [id^="edit-syndicat-"] .modal-dialog { width: calc(100% - 32px); max-width: 900px; }
  [id^="show-syndicat-"] .modal-dialog { width: calc(100% - 32px); max-width: 820px; }
  #create-syndicat-modal .modal-header,
  [id^="edit-syndicat-"] .modal-header,
  [id^="show-syndicat-"] .modal-header { margin-bottom: 0; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
  #create-syndicat-modal .teacher-form,
  [id^="edit-syndicat-"] .teacher-form { gap: 20px; padding: 22px 0 0; }
  #create-syndicat-modal .form-actions,
  [id^="edit-syndicat-"] .form-actions,
  [id^="show-syndicat-"] .form-actions { margin-top: 4px; padding-top: 18px; border-top: 1px solid #e2e8f0; }
  .syndicat-status-field { grid-column: 1 / -1; max-width: 320px; }
  .form-alert { margin: 12px 0; padding: 12px 16px; border-radius: 8px; background: #fef2f2; color: #b91c1c; }
  .syndicats-filters { margin-bottom: 20px; }
  .syndicats-filter-actions { align-self: end; }
  .pagination .page-btn[aria-disabled="true"] { cursor: not-allowed; opacity: .5; }
  .syndicat-detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 20px; }
  .syndicat-detail-grid div { position: relative; min-height: 108px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 11px; background: #f8fafc; }
  .syndicat-detail-grid div > i { margin-bottom: 12px; color: var(--primary); font-size: 1rem; }
  .syndicat-detail-grid div > span, .syndicat-detail-grid div > strong { display: block; }
  .syndicat-detail-grid div > span { margin-bottom: 6px; color: #64748b; font-size: .8rem; }
  .detail-hint { margin-top: 12px; color: #64748b; font-size: .8rem; }
  .inline-action-form { display: inline; }
  @media (max-width: 760px) {
    #create-syndicat-modal .modal-dialog,
    [id^="edit-syndicat-"] .modal-dialog,
    [id^="show-syndicat-"] .modal-dialog { width: calc(100% - 20px); }
    .syndicat-status-field { max-width: none; }
    .syndicat-detail-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/syndicat-form.js') }}" defer></script>
  <script src="{{ asset('assets/js/syndicat-list.js') }}" defer></script>
@endpush
