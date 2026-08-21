@extends('layouts.app')

@section('title', 'SICORE - Ventilations de la délégation')
@section('content')
<main class="main-content">
  <x-topbar
    title="Ventilations de la délégation"
    subtitle="Gestion de la paie > Délégation de crédit > Ventilations"
    icon="fa-solid fa-layer-group"
    search-id="ventilationSearch"
    search-placeholder="Rechercher un carton…"
    filter-target="#ventilationTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Ventiler une délégation de crédit par corps d'enseignant, IA et IEF.</li>
        <li>Rattacher chaque ligne à son imputation budgétaire (centre d'exécution, budget, activité).</li>
        <li>Suivre le montant engagé carton par carton.</li>
      </ul>
    </section>

    <div class="stats-grid four">
      <article class="stat-card">
        <div>
          <p class="stat-label">Lignes de ventilation</p>
          <p class="stat-value" id="statLignes">0</p>
          <p class="stat-note">Délégation courante</p>
        </div>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total ventilé</p>
          <p class="stat-value" id="statVentile">0</p>
          <p class="stat-note">FCFA</p>
        </div>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total engagé</p>
          <p class="stat-value" id="statEngage">0</p>
          <p class="stat-note">FCFA</p>
        </div>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Reste à engager</p>
          <p class="stat-value" id="statReste">0</p>
          <p class="stat-note">FCFA</p>
        </div>
      </article>
    </div>

    <p class="breadcrumb">Gestion de la paie &gt; Délégation de crédit &gt; Ventilations</p>

    {{-- Selection de la delegation : equivalent du bouton "Ventilations" de FINPRONET --}}
    <section class="filter-panel" aria-label="Délégation">
      <div class="form-group" style="min-width:320px;">
        <label for="selectDelegation">Délégation de crédit</label>
        <select class="form-control" id="selectDelegation">
          <option value="">Sélectionner une délégation…</option>
        </select>
      </div>
      <div class="form-group">
        <label for="infoAnnee">Année académique</label>
        <input class="form-control" id="infoAnnee" type="text" readonly>
      </div>
      <div class="form-group">
        <label for="infoPeriode">Période</label>
        <input class="form-control" id="infoPeriode" type="text" readonly>
      </div>
      <div class="form-group">
        <label for="infoObjet">Objet</label>
        <input class="form-control" id="infoObjet" type="text" readonly>
      </div>
    </section>

    {{-- Filtres de la grille --}}
    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filterType">Type d'édition</label>
        <select class="form-control" id="filterType">
          <option value="">Tous</option>
          <option value="salaire">État sur salaire</option>
          <option value="prime_scolaire">État sur prime scolaire</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterCorps">Corps d'enseignant</label>
        <select class="form-control" id="filterCorps"><option value="">Tous</option></select>
      </div>
      <div class="form-group">
        <label for="filterIa">IA</label>
        <select class="form-control" id="filterIa"><option value="">Toutes</option></select>
      </div>
      <div class="form-group">
        <label for="filterIef">IEF</label>
        <select class="form-control" id="filterIef"><option value="">Toutes</option></select>
      </div>
      <div class="form-group">
        <label for="filterCarton">N° Carton</label>
        <input class="form-control" id="filterCarton" type="text" placeholder="25-DC…">
      </div>
      <div class="actions-group">
        <button class="btn-secondary" type="button" id="btnFiltrer">Filtrer</button>
        <button class="btn-secondary" type="button" id="btnReinitialiser">Réinitialiser</button>
      </div>
    </section>

    {{-- Formulaire de detail : reprend frmDetailDelegation.aspx --}}
    <section class="table-card" id="panelDetail" style="display:none;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">
          <i class="fa-solid fa-file-invoice"></i> Détail de la ventilation
        </h2>
        <span id="modeLabel" style="font-weight:600; color:#c92a2a;">Vous êtes en mode Consultation…</span>
      </div>

      <form id="formVentilation">
        <input type="hidden" id="ventilationId">

        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr); gap:16px;">
          <div class="form-group">
            <label for="corps_enseignant_id">Corps d'enseignant</label>
            <select class="form-control" id="corps_enseignant_id" disabled></select>
          </div>
          <div class="form-group">
            <label for="ia_id">IA</label>
            <select class="form-control" id="ia_id" disabled></select>
          </div>
          <div class="form-group">
            <label for="ief_id">IEF</label>
            <select class="form-control" id="ief_id" disabled></select>
          </div>

          <div class="form-group">
            <label for="centre_execution_id">Centre d'exécution</label>
            <select class="form-control" id="centre_execution_id" disabled></select>
          </div>
          <div class="form-group">
            <label for="budget_id">Budget</label>
            <select class="form-control" id="budget_id" disabled></select>
          </div>
          <div class="form-group">
            <label for="activite_id">Activité</label>
            <select class="form-control" id="activite_id" disabled></select>
          </div>

          <div class="form-group">
            <label for="imputation_budgetaire">Imputation budgétaire</label>
            <input class="form-control" id="imputation_budgetaire" type="text" maxlength="50" disabled>
          </div>
          <div class="form-group">
            <label for="numero_autorisation">N° Autorisation</label>
            <input class="form-control" id="numero_autorisation" type="text" maxlength="50" disabled>
          </div>
          <div class="form-group">
            <label for="numero_carton">N° Carton</label>
            <input class="form-control" id="numero_carton" type="text" maxlength="50" disabled>
          </div>

          <div class="form-group">
            <label for="montant">Montant (FCFA) *</label>
            <input class="form-control" id="montant" type="number" min="0" step="1" disabled>
          </div>
          <div class="form-group">
            <label for="montant_engagement">Engagement (FCFA)</label>
            <input class="form-control" id="montant_engagement" type="number" min="0" step="1" disabled>
          </div>
          <div class="form-group">
            <label>Type d'édition</label>
            <div style="display:flex; gap:20px; padding-top:8px;">
              <label style="font-weight:400;">
                <input type="radio" name="type" value="salaire" checked disabled> État sur salaire
              </label>
              <label style="font-weight:400;">
                <input type="radio" name="type" value="prime_scolaire" disabled> État sur prime scolaire
              </label>
            </div>
          </div>
        </div>

        <p class="empty-message" id="formError" style="display:none; color:#c92a2a;"></p>

        <div class="actions-group" style="margin-top:16px;">
          <button class="btn-primary"   type="button" id="btnAjouter">Ajouter</button>
          <button class="btn-secondary" type="button" id="btnModifier" disabled>Modifier</button>
          <button class="btn-secondary" type="button" id="btnSupprimer" disabled>Supprimer</button>
          <button class="btn-primary"   type="submit" id="btnEnregistrer" disabled>Enregistrer</button>
          <button class="btn-secondary" type="button" id="btnAnnuler" disabled>Annuler</button>
        </div>
      </form>
    </section>

    {{-- Grille des cartons --}}
    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="ventilationTable">
          <thead>
            <tr>
              <th>N° Carton</th>
              <th>Corps</th>
              <th>IA</th>
              <th>Imputation budgétaire</th>
              <th style="text-align:right;">Montant</th>
              <th style="text-align:right;">Engagement</th>
              <th style="text-align:right;">Reste</th>
              <th>N° Autorisation</th>
              <th>Type</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody id="ventilationBody"></tbody>
          <tfoot>
            <tr style="font-weight:700; background:#f1f3f5;">
              <td colspan="4">Total</td>
              <td style="text-align:right;" id="totalMontant">0</td>
              <td style="text-align:right;" id="totalEngagement">0</td>
              <td style="text-align:right;" id="totalReste">0</td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg">Sélectionnez une délégation pour afficher ses ventilations.</p>
      <div class="pagination" id="paginationControls"></div>
    </section>
  </section>
