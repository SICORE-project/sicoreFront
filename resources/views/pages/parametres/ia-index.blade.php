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
    <div class="search-wrap">
      <label class="sr-only" for="iaSearch">Rechercher une IA</label>
      <input class="search-input" id="iaSearch" type="search" placeholder="Code, libellé, région..." data-table-filter="#iaTable">
    </div>
  </header>

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Consulter les inspections d’académie enregistrées dans SICORE.</li>
        <li>Identifier leur région, leur responsable et leurs coordonnées.</li>
        <li>Suivre clairement les inspections actives et inactives.</li>
      </ul>
    </section>

    <div class="stats-grid four">
      <article class="stat-card">
        <div><p class="stat-label">Inspections d’académie</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Toutes les IA</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Actives</p><p class="stat-value" id="iaActiveCount">{{ $activeCount }}</p><p class="stat-note">Disponibles</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Inactives</p><p class="stat-value" id="iaInactiveCount">{{ $inactiveCount }}</p><p class="stat-note">À vérifier</p></div>
        <span class="stat-icon yellow"><i class="fa-solid fa-building-circle-xmark" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Régions couvertes</p><p class="stat-value">{{ $regionCount }}</p><p class="stat-note">Couverture territoriale</p></div>
        <span class="stat-icon purple"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></span>
      </article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Inspections d’académie</p>
      <div class="actions-group">
        <a class="btn-primary" href="{{ route('parametres.ia.create') }}">+ Nouvelle IA</a>
        <a class="btn-secondary" href="{{ route('parametres.ia.index') }}">Actualiser</a>
      </div>
    </div>

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
            <th>Code</th><th>Libellé</th><th>Région</th><th>Responsable</th>
            <th>Téléphone</th><th>E-mail</th><th>Statut</th><th class="actions-cell">Actions</th>
          </tr></thead>
          <tbody>
            @foreach ($items as $ia)
              @php
                $status = data_get($ia, 'statut', data_get($ia, 'status', data_get($ia, 'est_actif', data_get($ia, 'actif'))));
                $normalizedStatus = is_string($status) ? mb_strtolower(trim($status)) : $status;
                $active = in_array($normalizedStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
                $responsable = data_get($ia, 'responsable.nom_complet', data_get($ia, 'responsable.nom', data_get($ia, 'responsable', '—')));
              @endphp
              <tr data-ia-row data-ia-active="{{ $active ? 'true' : 'false' }}">
                <td>{{ data_get($ia, 'code', '—') }}</td>
                <td>{{ data_get($ia, 'libelle', data_get($ia, 'nom', '—')) }}</td>
                <td>{{ data_get($ia, 'region.libelle', data_get($ia, 'region.nom', data_get($ia, 'region', '—'))) }}</td>
                <td>{{ is_string($responsable) ? $responsable : '—' }}</td>
                <td>{{ data_get($ia, 'telephone', data_get($ia, 'contact.telephone', '—')) }}</td>
                <td>{{ data_get($ia, 'email', data_get($ia, 'contact.email', '—')) }}</td>
                <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}" data-ia-status>{{ $active ? 'Actif' : 'Inactif' }}</span></td>
                <td class="actions-cell">
                  <button class="table-action" type="button" data-modal-open="ia-edit-modal" data-ia-edit='@json($ia)'>Modifier</button>
                  <button class="table-action" type="button" data-ia-toggle>{{ $active ? 'Désactiver' : 'Activer' }}</button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}" role="status">Aucune inspection d’académie trouvée.</p>

      <nav class="pagination" aria-label="Pagination">
        @for ($page = 1; $page <= $pagination['last_page']; $page++)
          <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.ia.index', ['page' => $page]) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
        @endfor
      </nav>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="ia-edit-modal" title="Modifier une inspection d’académie">
  <form class="teacher-form" id="iaEditForm">
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
        <select class="form-control" id="iaEditRegion" name="region" required>
          <option value="">Sélectionner une région</option>
          @foreach (['Dakar', 'Diourbel', 'Fatick', 'Kaffrine', 'Kaolack', 'Kédougou', 'Kolda', 'Louga', 'Matam', 'Saint-Louis', 'Sédhiou', 'Tambacounda', 'Thiès', 'Ziguinchor'] as $region)
            <option value="{{ $region }}">{{ $region }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="iaEditResponsable">Responsable</label>
        <input class="form-control" id="iaEditResponsable" name="responsable" type="text" maxlength="150">
      </div>
      <div class="form-group">
        <label for="iaEditTelephone">Téléphone</label>
        <input class="form-control" id="iaEditTelephone" name="telephone" type="tel" maxlength="30">
      </div>
      <div class="form-group">
        <label for="iaEditEmail">E-mail</label>
        <input class="form-control" id="iaEditEmail" name="email" type="email" maxlength="150">
      </div>
      <div class="form-group">
        <label for="iaEditStatut">Statut</label>
        <select class="form-control" id="iaEditStatut" name="statut">
          <option value="actif">Actif</option>
          <option value="inactif">Inactif</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-primary" type="submit">Enregistrer les modifications</button>
    </div>
  </form>
</x-module-indemnite>

@push('scripts')
<script>
  (function () {
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
        var status = String(value(ia, ['statut', 'status', 'est_actif', 'actif'], '')).toLowerCase();
        document.getElementById('iaEditId').value = value(ia, ['id', 'uuid'], '');
        document.getElementById('iaEditCode').value = value(ia, ['code'], '');
        document.getElementById('iaEditLibelle').value = value(ia, ['libelle', 'nom'], '');
        document.getElementById('iaEditRegion').value = value(ia, ['region.libelle', 'region.nom', 'region'], '');
        document.getElementById('iaEditResponsable').value = value(ia, ['responsable.nom_complet', 'responsable.nom', 'responsable'], '');
        document.getElementById('iaEditTelephone').value = value(ia, ['telephone', 'contact.telephone'], '');
        document.getElementById('iaEditEmail').value = value(ia, ['email', 'contact.email'], '');
        document.getElementById('iaEditStatut').value = ['1', 'true', 'actif', 'active', 'oui', 'yes'].indexOf(status) !== -1 ? 'actif' : 'inactif';
        document.getElementById('iaEditFeedback').hidden = true;
      });
    });

    document.querySelectorAll('[data-ia-toggle]').forEach(function (button) {
      button.addEventListener('click', function () {
        var row = button.closest('[data-ia-row]');
        var isActive = row.getAttribute('data-ia-active') === 'true';

        if (isActive && !window.confirm('Désactiver cette inspection d’académie ? Elle ne sera plus proposée dans les nouveaux processus. Son historique sera conservé.')) return;

        var nextActive = !isActive;
        var badge = row.querySelector('[data-ia-status]');
        var activeCount = document.getElementById('iaActiveCount');
        var inactiveCount = document.getElementById('iaInactiveCount');

        row.setAttribute('data-ia-active', nextActive ? 'true' : 'false');
        badge.textContent = nextActive ? 'Actif' : 'Inactif';
        badge.classList.toggle('badge-active', nextActive);
        badge.classList.toggle('badge-suspended', !nextActive);
        button.textContent = nextActive ? 'Désactiver' : 'Activer';
        activeCount.textContent = String(Number(activeCount.textContent) + (nextActive ? 1 : -1));
        inactiveCount.textContent = String(Number(inactiveCount.textContent) + (nextActive ? -1 : 1));
      });
    });

    document.getElementById('iaEditForm').addEventListener('submit', function (event) {
      event.preventDefault();
      if (!this.reportValidity()) return;
      document.getElementById('iaEditFeedback').hidden = false;
    });
  }());
</script>
@endpush
@endsection
