@extends('layouts.app')

@section('title', 'SICORE - Institutions financières')
@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-building-columns"></i></span>
      <div><h1>Institutions financières</h1><p>Banques et établissements financiers enregistrés dans SICORE</p></div>
    </div>
    <div class="search-wrap">
      <label class="sr-only" for="institutionSearch">Rechercher une institution financière</label>
      <input class="search-input" id="institutionSearch" type="search" placeholder="Code, nom, sigle, type..." data-table-filter="#institutionsTable">
    </div>
  </header>

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Consulter les institutions financières enregistrées dans SICORE.</li>
        <li>Identifier les banques disponibles pour les coordonnées bancaires des enseignants.</li>
        <li>Suivre clairement les institutions actives et inactives.</li>
      </ul>
    </section>

    <div class="stats-grid four">
      <article class="stat-card">
        <div><p class="stat-label">Institutions</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Toutes catégories</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Actives</p><p class="stat-value">{{ $activeCount }}</p><p class="stat-note">Disponibles</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Inactives</p><p class="stat-value">{{ $inactiveCount }}</p><p class="stat-note">À vérifier</p></div>
        <span class="stat-icon yellow"><i class="fa-solid fa-building-circle-xmark" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Types</p><p class="stat-value">{{ $typeCount }}</p><p class="stat-note">Banques et microfinance</p></div>
        <span class="stat-icon purple"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
      </article>
    </div>
    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Institutions financières</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="newInstitution" data-modal-open="institution-form-modal">+ Nouvelle institution</button>
        <input class="sr-only" id="importInstitutionsFile" type="file" accept=".csv,.xlsx,.xls">
        <label class="btn-secondary" for="importInstitutionsFile">Importer</label>
        <label class="sr-only" for="institutionStatusFilter">Filtrer par statut</label>
        <select class="form-select" id="institutionStatusFilter">
          <option value="">Tous les statuts</option>
          <option value="actif">Actifs</option>
          <option value="inactif">Inactifs</option>
        </select>
        <a class="btn-secondary" href="{{ route('parametres.institutions-financieres') }}">Actualiser</a>
        <button class="btn-secondary" id="exportInstitutions" type="button">Exporter</button>
      </div>
    </div>

    @if ($error)
      <div class="alert alert-error" role="alert">{{ $error }}</div>
    @endif

    <section class="table-card" aria-labelledby="institutionsTitle">
      <div class="table-card-header">
        <div>
          <h2 id="institutionsTitle">Liste des institutions financières</h2>
          <p class="table-card-subtitle">{{ $pagination['total'] }} institution{{ $pagination['total'] > 1 ? 's' : '' }} enregistrée{{ $pagination['total'] > 1 ? 's' : '' }}</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table" id="institutionsTable">
          <thead><tr>
            <th scope="col">Code</th><th scope="col">Nom ou libellé</th><th scope="col">Sigle</th>
            <th scope="col">Type d’institution</th><th scope="col">Téléphone</th><th scope="col">E-mail</th>
            <th scope="col">Adresse</th><th scope="col">Statut</th><th scope="col" class="actions-cell">Actions</th>
          </tr></thead>
          <tbody>
            @foreach ($items as $institution)
              @php
                $status = data_get($institution, 'statut', data_get($institution, 'status', data_get($institution, 'actif', data_get($institution, 'active', data_get($institution, 'is_active', data_get($institution, 'est_actif'))))));
                $normalizedStatus = is_string($status) ? mb_strtolower(trim($status)) : $status;
                $active = in_array($normalizedStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
              @endphp
              <tr data-institution-status="{{ $active ? 'actif' : 'inactif' }}">
                <td>{{ data_get($institution, 'code', '—') }}</td>
                <td>{{ data_get($institution, 'nom', data_get($institution, 'libelle', '—')) }}</td>
                <td>{{ data_get($institution, 'sigle', '—') }}</td>
                <td>{{ data_get($institution, 'type.nom', data_get($institution, 'type_institution', data_get($institution, 'type', '—'))) }}</td>
                <td>{{ data_get($institution, 'telephone', '—') }}</td>
                <td>{{ data_get($institution, 'email', '—') }}</td>
                <td>{{ data_get($institution, 'adresse', '—') }}</td>
                <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td>
                <td class="actions-cell">
                  <button class="table-action" type="button" title="Voir" data-modal-open="view-institution-modal" data-institution-view='@json($institution)'>Voir</button>
                  <button class="table-action" type="button" title="Modifier" data-modal-open="institution-form-modal" data-institution-edit='@json($institution)'>Modifier</button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}" role="status">Aucune institution financière trouvée.</p>

      <nav class="pagination" aria-label="Pagination">
        <a class="page-btn {{ $pagination['current_page'] <= 1 ? 'disabled' : '' }}"
           href="{{ $pagination['current_page'] > 1 ? route('parametres.institutions-financieres', ['page' => $pagination['current_page'] - 1]) : '#' }}"
           aria-label="Page précédente" @if ($pagination['current_page'] <= 1) aria-disabled="true" tabindex="-1" @endif>&larr;</a>
        @for ($page = 1; $page <= $pagination['last_page']; $page++)
          <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.institutions-financieres', ['page' => $page]) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
        @endfor
        <a class="page-btn {{ $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' }}"
           href="{{ $pagination['current_page'] < $pagination['last_page'] ? route('parametres.institutions-financieres', ['page' => $pagination['current_page'] + 1]) : '#' }}"
           aria-label="Page suivante" @if ($pagination['current_page'] >= $pagination['last_page']) aria-disabled="true" tabindex="-1" @endif>&rarr;</a>
      </nav>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="view-institution-modal" title="Détails de l’institution financière">
  <div class="form-grid form-grid--balanced institution-details">
    <div class="form-group"><label>Code</label><p data-view-field="code">—</p></div>
    <div class="form-group"><label>Sigle</label><p data-view-field="sigle">—</p></div>
    <div class="form-group full"><label>Nom ou libellé</label><p data-view-field="nom">—</p></div>
    <div class="form-group"><label>Type d’institution</label><p data-view-field="type">—</p></div>
    <div class="form-group"><label>Statut</label><p data-view-field="statut">—</p></div>
    <div class="form-group"><label>Téléphone</label><p data-view-field="telephone">—</p></div>
    <div class="form-group"><label>E-mail</label><p data-view-field="email">—</p></div>
    <div class="form-group full"><label>Adresse</label><p data-view-field="adresse">—</p></div>
  </div>
  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>

