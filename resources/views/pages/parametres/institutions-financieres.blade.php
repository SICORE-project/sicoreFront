@extends('layouts.app')

@section('title', 'SICORE - Banques')
@section('content')
@php
    $peutSupprimer = \App\Support\SicoreAccess::can(request(), 'parametrage.institutions_financieres.manage');
@endphp
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-building-columns"></i></span>
      <div><h1>Banques</h1><p>Banques enregistrées dans SICORE</p></div>
    </div>
  </header>

  <section class="content-area">
    @if (!empty($stats))
      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Banques</p><p class="stat-value">{{ number_format((int) ($stats['total'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Toutes les banques</p></div>
          <span class="stat-icon green"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Guichets</p><p class="stat-value">{{ number_format((int) ($stats['guichets'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Agences enregistrées</p></div>
          <span class="stat-icon blue"><i class="fa-solid fa-store" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Comptes bancaires</p><p class="stat-value">{{ number_format((int) ($stats['comptes'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Comptes des enseignants</p></div>
          <span class="stat-icon purple"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Enseignants bancarisés</p><p class="stat-value">{{ number_format((int) ($stats['enseignants'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Avec au moins un compte</p></div>
          <span class="stat-icon yellow"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span>
        </article>
      </div>
    @endif
    <form class="filter-panel parametrage-filters institution-filters" id="institutionFilterForm" method="GET" action="{{ route('parametres.institutions-financieres') }}">
      <div class="form-group"><label for="institutionSearch">Rechercher</label><input class="form-control" id="institutionSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Libellé, sigle ou code banque"></div>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="newInstitution" data-modal-open="institution-form-modal">+ Nouvelle banque</button>
      </div>    </form>

    

    @if ($error)
      <div class="alert alert-error" role="alert">{{ $error }}</div>
    @endif

    <section class="table-card" aria-labelledby="institutionsTitle">
      <div class="table-card-header">
        <div>
          <h2 id="institutionsTitle">Liste des banques</h2>
          <p class="table-card-subtitle">{{ $pagination['total'] }} banque{{ $pagination['total'] > 1 ? 's' : '' }} enregistrée{{ $pagination['total'] > 1 ? 's' : '' }}</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table" id="institutionsTable">
          <thead><tr>
            @if ($peutSupprimer)<th class="checkbox-cell"><input type="checkbox" data-select-all aria-label="Tout sélectionner"></th>@endif
            <th scope="col">Sigle</th>
            <th scope="col">Libellé</th>
            <th scope="col">Code banque (Lg 5 c)</th>
            <!-- <th scope="col">Guichets</th>
            <th scope="col">Statut</th> -->
            <th scope="col" class="actions-cell">Actions</th>
          </tr></thead>
          <tbody>
            @foreach ($items as $institution)
              @php
                $status = data_get($institution, 'est_actif', data_get($institution, 'statut', data_get($institution, 'status', data_get($institution, 'actif', data_get($institution, 'is_active')))));
                $normalizedStatus = is_string($status) ? mb_strtolower(trim($status)) : $status;
                $active = in_array($normalizedStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
                $institutionId = data_get($institution, 'id', data_get($institution, 'uuid', 0));
                $guichets = data_get($institution, 'guichets', []);
              @endphp
              <tr data-institution-status="{{ $active ? 'actif' : 'inactif' }}">
                @if ($peutSupprimer)<td class="checkbox-cell"><input type="checkbox" data-row-checkbox value="{{ $institutionId }}" aria-label="Sélectionner {{ data_get($institution, 'libelle') }}"></td>@endif
                <td>{{ data_get($institution, 'sigle', '—') }}</td>
                <td>{{ data_get($institution, 'libelle', '—') }}</td>
                <td>{{ data_get($institution, 'code_banque', '—') }}</td>
                <!-- <td>{{ is_array($guichets) ? count($guichets) : 0 }}</td> -->
                <!-- <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td> -->
                <td class="actions-cell">
                  <button class="icon-action" type="button" title="Consulter" data-modal-open="view-institution-modal" data-institution-view='@json($institution)'><i class="fa-solid fa-eye"></i></button>
                  <button class="icon-action" type="button" title="Modifier" data-modal-open="institution-form-modal" data-update-url="{{ route('parametres.institutions-financieres.update', ['institution' => $institutionId]) }}" data-institution-edit='@json($institution)'><i class="fa-solid fa-pen-to-square"></i></button>

                  <form class="inline-form" method="POST" action="{{ route('parametres.institutions-financieres.destroy', ['institution' => $institutionId]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ data_get($institution, 'libelle', 'cette banque') }}"><i class="fa-solid fa-trash-can"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}" role="status"><x-table-empty-state>Aucune banque trouvée.</x-table-empty-state></p>

      @include('components.pagination', ['pagination' => $pagination])
    </section>
    @if ($peutSupprimer)
@include('pages.parametres.partials.suppression-multiple', ['cible' => 'institution financière', 'action' => route('parametres.institutions-financieres.destroy-multiple'), 'cibles' => 'institutions financières', 'feminin' => true])
@endif
  </section>
</main>

{{-- ================= MODALE DÉTAILS ================= --}}
<x-module-indemnite type="modal" id="view-institution-modal" title="Détails de la banque">
  <div class="institution-detail-hero">
    <span class="institution-detail-logo"><i class="fa-solid fa-building-columns"></i></span>
    <div><span class="institution-detail-kicker">Fiche banque</span><h3 data-view-field="libelle">—</h3></div>
  </div>
  <div class="form-grid form-grid--balanced institution-details">
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-signature"></i></span><div><label>Sigle</label><p data-view-field="sigle">—</p></div></div>
    <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-hashtag"></i></span><div><label>Code banque (Lg 5 c)</label><p data-view-field="code_banque">—</p></div></div>
    <!-- <div class="institution-detail-item"><span class="detail-icon"><i class="fa-solid fa-circle-check"></i></span><div><label>Statut</label><p data-view-field="statut">—</p></div></div> -->
  </div>

  <div class="institution-guichets-block">
    <h4><i class="fa-solid fa-door-open"></i> Guichets</h4>
    <ul class="institution-guichets-list" data-view-field="guichets">
      <li class="institution-guichets-empty">Aucun guichet</li>
    </ul>
  </div>

  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>

{{-- ================= MODALE FORMULAIRE ================= --}}
<x-module-indemnite type="modal" id="institution-form-modal" title="Banque" :open="$errors->any() || session()->has('institution_form_open')">
  <form id="institutionForm" class="teacher-form" method="POST" action="{{ route('parametres.institutions-financieres.store') }}" data-create-url="{{ route('parametres.institutions-financieres.store') }}">
    @csrf
    <input id="institutionMethod" type="hidden" name="_method" value="PUT" disabled>
    @if ($errors->any())
      <div class="alert alert-error" role="alert"><strong>Veuillez corriger les champs obligatoires.</strong><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <div class="form-grid form-grid--balanced">
      <div class="form-group"><label for="institutionSigle">Sigle <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionSigle" name="sigle" value="{{ old('sigle') }}" maxlength="30" required aria-required="true"></div>
      <div class="form-group"><label for="institutionCodeBanque">Code banque(Lg 5 c) <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionCodeBanque" name="code_banque" value="{{ old('code_banque') }}" maxlength="10" required aria-required="true"></div>
      <div class="form-group full"><label for="institutionLibelle">Libellé <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionLibelle" name="libelle" value="{{ old('libelle') }}" maxlength="255" required aria-required="true"></div>
      <!-- <div class="form-group"><label for="institutionType">Type de banque <span class="required" aria-hidden="true">*</span></label><input class="form-control" id="institutionType" name="type_institution" value="{{ old('type_institution') }}" maxlength="100" required aria-required="true"></div> -->
    </div>

    {{-- Guichets (code + libelle) --}}
    <div class="institution-guichets-block">
      <h4><i class="fa-solid fa-door-open"></i> Guichets</h4>
      <p class="form-hint">Chaque guichet a un code unique par banque et un libellé.</p>
      <div id="guichetsRows"></div>
      <button class="btn-secondary" type="button" id="addGuichetRow"><i class="fa-solid fa-plus"></i> Ajouter un guichet</button>
    </div>

    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-primary" type="submit">Enregistrer</button>
    </div>
  </form>
</x-module-indemnite>

{{-- Modale compte bancaire enseignant inchangée --}}
<x-module-indemnite type="modal" id="teacher-bank-account-modal" title="Associer une banque à un enseignant" :open="$errors->bankAccount->any() || session()->has('bank_account_form_open')">
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
        <label for="bankInstitution">Banque active <span class="required">*</span></label>
        <select class="form-control" id="bankInstitution" name="institut_financier_id" required>
          <option value="">Sélectionner une banque</option>
          @foreach ($availableInstitutions as $institution)
            @php
              $bankStatus = data_get($institution, 'est_actif', data_get($institution, 'is_active', data_get($institution, 'statut', data_get($institution, 'status'))));
              $bankStatus = is_string($bankStatus) ? mb_strtolower(trim($bankStatus)) : $bankStatus;
              $bankIsActive = in_array($bankStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
              $bankId = data_get($institution, 'id', data_get($institution, 'uuid'));
            @endphp
            @if ($bankIsActive && $bankId)
              <option value="{{ $bankId }}" @selected((string) old('institut_financier_id') === (string) $bankId)>{{ data_get($institution, 'libelle') }} ({{ data_get($institution, 'sigle') }})</option>
            @endif
          @endforeach
        </select>
      </div>
      <div class="form-group"><label for="bankAccountNumber">Numéro de compte <span class="required">*</span></label><input class="form-control" id="bankAccountNumber" name="numero_compte" value="{{ old('numero_compte') }}" maxlength="100" required></div>
      <div class="form-group"><label for="bankRib">RIB <span class="required">*</span></label><input class="form-control" id="bankRib" name="rib" value="{{ old('rib') }}" minlength="24" maxlength="24" placeholder="24 caractères" required></div>
      <div class="form-group"><label for="bankAccountStatus">Statut du compte <span class="required">*</span></label><select class="form-control" id="bankAccountStatus" name="est_actif" required><option value="1" @selected(old('est_actif', '1') === '1')>Actif</option><option value="0" @selected(old('est_actif') === '0')>Inactif</option></select></div>
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer l’association</button></div>
  </form>
</x-module-indemnite>
@endsection

@push('styles')
<style>
  .institution-filters { align-items: end; padding: 20px; border: 1px solid #e2e8f0; border-radius: 16px; background: linear-gradient(135deg, #fff, #f8fafc); box-shadow: 0 8px 24px rgba(15, 23, 42, .05); }
  .filter-panel.parametrage-filters.institution-filters { grid-template-columns: minmax(0, 1fr) auto; gap: 20px; }
  .institution-filters .form-group { min-width: 0; }
  @media (max-width: 600px) { .filter-panel.parametrage-filters.institution-filters { grid-template-columns: minmax(0, 1fr); } }
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

  .institution-guichets-block { margin-top: 22px; padding: 18px; border: 1px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; }
  .institution-guichets-block h4 { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; color: #0f172a; font-size: 15px; }
  .institution-guichets-block .form-hint { margin: 0 0 12px; color: #64748b; font-size: 13px; }
  .institution-guichets-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
  .institution-guichets-list li { display: flex; gap: 10px; padding: 8px 12px; border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; font-size: 14px; }
  .institution-guichets-list li code { font-weight: 700; color: #047857; background: #ecfdf5; padding: 2px 6px; border-radius: 6px; }
  .institution-guichets-empty { color: #94a3b8; font-style: italic; background: transparent !important; border: 0 !important; }
  .guichet-row { display: grid; grid-template-columns: 120px 1fr 40px; gap: 10px; align-items: center; margin-bottom: 8px; }
  .guichet-row .icon-action.delete { color: #dc2626; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('institutionsTable');

  const filterForm = document.getElementById('institutionFilterForm');
  const searchInput = document.getElementById('institutionSearch');
  if (!table || !searchInput) return;

  const card = table.closest('.table-card');
  const initialCard = card.innerHTML;
  const initialUrl = new URL(window.location.href);
  let pendingSearch;
  let requestId = 0;
  const cache = new Map();
  if (!initialUrl.searchParams.get('search')) cache.set('', initialCard);

  async function filterBanks() {
    const id = ++requestId;
    pendingSearch?.abort();
    const term = searchInput.value.trim();
    const url = new URL(filterForm.action);
    if (term) url.searchParams.set('search', term);
    if (!term && !initialUrl.searchParams.get('search')) {
      const page = initialUrl.searchParams.get('page');
      if (page) url.searchParams.set('page', page);
    }
    if (cache.has(term)) {
      card.innerHTML = cache.get(term);
      card.removeAttribute('aria-busy');
      history.replaceState(null, '', url);
      return;
    }
    pendingSearch = new AbortController();
    card.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(url, {
        signal: pendingSearch.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) throw new Error('Recherche indisponible');
      if (response.redirected) { window.location.assign(response.url); return; }
      const page = new DOMParser().parseFromString(await response.text(), 'text/html');
      const result = page.querySelector('#institutionsTable')?.closest('.table-card');
      if (!result || id !== requestId) return;
      cache.set(term, result.innerHTML);
      card.innerHTML = result.innerHTML;
      history.replaceState(null, '', url);
    } catch (error) {
      if (error.name !== 'AbortError' && id === requestId) {
        window.location.assign(url);
      }
    } finally {
      if (id === requestId) card.removeAttribute('aria-busy');
    }
  }
  searchInput.addEventListener('input', filterBanks);
  filterForm.addEventListener('submit', function (event) {
    event.preventDefault();
    filterBanks();
  });
  const valueOf = (institution, ...keys) => {
    for (const key of keys) {
      const value = key.split('.').reduce((item, part) => item && item[part], institution);
      if (value !== undefined && value !== null && value !== '') return value;
    }
    return '—';
  };
  // const isActive = (institution) => {
  //   const value = valueOf(institution, 'est_actif', 'statut', 'status', 'actif', 'active', 'is_active');
  //   return [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'].includes(typeof value === 'string' ? value.trim().toLowerCase() : value);
  // };

  // ---------- Modale Détails ----------
  document.addEventListener('click', function (event) {
      const button = event.target.closest('[data-institution-view]');
      if (!button) return;
      document.getElementById('view-institution-modal').hidden = false;
      const institution = JSON.parse(button.dataset.institutionView);
      const values = {
        sigle: valueOf(institution, 'sigle'),
        libelle: valueOf(institution, 'libelle'),
        code_banque: valueOf(institution, 'code_banque'),
      };
      Object.entries(values).forEach(([field, value]) => {
        const target = document.querySelector('[data-view-field="' + field + '"]');
        if (target) target.textContent = value;
      });

      // Guichets
      const list = document.querySelector('[data-view-field="guichets"]');
      list.innerHTML = '';
      const guichets = Array.isArray(institution.guichets) ? institution.guichets : [];
      if (!guichets.length) {
        list.innerHTML = '<li class="institution-guichets-empty">Aucun guichet</li>';
      } else {
        guichets.forEach(function (g) {
          const li = document.createElement('li');
          const code = document.createElement('code');
          code.textContent = g.code || '—';
          const label = document.createElement('span');
          label.textContent = g.libelle || '—';
          li.append(code, label);
          list.appendChild(li);
        });
      }
  });

  // ---------- Formulaire ----------
  const form = document.getElementById('institutionForm');
  const methodOverride = document.getElementById('institutionMethod');
  const newButton = document.getElementById('newInstitution');
  const setField = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.value = value === '—' ? '' : value;
  };

  const guichetsRows = document.getElementById('guichetsRows');
  const addGuichetRowButton = document.getElementById('addGuichetRow');
  let nextGuichetIndex = 0;

  function addGuichetRow(code = '', libelle = '') {
    const index = nextGuichetIndex++;
    const row = document.createElement('div');
    row.className = 'guichet-row';
    row.innerHTML =
      '<input class="form-control" name="guichets[' + index + '][code]" placeholder="Code" maxlength="10" required>' +
      '<input class="form-control" name="guichets[' + index + '][libelle]" placeholder="Libellé" maxlength="255" required>' +
      '<button type="button" class="icon-action delete" title="Retirer"><i class="fa-solid fa-xmark"></i></button>';
    row.querySelector('button').addEventListener('click', () => row.remove());
    row.querySelectorAll('input')[0].value = code;
    row.querySelectorAll('input')[1].value = libelle;
    guichetsRows.appendChild(row);
  }

  function resetGuichets(guichets) {
    guichetsRows.innerHTML = '';
    nextGuichetIndex = 0;
    if (Array.isArray(guichets) && guichets.length) {
      guichets.forEach(g => addGuichetRow(g.code, g.libelle));
    }
  }

  addGuichetRowButton.addEventListener('click', () => addGuichetRow());
  resetGuichets(@json(array_values(old('guichets', []))));

  newButton.addEventListener('click', function () {
    form.dataset.mode = 'create';
    form.action = form.dataset.createUrl;
    methodOverride.disabled = true;
    form.reset();
    resetGuichets([]);
    document.querySelector('#institution-form-modal-title [data-modal-title-text]').textContent = 'Nouvelle banque';
  });

  document.addEventListener('click', function (event) {
      const button = event.target.closest('[data-institution-edit]');
      if (!button) return;
      document.getElementById('institution-form-modal').hidden = false;
      form.dataset.mode = 'edit';
      form.action = button.dataset.updateUrl;
      methodOverride.disabled = false;
      const institution = JSON.parse(button.dataset.institutionEdit);
      document.querySelector('#institution-form-modal-title [data-modal-title-text]').textContent = 'Modifier la banque';
      setField('institutionSigle', valueOf(institution, 'sigle'));
      setField('institutionLibelle', valueOf(institution, 'libelle'));
      setField('institutionCodeBanque', valueOf(institution, 'code_banque'));
      // setField('institutionType', valueOf(institution, 'type.nom', 'type_institution', 'type'));
      resetGuichets(institution.guichets || []);
  });
});
</script>
@endpush
