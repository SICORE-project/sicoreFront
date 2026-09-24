@php
    $personalUser = $personalUser ?? [];
    $fieldPrefix = $fieldPrefix ?? '';
    $birthDate = data_get($personalUser, 'date_naiss_iso');
    if (! $birthDate && data_get($personalUser, 'date_naiss')) {
        try { $birthDate = \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $personalUser['date_naiss'])->format('Y-m-d'); } catch (\Throwable $e) { $birthDate = substr($personalUser['date_naiss'], 0, 10); }
    }
@endphp
@foreach (['date_naiss' => ['Date de naissance', 'date', null], 'lieu_naissance' => ['Lieu de naissance', 'text', 100]] as $field => [$label, $type, $max])
<div class="form-group">
    <label for="{{ $fieldPrefix.$field }}">{{ $label }} @if(in_array($field, ['date_naiss', 'telephone']))<span class="required">*</span>@endif</label>
    <input class="form-control" id="{{ $fieldPrefix.$field }}" name="{{ $field }}" type="{{ $type }}" @required(in_array($field, ['date_naiss', 'telephone'])) value="{{ old($field, $field === 'date_naiss' ? $birthDate : data_get($personalUser, $field)) }}" @if($max) maxlength="{{ $max }}" @endif @if($type === 'date') max="{{ now()->format('Y-m-d') }}" @endif>
    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@endforeach
<div class="form-group full">
    <label for="{{ $fieldPrefix }}genre">Genre <span class="required">*</span></label>
    <select class="form-control" id="{{ $fieldPrefix }}genre" name="genre" required>
        @foreach (['' => 'Sélectionner un genre', 'masculin' => 'Masculin', 'feminin' => 'Féminin'] as $value => $label)
            <option value="{{ $value }}" @selected(old('genre', data_get($personalUser, 'genre', '')) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('genre')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="user-form-heading"><span>2</span><h3>Coordonnées</h3></div>
@foreach (['telephone' => ['Téléphone', 'tel', 20], 'adresse' => ['Adresse', 'text', 255]] as $field => [$label, $type, $max])
<div class="form-group">
    <label for="{{ $fieldPrefix.$field }}">{{ $label }} @if(in_array($field, ['date_naiss', 'telephone']))<span class="required">*</span>@endif</label>
    <input class="form-control" id="{{ $fieldPrefix.$field }}" name="{{ $field }}" type="{{ $type }}" @required(in_array($field, ['date_naiss', 'telephone'])) value="{{ old($field, $field === 'date_naiss' ? $birthDate : data_get($personalUser, $field)) }}" @if($max) maxlength="{{ $max }}" @endif @if($type === 'date') max="{{ now()->format('Y-m-d') }}" @endif>
    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@endforeach
@once
@push('styles')
<style>
    .user-form-heading { grid-column: 1 / -1; display: flex; align-items: center; gap: 10px; padding: 12px 16px; margin: 8px 0 0; background: #eff7f2; border: 1px solid #dcece2; border-radius: 10px; color: #176637; }
    .user-form-heading span { display: grid; place-items: center; width: 26px; height: 26px; border-radius: 50%; background: #176637; color: white; font-size: 13px; font-weight: 700; }
    .user-form-heading h3 { margin: 0 !important; padding: 0 !important; border: 0 !important; font-size: 15px; color: inherit; }
    #create-user-modal .form-group.full, #edit-user-modal .form-group.full { grid-column: 1 / -1; }
</style>
@endpush
@endonce

<div class="form-group full">
    <label for="{{ $fieldPrefix }}matricule_enseignant">Matricule du dossier enseignant</label>
    <input class="form-control" id="{{ $fieldPrefix }}matricule_enseignant" name="matricule_enseignant" value="{{ old('matricule_enseignant', data_get($personalUser, 'matricule_enseignant')) }}" maxlength="100">
    <small>Pour un compte enseignant, indiquez le matricule exact de son dossier existant.</small>
    @error('matricule_enseignant')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>