</main>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';

var nomenclature = null;
var allDelegations = [];
var allVentilations = [];
var filteredVentilations = [];
var currentPage = 1;
var perPage = 10;
var mode = 'consultation'; // consultation | ajout | modification
var selectedId = null;

document.addEventListener('DOMContentLoaded', function () {
  Promise.all([loadNomenclature(), loadDelegations()]).then(setupEvents);
});

/* ---------------------------------------------------------------- chargement */

function loadNomenclature() {
  return fetch(API + '/ventilations/nomenclature')
    .then(function (res) { return res.json(); })
    .then(function (data) {
      nomenclature = data;
      remplirSelect('corps_enseignant_id', data.corps_enseignants, 'libelle');
      remplirSelect('ia_id', data.ias, 'libelle', 'code');
      remplirSelect('ief_id', data.iefs, 'libelle', 'code');
      remplirSelect('centre_execution_id', data.centres_execution, 'libelle', 'code');
      remplirSelect('budget_id', data.budgets, 'libelle', 'code');
      remplirSelect('activite_id', data.activites, 'libelle', 'code');
      remplirSelect('filterCorps', data.corps_enseignants, 'libelle');
      remplirSelect('filterIa', data.ias, 'libelle', 'code');
      remplirSelect('filterIef', data.iefs, 'libelle', 'code');
    })
    .catch(function (e) { erreurReseau('nomenclature', e); });
}

