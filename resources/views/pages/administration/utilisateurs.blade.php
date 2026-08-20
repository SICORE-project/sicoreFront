@extends('layouts.app')

@section('title', 'SICORE - Utilisateurs')
@section('content')
  @if ($usersError)
    <div class="alert alert-danger" role="alert">{{ $usersError }}</div>
  @endif
  <x-module-page slug="utilisateurs" />

  <x-module-indemnite
    type="modal"
    id="create-user-modal"
    title="Créer un utilisateur"
    :open="$errors->any() || session()->has('error')"
  >
    <p class="breadcrumb">Ajouter un nouveau compte et lui attribuer un rôle.</p>

    <form class="teacher-form" id="createUserForm" method="POST" action="{{ route('utilisateurs.store') }}">
      @csrf

      <div class="form-grid form-grid--balanced">
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

        <div class="form-group full">
          <label for="email">Adresse e-mail <span class="required">*</span></label>
          <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" placeholder="nom@exemple.sn" data-check-url="{{ route('utilisateurs.check-email') }}" aria-describedby="email-error">
          <div class="invalid-feedback" id="email-error" aria-live="polite">@error('email'){{ $message }}@enderror</div>
        </div>

        <div class="form-group">
          <label for="role_id">Rôle <span class="required">*</span></label>
          <select class="form-control @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
            <option value="">Sélectionner un rôle</option>
            @forelse ($roles as $role)
              <option value="{{ $role['id'] }}" data-structure-types='@json($role['structure_types'] ?? [])' @selected((string) old('role_id') === (string) $role['id'])>{{ $role['nom'] }}</option>
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

        <div class="form-group full organisation-section">
          <strong>Accès organisationnel</strong>
          <small>Choisissez une structure nationale ou une IA et son IEF.</small>
        </div>

        <div class="form-group">
          <label for="perimetre">Périmètre <span class="required">*</span></label>
          <select class="form-control @error('perimetre') is-invalid @enderror" id="perimetre" name="perimetre" required>
            <option value="national" @selected(old('perimetre', 'national') === 'national')>National</option>
            <option value="regional" @selected(old('perimetre') === 'regional')>Régional</option>
          </select>
          @error('perimetre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" id="national-structure-group">
          <label for="structure_organisationnelle_id">Structure nationale <span class="required">*</span></label>
          <select class="form-control @error('structure_organisationnelle_id') is-invalid @enderror" id="structure_organisationnelle_id" name="structure_organisationnelle_id">
            <option value="">Sélectionner DRH, DAGE ou DECPC</option>
            @foreach(($organisation['national'] ?? []) as $structure)
              @if(is_array($structure) && data_get($structure, 'id') && in_array(strtoupper((string) data_get($structure, 'type', data_get($structure, 'code'))), ['DRH', 'DAGE', 'DECPC'], true))
                <option value="{{ data_get($structure, 'id') }}" @selected((string) old('structure_organisationnelle_id') === (string) data_get($structure, 'id'))>{{ collect([data_get($structure, 'code'), data_get($structure, 'libelle', data_get($structure, 'nom'))])->filter()->join(' — ') }}</option>
              @endif
            @endforeach
          </select>
          @error('structure_organisationnelle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" id="ia-group" hidden>
          <label for="ia_id">IA <span class="required">*</span></label>
          <select class="form-control @error('ia_id') is-invalid @enderror" id="ia_id" name="ia_id">
            <option value="">Sélectionner une IA</option>
          </select>
          @error('ia_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" id="ief-group" hidden>
          <label for="ief_id">IEF</label>
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
@endsection

@push('styles')
  <style>
    #create-user-modal .modal-dialog { max-width: 760px; }
    #create-user-modal .teacher-form { margin-top: 18px; }
    #create-user-modal .organisation-section { display:flex; flex-direction:column; gap:4px; margin-top:8px; padding-top:16px; border-top:1px solid var(--border); }
    #create-user-modal .organisation-section small { color:var(--text-muted); }
    #create-user-modal .form-group[hidden] { display:none !important; }
    .users-filter-panel { grid-template-columns: repeat(3, minmax(0, 1fr)) auto; align-items:end; }
    .users-filter-panel .actions-group { min-width:120px; }
    .users-filter-panel .actions-group .btn-secondary { width:100%; }
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('createUserForm');
      const password = document.getElementById('password');
      const confirmation = document.getElementById('password_confirmation');
      const feedback = document.getElementById('password-confirmation-feedback');
      const email = document.getElementById('email');
      const emailError = document.getElementById('email-error');
      const hierarchy = @json($organisation['regional'] ?? []);
      const role = document.getElementById('role_id');
      const perimeter = document.getElementById('perimetre');
      const nationalGroup = document.getElementById('national-structure-group');
      const national = document.getElementById('structure_organisationnelle_id');
      const iaGroup = document.getElementById('ia-group');
      const ia = document.getElementById('ia_id');
      const iefGroup = document.getElementById('ief-group');
      const ief = document.getElementById('ief_id');
      const oldIa = @json((string) old('ia_id', ''));
      const oldIef = @json((string) old('ief_id', ''));
      let emailTimer;
      let emailRequest;
      let emailIsChecking = false;

      function optionLabel(item) {
        return [item.code, item.libelle].filter(Boolean).join(' — ');
      }

      function fillIefs(selectedValue = '') {
        ief.replaceChildren(new Option("Toutes les IEF de l'IA", ''));
        const selectedIa = hierarchy.find(item => String(item.id) === ia.value);
        (selectedIa?.iefs ?? []).forEach(item => ief.add(new Option(optionLabel(item), item.id)));
        ief.disabled = !ia.value || perimeter.value !== 'regional';
        ief.value = selectedValue;
      }

      hierarchy
        .filter(item => item.type_perimetre === 'regional')
        .forEach(item => ia.add(new Option(optionLabel(item), item.id)));

      function toggleOrganisation() {
        const regional = perimeter.value === 'regional';
        nationalGroup.hidden = regional;
        national.disabled = regional;
        national.required = !regional;
        iaGroup.hidden = !regional;
        iefGroup.hidden = !regional;
        ia.disabled = !regional;
        ia.required = regional;

        if (regional) {
          national.value = '';
          fillIefs(ief.value);
        } else {
          ia.value = '';
          fillIefs();
        }
      }

      function applyRoleStructureRules() {
        const selectedRole = role.options[role.selectedIndex];
        const allowedTypes = JSON.parse(selectedRole?.dataset.structureTypes || '[]');
        const hasSelectedRole = Boolean(role.value);
        const allowsNational = allowedTypes.includes('national');
        const allowsIa = allowedTypes.includes('ia');
        const allowsIef = allowedTypes.includes('ief');
        const nationalOption = perimeter.querySelector('option[value="national"]');
        const regionalOption = perimeter.querySelector('option[value="regional"]');

        nationalOption.disabled = hasSelectedRole && !allowsNational;
        regionalOption.disabled = hasSelectedRole && !allowsIa && !allowsIef;

        if (!hasSelectedRole) {
          perimeter.disabled = false;
          if (!perimeter.value) perimeter.value = 'national';
        } else if (!allowedTypes.length) {
          perimeter.value = '';
          perimeter.disabled = true;
        } else {
          perimeter.disabled = false;
          if (perimeter.value === 'national' && !allowsNational) perimeter.value = 'regional';
          if (perimeter.value === 'regional' && !allowsIa && !allowsIef) perimeter.value = 'national';
        }

        toggleOrganisation();

        if (perimeter.value === 'regional') {
          iefGroup.hidden = !allowsIef;
          ief.disabled = !allowsIef || !ia.value;
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
    });
  </script>
@endpush
