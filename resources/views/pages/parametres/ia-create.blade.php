@extends('layouts.app')

@section('title', 'SICORE - Créer une inspection d’académie')

@section('content')
<main class="main-content">
  <x-topbar title="Créer une inspection d’académie" subtitle="Ajouter une IA aux référentiels de SICORE" icon="fa-solid fa-building-columns" />

  <section class="content-area">
    <section class="form-card" aria-labelledby="iaFormTitle">
      <div class="form-card-header">
        <div>
          <h2 id="iaFormTitle">Informations de l’IA</h2>
          <p class="breadcrumb"><a href="{{ route('parametres.ia.index') }}">Inspections d’académie</a> &gt; Nouvelle IA</p>
        </div>
        <span class="badge badge-primary">IA</span>
      </div>

      <div class="alert alert-success" id="iaFormFeedback" role="status" hidden>Le formulaire est valide et prêt à être transmis.</div>
      <p class="form-required-note"><span class="required" aria-hidden="true">*</span> Champs obligatoires</p>

      <form class="teacher-form" id="iaCreateForm">
        <div class="form-section">
          <h3>Identification</h3>
          <div class="form-grid form-grid--balanced">
            <div class="form-group">
              <label for="iaCode">Code <span class="required">*</span></label>
              <input class="form-control" id="iaCode" name="code" type="text" maxlength="20" required autocomplete="off" placeholder="Ex. IA-DKR" aria-describedby="iaCodeHelp">
              <small id="iaCodeHelp">Identifiant court et unique de l’inspection.</small>
            </div>
            <div class="form-group">
              <label for="iaLibelle">Libellé <span class="required">*</span></label>
              <input class="form-control" id="iaLibelle" name="libelle" type="text" maxlength="150" required placeholder="Ex. Inspection d’académie de Dakar">
            </div>
            <div class="form-group">
              <label for="iaRegion">Région <span class="required">*</span></label>
              <select class="form-control" id="iaRegion" name="region" required>
                <option value="">Sélectionner une région</option>
                @foreach (['Dakar', 'Diourbel', 'Fatick', 'Kaffrine', 'Kaolack', 'Kédougou', 'Kolda', 'Louga', 'Matam', 'Saint-Louis', 'Sédhiou', 'Tambacounda', 'Thiès', 'Ziguinchor'] as $region)
                  <option value="{{ $region }}">{{ $region }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="iaStatut">Statut <span class="form-optional">(à confirmer métier)</span></label>
              <select class="form-control" id="iaStatut" name="statut">
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-actions">
          <a class="btn-secondary" href="{{ route('parametres.ia.index') }}">Annuler</a>
          <button class="btn-primary" type="submit">Créer l’IA</button>
        </div>
      </form>
    </section>
  </section>
</main>

@push('scripts')
<script>
  document.getElementById('iaCreateForm').addEventListener('submit', function (event) {
    event.preventDefault();
    if (!this.reportValidity()) return;
    document.getElementById('iaFormFeedback').hidden = false;
  });
</script>
@endpush
@endsection
