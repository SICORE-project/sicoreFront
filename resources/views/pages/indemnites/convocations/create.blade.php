@extends('layouts.app')

@section('title', 'SICORE - Nouvelle convocation')
@section('content')
<main class="main-content">
    <x-topbar
      title="Nouvelle convocation"
      subtitle="Emission d'une convocation et selection des beneficiaires"
      icon="fa-solid fa-calendar-plus"
    />

    <section class="content-area">
      <section class="form-card wizard-card">
        <div class="form-card-header">
          <div>
            <h2>Workflow convocation</h2>
            <p class="breadcrumb">Saisie progressive de la convocation</p>
          </div>
          <span class="badge badge-primary">2 etapes</span>
        </div>

        <form class="convocation-form" data-convocation-wizard novalidate>
          <div class="wizard-progress" aria-label="Progression du formulaire">
            <button class="wizard-step" type="button" data-step-indicator="1">
              <span class="wizard-step-number">1</span>
              <span>Informations generales</span>
            </button>
            <button class="wizard-step" type="button" data-step-indicator="2">
              <span class="wizard-step-number">2</span>
              <span>Beneficiaires</span>
            </button>
          </div>

          {{-- Etape 1 : informations generales de la convocation --}}
          <section class="wizard-panel" data-wizard-panel="1">
            <div class="form-section">
              <h3>Informations generales</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="date_emission">Date d'emission <span class="required">*</span></label>
                  <input class="form-control" id="date_emission" name="date_emission" type="date" required>
                </div>
                <div class="form-group">
                  <label for="statut">Statut <span class="required">*</span></label>
                  <select class="form-control" id="statut" name="statut" required>
                    <option value="brouillon" selected>Brouillon</option>
                    <option value="emise">Emise</option>
                    <option value="envoyee">Envoyee</option>
                    <option value="cloturee">Cloturee</option>
                  </select>
                </div>
                <div class="form-group full">
                  <label for="objet">Objet <span class="required">*</span></label>
                  <input class="form-control" id="objet" name="objet" type="text" maxlength="255" placeholder="Ex : Certification en Brevet de Technicien (BT) - Jury 1" required>
                </div>
                <div class="form-group">
                  <label for="lieu_examen">Centre d'examen</label>
                  <input class="form-control" id="lieu_examen" name="lieu_examen" type="text" maxlength="255" placeholder="Ex : LTP FXN/THIES">
                </div>
                <div class="form-group">
                  <label for="lieu_affectation">Lieu d'affectation</label>
                  <input class="form-control" id="lieu_affectation" name="lieu_affectation" type="text" maxlength="255" placeholder="Ex : Thies">
                </div>
                <div class="form-group">
                  <label for="utilisateur_id">Emise par <span class="required">*</span></label>
                  <select class="form-control" id="utilisateur_id" name="utilisateur_id" required>
                    <option value="">Selectionner un utilisateur</option>
                    <option value="1">Le Directeur - DECPC</option>
                    <option value="2">Souleymane Toure - Chef de centre</option>
                    <option value="3">Admin SICORE</option>
                  </select>
                </div>
                <div class="form-group checkbox-inline">
                  <label for="ordre_de_mission">
                    <input id="ordre_de_mission" name="ordre_de_mission" type="checkbox" value="1">
                    Ordre de mission joint
                  </label>
                </div>
              </div>
            </div>
          </section>

          {{-- Etape 2 : selection des beneficiaires --}}
          <section class="wizard-panel" data-wizard-panel="2" hidden>
            <div class="form-section">
              <h3>Beneficiaires</h3>
              <p class="breadcrumb">Cochez les enseignants a convoquer et precisez leur fonction pour cette session.</p>

              <div class="form-group full">
                <label for="beneficiaireSearch">Rechercher un enseignant</label>
                <input class="form-control" id="beneficiaireSearch" type="search" placeholder="Nom, prenom ou matricule…" data-table-filter="#beneficiaireTable">
              </div>

              <div class="table-responsive">
                <table class="table" id="beneficiaireTable">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Matricule</th>
                      <th>Nom &amp; prenom</th>
                      <th>Provenance</th>
                      <th>Telephone</th>
                      <th>Fonction</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="101" data-beneficiaire-check></td>
                      <td>ENS101</td>
                      <td>GUEYE Adama</td>
                      <td>I.S.I.L</td>
                      <td>77 377 02 28</td>
                      <td><input class="form-control" type="text" name="fonction[101]" placeholder="Ex : President de jury" disabled></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="102" data-beneficiaire-check></td>
                      <td>ENS102</td>
                      <td>SARR Nfaly</td>
                      <td>LTP-FXN/THIES</td>
                      <td>77 361 13 51</td>
                      <td><input class="form-control" type="text" name="fonction[102]" placeholder="Ex : Surveillant/correcteur" disabled></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="103" data-beneficiaire-check></td>
                      <td>ENS103</td>
                      <td>SARR Papa Alioune Badara</td>
                      <td>LTID</td>
                      <td>77 168 38 99</td>
                      <td><input class="form-control" type="text" name="fonction[103]" placeholder="Ex : Surveillant/correcteur" disabled></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="104" data-beneficiaire-check></td>
                      <td>ENS104</td>
                      <td>WONE Mamadou Moustapha</td>
                      <td>LTAP/SAINT-LOUIS</td>
                      <td>77 613 89 24</td>
                      <td><input class="form-control" type="text" name="fonction[104]" placeholder="Ex : Surveillant/correcteur" disabled></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="105" data-beneficiaire-check></td>
                      <td>ENS105</td>
                      <td>MBAYE Gueye</td>
                      <td>LTAB/DIOURBEL</td>
                      <td>77 104 95 18</td>
                      <td><input class="form-control" type="text" name="fonction[105]" placeholder="Ex : Surveillant/correcteur" disabled></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="106" data-beneficiaire-check></td>
                      <td>ENS106</td>
                      <td>THIAM Assane</td>
                      <td>LTP-FXN/THIES</td>
                      <td>77 209 67 25</td>
                      <td><input class="form-control" type="text" name="fonction[106]" placeholder="Ex : Surveillant/correcteur" disabled></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" name="enseignant_ids[]" value="107" data-beneficiaire-check></td>
                      <td>ENS107</td>
                      <td>FAYE El Hadji</td>
                      <td>LTP-FXN/THIES</td>
                      <td>77 209 67 26</td>
                      <td><input class="form-control" type="text" name="fonction[107]" placeholder="Ex : Surveillant/correcteur" disabled></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p class="breadcrumb" data-beneficiaire-count>0 beneficiaire(s) selectionne(s)</p>
            </div>
          </section>

          <p class="form-status" data-form-status aria-live="polite"></p>
          <div class="form-actions">
            <a class="btn-secondary" href="{{ route('indemnites.convocations') }}" data-wizard-cancel>Annuler</a>
            <button class="btn-secondary" type="button" data-wizard-prev hidden>Precedent</button>
            <button class="btn-primary" type="button" data-wizard-next>Suivant</button>
            <button class="btn-primary" type="submit" data-wizard-submit hidden>Enregistrer la convocation</button>
          </div>
        </form>
      </section>
    </section>
  </main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/convocation-wizard.js') }}" defer></script>
@endpush
