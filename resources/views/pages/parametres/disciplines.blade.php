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
      </tr></thead><tbody>
        @forelse ($items as $discipline)
          @php
            $status = data_get($discipline, 'statut', data_get($discipline, 'est_actif', data_get($discipline, 'actif', false)));
            $active = in_array(is_string($status) ? mb_strtolower($status) : $status, [true, 1, '1', 'actif', 'active'], true);
          @endphp
          <tr><td><strong>{{ data_get($discipline, 'code', '—') }}</strong></td><td>{{ data_get($discipline, 'libelle', data_get($discipline, 'nom', '—')) }}</td><td>{{ data_get($discipline, 'description', '—') ?: '—' }}</td><td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td></tr>
        @empty
          <tr><td colspan="4" class="empty-message show">Aucune discipline trouvée.</td></tr>
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
  @if (session('discipline_create_form_open'))
    document.querySelector('[data-modal-open="discipline-create-modal"]')?.click();
  @endif
</script>
@endpush
@endsection
