@php
  $isUpdate = $bag !== null;
  $oldValue = fn (string $key, mixed $default = null) => $isUpdate ? $default : old($key, $default);
@endphp
<div class="form-grid form-grid--balanced">
  <div class="form-group">
    <label for="{{ $prefix }}Code">Code <span class="required">*</span></label>
    <input class="form-control" id="{{ $prefix }}Code" name="code" value="{{ $oldValue('code') }}" maxlength="20" pattern="[A-Z0-9]+(?:[-_][A-Z0-9]+)*" placeholder="Ex. SALAIRE_BASE" required>
    @if ($bag) @error('code', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group">
    <label for="{{ $prefix }}Libelle">Libellé <span class="required">*</span></label>
    <input class="form-control" id="{{ $prefix }}Libelle" name="libelle" value="{{ $oldValue('libelle') }}" maxlength="100" placeholder="Ex. Salaire de base" required>
    @if ($bag) @error('libelle', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('libelle')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group">
    <label for="{{ $prefix }}Type">Type <span class="required">*</span></label>
    <select class="form-control" id="{{ $prefix }}Type" name="type" required><option value="gain" @selected($oldValue('type', 'gain') === 'gain')>Gain</option><option value="retenue" @selected($oldValue('type') === 'retenue')>Retenue</option></select>
    @if ($bag) @error('type', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group">
    <label for="{{ $prefix }}Periodicite">Périodicité <span class="required">*</span></label>
    <select class="form-control" id="{{ $prefix }}Periodicite" name="periodicite" required><option value="mensuelle" @selected($oldValue('periodicite', 'mensuelle') === 'mensuelle')>Mensuelle</option><option value="ponctuelle" @selected($oldValue('periodicite') === 'ponctuelle')>Ponctuelle</option><option value="annuelle" @selected($oldValue('periodicite') === 'annuelle')>Annuelle</option></select>
    @if ($bag) @error('periodicite', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('periodicite')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group">
    <label for="{{ $prefix }}Montant">Montant par défaut (FCFA)</label>
    <input class="form-control" id="{{ $prefix }}Montant" name="montant_defaut" type="number" min="0" max="9999999999999.99" step="0.01" value="{{ $oldValue('montant_defaut') }}" placeholder="0">
    @if ($bag) @error('montant_defaut', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('montant_defaut')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group">
    <label for="{{ $prefix }}Taux">Taux par défaut (%)</label>
    <input class="form-control" id="{{ $prefix }}Taux" name="taux_defaut" type="number" min="0" max="100" step="0.01" value="{{ $oldValue('taux_defaut') }}" placeholder="0">
    @if ($bag) @error('taux_defaut', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('taux_defaut')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group full">
    <label for="{{ $prefix }}Formule">Formule de calcul</label>
    <input class="form-control" id="{{ $prefix }}Formule" name="formule_calcul" value="{{ $oldValue('formule_calcul') }}" maxlength="255" placeholder="Ex. salaire_base * 0.05">
    @if ($bag) @error('formule_calcul', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('formule_calcul')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group full">
    <label for="{{ $prefix }}Description">Description</label>
    <textarea class="form-control" id="{{ $prefix }}Description" name="description" rows="3" maxlength="1000" placeholder="Précisez l’utilisation de cette rubrique…">{{ $oldValue('description') }}</textarea>
    @if ($bag) @error('description', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
  <div class="form-group full">
    <div class="rubrique-flags">
      <label class="rubrique-flag"><input type="hidden" name="est_cotisable" value="0"><input id="{{ $prefix }}Cotisable" name="est_cotisable" type="checkbox" value="1" @checked((bool) $oldValue('est_cotisable', false))><span><strong>Cotisable</strong><small>Entre dans le calcul des cotisations.</small></span></label>
      <label class="rubrique-flag"><input type="hidden" name="est_imposable" value="0"><input id="{{ $prefix }}Imposable" name="est_imposable" type="checkbox" value="1" @checked((bool) $oldValue('est_imposable', false))><span><strong>Imposable</strong><small>Entre dans la base imposable.</small></span></label>
      <label class="rubrique-flag"><input type="hidden" name="est_afficher_bulletin" value="0"><input id="{{ $prefix }}Bulletin" name="est_afficher_bulletin" type="checkbox" value="1" @checked((bool) $oldValue('est_afficher_bulletin', true))><span><strong>Afficher sur le bulletin</strong><small>Rend la rubrique visible sur le bulletin.</small></span></label>
    </div>
  </div>
  <div class="form-group full">
    <label for="{{ $prefix }}Actif">Statut <span class="required">*</span></label>
    <select class="form-control" id="{{ $prefix }}Actif" name="est_actif" required><option value="1" @selected((string) $oldValue('est_actif', '1') === '1')>Actif</option><option value="0" @selected((string) $oldValue('est_actif', '1') === '0')>Inactif</option></select>
    @if ($bag) @error('est_actif', $bag)<span class="invalid-feedback">{{ $message }}</span>@enderror @else @error('est_actif')<span class="invalid-feedback">{{ $message }}</span>@enderror @endif
  </div>
</div>
