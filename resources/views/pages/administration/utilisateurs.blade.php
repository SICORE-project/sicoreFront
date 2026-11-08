@extends('layouts.app')

@section('title', 'SICORE - Utilisateurs')
@section('content')
  
  <x-module-page slug="utilisateurs" :error="$listError ?? null" />

  <x-module-indemnite
    type="modal"
    id="create-user-modal"
    title="Créer un utilisateur"
    :open="($errors->any() || session()->has('error')) && ! session()->has('edit_user_id')"
  >
    <p class="breadcrumb">Ajouter un nouveau compte et lui attribuer un rôle.</p>

    <form class="teacher-form" id="createUserForm" method="POST" action="{{ route('utilisateurs.store') }}">
      @csrf

      <div class="form-grid form-grid--balanced">
<div class="user-form-heading"><span>1</span><h3>Identité</h3></div>
        <div class="form-group">
          <label for="nom">Nom <span class="required">*</span></label>
          <input class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" type="text" value="{{ old('nom') }}" maxlength="100" required autocomplete="family-name">
          @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="prenom">Prénom <span class="required">*</span></label>
          <input class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" maxlength="100" required autocomplete="given-name">
          @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @include('pages.administration.utilisateurs.personal-fields')
<div class="form-group full">
          <label for="email">Adresse e-mail <span class="required">*</span></label>
          <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" placeholder="nom@exemple.sn" data-check-url="{{ route('utilisateurs.check-email') }}" aria-describedby="email-error">
          <div class="invalid-feedback" id="email-error" aria-live="polite">@error('email'){{ $message }}@enderror</div>
        </div>

        <div class="user-form-heading"><span>3</span><h3>Accès et rattachement</h3></div>
