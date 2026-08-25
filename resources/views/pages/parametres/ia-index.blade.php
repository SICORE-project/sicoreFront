@extends('layouts.app')

@section('title', 'SICORE - Inspections d’académie')

@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-building-columns"></i></span>
      <div><h1>Inspections d’académie</h1><p>Structures académiques enregistrées dans SICORE</p></div>
    </div>
  </header>

  <section class="content-area">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Consulter les inspections d’académie enregistrées dans SICORE.</li>
        <li>Identifier la région de rattachement de chaque inspection.</li>
      </ul>
    </section>

    <div class="stats-grid">
      <article class="stat-card">
        <div><p class="stat-label">Inspections d’académie</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Toutes les IA</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Régions couvertes</p><p class="stat-value">{{ $regionCount }}</p><p class="stat-note">Couverture territoriale</p></div>
        <span class="stat-icon purple"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></span>
      </article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb">Paramétrage &gt; Inspections d’académie</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" data-modal-open="ia-create-modal">+ Nouvelle IA</button>
      </div>
    </div>

    <form class="filter-panel" id="iaFilterForm" method="GET" action="{{ route('parametres.ia.index') }}">
      <div class="form-group"><label for="iaSearch">Rechercher</label><input class="form-control" id="iaSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Code ou libellé"></div>
      <div class="form-group"><label for="iaRegionFilter">Région</label><select class="form-control" id="iaRegionFilter" name="region_id"><option value="">Toutes les régions</option>@foreach ($regions as $region)<option value="{{ data_get($region, 'id') }}" @selected((string) request('region_id') === (string) data_get($region, 'id'))>{{ data_get($region, 'nom') }}</option>@endforeach</select></div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.ia.index') }}">Réinitialiser</a></div>
    </form>

    @if ($error)
      <div class="alert {{ $usingDemoData ? 'alert-warning' : 'alert-error' }}" role="alert">
        {{ $error }}
        @if ($usingDemoData) Les données affichées ci-dessous sont des données de démonstration frontend.@endif
      </div>
    @endif

    <section class="table-card" aria-labelledby="iaListTitle">
      <div class="table-card-header">
        <div>
          <h2 id="iaListTitle">Liste des inspections d’académie</h2>
          <p class="table-card-subtitle">{{ $pagination['total'] }} IA enregistrée{{ $pagination['total'] > 1 ? 's' : '' }}</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table" id="iaTable">
          <thead><tr>
            <th>Code</th><th>Libellé</th><th>Région</th><th class="actions-cell">Actions</th>
          </tr></thead>
          <tbody>
            @foreach ($items as $ia)
              <tr data-ia-row>
                <td>{{ data_get($ia, 'code', '—') }}</td>
                <td>{{ data_get($ia, 'libelle', data_get($ia, 'nom', '—')) }}</td>
                <td>{{ data_get($ia, 'region.libelle', data_get($ia, 'region.nom', data_get($ia, 'region', '—'))) }}</td>
                <td class="actions-cell">
                  @php($iaId = data_get($ia, 'id', data_get($ia, 'uuid')))
                  <button class="icon-action" type="button" data-modal-open="ia-edit-modal" data-ia-edit='@json($ia)' title="Modifier" aria-label="Modifier {{ data_get($ia, 'code') }}">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                  </button>
                  @if ($iaId && in_array(session('sicore_user.role_slug'), ['admin', 'super_admin'], true))
                    <form method="POST" action="{{ route('parametres.ia.destroy', ['ia' => $iaId]) }}" class="inline-form" onsubmit="return confirm('Supprimer définitivement cette IA ? Cette action est irréversible.');">
                      @csrf
                      @method('DELETE')
                      <button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ data_get($ia, 'code') }}">
                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                      </button>
                    </form>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}" role="status">Aucune inspection d’académie trouvée.</p>

      <nav class="pagination" aria-label="Pagination">
        @for ($page = 1; $page <= $pagination['last_page']; $page++)
          <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.ia.index', array_merge(request()->except('page'), ['page' => $page])) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
        @endfor
      </nav>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="ia-create-modal" title="Créer une inspection d’académie">
  <form class="teacher-form" id="iaCreateModalForm" method="POST" action="{{ route('parametres.ia.store') }}">
    @csrf
    <div class="alert alert-success" id="iaCreateModalFeedback" role="status" hidden>La nouvelle IA est valide et prête à être transmise.</div>
    <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group">
        <label for="iaCreateCode">Code <span class="required">*</span></label>
        <input class="form-control" id="iaCreateCode" name="code" type="text" maxlength="20" required autocomplete="off" placeholder="Ex. IA-DKR">
      </div>
      <div class="form-group">
        <label for="iaCreateLibelle">Libellé <span class="required">*</span></label>
        <input class="form-control" id="iaCreateLibelle" name="libelle" type="text" maxlength="150" required placeholder="Ex. Inspection d’académie de Dakar">
      </div>
      <div class="form-group">
        <label for="iaCreateRegion">Région <span class="required">*</span></label>
        <select class="form-control" id="iaCreateRegion" name="region_id" required>
          <option value="">Sélectionner une région</option>
          @foreach ($regions as $region)
            <option value="{{ data_get($region, 'id') }}">{{ data_get($region, 'nom') }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-primary" type="submit">Créer l’IA</button>
    </div>
  </form>
</x-module-indemnite>

<x-module-indemnite type="modal" id="ia-edit-modal" title="Modifier une inspection d’académie">
  <form class="teacher-form" id="iaEditForm" method="POST">
    @csrf
    @method('PUT')
    <input id="iaEditId" name="id" type="hidden">
    <div class="alert alert-success" id="iaEditFeedback" role="status" hidden>Les modifications sont valides et prêtes à être transmises.</div>
    <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group">
        <label for="iaEditCode">Code <span class="required">*</span></label>
        <input class="form-control" id="iaEditCode" name="code" type="text" maxlength="20" required>
      </div>
      <div class="form-group">
        <label for="iaEditLibelle">Libellé <span class="required">*</span></label>
        <input class="form-control" id="iaEditLibelle" name="libelle" type="text" maxlength="150" required>
      </div>
      <div class="form-group">
        <label for="iaEditRegion">Région <span class="required">*</span></label>
        <select class="form-control" id="iaEditRegion" name="region_id" required>
          <option value="">Sélectionner une région</option>
          @foreach ($regions as $region)
            <option value="{{ data_get($region, 'id') }}">{{ data_get($region, 'nom') }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-primary" type="submit">Enregistrer les modifications</button>
    </div>
  </form>
</x-module-indemnite>

@push('styles')
<style>
  #ia-create-modal .modal-dialog,
  #ia-edit-modal .modal-dialog { max-width: 920px; width: calc(100% - 32px); }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    var filterForm = document.getElementById('iaFilterForm');
    var searchInput = document.getElementById('iaSearch');
    var regionFilter = document.getElementById('iaRegionFilter');
    var searchTimer;

    searchInput.addEventListener('input', function () {
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () { filterForm.requestSubmit(); }, 400);
    });

    regionFilter.addEventListener('change', function () { filterForm.requestSubmit(); });

    document.addEventListener('DOMContentLoaded', function () {
      @if (session('error'))
        window.showToast?.('error', @json(session('error')));
      @elseif (session('success'))
        window.showToast?.('success', @json(session('success')));
      @endif
    });

    var updateUrl = @json(route('parametres.ia.update', ['ia' => '__IA__']));
    function value(data, paths, fallback) {
      for (var i = 0; i < paths.length; i += 1) {
        var current = data;
        var parts = paths[i].split('.');
        for (var j = 0; j < parts.length && current != null; j += 1) current = current[parts[j]];
        if (current !== undefined && current !== null && typeof current !== 'object') return current;
      }
      return fallback || '';
    }

    document.querySelectorAll('[data-ia-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var ia = JSON.parse(button.getAttribute('data-ia-edit'));
        document.getElementById('iaEditId').value = value(ia, ['id', 'uuid'], '');
        document.getElementById('iaEditForm').action = updateUrl.replace('__IA__', value(ia, ['id', 'uuid'], ''));
        document.getElementById('iaEditCode').value = value(ia, ['code'], '');
        document.getElementById('iaEditLibelle').value = value(ia, ['libelle', 'nom'], '');
        document.getElementById('iaEditRegion').value = value(ia, ['region_id', 'region.id'], '');
        document.getElementById('iaEditFeedback').hidden = true;
      });
    });

  }());
</script>
@endpush
@endsection