function loadDelegations() {
  return fetch(API + '/delegation-credits')
    .then(function (res) { return res.json(); })
    .then(function (response) {
      allDelegations = response.data || response;
      var select = document.getElementById('selectDelegation');
      allDelegations.forEach(function (d) {
        select.innerHTML += '<option value="' + d.id + '">' + d.reference + ' — ' + d.objet + '</option>';
      });

      // Ouverture depuis le bouton "Ventilations" de l'ecran Delegation de credit
      var demandee = new URLSearchParams(window.location.search).get('delegation');
      if (demandee && select.querySelector('option[value="' + demandee + '"]')) {
        select.value = demandee;
        majInfosDelegation();
        return loadVentilations();
      }
    })
    .catch(function (e) { erreurReseau('délégations', e); });
}

function loadVentilations() {
  var delegationId = document.getElementById('selectDelegation').value;
  if (!delegationId) {
    allVentilations = [];
    renderTable([]);
    document.getElementById('panelDetail').style.display = 'none';
    document.getElementById('emptyMsg').textContent = 'Sélectionnez une délégation pour afficher ses ventilations.';
    document.getElementById('emptyMsg').style.display = 'block';
    majStats({ montant: 0, montant_engagement: 0, nombre_lignes: 0 });
    return Promise.resolve();
  }

  document.getElementById('panelDetail').style.display = 'block';

  var params = [];
  ajouterParam(params, 'type', 'filterType');
  ajouterParam(params, 'corps_enseignant_id', 'filterCorps');
  ajouterParam(params, 'ia_id', 'filterIa');
  ajouterParam(params, 'ief_id', 'filterIef');
  ajouterParam(params, 'numero_carton', 'filterCarton');
  var query = params.length ? '?' + params.join('&') : '';

  return fetch(API + '/delegation-credits/' + delegationId + '/ventilations' + query)
    .then(function (res) { return res.json(); })
    .then(function (response) {
      allVentilations = response.data || [];
      currentPage = 1;
      renderTable(allVentilations);
      majStats(response.totaux || { montant: 0, montant_engagement: 0, nombre_lignes: 0 });
    })
    .catch(function (e) { erreurReseau('ventilations', e); });
}

/* ---------------------------------------------------------------- rendu */