<x-module-indemnite type="modal" id="institution-form-modal" title="Institution financière" :open="$errors->any() || session()->has('error')">
  <form id="institutionForm" class="teacher-form" method="POST" action="{{ route('parametres.institutions-financieres.store') }}">
    @csrf
    @if ($errors->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger les champs obligatoires.</strong><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>
    <div class="form-grid form-grid--balanced">
      <div class="form-group"><label for="institutionCode">Code <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionCode" name="code" value="{{ old('code') }}" maxlength="30" required aria-required="true"></div>
      <div class="form-group"><label for="institutionSigle">Sigle <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionSigle" name="sigle" value="{{ old('sigle') }}" maxlength="30" required aria-required="true"></div>
      <div class="form-group full"><label for="institutionNom">Nom ou libellé <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionNom" name="nom" value="{{ old('nom') }}" maxlength="255" required aria-required="true"></div>
      <div class="form-group"><label for="institutionType">Type d’institution <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionType" name="type_institution" value="{{ old('type_institution') }}" maxlength="100" required aria-required="true"></div>
      <div class="form-group"><label for="institutionTelephone">Téléphone <span class="form-optional">(facultatif)</span></label><input class="form-control" id="institutionTelephone" name="telephone" type="tel" value="{{ old('telephone') }}" maxlength="30"></div>
      <div class="form-group"><label for="institutionEmail">E-mail <span class="form-optional">(facultatif)</span></label><input class="form-control" id="institutionEmail" name="email" type="email" value="{{ old('email') }}" maxlength="255"></div>
      <div class="form-group"><label for="institutionStatut">Statut <span class="required" aria-hidden="true">*</span></label><select class="form-control" id="institutionStatut" name="statut" required aria-required="true"><option value="actif" @selected(old('statut', 'actif') === 'actif')>Actif</option><option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option></select></div>
      <div class="form-group full"><label for="institutionAdresse">Adresse <span class="form-optional">(facultatif)</span></label><textarea class="form-control" id="institutionAdresse" name="adresse" rows="2" maxlength="500">{{ old('adresse') }}</textarea></div>
    </div>
    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-primary" type="submit">Enregistrer</button>
    </div>
  </form>
</x-module-indemnite>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('institutionsTable');
  const statusFilter = document.getElementById('institutionStatusFilter');
  const exportButton = document.getElementById('exportInstitutions');
  if (!table || !statusFilter || !exportButton) return;

  const valueOf = (institution, ...keys) => {
    for (const key of keys) {
      const value = key.split('.').reduce((item, part) => item && item[part], institution);
      if (value !== undefined && value !== null && value !== '') return value;
    }
    return '—';
  };
  const isActive = (institution) => {
    const value = valueOf(institution, 'statut', 'status', 'actif', 'active', 'is_active', 'est_actif');
    return [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'].includes(typeof value === 'string' ? value.trim().toLowerCase() : value);
  };

  document.querySelectorAll('[data-institution-view]').forEach(function (button) {
    button.addEventListener('click', function () {
      const institution = JSON.parse(button.dataset.institutionView);
      const values = {
        code: valueOf(institution, 'code'),
        sigle: valueOf(institution, 'sigle'),
        nom: valueOf(institution, 'nom', 'libelle'),
        type: valueOf(institution, 'type.nom', 'type_institution', 'type'),
        telephone: valueOf(institution, 'telephone'),
        email: valueOf(institution, 'email'),
        adresse: valueOf(institution, 'adresse'),
        statut: isActive(institution) ? 'Actif' : 'Inactif'
      };
      Object.entries(values).forEach(([field, value]) => {
        const target = document.querySelector('[data-view-field="' + field + '"]');
        if (target) target.textContent = value;
      });
    });
  });

  const form = document.getElementById('institutionForm');
  const newButton = document.getElementById('newInstitution');
  const setField = (id, value) => { document.getElementById(id).value = value === '—' ? '' : value; };
  newButton.addEventListener('click', function () {
    form.dataset.mode = 'create';
    form.reset();
    document.getElementById('institution-form-modal-title').textContent = 'Nouvelle institution financière';
  });
  document.querySelectorAll('[data-institution-edit]').forEach(function (button) {
    button.addEventListener('click', function () {
      form.dataset.mode = 'edit';
      const institution = JSON.parse(button.dataset.institutionEdit);
      document.getElementById('institution-form-modal-title').textContent = 'Modifier l’institution financière';
      setField('institutionCode', valueOf(institution, 'code'));
      setField('institutionSigle', valueOf(institution, 'sigle'));
      setField('institutionNom', valueOf(institution, 'nom', 'libelle'));
      setField('institutionType', valueOf(institution, 'type.nom', 'type_institution', 'type'));
      setField('institutionTelephone', valueOf(institution, 'telephone'));
      setField('institutionEmail', valueOf(institution, 'email'));
      setField('institutionAdresse', valueOf(institution, 'adresse'));
      setField('institutionStatut', isActive(institution) ? 'actif' : 'inactif');
    });
  });


  form.addEventListener('submit', function (event) {
    if (form.dataset.mode === 'edit') {
      event.preventDefault();
      if (typeof window.showToast === 'function') window.showToast('info', 'La mise à jour sera activée avec l’US de modification.');
    }
  });

  statusFilter.addEventListener('change', function () {
    let visible = 0;
    table.querySelectorAll('tbody tr').forEach(function (row) {
      const matches = !statusFilter.value || row.dataset.institutionStatus === statusFilter.value;
      row.classList.toggle('is-hidden', !matches);
      if (matches) visible++;
    });
    const empty = table.closest('.table-card').querySelector('.empty-message');
    if (empty) empty.classList.toggle('show', visible === 0);
  });

  exportButton.addEventListener('click', function () {
    const rows = Array.from(table.querySelectorAll('tr:not(.is-hidden)'));
    const csv = rows.map(function (row) {
      return Array.from(row.querySelectorAll('th, td')).map(function (cell) {
        return '"' + cell.textContent.trim().replace(/"/g, '""') + '"';
      }).join(';');
    }).join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' }));
    link.download = 'institutions-financieres.csv';
    link.click();
    URL.revokeObjectURL(link.href);
  });
});
</script>
@endpush