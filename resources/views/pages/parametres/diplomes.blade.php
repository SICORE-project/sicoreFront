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

    <form id="diplomesFilterForm" action="{{ route('parametres.diplomes.index') }}" method="GET" class="filter-panel" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
      <div class="form-group">
        <label for="diplomeFilter">Diplôme</label>
        <select class="form-control" id="diplomeFilter" name="libelle" form="diplomesFilterForm" data-diploma-filter>
          <option value="">Tous les diplômes</option>
          @foreach(collect($diplomaOptions)->pluck('libelle')->map(fn ($label) => mb_strtoupper(trim((string) $label), 'UTF-8'))->unique()->sort()->values() as $label)
            <option value="{{ $label }}" @selected(mb_strtoupper(trim((string) request('libelle')), 'UTF-8') === $label)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="diplomeCategoryFilter">Catégorie</label>
        <select class="form-control" id="diplomeCategoryFilter" name="categorie_id" form="diplomesFilterForm" data-diploma-filter>
          <option value="">Toutes les catégories</option>
          @foreach($categoryOptions as $category)
            <option value="{{ data_get($category, 'id') }}" @selected((string) request('categorie_id') === (string) data_get($category, 'id'))>{{ data_get($category, 'libelle') }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="salaryMinFilter">Salaire brut minimum (FCFA)</label>
        <input class="form-control" id="salaryMinFilter" name="salaire_min" type="number" min="0" step="0.01" value="{{ request('salaire_min') }}" placeholder="Minimum" data-salary-filter>
      </div>
      <div class="form-group">
        <label for="salaryMaxFilter">Salaire brut maximum (FCFA)</label>
        <input class="form-control" id="salaryMaxFilter" name="salaire_max" type="number" min="0" step="0.01" value="{{ request('salaire_max') }}" placeholder="Maximum" data-salary-filter>
      </div>
    </form>

    @if ($error)
      <p class="empty-message">{{ $error }}</p>
    @endif

    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="diplomesTable">
          <thead>
            <tr>
              <th>Libellé</th>
              <th>Catégorie</th>
              <th>Salaire brut</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($diplomes as $diplome)
              <tr>
                <td>{{ $diplome['libelle'] ?? '—' }}</td>
                <td>{{ data_get($diplome, 'categorie.libelle', '—') }}</td>
                <td>{{ number_format((float) ($diplome['salaire_brut'] ?? 0), 0, ',', ' ') }} FCFA</td>
                <td class="actions-cell">
                  <button class="icon-action" type="button" title="Modifier"
                          data-edit-diplome
                          data-id="{{ $diplome['id'] }}"
                          data-libelle="{{ $diplome['libelle'] ?? '' }}"
                          data-categorie-id="{{ $diplome['categorie_id'] ?? data_get($diplome, 'categorie.id') }}"
                          data-salaire-brut="{{ $diplome['salaire_brut'] ?? 0 }}">
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
                <td colspan="4" class="empty-message">Aucun diplôme trouvé.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="actions-row diplome-pagination-controls">
        <p>{{ $meta['total'] ?? 0 }} résultat(s)</p>
        <div class="form-group">
          <label for="diplomesPerPage">Résultats par page</label>
          <select class="form-control" id="diplomesPerPage" name="per_page" form="diplomesFilterForm" data-diploma-filter>
            @foreach([10, 25, 50, 100] as $limit)
              <option value="{{ $limit }}" @selected((int) ($meta['per_page'] ?? 10) === $limit)>{{ $limit }}</option>
            @endforeach
          </select>
        </div>
      </div>
      @php
        $currentPage = max(1, (int) ($meta['current_page'] ?? 1));
        $lastPage = max(1, (int) ($meta['last_page'] ?? 1));
        $pageFilters = request()->only(['libelle', 'categorie_id', 'salaire_min', 'salaire_max', 'per_page']);
      @endphp
      <nav class="pagination" aria-label="Pagination des diplômes">
        @if ($currentPage > 1)
          <a class="page-btn" href="{{ route('parametres.diplomes.index', array_merge($pageFilters, ['page' => $currentPage - 1])) }}" aria-label="Page précédente">←</a>
        @else
          <button class="page-btn" type="button" aria-label="Page précédente" disabled>←</button>
        @endif
        @for ($pageNumber = 1; $pageNumber <= $lastPage; $pageNumber++)
          <a class="page-btn {{ $pageNumber === $currentPage ? 'active' : '' }}"
             href="{{ route('parametres.diplomes.index', array_merge($pageFilters, ['page' => $pageNumber])) }}"
             @if ($pageNumber === $currentPage) aria-current="page" @endif>{{ $pageNumber }}</a>
        @endfor
        @if ($currentPage < $lastPage)
          <a class="page-btn" href="{{ route('parametres.diplomes.index', array_merge($pageFilters, ['page' => $currentPage + 1])) }}" aria-label="Page suivante">→</a>
        @else
          <button class="page-btn" type="button" aria-label="Page suivante" disabled>→</button>
        @endif
      </nav>
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
        <label for="diplome-libelle">Libellé <span aria-hidden="true">*</span></label>
        <input id="diplome-libelle" name="libelle" type="text" value="{{ old('libelle') }}" maxlength="100" required autofocus>
        @error('libelle') <p class="form-error" role="alert">{{ $message }}</p> @enderror
      </div>

      <div class="form-field">
        <label for="diplome-categorie">Catégorie <span aria-hidden="true">*</span></label>
        <select id="diplome-categorie" name="categorie_id" required>
          <option value="">Sélectionner une catégorie</option>
          @foreach($categoryOptions as $categorie)<option value="{{ data_get($categorie, 'id') }}" @selected((string) old('categorie_id') === (string) data_get($categorie, 'id'))>{{ data_get($categorie, 'libelle') }}</option>@endforeach
        </select>
        @error('categorie_id') <p class="form-error" role="alert">{{ $message }}</p> @enderror
      </div>

      <div class="form-field">
        <label for="diplome-salaire">Salaire brut <span aria-hidden="true">*</span></label>
        <input id="diplome-salaire" name="salaire_brut" type="number" min="0" step="1" value="{{ old('salaire_brut') }}" required>
        @error('salaire_brut') <p class="form-error" role="alert">{{ $message }}</p> @enderror
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
        <label for="edit-diplome-libelle">Libellé <span aria-hidden="true">*</span></label>
        <input id="edit-diplome-libelle" name="libelle" type="text" maxlength="100" required>
      </div>
      <div class="form-field">
        <label for="edit-diplome-categorie">Catégorie <span aria-hidden="true">*</span></label>
        <select id="edit-diplome-categorie" name="categorie_id" required>
          <option value="">Sélectionner une catégorie</option>
          @foreach($categoryOptions as $categorie)<option value="{{ data_get($categorie, 'id') }}">{{ data_get($categorie, 'libelle') }}</option>@endforeach
        </select>
      </div>
      <div class="form-field">
        <label for="edit-diplome-salaire">Salaire brut <span aria-hidden="true">*</span></label>
        <input id="edit-diplome-salaire" name="salaire_brut" type="number" min="0" step="1" required>
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
  .diplome-pagination-controls { padding: 20px 24px; gap: 24px; flex-wrap: wrap; }
  .diplome-pagination-controls p { margin: 0; }
  .diplome-pagination-controls .form-group { gap: 10px; }
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

    var diplomaOptions = @json($diplomaOptions);
    var editingDiplomaId = '';
    function filterDiplomaCategories(prefix, excludedId) {
      var input = document.getElementById(prefix + 'libelle');
      var start = input.selectionStart;
      var end = input.selectionEnd;
      var value = input.value.toUpperCase();
      if (input.value !== value) {
        input.value = value;
        input.setSelectionRange(start, end);
      }
      var label = value.trim();
      var select = document.getElementById(prefix + 'categorie');
      var used = new Set(diplomaOptions.filter(function (item) {
        return String(item.id) !== String(excludedId) && String(item.libelle || '').trim().toUpperCase() === label;
      }).map(function (item) { return String(item.categorie_id || (item.categorie && item.categorie.id) || ''); }));
      Array.from(select.options).forEach(function (option) {
        option.hidden = option.value !== '' && used.has(option.value);
        option.disabled = option.hidden;
      });
      if (select.selectedOptions[0] && select.selectedOptions[0].disabled) { select.value = ''; }
    }
    document.getElementById('diplome-libelle').addEventListener('input', function () {
      filterDiplomaCategories('diplome-', '');
    });
    document.getElementById('edit-diplome-libelle').addEventListener('input', function () {
      filterDiplomaCategories('edit-diplome-', editingDiplomaId);
    });
    filterDiplomaCategories('diplome-', '');

    var createModal = document.getElementById('create-diplome-modal');
    document.querySelectorAll('[data-modal-open="create-diplome-modal"]').forEach(function (button) {
      button.addEventListener('click', function () { openModal(createModal); });
    });

    var editModal = document.getElementById('edit-diplome-modal');
    var editForm = document.getElementById('edit-diplome-form');
    document.querySelectorAll('[data-edit-diplome]').forEach(function (button) {
      button.addEventListener('click', function () {
        editForm.action = editForm.dataset.actionTemplate.replace('__diplome__', button.dataset.id);
        document.getElementById('edit-diplome-libelle').value = button.dataset.libelle || '';
        document.getElementById('edit-diplome-categorie').value = button.dataset.categorieId || '';
        document.getElementById('edit-diplome-salaire').value = button.dataset.salaireBrut || 0;
        editingDiplomaId = button.dataset.id;
        filterDiplomaCategories('edit-diplome-', editingDiplomaId);
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

    var searchForm = document.getElementById('diplomesFilterForm');
    var salaryMin = document.getElementById('salaryMinFilter');
    var salaryMax = document.getElementById('salaryMaxFilter');
    var salaryTimer;
    try {
      var focusedSalary = sessionStorage.getItem('diplomesSalaryFocus');
      sessionStorage.removeItem('diplomesSalaryFocus');
      if (focusedSalary === salaryMin.id || focusedSalary === salaryMax.id) {
        document.getElementById(focusedSalary).focus();
      }
    } catch (error) {}
    searchForm.addEventListener('submit', function () { clearTimeout(salaryTimer); });
    function validateSalaryRange() {
      salaryMax.setCustomValidity(salaryMin.value !== '' && salaryMax.value !== '' && Number(salaryMax.value) < Number(salaryMin.value)
        ? 'Le maximum doit être supérieur ou égal au minimum.' : '');
    }
    document.querySelectorAll('[data-salary-filter]').forEach(function (input) {
      input.addEventListener('input', function () {
        clearTimeout(salaryTimer);
        validateSalaryRange();
        salaryTimer = setTimeout(function () {
          if (!searchForm.checkValidity()) { searchForm.reportValidity(); return; }
          try { sessionStorage.setItem('diplomesSalaryFocus', input.id); } catch (error) {}
          searchForm.requestSubmit();
        }, 500);
      });
    });
    document.querySelectorAll('[data-diploma-filter]').forEach(function (select) {
      select.addEventListener('change', function () {
        validateSalaryRange();
        searchForm.requestSubmit();
      });
    });
  });
</script>
@endpush
