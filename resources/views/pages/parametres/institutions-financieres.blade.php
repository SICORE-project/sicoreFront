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
      <p class="breadcrumb">Paramétrage &gt; Institutions financières</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="newInstitution" data-modal-open="institution-form-modal">+ Nouvelle institution</button>
        <button class="btn-secondary" type="button" data-modal-open="teacher-bank-account-modal">Associer à un enseignant</button>
        <button class="btn-secondary" id="exportInstitutions" type="button"><i class="fa-solid fa-file-export"></i> Exporter</button>
      </div>
    </div>

    <form class="filter-panel institution-filters" id="institutionFilterForm" method="GET" action="{{ route('parametres.institutions-financieres') }}">
      <div class="form-group"><label for="institutionSearch">Rechercher</label><input class="form-control" id="institutionSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Code, libellé ou sigle"></div>
      <div class="form-group"><label for="institutionTypeFilter">Type</label><input class="form-control" id="institutionTypeFilter" name="type_institution" value="{{ request('type_institution') }}" placeholder="Banque, microfinance..."></div>
      <div class="form-group"><label for="institutionStatusFilter">Statut</label><select class="form-control" id="institutionStatusFilter" name="est_actif"><option value="">Tous les statuts</option><option value="1" @selected(request('est_actif') === '1')>Actives</option><option value="0" @selected(request('est_actif') === '0')>Inactives</option></select></div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.institutions-financieres') }}">Réinitialiser</a></div>
    </form>

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
            <th scope="col">Nom ou libellé</th>
            <th scope="col">Téléphone</th>
            <th scope="col">Adresse</th><th scope="col">Statut</th><th scope="col" class="actions-cell">Actions</th>
          </tr></thead>
          <tbody>
            @foreach ($items as $institution)
              @php
                $status = data_get($institution, 'statut', data_get($institution, 'status', data_get($institution, 'actif', data_get($institution, 'active', data_get($institution, 'is_active', data_get($institution, 'est_actif'))))));
                $normalizedStatus = is_string($status) ? mb_strtolower(trim($status)) : $status;
                $active = in_array($normalizedStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
                $institutionId = data_get($institution, 'id', data_get($institution, 'uuid', data_get($institution, 'code')));
              @endphp
              <tr data-institution-status="{{ $active ? 'actif' : 'inactif' }}">
                <td>{{ data_get($institution, 'nom', data_get($institution, 'libelle', '—')) }}</td>
                <td>{{ data_get($institution, 'telephone', '—') }}</td>
                <td>{{ data_get($institution, 'adresse', '—') }}</td>
                <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td>
                <td class="actions-cell">
                  <button class="icon-action" type="button" title="Consulter" data-modal-open="view-institution-modal" data-institution-view='@json($institution)'><i class="fa-solid fa-eye"></i></button>
                  <button class="icon-action" type="button" title="Modifier" data-modal-open="institution-form-modal" data-update-url="{{ route('parametres.institutions-financieres.update', ['institution' => $institutionId]) }}" data-institution-edit='@json($institution)'><i class="fa-solid fa-pen-to-square"></i></button>
                  <form class="inline-form" method="POST" action="{{ route('parametres.institutions-financieres.status', ['institution' => $institutionId]) }}" onsubmit="return confirm('{{ $active ? 'Désactiver cette institution ? Elle ne sera plus proposée dans les nouveaux dossiers.' : 'Activer cette institution ? Elle pourra être sélectionnée dans les nouveaux dossiers.' }}');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="est_actif" value="{{ $active ? '0' : '1' }}">
                    <button class="icon-action" type="submit" title="{{ $active ? 'Désactiver' : 'Activer' }}"><i class="fa-solid {{ $active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                  </form>
                  <form class="inline-form" method="POST" action="{{ route('parametres.institutions-financieres.destroy', ['institution' => $institutionId]) }}" onsubmit="return confirm('Supprimer définitivement cette institution financière ?');">
                    @csrf
                    @method('DELETE')
                    <button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ data_get($institution, 'libelle') }}"><i class="fa-solid fa-trash-can"></i></button>
                  </form>
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
          <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.institutions-financieres', array_merge(request()->except('page'), ['page' => $page])) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
        @endfor
        <a class="page-btn {{ $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' }}"
           href="{{ $pagination['current_page'] < $pagination['last_page'] ? route('parametres.institutions-financieres', ['page' => $pagination['current_page'] + 1]) : '#' }}"
           aria-label="Page suivante" @if ($pagination['current_page'] >= $pagination['last_page']) aria-disabled="true" tabindex="-1" @endif>&rarr;</a>
      </nav>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="view-institution-modal" title="Détails de l’institution financière">
  <div class="institution-detail-hero">
    <span class="institution-detail-logo"><i class="fa-solid fa-building-columns"></i></span>
    <div><span class="institution-detail-kicker">Fiche institution</span><h3 data-view-field="nom">—</h3></div>
  </div>
  <div class="form-grid form-grid--balanced institution-details">
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-barcode"></i></span><div><label>Code</label><p data-view-field="code">—</p></div></div>
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-signature"></i></span><div><label>Sigle</label><p data-view-field="sigle">—</p></div></div>
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-landmark"></i></span><div><label>Type d’institution</label><p data-view-field="type">—</p></div></div>
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-circle-check"></i></span><div><label>Statut</label><p data-view-field="statut">—</p></div></div>
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-phone"></i></span><div><label>Téléphone</label><p data-view-field="telephone">—</p></div></div>
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-envelope"></i></span><div><label>E-mail</label><p data-view-field="email">—</p></div></div>
    <div class="institution-detail-item full"><span class="detail-icon"><i class="fa-solid fa-location-dot"></i></span><div><label>Adresse</label><p data-view-field="adresse">—</p></div></div>
  </div>
  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>

