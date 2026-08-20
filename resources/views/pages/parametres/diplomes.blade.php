@extends('layouts.app')

@section('title', 'SICORE - Diplômes')

@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
      <div>
        <h1>Diplômes</h1>
        <p>Administration &gt; Paramétrage &gt; Diplômes</p>
      </div>
    </div>
    <form class="search-wrap" action="{{ route('parametres.diplomes.index') }}" method="GET" data-diplomes-search>
      <label class="sr-only" for="diplomesSearch">Rechercher un diplôme</label>
      <input class="search-input" id="diplomesSearch" name="search" type="search"
             value="{{ request('search') }}" placeholder="Rechercher par code ou libellé...">
    </form>
  </header>

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb">Administration &gt; Paramétrage &gt; Diplômes</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" data-modal-open="create-diplome-modal">
          <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter un diplôme
        </button>
      </div>
    </div>

    @if ($error)
      <p class="empty-message">{{ $error }}</p>
    @endif

    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="diplomesTable">
          <thead>
            <tr>
              <th>Code</th>
              <th>Libellé</th>
              <th>Type</th>
              <th>Date d'obtention</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($diplomes as $diplome)
              <tr>
                <td>{{ $diplome['code'] ?? '—' }}</td>
                <td>{{ $diplome['libelle'] ?? '—' }}</td>
                <td>{{ $diplome['type'] ?? '—' }}</td>
                <td>{{ $diplome['date_obteention'] ?? '—' }}</td>
                <td class="actions-cell">
                  <button class="icon-action" type="button" title="Modifier"
                          data-edit-diplome
                          data-id="{{ $diplome['id'] }}"
                          data-code="{{ $diplome['code'] ?? '' }}"
                          data-libelle="{{ $diplome['libelle'] ?? '' }}"
                          data-type="{{ $diplome['type'] ?? '' }}"
                          data-date="{{ $diplome['date_obteention'] ?? '' }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i><span class="sr-only">Modifier</span>
                  </button>
                  <button class="icon-action icon-action-danger" type="button" title="Supprimer ce diplôme"
                          data-delete-diplome data-id="{{ $diplome['id'] }}" data-libelle="{{ $diplome['libelle'] ?? '' }}">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i><span class="sr-only">Supprimer</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="empty-message">Aucun diplôme trouvé.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if (($meta['last_page'] ?? 1) > 1)
        <nav class="pagination" aria-label="Pagination des diplômes">
          @if (($meta['current_page'] ?? 1) > 1)
            <a class="page-btn" href="{{ route('parametres.diplomes.index', array_filter(['search' => request('search'), 'type' => request('type'), 'page' => $meta['current_page'] - 1])) }}">&#8592;</a>
          @endif
          <span class="page-btn active">{{ $meta['current_page'] }} / {{ $meta['last_page'] }}</span>
          @if (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
            <a class="page-btn" href="{{ route('parametres.diplomes.index', array_filter(['search' => request('search'), 'type' => request('type'), 'page' => $meta['current_page'] + 1])) }}">&#8594;</a>
          @endif
        </nav>
      @endif
    </section>
  </section>
</main>

<div class="modal-backdrop" id="create-diplome-modal" data-modal @if (! $errors->isEmpty()) aria-hidden="false" @else hidden @endif>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="create-diplome-title">
    <div class="modal-header">
      <h2 id="create-diplome-title">Ajouter un diplôme</h2>
      <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
    </div>

    <form action="{{ route('parametres.diplomes.store') }}" method="POST" class="diplome-form">
      @csrf

      @error('diplome')
        <p class="form-error" role="alert">{{ $message }}</p>
      @enderror

      <div class="form-field">
        <label for="diplome-code">Code <span aria-hidden="true">*</span></label>
        <input id="diplome-code" name="code" type="text" value="{{ old('code') }}" maxlength="20" required autofocus>
        @error('code') <p class="form-error" role="alert">{{ $message }}</p> @enderror
      </div>

      <div class="form-field">
        <label for="diplome-libelle">Libellé <span aria-hidden="true">*</span></label>
        <input id="diplome-libelle" name="libelle" type="text" value="{{ old('libelle') }}" maxlength="100" required>
        @error('libelle') <p class="form-error" role="alert">{{ $message }}</p> @enderror
      </div>

      <div class="form-field">
        <label for="diplome-type">Type</label>
        <select id="diplome-type" name="type">
          <option value="">Sélectionner un type</option>
          <option value="academique" @selected(old('type') === 'academique')>Académique</option>
          <option value="professionnel" @selected(old('type') === 'professionnel')>Professionnel</option>
        </select>
        @error('type') <p class="form-error" role="alert">{{ $message }}</p> @enderror
      </div>

      <div class="form-field">
        <label for="diplome-date">Date d'obtention <span aria-hidden="true">*</span></label>
        <input id="diplome-date" name="date_obteention" type="date" value="{{ old('date_obteention') }}" required>
        @error('date_obteention') <p class="form-error" role="alert">{{ $message }}</p> @enderror
      </div>

      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-backdrop" id="edit-diplome-modal" data-modal hidden>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="edit-diplome-title">
    <div class="modal-header">
      <h2 id="edit-diplome-title">Modifier le diplôme</h2>
      <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
    </div>

    <form id="edit-diplome-form" method="POST" class="diplome-form" data-action-template="{{ route('parametres.diplomes.update', ['diplome' => '__diplome__']) }}">
      @csrf
      @method('PUT')
      <div class="form-field">
        <label for="edit-diplome-code">Code <span aria-hidden="true">*</span></label>
        <input id="edit-diplome-code" name="code" type="text" maxlength="20" required>
      </div>
      <div class="form-field">
        <label for="edit-diplome-libelle">Libellé <span aria-hidden="true">*</span></label>
        <input id="edit-diplome-libelle" name="libelle" type="text" maxlength="100" required>
      </div>
      <div class="form-field">
        <label for="edit-diplome-type">Type</label>
        <select id="edit-diplome-type" name="type">
          <option value="">Sélectionner un type</option>
          <option value="academique">Académique</option>
          <option value="professionnel">Professionnel</option>
        </select>
      </div>
      <div class="form-field">
        <label for="edit-diplome-date">Date d'obtention <span aria-hidden="true">*</span></label>
        <input id="edit-diplome-date" name="date_obteention" type="date" required>
      </div>
      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit">Enregistrer les modifications</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-backdrop" id="delete-diplome-modal" data-modal hidden>
  <div class="modal-dialog modal-confirm" role="dialog" aria-modal="true" aria-labelledby="delete-diplome-title">
    <div class="modal-header">
      <h2 id="delete-diplome-title">Supprimer le diplôme</h2>
      <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
    </div>
    <p>Voulez-vous vraiment supprimer <strong id="delete-diplome-label"></strong> ? Cette action est irréversible.</p>
    <form id="delete-diplome-form" method="POST" class="form-actions" data-action-template="{{ route('parametres.diplomes.destroy', ['diplome' => '__diplome__']) }}">
      @csrf
      @method('DELETE')
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-danger-soft" type="submit">Supprimer</button>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
  .modal-backdrop { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 28px; background: rgba(15, 23, 42, .58); }
  .modal-backdrop[hidden] { display: none; }
  .modal-dialog { width: 100%; max-width: 680px; max-height: calc(100vh - 56px); overflow-y: auto; padding: 32px 36px; border: 1px solid #e2e8f0; border-radius: 16px; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .3); }
  .modal-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 26px; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0; }
  .modal-header h2 { margin: 0; color: #0f172a; font-size: 1.45rem; }
  .modal-close { display: grid; width: 36px; height: 36px; place-items: center; border: 0; border-radius: 50%; background: #f1f5f9; color: #475569; font-size: 1.6rem; cursor: pointer; }
  .modal-close:hover { background: #e2e8f0; color: #0f172a; }
  .diplome-form { display: grid; gap: 20px; }
  .form-field { display: grid; gap: 8px; }
  .form-field label { color: #334155; font-size: .95rem; font-weight: 800; }
  .form-field label span { color: #dc2626; }
  .form-field input, .form-field select { box-sizing: border-box; width: 100%; height: 48px; min-height: 48px; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a; font-size: 1rem; line-height: 1.2; background: #fff; transition: border-color .15s ease, box-shadow .15s ease; }
  .form-field input::placeholder { color: #94a3b8; }
  .form-field input:focus, .form-field select:focus { outline: 0; border-color: #087f5b; box-shadow: 0 0 0 3px rgba(8, 127, 91, .15); }
  .form-error { margin: 0; color: #b91c1c; font-size: .875rem; font-weight: 600; }
  .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px; padding-top: 22px; border-top: 1px solid #e2e8f0; }
  .form-actions .btn-primary, .form-actions .btn-secondary { min-height: 46px; padding-inline: 22px; }
  .icon-action-danger { color: #b91c1c; }
  .modal-confirm p { margin: 0; color: #475569; line-height: 1.6; }
  @media (max-width: 640px) { .modal-backdrop { padding: 12px; } .modal-dialog { max-height: calc(100vh - 24px); padding: 24px 20px; } .form-actions { flex-direction: column-reverse; } .form-actions .btn-primary, .form-actions .btn-secondary { width: 100%; } }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    function openModal(modal) { modal.hidden = false; }
    function closeModal(modal) { modal.hidden = true; }

    var createModal = document.getElementById('create-diplome-modal');
    document.querySelectorAll('[data-modal-open="create-diplome-modal"]').forEach(function (button) {
      button.addEventListener('click', function () { openModal(createModal); });
    });

    var editModal = document.getElementById('edit-diplome-modal');
    var editForm = document.getElementById('edit-diplome-form');
    document.querySelectorAll('[data-edit-diplome]').forEach(function (button) {
      button.addEventListener('click', function () {
        editForm.action = editForm.dataset.actionTemplate.replace('__diplome__', button.dataset.id);
        document.getElementById('edit-diplome-code').value = button.dataset.code || '';
        document.getElementById('edit-diplome-libelle').value = button.dataset.libelle || '';
        document.getElementById('edit-diplome-type').value = button.dataset.type || '';
        document.getElementById('edit-diplome-date').value = button.dataset.date || '';
        openModal(editModal);
      });
    });

    var deleteModal = document.getElementById('delete-diplome-modal');
    var deleteForm = document.getElementById('delete-diplome-form');
    document.querySelectorAll('[data-delete-diplome]').forEach(function (button) {
      button.addEventListener('click', function () {
        deleteForm.action = deleteForm.dataset.actionTemplate.replace('__diplome__', button.dataset.id);
        document.getElementById('delete-diplome-label').textContent = button.dataset.libelle || 'ce diplôme';
        openModal(deleteModal);
      });
    });

    document.querySelectorAll('[data-modal]').forEach(function (modal) {
      modal.querySelectorAll('[data-modal-close]').forEach(function (button) {
        button.addEventListener('click', function () { closeModal(modal); });
      });
      modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal(modal);
      });
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') document.querySelectorAll('[data-modal]:not([hidden])').forEach(closeModal);
    });

    var searchForm = document.querySelector('[data-diplomes-search]');
    var searchInput = document.getElementById('diplomesSearch');
    var searchDelay;
    if (searchForm && searchInput) {
      var savedSearch = window.sessionStorage.getItem('diplomesSearchFocus');
      if (savedSearch !== null) {
        window.sessionStorage.removeItem('diplomesSearchFocus');
        searchInput.focus();
        searchInput.setSelectionRange(savedSearch.length, savedSearch.length);
      }

      searchInput.addEventListener('input', function () {
        window.clearTimeout(searchDelay);
        searchDelay = window.setTimeout(function () {
          window.sessionStorage.setItem('diplomesSearchFocus', searchInput.value);
          searchForm.submit();
        }, 350);
      });
    }
  });
</script>
@endpush
