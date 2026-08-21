@if ($errors->has('api'))
  <div class="form-alert" role="alert">{{ $errors->first('api') }}</div>
@endif

<p class="breadcrumb">Les champs marqués d’un astérisque sont obligatoires.</p>

<form class="teacher-form" action="{{ route('parametres.syndicats.store') }}" method="POST" data-syndicat-form data-uniqueness-url="{{ route('parametres.syndicats.check-uniqueness') }}">
  @csrf

  <div class="form-section">
    <h3>Identification</h3>
    <div class="form-grid">
      <div class="form-group">
        <label for="code">Code <span class="required">*</span></label>
        <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" type="text" value="{{ old('code') }}" maxlength="20" placeholder="Ex. SYND-01" required autofocus data-unique-field aria-describedby="code-feedback">
        <div class="invalid-feedback" id="code-feedback" aria-live="polite">@error('code'){{ $message }}@enderror</div>
      </div>

      <div class="form-group">
        <label for="libelle">Libellé <span class="required">*</span></label>
        <input class="form-control @error('libelle') is-invalid @enderror" id="libelle" name="libelle" type="text" value="{{ old('libelle') }}" maxlength="100" placeholder="Ex. SEPT 2" required data-unique-field aria-describedby="libelle-feedback">
        <div class="invalid-feedback" id="libelle-feedback" aria-live="polite">@error('libelle'){{ $message }}@enderror</div>
      </div>
    </div>
  </div>

  <div class="form-section">
    <h3>Cotisations</h3>
    <div class="form-grid">
      <div class="form-group">
        <label for="montant_check_off">Montant check-off (FCFA)</label>
        <input class="form-control @error('montant_check_off') is-invalid @enderror" id="montant_check_off" name="montant_check_off" type="number" value="{{ old('montant_check_off') }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" placeholder="Ex. 1500.00">
        @error('montant_check_off')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label for="montant_oeuvre_sociale">Montant œuvre sociale (FCFA)</label>
        <input class="form-control @error('montant_oeuvre_sociale') is-invalid @enderror" id="montant_oeuvre_sociale" name="montant_oeuvre_sociale" type="number" value="{{ old('montant_oeuvre_sociale') }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" placeholder="Ex. 1000.00">
        @error('montant_oeuvre_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label for="est_actif">Statut <span class="required">*</span></label>
        <select class="form-control @error('est_actif') is-invalid @enderror" id="est_actif" name="est_actif" required>
          <option value="1" @selected(old('est_actif', '1') === '1')>Actif</option>
          <option value="0" @selected(old('est_actif') === '0')>Inactif</option>
        </select>
        @error('est_actif')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  <div class="form-actions">
    <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
    <button class="btn-primary" type="submit" data-submit-button>
      <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
      Enregistrer le syndicat
    </button>
  </div>
</form>