<x-module-indemnite type="modal" id="institution-form-modal" title="Institution financière" :open="$errors->any() || session()->has('institution_form_open')">
  <form id="institutionForm" class="teacher-form" method="POST" action="{{ route('parametres.institutions-financieres.store') }}" data-create-url="{{ route('parametres.institutions-financieres.store') }}">
    @csrf
    <input id="institutionMethod" type="hidden" name="_method" value="PUT" disabled>
    @if ($errors->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger les champs obligatoires.</strong><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <div class="form-grid form-grid--balanced">
      <div class="form-group"><label for="institutionCode">Code <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionCode" name="code" value="{{ old('code') }}" maxlength="30" required aria-required="true"></div>
      <div class="form-group"><label for="institutionSigle">Sigle <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionSigle" name="sigle" value="{{ old('sigle') }}" maxlength="30" required aria-required="true"></div>
      <div class="form-group full"><label for="institutionNom">Nom ou libellé <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionNom" name="nom" value="{{ old('nom') }}" maxlength="255" required aria-required="true"></div>
      <div class="form-group"><label for="institutionType">Type d’institution <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionType" name="type_institution" value="{{ old('type_institution') }}" maxlength="100" required aria-required="true"></div>
      <div class="form-group"><label for="institutionTelephone">Téléphone <span class="form-optional">(facultatif)</span></label><input class="form-control" id="institutionTelephone" name="telephone" type="tel" value="{{ old('telephone') }}" maxlength="30"></div>
      <div class="form-group"><label for="institutionEmail">E-mail <span class="form-optional">(facultatif)</span></label><input class="form-control" id="institutionEmail" name="email" type="email" value="{{ old('email') }}" maxlength="255"></div>
      <div class="form-group" id="institutionStatusField"><label for="institutionStatut">Statut <span class="required" aria-hidden="true">*</span></label><select class="form-control" id="institutionStatut" name="statut" required aria-required="true"><option value="actif" @selected(old('statut', 'actif') === 'actif')>Actif</option><option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option></select></div>
      <div class="form-group full"><label for="institutionAdresse">Adresse <span class="form-optional">(facultatif)</span></label><textarea class="form-control" id="institutionAdresse" name="adresse" rows="2" maxlength="500">{{ old('adresse') }}</textarea></div>
    </div>
    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-primary" type="submit">Enregistrer</button>
    </div>
  </form>
</x-module-indemnite>
<x-module-indemnite type="modal" id="teacher-bank-account-modal" title="Associer une institution à un enseignant" :open="$errors->bankAccount->any() || session()->has('bank_account_form_open')">
  <p class="breadcrumb">Créer un compte bancaire distinct rattaché à l’enseignant.</p>
  <form class="teacher-form" method="POST" action="{{ route('parametres.comptes-bancaires-enseignants.store') }}">
    @csrf    @if ($errors->bankAccount->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger le formulaire.</strong><ul>@foreach ($errors->bankAccount->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <div class="form-grid form-grid--balanced">
      <div class="form-group full">
        <label for="bankTeacher">Enseignant <span class="required">*</span></label>
        <select class="form-control" id="bankTeacher" name="enseignant_id" required>
          <option value="">Sélectionner un enseignant</option>
          @foreach ($teachers as $teacher)
            <option value="{{ data_get($teacher, 'id') }}" @selected((string) old('enseignant_id') === (string) data_get($teacher, 'id'))>{{ trim(data_get($teacher, 'prenom', '').' '.data_get($teacher, 'nom', '')) }} — {{ data_get($teacher, 'matricule', 'Sans matricule') }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group full">
        <label for="bankInstitution">Institution financière active <span class="required">*</span></label>
        <select class="form-control" id="bankInstitution" name="institut_financier_id" required>
          <option value="">Sélectionner une institution</option>
          @foreach ($availableInstitutions as $institution)
            @php
              $bankStatus = data_get($institution, 'est_actif', data_get($institution, 'is_active', data_get($institution, 'statut', data_get($institution, 'status'))));
              $bankStatus = is_string($bankStatus) ? mb_strtolower(trim($bankStatus)) : $bankStatus;
              $bankIsActive = in_array($bankStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
              $bankId = data_get($institution, 'id', data_get($institution, 'uuid'));
            @endphp
            @if ($bankIsActive && $bankId)
              <option value="{{ $bankId }}" @selected((string) old('institut_financier_id') === (string) $bankId)>{{ data_get($institution, 'nom', data_get($institution, 'libelle')) }} ({{ data_get($institution, 'sigle') }})</option>
            @endif
          @endforeach
        </select>
      </div>
      <div class="form-group"><label for="bankAccountNumber">Numéro de compte <span class="required">*</span></label><input class="form-control" id="bankAccountNumber" name="numero_compte" value="{{ old('numero_compte') }}" maxlength="100" required></div>
      <div class="form-group"><label for="bankRib">RIB <span class="required">*</span></label><input class="form-control" id="bankRib" name="rib" value="{{ old('rib') }}" maxlength="100" required></div>
      <div class="form-group"><label for="bankAccountStatus">Statut du compte <span class="required">*</span></label><select class="form-control" id="bankAccountStatus" name="est_actif" required><option value="1" @selected(old('est_actif', '1') === '1')>Actif</option><option value="0" @selected(old('est_actif') === '0')>Inactif</option></select></div>
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer l’association</button></div>
  </form>
</x-module-indemnite>
@endsection
@push('styles')
<style>
  .institution-filters { align-items: end; padding: 20px; border: 1px solid #e2e8f0; border-radius: 16px; background: linear-gradient(135deg, #fff, #f8fafc); box-shadow: 0 8px 24px rgba(15, 23, 42, .05); }
  .institution-filters .form-group:first-child { flex: 1 1 420px; }
  .institution-filters .form-group { min-width: 210px; }
  #institution-form-modal .modal-dialog,
  #view-institution-modal .modal-dialog,
  #teacher-bank-account-modal .modal-dialog { width: calc(100% - 32px); max-width: 960px; }
  .institution-detail-hero { display: flex; align-items: center; gap: 16px; margin-bottom: 22px; padding: 20px; border-radius: 16px; background: linear-gradient(135deg, #3f8f68, #66ad82); color: #fff; }
  .institution-detail-logo { display: grid; width: 58px; height: 58px; place-items: center; flex: 0 0 58px; border-radius: 16px; background: rgba(255,255,255,.16); font-size: 25px; }
  .institution-detail-kicker { display: block; margin-bottom: 3px; color: #d1fae5; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
  .institution-detail-hero h3 { margin: 0; color: #fff; font-size: 21px; }
  .institution-details { gap: 14px; }
  .institution-detail-item { display: flex; align-items: flex-start; gap: 12px; min-width: 0; padding: 15px; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 5px 16px rgba(15,23,42,.04); }
  .institution-detail-item.full { grid-column: 1 / -1; }
  .institution-detail-item .detail-icon { display: grid; width: 38px; height: 38px; place-items: center; flex: 0 0 38px; border-radius: 10px; background: #ecfdf5; color: #047857; }
  .institution-detail-item label { display: block; margin-bottom: 3px; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; }
  .institution-detail-item p { min-height: auto; margin: 0; padding: 0; border: 0; background: transparent; color: #0f172a; font-weight: 600; overflow-wrap: anywhere; }
  #institutionsTable .actions-cell { white-space: nowrap; }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('institutionsTable');
  const statusFilter = document.getElementById('institutionStatusFilter');
  const exportButton = document.getElementById('exportInstitutions');
  const filterForm = document.getElementById('institutionFilterForm');
  const searchInput = document.getElementById('institutionSearch');
  const typeInput = document.getElementById('institutionTypeFilter');
  if (!table || !statusFilter || !exportButton) return;

  let filterTimer;
  [searchInput, typeInput].forEach(function (input) {
    input.addEventListener('input', function () {
      window.clearTimeout(filterTimer);
      filterTimer = window.setTimeout(function () { filterForm.requestSubmit(); }, 400);
    });
  });

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
  const methodOverride = document.getElementById('institutionMethod');
  const statusField = document.getElementById('institutionStatusField');
  const statusSelect = document.getElementById('institutionStatut');
  const newButton = document.getElementById('newInstitution');
  const setField = (id, value) => { document.getElementById(id).value = value === '—' ? '' : value; };
  newButton.addEventListener('click', function () {
    form.dataset.mode = 'create';
    form.action = form.dataset.createUrl;
    methodOverride.disabled = true;
    statusSelect.disabled = false;
    statusSelect.required = true;
    statusField.hidden = false;
    form.reset();
    document.getElementById('institution-form-modal-title').textContent = 'Nouvelle institution financière';
  });
  document.querySelectorAll('[data-institution-edit]').forEach(function (button) {
    button.addEventListener('click', function () {
      form.dataset.mode = 'edit';
      form.action = button.dataset.updateUrl;
      methodOverride.disabled = false;
      statusSelect.disabled = false;
      statusSelect.required = true;
      statusField.hidden = false;
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



  statusFilter.addEventListener('change', function () {
    filterForm.requestSubmit();
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