function renderTable(data) {
  filteredVentilations = data;
  var tbody = document.getElementById('ventilationBody');
  var empty = document.getElementById('emptyMsg');
  tbody.innerHTML = '';

  if (!data.length) {
    empty.textContent = 'Aucune ventilation pour cette délégation.';
    empty.style.display = 'block';
    document.getElementById('paginationControls').innerHTML = '';
    majTotaux(data);
    return;
  }

  empty.style.display = 'none';

  var totalPages = Math.ceil(data.length / perPage);
  if (currentPage > totalPages) currentPage = totalPages || 1;
  var debut = (currentPage - 1) * perPage;

  data.slice(debut, debut + perPage).forEach(function (v) {
    var reste = Number(v.montant) - Number(v.montant_engagement);
    var tr = document.createElement('tr');
    if (v.id === selectedId) tr.style.background = '#e6fcf5';
    tr.innerHTML =
      '<td><strong>' + (v.numero_carton || '-') + '</strong></td>' +
      '<td>' + (v.corps_enseignant ? v.corps_enseignant.libelle : '-') + '</td>' +
      '<td>' + (v.ia ? v.ia.libelle : '-') + '</td>' +
      '<td style="font-family:monospace; font-size:0.85rem;">' + (v.imputation_budgetaire || '-') + '</td>' +
      '<td style="text-align:right;">' + formatMontant(v.montant) + '</td>' +
      '<td style="text-align:right;">' + formatMontant(v.montant_engagement) + '</td>' +
      '<td style="text-align:right;' + (reste < 0 ? ' color:#c92a2a;' : '') + '">' + formatMontant(reste) + '</td>' +
      '<td>' + (v.numero_autorisation || '-') + '</td>' +
      '<td><span class="badge ' + (v.type === 'salaire' ? 'badge-active' : 'badge-pending') + '">' +
        (v.type === 'salaire' ? 'Salaire' : 'Prime scolaire') + '</span></td>' +
      '<td class="actions-cell"><div class="table-actions-inline">' +
        '<button class="table-action" type="button" onclick="selectionner(' + v.id + ')">Voir</button>' +
      '</div></td>';
    tbody.appendChild(tr);
  });

  renderPagination(totalPages);
  majTotaux(data);
}

function majTotaux(data) {
  var m = data.reduce(function (s, v) { return s + Number(v.montant); }, 0);
  var e = data.reduce(function (s, v) { return s + Number(v.montant_engagement); }, 0);
  document.getElementById('totalMontant').textContent = formatMontant(m);
  document.getElementById('totalEngagement').textContent = formatMontant(e);
  document.getElementById('totalReste').textContent = formatMontant(m - e);
}

function majStats(totaux) {
  document.getElementById('statLignes').textContent = totaux.nombre_lignes || 0;
  document.getElementById('statVentile').textContent = formatCourt(totaux.montant);
  document.getElementById('statEngage').textContent = formatCourt(totaux.montant_engagement);
  document.getElementById('statReste').textContent = formatCourt((totaux.montant || 0) - (totaux.montant_engagement || 0));
}

function renderPagination(totalPages) {
  var box = document.getElementById('paginationControls');
  box.innerHTML = '';
  if (totalPages <= 1) return;
  for (var i = 1; i <= totalPages; i++) {
    box.innerHTML += '<button type="button" class="' + (i === currentPage ? 'btn-primary' : 'btn-secondary') +
      '" onclick="allerPage(' + i + ')">' + i + '</button> ';
  }
}

function allerPage(page) {
  currentPage = page;
  renderTable(filteredVentilations);
}

/* ---------------------------------------------------------------- modes */

function setMode(nouveauMode) {
  mode = nouveauMode;

  var editable = (mode === 'ajout' || mode === 'modification');
  champs().forEach(function (id) { document.getElementById(id).disabled = !editable; });
  radios().forEach(function (r) { r.disabled = !editable; });

  document.getElementById('btnAjouter').disabled = editable;
  document.getElementById('btnModifier').disabled = editable || !selectedId;
  document.getElementById('btnSupprimer').disabled = editable || !selectedId;
  document.getElementById('btnEnregistrer').disabled = !editable;
  document.getElementById('btnAnnuler').disabled = !editable;

  var label = document.getElementById('modeLabel');
  if (mode === 'ajout') label.textContent = 'Vous êtes en mode Ajout…';
  else if (mode === 'modification') label.textContent = 'Vous êtes en mode Modification…';
  else label.textContent = 'Vous êtes en mode Consultation…';

  cacherErreur();
}

