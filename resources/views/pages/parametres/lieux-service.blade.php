@extends('layouts.app')
@section('title', 'SICORE - Lieux de service')
@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon"><i class="fa-solid fa-location-dot"></i></span>
      <div><h1>Lieux de service</h1><p>Structures d’affectation enregistrées dans SICORE</p></div>
    </div>
    <form class="search-wrap" id="lieuFilterForm" method="GET" action="{{ route('parametres.lieux-service.index') }}"><label class="sr-only" for="lieuSearch">Rechercher</label><input class="search-input" id="lieuSearch" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code ou libellé..."></form>
  </header>
  <section class="content-area">
    <div class="stats-grid four">
      <article class="stat-card"><div><p class="stat-label">Lieux de service</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Tous les lieux</p></div></article>
      <article class="stat-card"><div><p class="stat-label">Actifs</p><p class="stat-value">{{ $activeCount }}</p><p class="stat-note">Disponibles</p></div></article>
      <article class="stat-card"><div><p class="stat-label">Inactifs</p><p class="stat-value">{{ $inactiveCount }}</p><p class="stat-note">Non disponibles</p></div></article>
      <article class="stat-card"><div><p class="stat-label">Incohérences</p><p class="stat-value">{{ $inconsistentCount }}</p><p class="stat-note">Entre IA et IEF</p></div></article>
    </div>
    <div class="actions-row"><p class="breadcrumb">Paramétrage &gt; Lieux de service</p><div class="actions-group"><button class="btn-primary" type="button" data-modal-open="lieu-service-create-modal">+ Nouveau lieu</button><a class="btn-secondary" href="{{ route('parametres.lieux-service.index') }}">Actualiser</a></div></div>
    <section class="filter-panel" aria-label="Filtres des lieux de service">
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
      <div class="form-group"><label for="lieuFilterStatus">Statut</label><select class="form-control" id="lieuFilterStatus" name="statut" form="lieuFilterForm"><option value="">Tous</option><option value="actif" @selected(($filters['statut'] ?? '') === 'actif')>Actifs</option><option value="inactif" @selected(($filters['statut'] ?? '') === 'inactif')>Inactifs</option></select></div>
      <div class="form-group"><label for="lieuFilterSort">Trier par</label><select class="form-control" id="lieuFilterSort" name="sort" form="lieuFilterForm"><option value="libelle" @selected(($filters['sort'] ?? 'libelle') === 'libelle')>Libellé</option><option value="code" @selected(($filters['sort'] ?? '') === 'code')>Code</option></select></div>
      <div class="form-group"><label for="lieuFilterDirection">Ordre</label><select class="form-control" id="lieuFilterDirection" name="direction" form="lieuFilterForm"><option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>Croissant</option><option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>Décroissant</option></select></div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.lieux-service.index') }}">Réinitialiser</a><button class="btn-primary" type="submit" form="lieuFilterForm">Filtrer</button><span class="loading-indicator" id="lieuxLoading" role="status" hidden>Chargement…</span></div>
    </section>
    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif
    @if ($errors->statusLieu->any())<div class="alert alert-error" role="alert">{{ $errors->statusLieu->first() }}</div>@endif
    @if ($inconsistentCount)<div class="alert alert-warning" role="alert">Des lieux présentent une incohérence entre l’IA et l’IEF.</div>@endif
    <section class="table-card" aria-labelledby="lieuxTitle">
      <div class="table-card-header"><div><h2 id="lieuxTitle">Liste des lieux de service</h2><p class="table-card-subtitle">{{ $pagination['total'] }} résultat{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive"><table class="table" id="lieuxTable">
        <thead><tr><th>Code</th><th>Libellé</th><th>Type</th><th>IA</th><th>IEF</th><th>Adresse</th><th>Statut</th><th>Cohérence</th><th>Actions</th></tr></thead>
        <tbody>@foreach ($items as $lieu)
          @php
            $status = data_get($lieu, 'statut', data_get($lieu, 'status', data_get($lieu, 'est_actif', data_get($lieu, 'actif', true))));
            $status = is_string($status) ? mb_strtolower(trim($status)) : $status;
            $active = in_array($status, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
            $lieuId = data_get($lieu, 'id', data_get($lieu, 'uuid'));
            $iaId = data_get($lieu, 'ia.id', data_get($lieu, 'inspection_academie.id', data_get($lieu, 'ia_id', data_get($lieu, 'inspection_academie_id'))));
            $iefId = data_get($lieu, 'ief.id', data_get($lieu, 'inspection_education_formation.id', data_get($lieu, 'ief_id')));
            $iefIaId = data_get($lieu, 'ief.inspection_academie_id', data_get($lieu, 'ief.ia_id', data_get($lieu, 'ief.ia.id')));
            $coherenceApplicable = $iaId !== null || $iefIaId !== null;
            $consistent = ! $coherenceApplicable || ($iaId !== null && $iefIaId !== null && (string) $iaId === (string) $iefIaId);
            $editPayload = ['id' => $lieuId, 'code' => data_get($lieu, 'code'), 'libelle' => data_get($lieu, 'libelle', data_get($lieu, 'nom')), 'ia_id' => $iaId, 'ief_id' => $iefId];
            $detailPayload = $editPayload + ['type' => data_get($lieu, 'type.libelle', data_get($lieu, 'type')), 'ia' => data_get($lieu, 'ia.libelle', data_get($lieu, 'inspection_academie.libelle')), 'ief' => data_get($lieu, 'ief.libelle', data_get($lieu, 'inspection_education_formation.libelle')), 'adresse' => data_get($lieu, 'adresse', data_get($lieu, 'localisation')), 'statut' => $active ? 'Actif' : 'Inactif', 'coherence' => $coherenceApplicable ? ($consistent ? 'Conforme' : 'À vérifier') : 'Non applicable'];
          @endphp
          <tr>
            <td>{{ data_get($lieu, 'code', '—') }}</td><td>{{ data_get($lieu, 'libelle', data_get($lieu, 'nom', '—')) }}</td><td>{{ data_get($lieu, 'type.libelle', data_get($lieu, 'type', '—')) }}</td>
            <td>{{ data_get($lieu, 'ia.libelle', data_get($lieu, 'inspection_academie.libelle', '—')) }}</td><td>{{ data_get($lieu, 'ief.libelle', data_get($lieu, 'inspection_education_formation.libelle', '—')) }}</td><td>{{ data_get($lieu, 'adresse', data_get($lieu, 'localisation', '—')) }}</td>
            <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td><td><span class="badge {{ ! $coherenceApplicable ? '' : ($consistent ? 'badge-active' : 'badge-suspended') }}">{{ ! $coherenceApplicable ? 'Non applicable' : ($consistent ? 'Conforme' : 'À vérifier') }}</span></td>
            <td class="actions-cell">
              @if ($lieuId !== null)
                <button class="table-action" type="button" data-modal-open="lieu-service-detail-modal" data-lieu-detail='@json($detailPayload)'>Voir</button>
                <button class="table-action" type="button" data-modal-open="lieu-service-edit-modal" data-lieu-edit='@json($editPayload)' data-update-url="{{ route('parametres.lieux-service.update', ['lieu' => $lieuId]) }}">Modifier</button>
                @if ($active)<button class="table-action" type="button" data-modal-open="lieu-service-affectation-modal" data-lieu-affectation data-lieu-label="{{ data_get($lieu, 'code').' — '.data_get($lieu, 'libelle', data_get($lieu, 'nom')) }}" data-affectation-url="{{ route('parametres.lieux-service.affectations.store', ['lieu' => $lieuId]) }}">Affecter</button>@endif
                <form class="inline-form" method="POST" action="{{ route('parametres.lieux-service.status', ['lieu' => $lieuId]) }}" onsubmit="return confirm('{{ $active ? 'Désactiver ce lieu de service ? Il ne sera plus disponible pour les nouvelles affectations.' : 'Activer ce lieu de service ? Il sera disponible pour les nouvelles affectations.' }}');">
                  @csrf
                  @method('PATCH')
                  <input type="hidden" name="actif" value="{{ $active ? '0' : '1' }}">
                  <button class="table-action" type="submit" data-lieu-status>{{ $active ? 'Désactiver' : 'Activer' }}</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach</tbody>
      </table></div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}">Aucun lieu de service trouvé.</p>
      <nav class="pagination" aria-label="Pagination">@for ($page = 1; $page <= $pagination['last_page']; $page++)<a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.lieux-service.index', array_merge($filters, ['page' => $page])) }}">{{ $page }}</a>@endfor</nav>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="lieu-service-detail-modal" title="Fiche du lieu de service">
  <div class="form-grid form-grid--balanced">
    @foreach (['code' => 'Code', 'libelle' => 'Libellé', 'type' => 'Type', 'ia' => 'IA', 'ief' => 'IEF', 'adresse' => 'Adresse', 'statut' => 'Statut', 'coherence' => 'Cohérence'] as $field => $label)
      <div class="form-group"><label>{{ $label }}</label><p data-lieu-detail-field="{{ $field }}">—</p></div>
    @endforeach
  </div>
  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>

<x-module-indemnite type="modal" id="lieu-service-create-modal" title="Créer un lieu de service" :open="$errors->any() || session()->has('lieu_form_open')">
  <form class="teacher-form" method="POST" action="{{ route('parametres.lieux-service.store') }}" id="lieuServiceCreateForm">
    @csrf
    @if ($errors->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger le formulaire.</strong><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group">
        <label for="lieuCode">Code <span class="required">*</span></label>
        <input class="form-control" id="lieuCode" name="code" type="text" maxlength="30" required autocomplete="off" value="{{ old('code') }}" placeholder="Ex. LS-001">
        @error('code')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label for="lieuLibelle">Libellé <span class="required">*</span></label>
        <input class="form-control" id="lieuLibelle" name="libelle" type="text" maxlength="255" required value="{{ old('libelle') }}" placeholder="Ex. École élémentaire Liberté">
        @error('libelle')<small class="field-error">{{ $message }}</small>@enderror
      </div>
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
    </div>
    @if (empty($academies))<div class="alert alert-warning" role="note">Aucune IA n’est disponible. Vérifiez la connexion au référentiel avant de créer un lieu.</div>@endif
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" @disabled(empty($academies))>Créer le lieu</button></div>
  </form>
</x-module-indemnite>

<x-module-indemnite type="modal" id="lieu-service-edit-modal" title="Modifier un lieu de service" :open="$errors->updateLieu->any() || session()->has('lieu_edit_form_open')">
  <form class="teacher-form" method="POST" action="{{ session('lieu_edit_id') ? route('parametres.lieux-service.update', ['lieu' => session('lieu_edit_id')]) : '#' }}" id="lieuServiceEditForm">
    @csrf
    @method('PUT')
    @if ($errors->updateLieu->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger le formulaire.</strong><ul>@foreach ($errors->updateLieu->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group"><label for="lieuEditCode">Code <span class="required">*</span></label><input class="form-control" id="lieuEditCode" name="code" type="text" maxlength="30" required autocomplete="off" value="{{ $errors->updateLieu->any() ? old('code') : '' }}">@error('code', 'updateLieu')<small class="field-error">{{ $message }}</small>@enderror</div>
      <div class="form-group"><label for="lieuEditLibelle">Libellé <span class="required">*</span></label><input class="form-control" id="lieuEditLibelle" name="libelle" type="text" maxlength="255" required value="{{ $errors->updateLieu->any() ? old('libelle') : '' }}">@error('libelle', 'updateLieu')<small class="field-error">{{ $message }}</small>@enderror</div>
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
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer les modifications</button></div>
  </form>
</x-module-indemnite>

<x-module-indemnite type="modal" id="lieu-service-affectation-modal" title="Affecter un enseignant" :open="$errors->affectationLieu->any() || session()->has('affectation_form_open')">
  <form class="teacher-form" method="POST" action="{{ session('affectation_lieu_id') ? route('parametres.lieux-service.affectations.store', ['lieu' => session('affectation_lieu_id')]) : '#' }}" id="lieuServiceAffectationForm">
    @csrf
    @if ($errors->affectationLieu->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger le formulaire.</strong><ul>@foreach ($errors->affectationLieu->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <p class="breadcrumb">Lieu sélectionné : <strong id="affectationLieuLabel">{{ session('affectation_lieu_id') ? 'Lieu de service #'.session('affectation_lieu_id') : '—' }}</strong></p>
    <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group">
        <label for="affectationEnseignant">Enseignant <span class="required">*</span></label>
        <select class="form-control" id="affectationEnseignant" name="enseignant_id" required>
          <option value="">Sélectionner un enseignant</option>
          @foreach ($teachers as $teacher)
            @php $teacherId = data_get($teacher, 'id', data_get($teacher, 'uuid')); @endphp
            <option value="{{ $teacherId }}" @selected((string) old('enseignant_id') === (string) $teacherId)>{{ trim(data_get($teacher, 'prenom', '').' '.data_get($teacher, 'nom', data_get($teacher, 'name', ''))) }} — {{ data_get($teacher, 'matricule', 'Sans matricule') }}</option>
          @endforeach
        </select>
        @error('enseignant_id', 'affectationLieu')<small class="field-error">{{ $message }}</small>@enderror
      </div>
      <div class="form-group">
        <label for="affectationDateDebut">Date de début <span class="required">*</span></label>
        <input class="form-control" id="affectationDateDebut" name="date_debut" type="date" required value="{{ old('date_debut', now()->toDateString()) }}">
        @error('date_debut', 'affectationLieu')<small class="field-error">{{ $message }}</small>@enderror
      </div>
    </div>
    <div class="alert alert-warning" role="note">La nouvelle affectation clôturera l’affectation active précédente dans l’historique, selon les règles du backend.</div>
    @if (empty($teachers))<div class="alert alert-warning" role="note">Aucun enseignant n’est disponible.</div>@endif
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" @disabled(empty($teachers))>Confirmer l’affectation</button></div>
  </form>
</x-module-indemnite>

@push('scripts')
<script>
  (function () {
    var filterForm = document.getElementById('lieuFilterForm');
    var iaFilter = document.getElementById('lieuFilterIa');
    var iefFilter = document.getElementById('lieuFilterIef');
    var loading = document.getElementById('lieuxLoading');
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
        document.getElementById('lieuEditCode').value = lieu.code || '';
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

  (function () {
    var form = document.getElementById('lieuServiceAffectationForm');
    var label = document.getElementById('affectationLieuLabel');
    if (!form || !label) return;
    document.querySelectorAll('[data-lieu-affectation]').forEach(function (button) {
      button.addEventListener('click', function () {
        form.action = button.dataset.affectationUrl;
        label.textContent = button.dataset.lieuLabel;
      });
    });
  }());
</script>
@endpush
@endsection
