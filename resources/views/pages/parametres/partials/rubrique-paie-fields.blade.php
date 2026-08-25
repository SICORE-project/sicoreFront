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
</div>