function champs() {
  return ['corps_enseignant_id', 'ia_id', 'ief_id', 'centre_execution_id', 'budget_id',
          'activite_id', 'imputation_budgetaire', 'numero_autorisation', 'numero_carton',
          'montant', 'montant_engagement'];
}

function radios() {
  return Array.prototype.slice.call(document.querySelectorAll('input[name="type"]'));
}

function viderFormulaire() {
  document.getElementById('ventilationId').value = '';
  champs().forEach(function (id) { document.getElementById(id).value = ''; });
  radios()[0].checked = true;
}

function remplirFormulaire(v) {
  document.getElementById('ventilationId').value = v.id;
  document.getElementById('corps_enseignant_id').value = v.corps_enseignant_id || '';
  document.getElementById('ia_id').value = v.ia_id || '';
  document.getElementById('ief_id').value = v.ief_id || '';
  document.getElementById('centre_execution_id').value = v.centre_execution_id || '';
  document.getElementById('budget_id').value = v.budget_id || '';
  document.getElementById('activite_id').value = v.activite_id || '';
  document.getElementById('imputation_budgetaire').value = v.imputation_budgetaire || '';
  document.getElementById('numero_autorisation').value = v.numero_autorisation || '';
  document.getElementById('numero_carton').value = v.numero_carton || '';
  document.getElementById('montant').value = v.montant;
  document.getElementById('montant_engagement').value = v.montant_engagement;
  radios().forEach(function (r) { r.checked = (r.value === v.type); });
}

