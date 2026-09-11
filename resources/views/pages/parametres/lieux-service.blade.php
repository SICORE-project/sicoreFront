@extends('layouts.app')
@section('title', 'SICORE - Établissements')
@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon"><i class="fa-solid fa-location-dot"></i></span>
      <div><h1>Établissements</h1><p>Structures d’affectation enregistrées dans SICORE</p></div>
    </div>
  </header>
  <section class="content-area">
    <div class="actions-row"><p class="breadcrumb">Paramétrage &gt; Établissements</p><div class="actions-group"><button class="btn-primary" type="button" data-modal-open="lieu-service-create-modal">+ Nouvel établissement</button></div></div>
    <form class="filter-panel parametrage-filters" id="lieuFilterForm" method="GET" action="{{ route('parametres.lieux-service.index') }}" aria-label="Filtres des établissements">
      <input type="hidden" name="per_page" value="{{ $pagination['per_page'] }}">
      <div class="form-group">
        <label for="lieuFilterIa">IA</label>
        <select class="form-control" id="lieuFilterIa" name="ia_id" form="lieuFilterForm">
          <option value="">Toutes les IA</option>
          @foreach ($academies as $academy)
            @php $academyId = data_get($academy, 'id', data_get($academy, 'uuid')); @endphp
            <option value="{{ $academyId }}" @selected((string) ($filters['ia_id'] ?? '') === (string) $academyId)>{{ data_get($academy, 'libelle', data_get($academy, 'nom')) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="lieuFilterIef">IEF</label>
        <select class="form-control" id="lieuFilterIef" name="ief_id" form="lieuFilterForm">
          <option value="">Toutes les IEF</option>
          @foreach ($iefs as $ief)
            @php $iefId = data_get($ief, 'id', data_get($ief, 'uuid')); @endphp
            <option value="{{ $iefId }}" data-ia-id="{{ data_get($ief, 'ia_id') }}" @selected((string) ($filters['ief_id'] ?? '') === (string) $iefId)>{{ data_get($ief, 'libelle', data_get($ief, 'nom')) }}</option>
          @endforeach
        </select>
      </div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.lieux-service.index') }}">Réinitialiser</a><button class="btn-primary" type="submit" form="lieuFilterForm">Filtrer</button><span class="loading-indicator" id="lieuxLoading" role="status" hidden>Chargement…</span></div>
    </form>
    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif
    <section class="table-card" aria-labelledby="lieuxTitle">
      <div class="table-card-header"><div><h2 id="lieuxTitle">Liste des établissements</h2><p class="table-card-subtitle">{{ $pagination['total'] }} résultat{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive"><table class="table" id="lieuxTable">
        <thead><tr><th>Nom de l’établissement</th><th>IA</th><th>IEF</th><th>Téléphone</th><th>Actions</th></tr></thead>
        <tbody>@foreach ($items as $lieu)
          @php
            $lieuId = data_get($lieu, 'id', data_get($lieu, 'uuid'));
            $editPayload = [
                'id' => $lieuId,
                'libelle' => data_get($lieu, 'libelle', data_get($lieu, 'nom')),
                'ia_id' => data_get($lieu, 'ia.id', data_get($lieu, 'ia_id')),
                'ief_id' => data_get($lieu, 'ief.id', data_get($lieu, 'ief_id')),
                'telephone' => data_get($lieu, 'telephone'),
            ];
            $detailPayload = $editPayload + [
                'ia' => data_get($lieu, 'ia.libelle'),
                'ief' => data_get($lieu, 'ief.libelle'),
            ];
          @endphp
          <tr>
            <td>{{ $editPayload['libelle'] }}</td>
            <td>{{ $detailPayload['ia'] ?? '—' }}</td>
            <td>{{ $detailPayload['ief'] ?? '—' }}</td>
            <td>{{ $editPayload['telephone'] ?: '—' }}</td>
            <td class="actions-cell">
              @if ($lieuId !== null)
                <button class="icon-action" type="button" title="Voir" aria-label="Voir {{ $editPayload['libelle'] }}" data-modal-open="lieu-service-detail-modal" data-lieu-detail='@json($detailPayload)'><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                <button class="icon-action" type="button" title="Modifier" aria-label="Modifier {{ $editPayload['libelle'] }}" data-modal-open="lieu-service-edit-modal" data-lieu-edit='@json($editPayload)' data-update-url="{{ route('parametres.lieux-service.update', ['lieu' => $lieuId]) }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                <form method="POST" action="{{ route('parametres.lieux-service.destroy', ['lieu' => $lieuId]) }}" class="inline-form" data-lieu-delete data-confirm-message="Supprimer définitivement cet établissement ?">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ $editPayload['libelle'] }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button></form>
              @endif
            </td>
          </tr>
        @endforeach</tbody>
      </table></div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}"><x-table-empty-state>Aucun établissement trouvé.</x-table-empty-state></p>
      @if ($pagination['total'] > 0)
        @php
          $currentPage = $pagination['current_page'];
          $lastPage = $pagination['last_page'];
          $firstResult = ($currentPage - 1) * $pagination['per_page'] + 1;
          $lastResult = min($pagination['total'], $firstResult + count($items) - 1);
          $pageUrl = fn ($page) => route('parametres.lieux-service.index', array_merge($filters, ['per_page' => $pagination['per_page'], 'page' => $page]));
        @endphp
        <nav class="pagination" aria-label="Pagination des établissements">
          <p class="pagination-summary" aria-live="polite">{{ $firstResult }}–{{ $lastResult }} sur {{ $pagination['total'] }}</p>
          <div class="pagination-controls">
            @foreach ([['Première page', 'angles-left', 1], ['Page précédente', 'angle-left', $currentPage - 1]] as [$label, $icon, $targetPage])
              @if ($currentPage <= 1)
                <button class="page-btn page-btn-direction" type="button" disabled aria-label="{{ $label }}"><i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i></button>
              @else
                <a class="page-btn page-btn-direction" href="{{ $pageUrl($targetPage) }}" aria-label="{{ $label }}"><i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i></a>
              @endif
            @endforeach
            <span class="page-btn page-number active" aria-current="page" aria-label="Page {{ $currentPage }} sur {{ $lastPage }}">{{ $currentPage }}</span>
            @foreach ([['Page suivante', 'angle-right', $currentPage + 1], ['Dernière page', 'angles-right', $lastPage]] as [$label, $icon, $targetPage])
              @if ($currentPage >= $lastPage)
                <button class="page-btn page-btn-direction" type="button" disabled aria-label="{{ $label }}"><i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i></button>
              @else
                <a class="page-btn page-btn-direction" href="{{ $pageUrl($targetPage) }}" aria-label="{{ $label }}"><i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i></a>
              @endif
            @endforeach
            <form method="GET" action="{{ route('parametres.lieux-service.index') }}" id="lieuPageSizeForm">
              @foreach ($filters as $name => $value)
                @if ($value !== null)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
              @endforeach
              <input type="hidden" name="page" value="1">
              <label class="visually-hidden" for="lieuPageSize">Nombre de lignes par page</label>
              <select class="page-size-select" id="lieuPageSize" name="per_page" aria-label="Nombre de lignes par page">
                @foreach ([10, 20, 50] as $size)<option value="{{ $size }}" @selected($pagination['per_page'] === $size)>{{ $size }}</option>@endforeach
              </select>
              <noscript><button class="btn-secondary" type="submit">Appliquer</button></noscript>
            </form>
          </div>
        </nav>
      @endif
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="lieu-service-detail-modal" title="Fiche de l’établissement">
  <div class="form-grid form-grid--balanced">
    @foreach (['libelle' => 'Nom de l’établissement', 'ia' => 'IA', 'ief' => 'IEF', 'telephone' => 'Téléphone'] as $field => $label)
      <div class="form-group"><label>{{ $label }}</label><p data-lieu-detail-field="{{ $field }}">—</p></div>
    @endforeach
  </div>
  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>

<x-module-indemnite type="modal" id="lieu-service-create-modal" title="Créer un établissement" :open="$errors->any() || session()->has('lieu_form_open')">
  <form class="teacher-form" method="POST" action="{{ route('parametres.lieux-service.store') }}" id="lieuServiceCreateForm">
    @csrf
    @if ($errors->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger le formulaire.</strong><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group">
        <label for="lieuIa">Inspection d’académie (IA) <span class="required">*</span></label>
        <select class="form-control" id="lieuIa" name="ia_id" required>
          <option value="">Sélectionner une IA</option>
          @foreach ($academies as $academy)
            @php $academyId = data_get($academy, 'id', data_get($academy, 'uuid')); @endphp
            <option value="{{ $academyId }}" @selected((string) old('ia_id') === (string) $academyId)>{{ data_get($academy, 'code') ? data_get($academy, 'code').' — ' : '' }}{{ data_get($academy, 'libelle', data_get($academy, 'nom')) }}</option>
          @endforeach
        </select>
        @error('ia_id')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label for="lieuIef">IEF <span class="required">*</span></label>
        <select class="form-control" id="lieuIef" name="ief_id" required data-old-value="{{ old('ief_id') }}">
          <option value="">Sélectionner d’abord une IA</option>
          @foreach ($iefs as $ief)
            @php $iefId = data_get($ief, 'id', data_get($ief, 'uuid')); @endphp
            <option value="{{ $iefId }}" data-ia-id="{{ data_get($ief, 'ia_id') }}" @selected((string) old('ief_id') === (string) $iefId)>{{ data_get($ief, 'code') ? data_get($ief, 'code').' — ' : '' }}{{ data_get($ief, 'libelle', data_get($ief, 'nom')) }}</option>
          @endforeach
        </select>
        <small>Seules les IEF rattachées à l’IA choisie sont proposées.</small>
        @error('ief_id')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label for="lieuLibelle">Nom de l’établissement <span class="required">*</span></label>
        <input class="form-control" id="lieuLibelle" name="libelle" type="text" maxlength="100" required value="{{ old('libelle') }}" placeholder="Ex. École élémentaire Liberté">
        @error('libelle')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label for="lieuTelephone">Téléphone (facultatif)</label>
        <input class="form-control" id="lieuTelephone" name="telephone" type="tel" maxlength="20" value="{{ old('telephone') }}">
        @error('telephone')<small class="field-error">{{ $message }}</small>@enderror
      </div>
    </div>
    @if (empty($academies))<div class="alert alert-warning" role="note">Aucune IA n’est disponible. Vérifiez la connexion au référentiel avant de créer un établissement.</div>@endif
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" @disabled(empty($academies))>Créer l’établissement</button></div>
  </form>
</x-module-indemnite>

<x-module-indemnite type="modal" id="lieu-service-edit-modal" title="Modifier un établissement" :open="$errors->updateLieu->any() || session()->has('lieu_edit_form_open')">
  <form class="teacher-form" method="POST" action="{{ session('lieu_edit_id') ? route('parametres.lieux-service.update', ['lieu' => session('lieu_edit_id')]) : '#' }}" id="lieuServiceEditForm">
    @csrf
    @method('PUT')
    @if ($errors->updateLieu->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger le formulaire.</strong><ul>@foreach ($errors->updateLieu->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group">
        <label for="lieuEditIa">Inspection d’académie (IA) <span class="required">*</span></label>
        <select class="form-control" id="lieuEditIa" name="ia_id" required><option value="">Sélectionner une IA</option>@foreach ($academies as $academy) @php $academyId = data_get($academy, 'id', data_get($academy, 'uuid')); @endphp <option value="{{ $academyId }}">{{ data_get($academy, 'code') ? data_get($academy, 'code').' — ' : '' }}{{ data_get($academy, 'libelle', data_get($academy, 'nom')) }}</option>@endforeach</select>
        @error('ia_id', 'updateLieu')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label for="lieuEditIef">IEF <span class="required">*</span></label>
        <select class="form-control" id="lieuEditIef" name="ief_id" required><option value="">Sélectionner d’abord une IA</option>@foreach ($iefs as $ief) @php $iefId = data_get($ief, 'id', data_get($ief, 'uuid')); @endphp <option value="{{ $iefId }}" data-ia-id="{{ data_get($ief, 'ia_id') }}">{{ data_get($ief, 'code') ? data_get($ief, 'code').' — ' : '' }}{{ data_get($ief, 'libelle', data_get($ief, 'nom')) }}</option>@endforeach</select>
        <small>Le changement d’IA met à jour les IEF proposées.</small>
        @error('ief_id', 'updateLieu')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group"><label for="lieuEditLibelle">Nom de l’établissement <span class="required">*</span></label><input class="form-control" id="lieuEditLibelle" name="libelle" type="text" maxlength="100" required value="{{ $errors->updateLieu->any() ? old('libelle') : '' }}">@error('libelle', 'updateLieu')<small class="field-error">{{ $message }}</small>@enderror</div>
      <div class="form-group"><label for="lieuEditTelephone">Téléphone (facultatif)</label><input class="form-control" id="lieuEditTelephone" name="telephone" type="tel" maxlength="20" value="{{ $errors->updateLieu->any() ? old('telephone') : '' }}">@error('telephone', 'updateLieu')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer les modifications</button></div>
  </form>
</x-module-indemnite>

@push('scripts')
<script>
  (function () {
    var filterForm = document.getElementById('lieuFilterForm');
    var iaFilter = document.getElementById('lieuFilterIa');
    var iefFilter = document.getElementById('lieuFilterIef');
    var loading = document.getElementById('lieuxLoading');
    var pageSize = document.getElementById('lieuPageSize');
    if (pageSize) pageSize.addEventListener('change', function () { this.form.requestSubmit(); });
    if (filterForm) filterForm.addEventListener('submit', function () { if (loading) loading.hidden = false; });
    if (iaFilter && iefFilter) {
      var filterOptions = Array.prototype.slice.call(iefFilter.querySelectorAll('option[data-ia-id]'));
      var selectedIef = iefFilter.value;
      function filterFilterIefs(keepSelection) {
        filterOptions.forEach(function (option) {
          option.hidden = !!iaFilter.value && option.dataset.iaId !== iaFilter.value;
          option.disabled = option.hidden;
        });
        if (!keepSelection || !filterOptions.some(function (option) { return !option.hidden && option.value === selectedIef; })) iefFilter.value = '';
      }
      iaFilter.addEventListener('change', function () { selectedIef = ''; filterFilterIefs(false); });
      filterFilterIefs(true);
    }
    document.querySelectorAll('[data-lieu-detail]').forEach(function (button) {
      button.addEventListener('click', function () {
        var item = JSON.parse(button.dataset.lieuDetail);
        document.querySelectorAll('[data-lieu-detail-field]').forEach(function (field) { field.textContent = item[field.dataset.lieuDetailField] || '—'; });
      });
    });
  }());

  (function () {
    var iaSelect = document.getElementById('lieuIa');
    var iefSelect = document.getElementById('lieuIef');
    if (!iaSelect || !iefSelect) return;
    var options = Array.prototype.slice.call(iefSelect.querySelectorAll('option[data-ia-id]'));
    function filterIefs(keepSelection) {
      var selectedIa = iaSelect.value;
      var wantedIef = keepSelection ? iefSelect.dataset.oldValue : '';
      iefSelect.value = '';
      options.forEach(function (option) {
        option.hidden = !selectedIa || option.dataset.iaId !== selectedIa;
        option.disabled = option.hidden;
        if (!option.hidden && option.value === wantedIef) iefSelect.value = wantedIef;
      });
      iefSelect.options[0].textContent = selectedIa ? 'Sélectionner une IEF' : 'Sélectionner d’abord une IA';
    }
    iaSelect.addEventListener('change', function () { filterIefs(false); });
    filterIefs(true);
  }());

  (function () {
    var form = document.getElementById('lieuServiceEditForm');
    var iaSelect = document.getElementById('lieuEditIa');
    var iefSelect = document.getElementById('lieuEditIef');
    if (!form || !iaSelect || !iefSelect) return;
    var options = Array.prototype.slice.call(iefSelect.querySelectorAll('option[data-ia-id]'));
    function filterEditIefs(wantedIef) {
      options.forEach(function (option) {
        option.hidden = !iaSelect.value || option.dataset.iaId !== iaSelect.value;
        option.disabled = option.hidden;
      });
      var wanted = String(wantedIef || '');
      iefSelect.value = options.some(function (option) { return !option.hidden && option.value === wanted; }) ? wanted : '';
      iefSelect.options[0].textContent = iaSelect.value ? 'Sélectionner une IEF' : 'Sélectionner d’abord une IA';
    }
    document.querySelectorAll('[data-lieu-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var lieu = JSON.parse(button.dataset.lieuEdit);
        form.action = button.dataset.updateUrl;
        document.getElementById('lieuEditTelephone').value = lieu.telephone || '';
        document.getElementById('lieuEditLibelle').value = lieu.libelle || '';
        iaSelect.value = String(lieu.ia_id || '');
        filterEditIefs(lieu.ief_id);
      });
    });
    iaSelect.addEventListener('change', function () { filterEditIefs(''); });
    @if ($errors->updateLieu->any())
      iaSelect.value = @json((string) old('ia_id'));
      filterEditIefs(@json((string) old('ief_id')));
    @else
      filterEditIefs('');
    @endif
  }());

</script>
@endpush
@endsection
