@extends('layouts.app')

@section('title', 'SICORE - Utilisateurs')
@section('content')
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
              <option value="{{ $role['id'] }}" @selected((string) old('role_id') === (string) $role['id'])>{{ $role['nom'] }}</option>
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
      let emailTimer;
      let emailRequest;
      let emailIsChecking = false;

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
