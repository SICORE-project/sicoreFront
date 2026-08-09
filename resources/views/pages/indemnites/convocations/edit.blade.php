@extends('layouts.app')

@section('title', 'SICORE - Modifier une convocation')
@section('content')
<main class="main-content">
    <x-topbar
      title="Modifier la convocation"
      subtitle="{{ 'Indemnites > Convocations > Convocation #'.$id.' > Modifier' }}"
      icon="fa-solid fa-pen"
    />

    <section class="content-area">
      <section class="form-card">
        <div class="form-card-header">
          <div>
            <h2>Informations generales</h2>
            <p class="breadcrumb">Modifier les informations de la convocation #{{ $id }}</p>
          </div>
          <span class="badge badge-active">Envoyee</span>
        </div>

        <form data-validate-form data-success-message="Convocation mise a jour avec succes.">
          <div class="form-section">
            <div class="form-grid">
              <div class="form-group">
                <label for="date_emission">Date d'emission <span class="required">*</span></label>
                <input class="form-control" id="date_emission" name="date_emission" type="date" value="2025-07-23" required>
              </div>
              <div class="form-group">
                <label for="statut">Statut <span class="required">*</span></label>
                <select class="form-control" id="statut" name="statut" required>
                  <option value="brouillon">Brouillon</option>
                  <option value="emise">Emise</option>
                  <option value="envoyee" selected>Envoyee</option>
                  <option value="cloturee">Cloturee</option>
                </select>
              </div>
              <div class="form-group full">
                <label for="objet">Objet <span class="required">*</span></label>
                <input class="form-control" id="objet" name="objet" type="text" maxlength="255" value="Certification en Brevet de Technicien (BT) - Jury 1" required>
              </div>
              <div class="form-group">
                <label for="lieu_examen">Centre d'examen</label>
                <input class="form-control" id="lieu_examen" name="lieu_examen" type="text" maxlength="255" value="LTP FXN/THIES">
              </div>
              <div class="form-group">
                <label for="lieu_affectation">Lieu d'affectation</label>
                <input class="form-control" id="lieu_affectation" name="lieu_affectation" type="text" maxlength="255" value="Thies">
              </div>
              <div class="form-group">
                <label for="utilisateur_id">Emise par <span class="required">*</span></label>
                <select class="form-control" id="utilisateur_id" name="utilisateur_id" required>
                  <option value="1" selected>Le Directeur - DECPC</option>
                  <option value="2">Souleymane Toure - Chef de centre</option>
                  <option value="3">Admin SICORE</option>
                </select>
              </div>
              <div class="form-group checkbox-inline">
                <label for="ordre_de_mission">
                  <input id="ordre_de_mission" name="ordre_de_mission" type="checkbox" value="1" checked>
                  Ordre de mission joint
                </label>
              </div>
            </div>
          </div>

          <p class="form-status" data-form-status aria-live="polite"></p>
          <div class="form-actions">
            <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">Annuler</a>
            <button class="btn-primary" type="submit">Enregistrer les modifications</button>
          </div>
        </form>
      </section>
    </section>
  </main>
@endsection