<div class="form-group">
          <label for="role_id">Rôle <span class="required">*</span></label>
          <select class="form-control @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
            <option value="">Sélectionner un rôle</option>
            @forelse ($roles as $role)
              <option value="{{ $role['id'] }}" data-role-slug="{{ $role['slug'] ?? '' }}" data-structure-types='@json($role['structure_types'] ?? [])' @selected((string) old('role_id') === (string) $role['id'])>{{ $role['nom'] }}</option>
            @empty
              <option value="" disabled>Aucun rôle disponible</option>
            @endforelse
          </select>
          @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="statut">Statut <span class="required">*</span></label>
          <select class="form-control @error('statut') is-invalid @enderror" id="statut" name="statut" required>
            <option value="actif" @selected(old('statut', 'actif') === 'actif')>Actif</option>
            <option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option>
          </select>
          @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="perimetre">Périmètre <span class="required">*</span></label>
          <select class="form-control @error('perimetre') is-invalid @enderror" id="perimetre" name="perimetre" required @disabled(!old('role_id'))>
            <option value="" @selected(!old('role_id'))>Choisissez un rôle en premier</option>
            <option value="national" @selected(old('role_id') && old('perimetre', 'national') === 'national')>Central</option>
            <option value="regional" @selected(old('role_id') && old('perimetre') === 'regional')>Régional</option>
          </select>
          @error('perimetre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" id="national-structure-group" @if(!old('role_id') || old('perimetre', 'national') !== 'national') hidden @endif>
          <label for="lieu_service_id">Direction de rattachement <span class="required">*</span></label>
          <div class="user-direction-control">
          <select class="form-control @error('lieu_service_id') is-invalid @enderror" id="lieu_service_id" name="lieu_service_id">
            <option value="">Sélectionner une direction</option>
            @foreach(($organisation['national'] ?? []) as $structure)
              @if(is_array($structure) && data_get($structure, 'id'))
                <option value="{{ data_get($structure, 'id') }}" @selected((string) old('lieu_service_id') === (string) data_get($structure, 'id'))>{{ collect([data_get($structure, 'code'), data_get($structure, 'libelle', data_get($structure, 'nom'))])->filter()->join(' — ') }}</option>
              @endif
            @endforeach
          </select>
          @if (\App\Support\SicoreAccess::can(request(), 'parametrage.lieux_service.manage'))
            <button class="icon-action user-direction-add" type="button" data-modal-open="direction-create-modal" title="Ajouter une direction" aria-label="Ajouter une direction"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
          @endif
          </div>
          @error('lieu_service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" id="ia-group" hidden>
          <label for="ia_id">IA <span class="required">*</span></label>
          <select class="form-control @error('ia_id') is-invalid @enderror" id="ia_id" name="ia_id">
            <option value="">Sélectionner une IA</option>
          </select>
          @error('ia_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" id="ief-group" hidden>
          <label for="ief_id">IEF de rattachement</label>
          <select class="form-control @error('ief_id') is-invalid @enderror" id="ief_id" name="ief_id" disabled>
            <option value="">Toutes les IEF de l'IA</option>
          </select>
          @error('ief_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="password">Mot de passe <span class="required">*</span></label>
          <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmation <span class="required">*</span></label>
          <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
          <small class="form-text" id="password-confirmation-feedback" aria-live="polite"></small>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit" data-submit-button>
          <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer
        </button>
      </div>
    </form>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="direction-create-modal" title="Ajouter une direction centrale">
    <form id="direction-create-form" action="{{ route('utilisateurs.directions.store') }}" method="POST">
      @csrf
      <p>La direction sera disponible dans la liste selon le rôle choisi.</p>
      <div class="form-grid">
        <div class="form-group"><label for="direction-code">Code *</label><input class="form-control" id="direction-code" name="code" maxlength="20" pattern="[A-Z0-9_-]+" title="Lettres majuscules, chiffres, tirets ou tirets bas" required></div>
        <div class="form-group"><label for="direction-libelle">Libellé *</label><input class="form-control" id="direction-libelle" name="libelle" maxlength="100" required></div>
        <div class="form-group"><label for="direction-type">Type *</label><select class="form-control" id="direction-type" name="type" required><option value="">Sélectionner un type</option>@foreach(['CI', 'DAGE', 'DRH', 'DECPC'] as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></div>
      </div>
      <p id="direction-create-error" class="invalid-feedback" role="alert" hidden></p>
      <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer la direction</button></div>
    </form>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="view-user-modal" title="Détails de l’utilisateur">
    @include('pages.administration.utilisateurs.profile')
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="edit-user-modal" title="Modifier un utilisateur" :open="session()->has('edit_user_id')">
    <form class="teacher-form" id="edit-user-form" method="POST" action="">
      @csrf
      @method('PUT')
      <div class="form-grid form-grid--balanced">
<div class="user-form-heading"><span>1</span><h3>Identité</h3></div>
        <div class="form-group">
          <label for="edit-user-nom">Nom <span class="required">*</span></label>
          <input class="form-control" id="edit-user-nom" name="nom" type="text" maxlength="100" required>
        </div>
        <div class="form-group">
          <label for="edit-user-prenom">Prénom <span class="required">*</span></label>
          <input class="form-control" id="edit-user-prenom" name="prenom" type="text" maxlength="100" required>
        </div>
        @include('pages.administration.utilisateurs.personal-fields', ['fieldPrefix' => 'edit-user-'])
<div class="form-group full">
          <label for="edit-user-email">Adresse e-mail <span class="required">*</span></label>
          <input class="form-control" id="edit-user-email" name="email" type="email" maxlength="255" required>
        </div>
        <div class="user-form-heading"><span>3</span><h3>Accès et rattachement</h3></div>
<div class="form-group">
          <label for="edit-user-role">Rôle <span class="required">*</span></label>
          <select class="form-control" id="edit-user-role" name="role_id" required>
            @foreach ($roles as $role)
              <option value="{{ $role['id'] }}" data-role-slug="{{ $role['slug'] ?? '' }}">{{ $role['nom'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="edit-user-perimetre">Périmètre <span class="required">*</span></label>
          <select class="form-control" id="edit-user-perimetre" disabled>
            <option value="national">Central</option>
            <option value="regional">Régional</option>
          </select>
        </div>
        <div class="form-group">
          <label for="edit-user-structure" id="edit-user-structure-label">Direction de rattachement <span class="required">*</span></label>
          <select class="form-control" id="edit-user-structure" name="lieu_service_id" required>
            <option value="">Sélectionner une direction</option>
          </select>
        </div>
        <input id="edit-user-status" name="statut" type="hidden" value="actif">
      </div>
      <div class="form-actions">
        <button class="btn-secondary" type="button" id="edit-user-toggle-status"></button>
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </x-module-indemnite>

  <form id="delete-user-form" method="POST" hidden>@csrf @method('DELETE')</form>
  <dialog class="delete-confirmation" id="status-confirmation" aria-labelledby="status-confirmation-title" aria-describedby="status-confirmation-description">
    <button type="button" class="delete-confirmation-close" data-status-cancel aria-label="Fermer">&times;</button>
    <span class="delete-confirmation-icon" aria-hidden="true"><i class="fa-solid fa-power-off"></i></span>
    <h2 id="status-confirmation-title"></h2>
    <p id="status-confirmation-description"></p>
    <p class="delete-confirmation-note" id="status-confirmation-note"></p>
    <div class="delete-confirmation-actions">
      <button class="btn-secondary" type="button" data-status-cancel autofocus>Annuler</button>
      <button class="btn-primary" type="button" id="status-confirmation-accept"></button>
    </div>
  </dialog>
  <form id="toggle-user-status-form" method="POST" hidden>@csrf</form>
@endsection

@push('styles')
<style>
  #createUserForm .form-grid { align-items: start; }
  .user-direction-control { display: flex; align-items: center; gap: 8px; }
  .user-direction-control > select { flex: 1; min-width: 0; }
  .user-direction-add { flex: 0 0 42px; width: 42px; height: 42px; }
</style>
<link rel="stylesheet" href="{{ asset('assets/css/teacher-profile.css') }}?v={{ filemtime(public_path('assets/css/teacher-profile.css')) }}">
<style>#view-user-modal .modal-dialog { width: min(920px, calc(100% - 32px)); }</style>

  <style>
    .user-status-toggle { border: 0; cursor: pointer; font: inherit; font-weight: 700; }
    .user-status-toggle:hover { filter: brightness(.94); }
    .user-status-toggle:focus-visible { outline: 2px solid #176637; outline-offset: 3px; }
    #create-user-modal .modal-dialog,
    #edit-user-modal .modal-dialog {
      width: 80%;
      max-width: none;
      border-radius: 0;
      padding: 32px clamp(20px, 7vw, 120px);
    }
    #create-user-modal .teacher-form,
    #edit-user-modal .teacher-form { margin-top: 18px; }
    #create-user-modal .organisation-section { display:flex; flex-direction:column; gap:4px; margin-top:8px; padding-top:16px; border-top:1px solid var(--border); }
    #create-user-modal .organisation-section small { color:var(--text-muted); }
    #create-user-modal .form-group[hidden] { display:none !important; }
    #create-user-modal .form-group[data-organisation-visibility="hidden"] { display:none !important; }
    .users-filter-panel { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items:end; }
    .users-filter-panel .actions-group { min-width:120px; }
    .users-filter-panel .actions-group .btn-secondary { width:100%; }
    .user-icon-actions { gap: 8px; flex-wrap: nowrap; }
    .user-icon-actions .table-action { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; min-width: 34px; padding: 0; border-radius: 8px; }
    .user-icon-actions .table-action:focus-visible { outline: 2px solid #15803d; outline-offset: 2px; }
    .user-detail-cards { display: grid; gap: 8px; margin: 12px 0 16px; }
    .user-detail-card { padding: 10px 14px; border: 1px solid #e5ebe8; border-radius: 12px; background: #fff; box-shadow: 0 2px 6px #163b2a04; }
    .user-detail-card dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 20px; margin: 0; }
    .user-detail-card dl > div { min-width: 0; }
    .user-detail-card dl > div + div { border-left: 1px solid #edf1f0; padding-left: 20px; }
    .user-detail-card dt { font-size: 10px; color: #81908c; line-height: 1.5; }
    .user-detail-card dd { font-size: 12px; font-weight: 500; color: #263d34; margin: 4px 0 0; line-height: 1.5; overflow-wrap: anywhere; }
    @media (max-width: 380px) {
      .user-detail-card dl { grid-template-columns: 1fr; }
      .user-detail-card dl > div + div { border-left: 0; padding-left: 0; }
    }
    @media (max-width:1200px) {
      .users-filter-panel { grid-template-columns:repeat(2, minmax(0, 1fr)); }
      .users-filter-panel .actions-group { min-width:0; }
    }
    @media (max-width:700px) {
      .users-filter-panel { grid-template-columns:1fr; }
    }
  </style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user-direction.js') }}?v={{ filemtime(public_path('assets/js/user-direction.js')) }}"></script>
<script src="{{ asset('assets/js/user-profile.js') }}?v={{ filemtime(public_path('assets/js/user-profile.js')) }}"></script>

  <script src="{{ asset('assets/js/user-filters.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('createUserForm');
      const password = document.getElementById('password');
      const confirmation = document.getElementById('password_confirmation');
      const feedback = document.getElementById('password-confirmation-feedback');
      const email = document.getElementById('email');
      const emailError = document.getElementById('email-error');
      const hierarchy = @json($organisation['regional'] ?? []);
      const nationalStructures = @json($organisation['national'] ?? []);
      document.addEventListener('direction-created', function (event) {
        const existingIndex = nationalStructures.findIndex(item => String(item.id) === String(event.detail.id));
        if (existingIndex === -1) nationalStructures.push(event.detail);
        else nationalStructures[existingIndex] = event.detail;
        applyRoleStructureRules();
        if (Array.from(national.options).some(option => String(option.value) === String(event.detail.id))) national.value = String(event.detail.id);
        national.dispatchEvent(new Event('change', { bubbles: true }));
      });
      function compatibleNationalStructure(item, slug) {
        const option = Array.from(document.querySelectorAll('#role_id option, #edit-user-role option')).find(option => option.dataset.roleSlug === slug);
        return window.UserDirections.allowedTypes(slug, JSON.parse(option?.dataset.structureTypes || '[]')).includes(String(item.type || item.code || '').toUpperCase());
      }

      const iaOptionsUrl = @json(route('utilisateurs.ia-options'));
      const role = document.getElementById('role_id');
      const perimeter = document.getElementById('perimetre');
      const nationalGroup = document.getElementById('national-structure-group');
      const national = document.getElementById('lieu_service_id');
      const iaGroup = document.getElementById('ia-group');
      const ia = document.getElementById('ia_id');
      const iefGroup = document.getElementById('ief-group');
      const ief = document.getElementById('ief_id');
      const regionalStructure = document.createElement('input');
      regionalStructure.type = 'hidden';
      regionalStructure.name = 'lieu_service_id';
      form.append(regionalStructure);
      const oldStructure = @json((string) old('lieu_service_id', ''));
      const restoredStructure = hierarchy.find(item => String(item.id) === oldStructure);
      const oldIa = @json((string) old('ia_structure_id', ''));
      const oldIef = @json((string) old('ief_structure_id', ''));
      let emailTimer;
      let emailRequest;
      let emailIsChecking = false;
      let iaOptionsRequest;
      const coveredIefs = document.createElement('small');
      coveredIefs.className = 'form-text';
      iefGroup.append(coveredIefs);

      function optionLabel(item) {
        return [item.code, item.libelle].filter(Boolean).join(' — ');
      }

      function fillIefs(selectedValue = '') {
        ief.replaceChildren(new Option(role.selectedOptions[0]?.dataset.roleSlug === 'enseignant' ? 'Sélectionner une IEF de rattachement' : "Toutes les IEF de l'IA", ''));
        const selectedIa = hierarchy.find(item => String(item.id) === ia.value);
        hierarchy.filter(item => item.type === 'IEF' && String(item.ia_id) === String(selectedIa?.ia_id)).forEach(item => ief.add(new Option(optionLabel(item), item.id)));
        ief.disabled = !ia.value || perimeter.value !== 'regional' || role.selectedOptions[0]?.dataset.roleSlug === 'gestionnaire_ia';
        ief.value = selectedValue;
        const isIaManager = role.selectedOptions[0]?.dataset.roleSlug === 'gestionnaire_ia';
        coveredIefs.hidden = !isIaManager;
        coveredIefs.textContent = isIaManager && selectedIa
          ? (hierarchy.filter(item => item.type === 'IEF' && String(item.ia_id) === String(selectedIa.ia_id)).map(optionLabel).join(', ') || 'Aucune IEF enregistrée pour cette IA.')
          : '';
        syncRegionalStructure();
      }

      function syncRegionalStructure() {
        ia.disabled = perimeter.value !== 'regional';
        ia.name = 'ia_structure_id';
        ief.name = 'ief_structure_id';
        regionalStructure.value = perimeter.value === 'regional' ? (role.selectedOptions[0]?.dataset.roleSlug === 'gestionnaire_ia' ? ia.value : (ief.value || ia.value)) : '';
        regionalStructure.disabled = perimeter.value !== 'regional';

      }

      async function loadIaOptions() {
        if (iaOptionsRequest) return iaOptionsRequest;

        iaOptionsRequest = fetch(iaOptionsUrl, { headers: { 'Accept': 'application/json' } })
          .then(response => response.ok ? response.json() : [])
          .then(items => {
            const selectedValue = ia.value;
            ia.replaceChildren(new Option('Sélectionner une IA', ''));
            items.forEach(item => {
              if (item.lieu_service_id) {
                ia.add(new Option(optionLabel(item), item.lieu_service_id));
              }
            });
            ia.value = selectedValue || oldIa || String(hierarchy.find(item => item.type === 'IA' && String(item.ia_id) === String(restoredStructure?.ia_id))?.id || '');
            fillIefs(ief.value || oldIef || (restoredStructure?.type === 'IEF' ? oldStructure : ''));
          })
          .catch(() => {
            ia.replaceChildren(new Option('Impossible de charger les IA', ''));
          })
          .finally(() => { iaOptionsRequest = null; });

        return iaOptionsRequest;
      }

      function toggleOrganisation() {
        const regional = perimeter.value === 'regional';
        nationalGroup.hidden = regional;
        nationalGroup.dataset.organisationVisibility = regional ? 'hidden' : 'visible';
        national.disabled = regional;
        national.required = !regional;
        iaGroup.hidden = !regional;
        iefGroup.hidden = !regional;
        ia.disabled = !regional;
        ia.required = regional;

        if (regional) {
          national.value = '';
          national.name = 'national_structure_id';
          fillIefs(ief.value);
        } else {
          national.name = 'lieu_service_id';
          ia.value = '';
          fillIefs();
          regionalStructure.disabled = true;
          ia.name = 'ia_id';
          ief.name = 'ief_id';
        }
      }

    function applyRoleStructureRules() {
  const selectedRole = role.options[role.selectedIndex];
  const allowedTypes = JSON.parse(selectedRole?.dataset.structureTypes || '[]');
  const previousStructure = national.value;
  national.replaceChildren(new Option('Sélectionner une direction', ''));
  nationalStructures
    .filter(item => compatibleNationalStructure(item, selectedRole?.dataset.roleSlug))
    .forEach(item => national.add(new Option(optionLabel(item), item.id)));
  national.value = previousStructure;
  const hasSelectedRole = Boolean(role.value);
  const isGestionnaireIa = selectedRole?.dataset.roleSlug === 'gestionnaire_ia';
  const isTeacher = selectedRole?.dataset.roleSlug === 'enseignant';
  document.getElementById('matricule_enseignant').required = isTeacher;
  document.getElementById('matricule_enseignant').closest('.form-group').hidden = !isTeacher;
  const regionalOnly = isGestionnaireIa || isTeacher;
  const allowsIa = isGestionnaireIa || (!isTeacher && allowedTypes.includes('ia'));
  const allowsIef = isGestionnaireIa || isTeacher || allowedTypes.includes('ief');
  const nationalOption = perimeter.querySelector('option[value="national"]');
  const regionalOption = perimeter.querySelector('option[value="regional"]');

  // Les enseignants et gestionnaires IA relèvent du périmètre régional.
  nationalOption.disabled = hasSelectedRole && regionalOnly;
  regionalOption.disabled = hasSelectedRole && !regionalOnly && !allowsIa && !allowsIef;

  perimeter.disabled = !hasSelectedRole;

  if (!hasSelectedRole) {
    perimeter.value = '';
    [nationalGroup, iaGroup, iefGroup].forEach(group => { group.hidden = true; });
    nationalGroup.dataset.organisationVisibility = 'hidden';
    [national, ia, ief, regionalStructure].forEach(field => {
      field.value = '';
      field.disabled = true;
      field.required = false;
    });
    return;
  }

  perimeter.value = regionalOnly ? 'regional' : 'national';

  toggleOrganisation();

  if (perimeter.value === 'regional') {
    loadIaOptions();
    iefGroup.hidden = !allowsIef;
    ief.disabled = isGestionnaireIa || !allowsIef || !ia.value;
    ief.required = allowsIef && !allowsIa;
    if (!allowsIef) ief.value = '';
  } else {
    ief.required = false;
  }
}

      ia.value = oldIa;
      fillIefs(oldIef);
      perimeter.addEventListener('change', applyRoleStructureRules);
      role.addEventListener('change', applyRoleStructureRules);
      ia.addEventListener('change', () => fillIefs());
      ief.addEventListener('change', syncRegionalStructure);
      applyRoleStructureRules();

      function setEmailError(message) {
        email.setCustomValidity(message || '');
        email.classList.toggle('is-invalid', Boolean(message));
        emailError.textContent = message || '';
      }

      async function checkEmailAvailability() {
        const value = email.value.trim();

        if (!value || !email.validity.valid) return;

        if (emailRequest) emailRequest.abort();
        emailRequest = new AbortController();
        emailIsChecking = true;

        try {
          const url = new URL(email.dataset.checkUrl, window.location.origin);
          url.searchParams.set('email', value);

          const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            signal: emailRequest.signal
          });
          const result = await response.json();

          if (email.value.trim() !== value) return;

          setEmailError(result.available ? '' : (result.message || 'Cette adresse e-mail est déjà utilisée.'));
        } catch (error) {
          if (error.name !== 'AbortError') {
            setEmailError('Impossible de vérifier cette adresse pour le moment.');
          }
        } finally {
          emailIsChecking = false;
        }
      }

      email.addEventListener('input', function () {
        clearTimeout(emailTimer);
        setEmailError('');

        if (!email.value || !email.validity.valid) return;

        emailTimer = setTimeout(checkEmailAvailability, 450);
      });

      email.addEventListener('blur', function () {
        clearTimeout(emailTimer);
        checkEmailAvailability();
      });

      function checkPasswords() {
        if (!confirmation.value) {
          feedback.textContent = '';
          return;
        }

        const matches = password.value === confirmation.value;
        feedback.textContent = matches ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
        feedback.className = matches ? 'form-text text-success' : 'form-text text-danger';
      }

      password.addEventListener('input', checkPasswords);
      confirmation.addEventListener('input', checkPasswords);

      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearTimeout(emailTimer);

        if (!email.validationMessage) {
          await checkEmailAvailability();
        }

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        const button = form.querySelector('[data-submit-button]');
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enregistrement…';
        form.submit();
      });

      const viewModal = document.getElementById('view-user-modal');
      const editModal = document.getElementById('edit-user-modal');
      const editForm = document.getElementById('edit-user-form');
      const editRole = document.getElementById('edit-user-role');
      const editPerimeter = document.getElementById('edit-user-perimetre');
      const editStructure = document.getElementById('edit-user-structure');
      const editStructureLabel = document.getElementById('edit-user-structure-label');
      const editStructures = @json($structures);
      document.addEventListener('direction-created', event => editStructures.push(event.detail));
      const usersBaseUrl = @json(route('utilisateurs.index'));
      const deleteForm = document.getElementById('delete-user-form');
      const toggleForm = document.getElementById('toggle-user-status-form');
      const editStatusButton = document.getElementById('edit-user-toggle-status');
      const statusModal = document.getElementById('status-confirmation');
      const statusAccept = document.getElementById('status-confirmation-accept');
      let statusTrigger = null;
      let statusSubmitting = false;

      function confirmStatus(id, name, activate, trigger) {
        if (statusSubmitting || statusModal.open) return;
        statusTrigger = trigger;
        toggleForm.action = `${usersBaseUrl}/${id}/toggle-status`;
        document.getElementById('status-confirmation-title').textContent = activate
          ? 'Confirmer l’activation' : 'Confirmer la désactivation';
        document.getElementById('status-confirmation-description').textContent =
          `Voulez-vous ${activate ? 'activer' : 'désactiver'} « ${name} » ?`;
        document.getElementById('status-confirmation-note').textContent = activate
          ? 'Cet utilisateur pourra de nouveau se connecter.'
          : 'Cet utilisateur ne pourra plus se connecter.';
        statusAccept.textContent = activate ? 'Activer' : 'Désactiver';
        statusAccept.style.backgroundColor = activate ? '#176637' : '#b91c1c';
        statusAccept.style.borderColor = activate ? '#176637' : '#b91c1c';
        statusModal.showModal();
        statusModal.querySelector('[data-status-cancel]').focus();
      }
      statusModal.querySelectorAll('[data-status-cancel]').forEach(button => {
        button.addEventListener('click', () => { if (!statusSubmitting) statusModal.close(); });
      });
      statusModal.addEventListener('cancel', event => {
        if (statusSubmitting) event.preventDefault();
        event.stopPropagation();
      });
      statusModal.addEventListener('keydown', event => event.stopPropagation());
      statusModal.addEventListener('click', event => {
        const box = statusModal.getBoundingClientRect();
        if (!statusSubmitting && event.target === statusModal &&
          (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom)) statusModal.close();
      });
      statusModal.addEventListener('close', () => {
        if (statusTrigger?.isConnected) statusTrigger.focus();
      });
      statusAccept.addEventListener('click', () => {
        if (statusSubmitting) return;
        statusSubmitting = true;
        statusAccept.disabled = true;
        statusAccept.textContent = 'Enregistrement…';
        statusModal.setAttribute('aria-busy', 'true');
        toggleForm.submit();
      });
      window.addEventListener('pageshow', () => {
        statusSubmitting = false;
        statusAccept.disabled = false;
        statusModal.removeAttribute('aria-busy');
        if (statusModal.open) statusModal.close();
      });

      function openModal(modal) {
        modal.hidden = false;
      }

      const editIaGroup = document.createElement('div');
      editIaGroup.className = 'form-group';
      editIaGroup.innerHTML = '<label for="edit-user-ia">IA <span class="required">*</span></label><select class="form-control" id="edit-user-ia" name="ia_structure_id"><option value="">Sélectionner une IA</option></select>';
      editStructure.closest('.form-group').before(editIaGroup);
      const editIa = editIaGroup.querySelector('select');
      function fillEditIefs(selectedValue = '') {
        const selectedIa = hierarchy.find(item => String(item.id) === editIa.value);
        editStructure.replaceChildren(new Option('Sélectionner une IEF de rattachement', ''));
        hierarchy.filter(item => item.type === 'IEF' && selectedIa && String(item.ia_id) === String(selectedIa.ia_id))
          .forEach(item => editStructure.add(new Option(optionLabel(item), item.id)));
        editStructure.value = String(selectedValue);
      }
      editIa.addEventListener('change', () => fillEditIefs());

      function configureEditOrganisation(selectedValue = '') {
        const selectedRole = editRole.options[editRole.selectedIndex];
        const isGestionnaireIa = selectedRole?.dataset.roleSlug === 'gestionnaire_ia';
        const isTeacher = selectedRole?.dataset.roleSlug === 'enseignant';
  document.getElementById('edit-user-matricule_enseignant').required = isTeacher;
  document.getElementById('edit-user-matricule_enseignant').closest('.form-group').hidden = !isTeacher;
        editIaGroup.hidden = !isTeacher;
        editIaGroup.style.display = isTeacher ? '' : 'none';
        editIa.disabled = !isTeacher;
        editIa.required = isTeacher;
        if (isTeacher) {
          editPerimeter.value = 'regional';
          editStructureLabel.innerHTML = 'IEF de rattachement <span class="required">*</span>';
          editIa.replaceChildren(new Option('Sélectionner une IA', ''));
          hierarchy.filter(item => item.type === 'IA').forEach(item => editIa.add(new Option(optionLabel(item), item.id)));
          const structure = hierarchy.find(item => String(item.id) === String(selectedValue));
          editIa.value = String(hierarchy.find(item => item.type === 'IA' && String(item.ia_id) === String(structure?.ia_id))?.id || '');
          fillEditIefs(selectedValue);
          return;
        }
        const expectedType = isGestionnaireIa ? 'IA' : null;

        editPerimeter.value = isGestionnaireIa ? 'regional' : 'national';
        editStructureLabel.innerHTML = isGestionnaireIa
          ? 'IA <span class="required">*</span>'
          : 'Direction de rattachement <span class="required">*</span>';
        editStructure.replaceChildren(new Option(
          isGestionnaireIa ? 'Sélectionner une IA' : 'Sélectionner une direction',
          ''
        ));

        editStructures
          .filter(item => compatibleNationalStructure(item, selectedRole?.dataset.roleSlug))
          .filter(item => isGestionnaireIa
            ? String(item.type).toUpperCase() === expectedType
            : String(item.perimetre).toLowerCase() === 'national')
          .forEach(item => editStructure.add(new Option(optionLabel(item), item.id)));

        editStructure.value = String(selectedValue ?? '');
      }

      editRole.addEventListener('change', () => configureEditOrganisation());

      document.querySelectorAll('[data-user-action]').forEach(function (button) {
        button.addEventListener('click', function () {
          const action = button.dataset.userAction;
          const id = button.dataset.userId;
          const name = button.dataset.userName || 'cet utilisateur';

          if (action === 'delete') {
            deleteForm.action = `${usersBaseUrl}/${id}`;
            deleteForm.dataset.deleteLabel = name;
            deleteForm.requestSubmit();
            return;
          }
          if (action === 'toggle') {
            confirmStatus(id, name, button.dataset.currentStatus !== 'actif', button);
            return;
          }
          const user = JSON.parse(button.dataset.user);

          if (action === 'view') {
            window.renderUserProfile(viewModal, user);
            openModal(viewModal);
            return;
          }

          if (action === 'edit') {
            editForm.action = `${usersBaseUrl}/${user.id}`;
            document.getElementById('edit-user-nom').value = user.nom || '';
            document.getElementById('edit-user-prenom').value = user.prenom || '';
            document.getElementById('edit-user-email').value = user.email || '';
            ['telephone', 'lieu_naissance', 'adresse', 'genre'].forEach(field => {
              document.getElementById(`edit-user-${field}`).value = user[field] || (field === 'genre' ? 'non_precise' : '');
            });
            const birthDate = user.date_naiss_iso || (user.date_naiss?.includes('/') ? user.date_naiss.split('/').reverse().join('-') : user.date_naiss?.slice(0, 10)) || '';
            document.getElementById('edit-user-date_naiss').value = birthDate;

            document.getElementById('edit-user-matricule_enseignant').value = user.matricule_enseignant || '';
            editRole.value = user.role?.id || '';
            configureEditOrganisation(user.lieu_service?.id || '');
            document.getElementById('edit-user-status').value = user.statut || 'actif';
            editStatusButton.dataset.userId = user.id;
            editStatusButton.dataset.userName = user.nom_complet || user.email || 'cet utilisateur';
            editStatusButton.dataset.currentStatus = user.statut || 'actif';
            editStatusButton.textContent = user.statut === 'actif' ? 'Désactiver (suspendre)' : 'Activer';
            openModal(editModal);
          }
        });
      });

      editStatusButton.addEventListener('click', function () {
        confirmStatus(editStatusButton.dataset.userId, editStatusButton.dataset.userName,
          editStatusButton.dataset.currentStatus !== 'actif', editStatusButton);
      });

      const failedEditUserId = @json(session('edit_user_id'));
      const failedEditValues = @json(old());
      if (failedEditUserId) {
        const editButton = Array.from(document.querySelectorAll('[data-user-action="edit"]')).find(button => {
          try {
            return String(JSON.parse(button.dataset.user).id) === String(failedEditUserId);
          } catch {
            return false;
          }
        });

        editButton?.click();

        ['matricule_enseignant', 'nom', 'prenom', 'email', 'role_id', 'telephone', 'date_naiss', 'lieu_naissance', 'adresse', 'genre'].forEach(field => {
          const value = failedEditValues?.[field];
          if (value !== undefined && value !== null) {
            document.getElementById(`edit-user-${field === 'role_id' ? 'role' : field}`).value = value;
          }
        });
        configureEditOrganisation(failedEditValues?.lieu_service_id || '');
      }
    });
  </script>
@endpush
