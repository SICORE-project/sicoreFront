@extends('layouts.app')

@section('title', 'SICORE - Disciplines')

@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap"><span class="title-icon"><i class="fa-solid fa-book"></i></span><div><h1>Gestion des disciplines</h1><p>Référentiel des disciplines d’enseignement</p></div></div>
  </header>
  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Disciplines</p>
      @if ($canCreate)
        <button class="btn-primary" type="button" data-modal-open="discipline-create-modal">+ Ajouter une discipline</button>
      @endif
    </div>

    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif
    @if ($errors->has('api'))<div class="alert alert-error" role="alert">{{ $errors->first('api') }}</div>@endif

    <form class="filter-panel" method="GET" action="{{ route('parametres.disciplines.index') }}" data-discipline-filter>
      <div class="form-group"><label for="disciplineSearch">Rechercher</label><input class="form-control" id="disciplineSearch" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code ou libellé"></div>
      <div class="form-group"><label for="disciplineStatus">Statut</label><select class="form-control" id="disciplineStatus" name="statut"><option value="">Tous les statuts</option><option value="actif" @selected(($filters['statut'] ?? '') === 'actif')>Actif</option><option value="inactif" @selected(($filters['statut'] ?? '') === 'inactif')>Inactif</option></select></div>
      <button class="btn-primary" type="submit">Filtrer</button>
      <span class="loading-indicator" role="status" hidden data-loading>Chargement…</span>
    </form>

    <section class="table-card" aria-labelledby="disciplineListTitle">
      <div class="table-card-header"><div><h2 id="disciplineListTitle">Liste des disciplines</h2><p class="table-card-subtitle">{{ $pagination['total'] }} résultat{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive"><table class="table"><thead><tr>
        @foreach (['code' => 'Code', 'libelle' => 'Libellé', 'description' => 'Description', 'statut' => 'Statut'] as $field => $label)
          @php
            $nextDirection = (($filters['sort'] ?? '') === $field && ($filters['direction'] ?? 'asc') === 'asc') ? 'desc' : 'asc';
          @endphp
          <th><a href="{{ route('parametres.disciplines.index', array_merge(request()->except(['sort', 'direction', 'page']), ['sort' => $field, 'direction' => $nextDirection])) }}">{{ $label }} <span aria-hidden="true">↕</span></a></th>
        @endforeach
        @if ($canUpdate || $canChangeStatus)<th class="actions-cell">Actions</th>@endif
      </tr></thead><tbody>
        @forelse ($items as $discipline)
          @php
            $status = data_get($discipline, 'statut', data_get($discipline, 'est_actif', data_get($discipline, 'actif', false)));
            $active = in_array(is_string($status) ? mb_strtolower($status) : $status, [true, 1, '1', 'actif', 'active'], true);
          @endphp
          <tr><td><strong>{{ data_get($discipline, 'code', '—') }}</strong></td><td>{{ data_get($discipline, 'libelle', data_get($discipline, 'nom', '—')) }}</td><td>{{ data_get($discipline, 'description', '—') ?: '—' }}</td><td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td>@if ($canUpdate || $canChangeStatus)<td class="actions-cell">@if ($canUpdate)<button class="table-action" type="button" data-modal-open="discipline-update-modal" data-discipline-edit="{{ json_encode($discipline, JSON_UNESCAPED_UNICODE) }}">Modifier</button>@endif @if ($canChangeStatus && data_get($discipline, 'id', data_get($discipline, 'uuid')))<form method="POST" action="{{ route('parametres.disciplines.status', ['discipline' => data_get($discipline, 'id', data_get($discipline, 'uuid'))]) }}" class="inline-form" data-status-form data-confirm="{{ $active ? 'Désactiver cette discipline ? Elle restera consultable et ses anciennes associations seront conservées.' : 'Réactiver cette discipline ? Elle sera de nouveau disponible dans les listes de sélection.' }}">@csrf @method('PATCH')<input type="hidden" name="statut" value="{{ $active ? 'inactif' : 'actif' }}"><button class="table-action" type="submit" data-status-submit>{{ $active ? 'Désactiver' : 'Activer' }}</button></form>@endif</td>@endif</tr>
        @empty
          <tr><td colspan="{{ ($canUpdate || $canChangeStatus) ? 5 : 4 }}" class="empty-message show">Aucune discipline trouvée.</td></tr>
        @endforelse
      </tbody></table></div>
      @if ($pagination['last_page'] > 1)<nav class="pagination" aria-label="Pagination">
        @for ($page = 1; $page <= $pagination['last_page']; $page++)
          <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.disciplines.index', array_merge(request()->except('page'), ['page' => $page])) }}" @if($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
        @endfor
      </nav>@endif
    </section>
  </section>
</main>

@if ($canCreate)
<x-module-indemnite type="modal" id="discipline-create-modal" title="Ajouter une discipline">
  <form class="teacher-form" id="disciplineCreateForm" method="POST" action="{{ route('parametres.disciplines.store') }}">@csrf
    <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group"><label for="disciplineCode">Code <span class="required">*</span></label><input class="form-control" id="disciplineCode" name="code" value="{{ old('code') }}" required maxlength="30" pattern="[A-Z0-9]+(?:[-_][A-Z0-9]+)*" title="Utilisez uniquement des lettres majuscules, chiffres, tirets ou underscores.">@error('code')<span class="field-error">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="disciplineLibelle">Libellé *</label><input class="form-control" id="disciplineLibelle" name="libelle" value="{{ old('libelle') }}" required maxlength="150">@error('libelle')<span class="field-error">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="disciplineDescription">Description</label><textarea class="form-control" id="disciplineDescription" name="description" maxlength="500">{{ old('description') }}</textarea>@error('description')<span class="field-error">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="disciplineCreateStatus">Statut *</label><select class="form-control" id="disciplineCreateStatus" name="statut" required><option value="actif" @selected(old('statut', 'actif') === 'actif')>Actif</option><option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option></select>@error('statut')<span class="field-error">{{ $message }}</span>@enderror</div>
    </div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" data-discipline-submit><span data-submit-label>Ajouter</span></button></div>
  </form>
</x-module-indemnite>
@endif
@if ($canUpdate)
<x-module-indemnite type="modal" id="discipline-update-modal" title="Modifier une discipline">
  <form class="teacher-form" id="disciplineUpdateForm" method="POST" data-update-url="{{ route('parametres.disciplines.update', ['discipline' => '__ID__']) }}">@csrf @method('PUT')
    <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group"><label for="disciplineUpdateCode">Code <span class="required">*</span></label><input class="form-control" id="disciplineUpdateCode" name="code" required maxlength="30" pattern="[A-Z0-9]+(?:[-_][A-Z0-9]+)*">@error('code', 'updateDiscipline')<span class="field-error">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="disciplineUpdateLibelle">Libellé <span class="required">*</span></label><input class="form-control" id="disciplineUpdateLibelle" name="libelle" required maxlength="150">@error('libelle', 'updateDiscipline')<span class="field-error">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="disciplineUpdateDescription">Description</label><textarea class="form-control" id="disciplineUpdateDescription" name="description" maxlength="500"></textarea>@error('description', 'updateDiscipline')<span class="field-error">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="disciplineUpdateStatut">Statut <span class="required">*</span></label><select class="form-control" id="disciplineUpdateStatut" name="statut" required><option value="actif">Actif</option><option value="inactif">Inactif</option></select>@error('statut', 'updateDiscipline')<span class="field-error">{{ $message }}</span>@enderror</div>
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" data-update-submit><span data-update-label>Enregistrer</span></button></div>
  </form>
</x-module-indemnite>
@endif
@push('scripts')
<script>
  document.querySelector('[data-discipline-filter]').addEventListener('submit', function () { this.querySelector('[data-loading]').hidden = false; });
  var createForm = document.getElementById('disciplineCreateForm');
  if (createForm) {
    createForm.addEventListener('submit', function () {
      if (!createForm.checkValidity()) return;
      var submit = createForm.querySelector('[data-discipline-submit]');
      submit.disabled = true;
      submit.setAttribute('aria-busy', 'true');
      submit.querySelector('[data-submit-label]').textContent = 'Enregistrement…';
    });
  }
  var updateForm = document.getElementById('disciplineUpdateForm');
  function fillUpdateForm(discipline) {
    var id = discipline.id ?? discipline.uuid;
    updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', encodeURIComponent(id));
    document.getElementById('disciplineUpdateCode').value = discipline.code ?? '';
    document.getElementById('disciplineUpdateLibelle').value = discipline.libelle ?? discipline.nom ?? '';
    document.getElementById('disciplineUpdateDescription').value = discipline.description ?? '';
    var status = String(discipline.statut ?? discipline.est_actif ?? discipline.actif ?? '').toLowerCase();
    document.getElementById('disciplineUpdateStatut').value = ['actif', 'active', '1', 'true'].includes(status) ? 'actif' : 'inactif';
  }
  document.querySelectorAll('[data-discipline-edit]').forEach(function (button) {
    button.addEventListener('click', function () { fillUpdateForm(JSON.parse(button.dataset.disciplineEdit)); });
  });
  if (updateForm) {
    updateForm.addEventListener('submit', function () {
      if (!updateForm.checkValidity()) return;
      var submit = updateForm.querySelector('[data-update-submit]');
      submit.disabled = true;
      submit.setAttribute('aria-busy', 'true');
      submit.querySelector('[data-update-label]').textContent = 'Enregistrement…';
    });
  }
  document.querySelectorAll('[data-status-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
      }
      var button = form.querySelector('[data-status-submit]');
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
    });
  });
  @if (session('discipline_create_form_open'))
    document.querySelector('[data-modal-open="discipline-create-modal"]')?.click();
  @endif
  @if (session('discipline_update_form_open'))
    document.querySelector('[data-modal-open="discipline-update-modal"]')?.click();
    updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', @json(session('discipline_update_id')));
    document.getElementById('disciplineUpdateCode').value = @json(old('code'));
    document.getElementById('disciplineUpdateLibelle').value = @json(old('libelle'));
    document.getElementById('disciplineUpdateDescription').value = @json(old('description'));
    document.getElementById('disciplineUpdateStatut').value = @json(old('statut'));
  @endif
</script>
@endpush
@endsection
