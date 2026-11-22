@extends('layouts.app')
@section('title', 'SICORE - Modifier un enseignant')
@section('content')
@php
  $v = $form['values'] ?? [];
  $o = $form['options'] ?? [];
  $val = fn (string $key) => old($key, $v[$key] ?? '');
  $select = function (string $name, array $options, ?string $empty = null) use ($val) {
      $html = $empty !== null ? '<option value="">'.e($empty).'</option>' : '';
      foreach ($options as $opt) {
          $selected = (string) $val($name) === (string) $opt['value'] ? ' selected' : '';
          $data = isset($opt['ief_id']) ? ' data-ief="'.e($opt['ief_id']).'"' : '';
          $html .= '<option value="'.e($opt['value']).'"'.$data.$selected.'>'.e($opt['label']).'</option>';
      }
      return $html;
  };
@endphp
<main class="main-content">
  <x-topbar title="Modifier un enseignant" :subtitle="$scopeLabel" icon="fa-solid fa-user-pen" />
  <section class="content-area">
    <div class="actions-group">
      <a class="btn-secondary" href="{{ $form ? route('ia.teachers.show', $form['id']) : route('enseignants.index') }}">Retour au dossier</a>
    </div>

    @if ($error || ! $form)
      <p class="alert alert-warning" role="alert">{{ $error ?? 'Fiche indisponible.' }}</p>
    @else
      @if (session('error'))<div class="alert alert-error" role="alert" style="margin-top: 12px">{{ session('error') }}</div>@endif

      <form class="table-card teacher-edit-form" method="POST" action="{{ route('ia.teachers.update', $form['id']) }}">
        @csrf @method('PUT')
        <h2>{{ $form['matricule'] }} — {{ $form['name'] }}</h2>
        <p class="table-card-subtitle">La fiche est modifiée tout de suite. Elle sera prise en compte à la validation de la prochaine Édition non encore validée (Paie &gt; États de présence &gt; Édition) ; un mois déjà validé ne change plus.</p>

        <fieldset class="edition-fieldset">
          <legend>Situation familiale</legend>
          <div class="form-grid two-columns">
            <div class="form-group"><label for="ed_situation">Situation matrimoniale</label>
              <select class="form-control" id="ed_situation" name="situation_matrimoniale">{!! $select('situation_matrimoniale', $o['situations'] ?? []) !!}</select>
              @error('situation_matrimoniale')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_enfants">Nombre d’enfants</label>
              <input class="form-control" id="ed_enfants" name="nombre_enfants" type="number" min="0" value="{{ $val('nombre_enfants') }}">
              @error('nombre_enfants')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_femmes">Nombre d’épouses</label>
              <input class="form-control" id="ed_femmes" name="nombre_femmes" type="number" min="0" value="{{ $val('nombre_femmes') }}">
              @error('nombre_femmes')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_conjoint"><input id="ed_conjoint" name="conjoint_travaille" type="checkbox" value="1" @checked(old('conjoint_travaille', $v['conjoint_travaille'] ?? false))> Conjoint salarié</label></div>
          </div>
        </fieldset>

        <fieldset class="edition-fieldset">
          <legend>Dates de service et contrat</legend>
          <div class="form-grid two-columns">
            <div class="form-group"><label for="ed_prise">Prise de service</label>
              <input class="form-control" id="ed_prise" name="date_prise_service" type="date" value="{{ $val('date_prise_service') }}">
              @error('date_prise_service')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_recrutement">Recrutement</label>
              <input class="form-control" id="ed_recrutement" name="date_recrutement" type="date" value="{{ $val('date_recrutement') }}">
              @error('date_recrutement')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_fin">Fin de contrat</label>
              <input class="form-control" id="ed_fin" name="date_fin_contrat" type="date" value="{{ $val('date_fin_contrat') }}">
              @error('date_fin_contrat')<small class="field-error">{{ $message }}</small>@enderror</div>
          </div>
        </fieldset>

        <fieldset class="edition-fieldset">
          <legend>Affectation et profil salarial</legend>
          <div class="form-grid two-columns">
            <div class="form-group"><label for="ed_ief">IEF</label>
              <select class="form-control" id="ed_ief" name="ief_id">{!! $select('ief_id', $o['iefs'] ?? []) !!}</select>
              @error('ief_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_lieu">Lieu de service</label>
              <select class="form-control" id="ed_lieu" name="lieu_service_id">{!! $select('lieu_service_id', $o['lieux_service'] ?? [], '—') !!}</select>
              @error('lieu_service_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_diplome">Diplôme</label>
              <select class="form-control" id="ed_diplome" name="diplome_id">{!! $select('diplome_id', $o['diplomes'] ?? [], '—') !!}</select>
              @error('diplome_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_categorie">Catégorie</label>
              <select class="form-control" id="ed_categorie" name="categorie_id">{!! $select('categorie_id', $o['categories'] ?? [], '—') !!}</select>
              @error('categorie_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_syndicat">Syndicat</label>
              <select class="form-control" id="ed_syndicat" name="syndicat_id">{!! $select('syndicat_id', $o['syndicats'] ?? [], '—') !!}</select>
              @error('syndicat_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_mutuelle">Mutuelle</label>
              <select class="form-control" id="ed_mutuelle" name="mutuelle_id">{!! $select('mutuelle_id', $o['mutuelles'] ?? [], 'Aucune') !!}</select>
              @error('mutuelle_id')<small class="field-error">{{ $message }}</small>@enderror</div>
          </div>
        </fieldset>

        <fieldset class="edition-fieldset">
          <legend>Banque / RIB</legend>
          <div class="form-grid two-columns">
            <div class="form-group"><label for="ed_banque">Banque</label>
              <select class="form-control" id="ed_banque" name="institut_financier_id">{!! $select('institut_financier_id', $o['banques'] ?? [], '—') !!}</select>
              @error('institut_financier_id')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_code_banque">Code banque</label>
              <input class="form-control" id="ed_code_banque" name="code_banque" maxlength="5" placeholder="SN000" value="{{ $val('code_banque') }}">
              @error('code_banque')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_code_guichet">Code guichet</label>
              <input class="form-control" id="ed_code_guichet" name="code_guichet" maxlength="5" inputmode="numeric" value="{{ $val('code_guichet') }}">
              @error('code_guichet')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_numero_compte">N° de compte</label>
              <input class="form-control" id="ed_numero_compte" name="numero_compte" maxlength="12" inputmode="numeric" value="{{ $val('numero_compte') }}">
              @error('numero_compte')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="ed_cle_rib">Clé RIB</label>
              <input class="form-control" id="ed_cle_rib" name="cle_rib" maxlength="2" inputmode="numeric" value="{{ $val('cle_rib') }}">
              @error('cle_rib')<small class="field-error">{{ $message }}</small>@enderror
              @error('rib')<small class="field-error">{{ $message }}</small>@enderror</div>
          </div>
        </fieldset>

        <div class="form-actions">
          <a class="btn-secondary" href="{{ route('ia.teachers.show', $form['id']) }}">Annuler</a>
          <button class="btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer</button>
        </div>
      </form>
    @endif
  </section>
</main>
@endsection

@push('styles')
<style>
  .teacher-edit-form { padding: 18px 20px; margin-top: 16px; }
  .teacher-edit-form h2 { margin: 0 0 4px; font-size: 1.1rem; }
  .edition-fieldset { border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px 4px; margin: 14px 0 0; }
  .edition-fieldset legend { font-weight: 700; padding: 0 6px; color: #196438; }
  .teacher-edit-form .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0; }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    // Lieux de service limités à l'IEF choisie.
    var ief = document.getElementById('ed_ief');
    var lieu = document.getElementById('ed_lieu');
    if (!ief || !lieu) return;
    function filter() {
      Array.prototype.forEach.call(lieu.options, function (o) { o.hidden = !!o.dataset.ief && o.dataset.ief !== ief.value; });
      var current = lieu.options[lieu.selectedIndex];
      if (current && current.hidden) lieu.value = '';
    }
    ief.addEventListener('change', filter);
    filter();
  }());
</script>
@endpush