function selectionner(id) {
  selectedId = id;
  var v = allVentilations.filter(function (x) { return x.id === id; })[0];
  if (!v) return;
  remplirFormulaire(v);
  setMode('consultation');
  renderTable(filteredVentilations);
  document.getElementById('panelDetail').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ---------------------------------------------------------------- evenements */

function setupEvents() {
  document.getElementById('selectDelegation').addEventListener('change', function () {
    selectedId = null;
    viderFormulaire();
    setMode('consultation');
    majInfosDelegation();
    loadVentilations();
  });

  document.getElementById('btnFiltrer').addEventListener('click', loadVentilations);

  document.getElementById('btnReinitialiser').addEventListener('click', function () {
    ['filterType', 'filterCorps', 'filterIa', 'filterIef', 'filterCarton'].forEach(function (id) {
      document.getElementById(id).value = '';
    });
    loadVentilations();
  });

  document.getElementById('btnAjouter').addEventListener('click', function () {
    selectedId = null;
    viderFormulaire();
    setMode('ajout');
  });

  document.getElementById('btnModifier').addEventListener('click', function () {
    if (!selectedId) return;
    setMode('modification');
  });

  document.getElementById('btnAnnuler').addEventListener('click', function () {
    if (selectedId) {
      var v = allVentilations.filter(function (x) { return x.id === selectedId; })[0];
      if (v) remplirFormulaire(v);
    } else {
      viderFormulaire();
    }
    setMode('consultation');
  });

  document.getElementById('btnSupprimer').addEventListener('click', supprimer);
  document.getElementById('formVentilation').addEventListener('submit', enregistrer);
}

function majInfosDelegation() {
  var id = document.getElementById('selectDelegation').value;
  var d = allDelegations.filter(function (x) { return String(x.id) === String(id); })[0];
  document.getElementById('infoAnnee').value = d ? (d.annee_academique || '') : '';
  document.getElementById('infoPeriode').value = d ? (d.periode_paie || '') : '';
  document.getElementById('infoObjet').value = d ? (d.objet || '') : '';
}

/* ---------------------------------------------------------------- ecriture */

function enregistrer(e) {
  e.preventDefault();
  cacherErreur();

  var delegationId = document.getElementById('selectDelegation').value;
  if (!delegationId) return afficherErreur('Sélectionnez d\'abord une délégation.');

  var payload = {
    corps_enseignant_id: valeurOuNull('corps_enseignant_id'),
    ia_id: valeurOuNull('ia_id'),
    ief_id: valeurOuNull('ief_id'),
    centre_execution_id: valeurOuNull('centre_execution_id'),
    budget_id: valeurOuNull('budget_id'),
    activite_id: valeurOuNull('activite_id'),
    imputation_budgetaire: valeurOuNull('imputation_budgetaire'),
    numero_autorisation: valeurOuNull('numero_autorisation'),
    numero_carton: valeurOuNull('numero_carton'),
    montant: Number(document.getElementById('montant').value || 0),
    montant_engagement: Number(document.getElementById('montant_engagement').value || 0),
    type: radios().filter(function (r) { return r.checked; })[0].value
  };

  var enModification = (mode === 'modification');
  var url = enModification
    ? API + '/ventilations/' + document.getElementById('ventilationId').value
    : API + '/delegation-credits/' + delegationId + '/ventilations';

  fetch(url, {
    method: enModification ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(function (res) { return res.json().then(function (b) { return { ok: res.ok, body: b }; }); })
    .then(function (r) {
      if (!r.ok) return afficherErreur(messageErreur(r.body));
      selectedId = r.body.data ? r.body.data.id : null;
      setMode('consultation');
      loadVentilations();
    })
    .catch(function (e) { afficherErreur('Erreur de connexion au serveur backend (port 8000).'); console.error(e); });
}

function supprimer() {
  if (!selectedId) return;
  if (!confirm('Supprimer définitivement cette ventilation ?')) return;

  fetch(API + '/ventilations/' + selectedId, {
    method: 'DELETE',
    headers: { 'Accept': 'application/json' }
  })
    .then(function (res) { return res.json().then(function (b) { return { ok: res.ok, body: b }; }); })
    .then(function (r) {
      if (!r.ok) return afficherErreur(messageErreur(r.body));
      selectedId = null;
      viderFormulaire();
      setMode('consultation');
      loadVentilations();
    })
    .catch(function (e) { afficherErreur('Erreur de connexion au serveur backend (port 8000).'); console.error(e); });
}

/* ---------------------------------------------------------------- utilitaires */

function remplirSelect(id, items, champLibelle, champCode) {
  var select = document.getElementById(id);
  if (!select || !items) return;
  items.forEach(function (item) {
    var texte = champCode && item[champCode] ? item[champCode] + ' — ' + item[champLibelle] : item[champLibelle];
    select.innerHTML += '<option value="' + item.id + '">' + texte + '</option>';
  });
}

function ajouterParam(params, cle, elementId) {
  var valeur = document.getElementById(elementId).value;
  if (valeur) params.push(cle + '=' + encodeURIComponent(valeur));
}

function valeurOuNull(id) {
  var v = document.getElementById(id).value;
  return v === '' ? null : v;
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(Number(val) || 0);
}

function formatCourt(val) {
  var n = Number(val) || 0;
  if (n >= 1000000) return (n / 1000000).toFixed(1).replace('.0', '') + 'M';
  if (n >= 1000) return Math.round(n / 1000) + 'K';
  return String(n);
}

function messageErreur(body) {
  if (body && body.errors) {
    var cles = Object.keys(body.errors);
    if (cles.length) return body.errors[cles[0]][0];
  }
  return (body && body.message) ? body.message : 'Une erreur est survenue.';
}

function afficherErreur(message) {
  var p = document.getElementById('formError');
  p.textContent = message;
  p.style.display = 'block';
}

function cacherErreur() {
  document.getElementById('formError').style.display = 'none';
}

function erreurReseau(quoi, e) {
  console.error('Erreur chargement ' + quoi + ':', e);
  var empty = document.getElementById('emptyMsg');
  empty.textContent = 'Erreur de connexion au serveur backend (port 8000).';
  empty.style.display = 'block';
}
</script>
@endpush
