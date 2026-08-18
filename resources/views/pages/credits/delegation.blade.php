@extends('layouts.app')

@section('title', 'SICORE - Délégation de crédit')
@section('content')
<main class="main-content">
  <x-topbar
    title="Délégation de crédit"
    subtitle="Gestion de la paie > Délégation de crédit"
    icon="fa-solid fa-scale-balanced"
    search-id="delegationSearch"
    search-placeholder="Rechercher…"
    filter-target="#delegationTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Créer une délégation de crédit.</li>
        <li>Affecter une délégation à un service ou à une structure.</li>
      </ul>
    </section>

    <div class="stats-grid four">
      <article class="stat-card">
        <div>
          <p class="stat-label">Délégations</p>
          <p class="stat-value" id="statTotal">0</p>
          <p class="stat-note">Période active</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Validées</p>
          <p class="stat-value" id="statValidees">0</p>
          <p class="stat-note" id="statValideesPct">0% traités</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">En attente</p>
          <p class="stat-value" id="statAttente">0</p>
          <p class="stat-note">À suivre</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Rejetées</p>
          <p class="stat-value" id="statRejetees">0</p>
          <p class="stat-note">Correction requise</p>
        </div>
        <span class="stat-icon red"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></span>
      </article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb">Gestion de la paie > Délégation de crédit</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnNouvelleDelegation">Nouvelle délégation</button>
        <button class="btn-secondary" type="button" id="btnExporter">Exporter</button>
      </div>
    </div>

    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filterStructure">Structure</label>
        <select class="form-control" id="filterStructure">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterService">Service</label>
        <select class="form-control" id="filterService">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterStatut">Statut</label>
        <select class="form-control" id="filterStatut">
          <option value="">Tous</option>
          <option value="En attente">En attente</option>
          <option value="Validée">Validée</option>
          <option value="Rejetée">Rejetée</option>
        </select>
      </div>
      <div class="actions-group">
        <button class="btn-secondary" type="button" id="btnFiltrer">Filtrer</button>
        <button class="btn-secondary" type="button" id="btnReinitialiser">Réinitialiser</button>
      </div>
    </section>

    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="delegationTable">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Libellé</th>
              <th>Structure</th>
              <th>Service</th>
              <th>Montant initial</th>
              <th>Montant disponible</th>
              <th>Début validité</th>
              <th>Fin validité</th>
              <th>Statut</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody id="delegationBody">
          </tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucune délégation trouvée.</p>
      <div class="pagination" id="paginationControls"></div>
    </section>
  </section>
</main>

<!-- Modal Nouvelle Délégation -->
<div id="modalDelegation" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:600px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Nouvelle délégation de crédit</h2>
      <button type="button" id="btnCloseModal" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <form id="formDelegation">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="form-group">
          <label for="annee_academique">Année académique *</label>
          <input type="text" class="form-control" id="annee_academique" name="annee_academique" placeholder="2025-2026" required>
        </div>
        <div class="form-group">
          <label for="reference">Référence *</label>
          <input type="text" class="form-control" id="reference" name="reference" placeholder="DC-2026-001" required>
        </div>
        <div class="form-group" style="grid-column:span 2;">
          <label for="objet">Objet *</label>
          <input type="text" class="form-control" id="objet" name="objet" placeholder="Délégation pour paie enseignants" required>
        </div>
        <div class="form-group">
          <label for="structure_id">Structure *</label>
          <select class="form-control" id="structure_id" name="structure_id" required>
            <option value="">-- Choisir --</option>
          </select>
        </div>
        <div class="form-group">
          <label for="service_id">Service *</label>
          <select class="form-control" id="service_id" name="service_id" required>
            <option value="">-- Choisir une structure d'abord --</option>
          </select>
        </div>
        <div class="form-group">
          <label for="montant_initial">Montant initial (FCFA)</label>
          <input type="number" class="form-control" id="montant_initial" name="montant_initial" min="0" step="1">
        </div>
        <div class="form-group">
          <label for="montant_disponible">Montant disponible (FCFA) *</label>
          <input type="number" class="form-control" id="montant_disponible" name="montant_disponible" min="0" step="1" required>
        </div>
        <div class="form-group">
          <label for="date_delegation">Date début de validité *</label>
          <input type="date" class="form-control" id="date_delegation" name="date_delegation" required>
        </div>
        <div class="form-group">
          <label for="date_fin">Date fin de validité</label>
          <input type="date" class="form-control" id="date_fin" name="date_fin">
        </div>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
        <button type="button" class="btn-secondary" id="btnAnnuler">Annuler</button>
        <button type="submit" class="btn-primary" id="btnSubmit">Créer la délégation</button>
      </div>
      <p id="formError" style="color:#e03131; margin-top:12px; display:none;"></p>
      <p id="formSuccess" style="color:#087f5b; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>

<!-- Modal Affectation -->
<div id="modalAffectation" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Affecter la délégation</h2>
      <button type="button" id="btnCloseAffectation" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="affectationInfo" style="background:#f1f3f5; border-radius:8px; padding:16px; margin-bottom:20px;"></div>
    <form id="formAffectation">
      <input type="hidden" id="affectation_delegation_id">
      <div style="display:grid; gap:16px;">
        <div class="form-group">
          <label for="affectation_structure_id">Structure *</label>
          <select class="form-control" id="affectation_structure_id" name="structure_id" required>
            <option value="">-- Choisir une structure --</option>
          </select>
        </div>
        <div class="form-group">
          <label for="affectation_service_id">Service (optionnel)</label>
          <select class="form-control" id="affectation_service_id" name="service_id">
            <option value="">-- Aucun service --</option>
          </select>
        </div>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
        <button type="button" class="btn-secondary" id="btnAnnulerAffectation">Annuler</button>
        <button type="submit" class="btn-primary" id="btnSubmitAffectation">Affecter</button>
      </div>
      <p id="affectationError" style="color:#e03131; margin-top:12px; display:none;"></p>
      <p id="affectationSuccess" style="color:#087f5b; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>

<!-- Modal Montant disponible -->
<div id="modalMontant" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Définir le montant disponible</h2>
      <button type="button" id="btnCloseMontant" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="montantInfo" style="background:#f1f3f5; border-radius:8px; padding:16px; margin-bottom:20px;"></div>
    <form id="formMontant">
      <input type="hidden" id="montant_delegation_id">
      <div style="display:grid; gap:16px;">
        <div class="form-group">
          <label>Montant initial (FCFA)</label>
          <input type="text" class="form-control" id="montant_initial_affiche" disabled style="background:#e9ecef;">
        </div>
        <div class="form-group">
          <label>Montant consommé (FCFA)</label>
          <input type="text" class="form-control" id="montant_consomme_affiche" disabled style="background:#e9ecef;">
        </div>
        <div class="form-group">
          <label for="nouveau_montant_disponible">Nouveau montant disponible (FCFA) *</label>
          <input type="number" class="form-control" id="nouveau_montant_disponible" min="1" step="1" required>
          <small id="montantHint" style="color:#868e96; font-size:0.82rem;"></small>
        </div>
        <div class="form-group">
          <label>Nouveau solde estimé</label>
          <input type="text" class="form-control" id="solde_estime" disabled style="background:#d3f9d8; font-weight:bold; color:#087f5b;">
        </div>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
        <button type="button" class="btn-secondary" id="btnAnnulerMontant">Annuler</button>
        <button type="submit" class="btn-primary" id="btnSubmitMontant">Enregistrer</button>
      </div>
      <p id="montantError" style="color:#e03131; margin-top:12px; display:none;"></p>
      <p id="montantSuccess" style="color:#087f5b; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>

<!-- Modal Engager -->
<div id="modalEngager" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Enregistrer un engagement</h2>
      <button type="button" id="btnCloseEngager" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="engagerInfo" style="background:#f1f3f5; border-radius:8px; padding:16px; margin-bottom:20px;"></div>
    <form id="formEngager">
      <input type="hidden" id="engager_delegation_id">
      <div style="display:grid; gap:16px;">
        <div class="form-group">
          <label for="engager_motif">Motif de l'engagement *</label>
          <input type="text" class="form-control" id="engager_motif" placeholder="Ex : Paie juin 2026" required>
        </div>
        <div class="form-group">
          <label for="engager_montant">Montant (FCFA) *</label>
          <input type="number" class="form-control" id="engager_montant" min="1" step="1" required>
          <small id="engagerHint" style="color:#868e96; font-size:0.82rem;"></small>
        </div>
        <div class="form-group">
          <label for="engager_date">Date d'engagement</label>
          <input type="date" class="form-control" id="engager_date">
        </div>
        <div class="form-group">
          <label for="engager_ref">Référence opération</label>
          <input type="text" class="form-control" id="engager_ref" placeholder="Optionnel">
        </div>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
        <button type="button" class="btn-secondary" id="btnAnnulerEngager">Annuler</button>
        <button type="submit" class="btn-primary" id="btnSubmitEngager">Engager</button>
      </div>
      <p id="engagerError" style="color:#e03131; margin-top:12px; display:none;"></p>
      <p id="engagerSuccess" style="color:#087f5b; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>

<!-- Modal Historique Engagements -->
<div id="modalHistorique" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:750px; width:95%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Suivi des engagements</h2>
      <button type="button" id="btnCloseHistorique" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="historiqueResume" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:20px;"></div>
    <div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; align-items:flex-end;">
      <div class="form-group" style="margin:0;">
        <label for="histDateDebut" style="font-size:0.82rem;">Date début</label>
        <input type="date" class="form-control" id="histDateDebut" style="padding:6px 10px;">
      </div>
      <div class="form-group" style="margin:0;">
        <label for="histDateFin" style="font-size:0.82rem;">Date fin</label>
        <input type="date" class="form-control" id="histDateFin" style="padding:6px 10px;">
      </div>
      <button class="btn-secondary" type="button" id="btnHistFiltrer" style="padding:7px 14px;">Filtrer</button>
    </div>
    <div class="table-responsive">
      <table class="table" id="historiqueTable">
        <thead>
          <tr>
            <th>Date</th>
            <th>Motif</th>
            <th>Référence</th>
            <th>Montant</th>
          </tr>
        </thead>
        <tbody id="historiqueBody"></tbody>
      </table>
    </div>
    <p id="historiqueEmpty" style="display:none; color:#868e96; text-align:center; margin-top:12px;">Aucun engagement enregistré.</p>
  </div>
</div>

<!-- Modal Détail -->
<div id="modalDetail" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:600px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Détail de la délégation</h2>
      <button type="button" id="btnCloseDetail" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="detailContent"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';
var allDelegations = [];
var allStructures = [];
var filteredDelegations = [];
var currentPage = 1;
var perPage = 5;

document.addEventListener('DOMContentLoaded', function() {
  Promise.all([loadStructures(), loadDelegations()]).then(function() {
    setupEvents();
  });
});

function loadStructures() {
  return fetch(API + '/structures')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      allStructures = data;
      var filterSelect = document.getElementById('filterStructure');
      var formSelect = document.getElementById('structure_id');
      allStructures.forEach(function(s) {
        filterSelect.innerHTML += '<option value="' + s.id + '">' + s.nom + '</option>';
        formSelect.innerHTML += '<option value="' + s.id + '">' + s.nom + '</option>';
      });
      majServicesFiltre();
    })
    .catch(function(e) {
      console.error('Erreur chargement structures:', e);
    });
}

function loadDelegations() {
  return fetch(API + '/delegation-credits')
    .then(function(res) { return res.json(); })
    .then(function(response) {
      allDelegations = response.data || response;
      renderTable(allDelegations);
      updateStats(allDelegations);
    })
    .catch(function(e) {
      console.error('Erreur chargement délégations:', e);
      document.getElementById('emptyMsg').style.display = 'block';
      document.getElementById('emptyMsg').textContent = 'Erreur de connexion au serveur backend (port 8000).';
    });
}

function updateStats(data) {
  var total = data.length;
  var validees = data.filter(function(d) { return d.statut === 'Validée' || d.statut === 'Validee'; }).length;
  var attente = data.filter(function(d) { return d.statut === 'En attente'; }).length;
  var rejetees = data.filter(function(d) { return d.statut === 'Rejetée' || d.statut === 'Rejetee'; }).length;

  document.getElementById('statTotal').textContent = total;
  document.getElementById('statValidees').textContent = validees;
  document.getElementById('statAttente').textContent = attente;
  document.getElementById('statRejetees').textContent = rejetees;
  document.getElementById('statValideesPct').textContent = total > 0 ? Math.round(validees / total * 100) + '% traités' : '0% traités';
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  var d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR');
}

function badgeClass(statut) {
  if (statut === 'Validée' || statut === 'Validee') return 'badge-active';
  if (statut === 'Rejetée' || statut === 'Rejetee') return 'badge-rejected';
  return 'badge-pending';
}

function renderTable(data) {
  filteredDelegations = data;
  var totalPages = Math.ceil(data.length / perPage);
  if (currentPage > totalPages) currentPage = totalPages || 1;

  var tbody = document.getElementById('delegationBody');
  var empty = document.getElementById('emptyMsg');

  if (data.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    empty.textContent = 'Aucune délégation trouvée.';
    document.getElementById('paginationControls').innerHTML = '';
    return;
  }
  empty.style.display = 'none';

  var start = (currentPage - 1) * perPage;
  var pageData = data.slice(start, start + perPage);

  tbody.innerHTML = pageData.map(function(d) {
    var structNom = d.structure ? d.structure.nom : '-';
    var servNom = d.service ? d.service.nom : '-';
    var actions = '<button class="table-action" type="button" onclick="voirDetail(' + d.id + ')">Voir</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirMontant(' + d.id + ')">Montant</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirEngager(' + d.id + ')">Engager</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirHistorique(' + d.id + ')">Suivi</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirAffectation(' + d.id + ')">Affecter</button>';
    if (d.statut === 'En attente') {
      actions += '<button class="table-action primary" type="button" onclick="validerDelegation(' + d.id + ')">Valider</button>';
    }
    return '<tr>' +
      '<td>' + d.reference + '</td>' +
      '<td>' + (d.objet || '-') + '</td>' +
      '<td>' + structNom + '</td>' +
      '<td>' + servNom + '</td>' +
      '<td>' + (d.montant_initial ? formatMontant(d.montant_initial) : '-') + '</td>' +
      '<td>' + formatMontant(d.montant_disponible) + '</td>' +
      '<td>' + formatDate(d.date_delegation) + '</td>' +
      '<td>' + (d.date_fin ? formatDate(d.date_fin) : '-') + '</td>' +
      '<td><span class="badge ' + badgeClass(d.statut) + '">' + d.statut + '</span></td>' +
      '<td class="actions-cell"><div class="table-actions-inline">' + actions + '</div></td>' +
      '</tr>';
  }).join('');

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  var container = document.getElementById('paginationControls');
  if (totalPages <= 1) { container.innerHTML = ''; return; }

  var html = '';

  html += '<button class="page-btn" ' + (currentPage === 1 ? 'disabled' : '') +
    ' onclick="goToPage(' + (currentPage - 1) + ')">&laquo;</button>';

  for (var i = 1; i <= totalPages; i++) {
    html += '<button class="page-btn' + (i === currentPage ? ' active' : '') +
      '" onclick="goToPage(' + i + ')">' + i + '</button>';
  }

  html += '<button class="page-btn" ' + (currentPage === totalPages ? 'disabled' : '') +
    ' onclick="goToPage(' + (currentPage + 1) + ')">&raquo;</button>';

  container.innerHTML = html;
}

function goToPage(page) {
  currentPage = page;
  renderTable(filteredDelegations);
}

function majServicesFiltre() {
  var structureId = document.getElementById('filterStructure').value;
  var select = document.getElementById('filterService');
  var ancienChoix = select.value;
  var structures = structureId
    ? allStructures.filter(function(s) { return s.id == structureId; })
    : allStructures;

  var html = '<option value="">Tous</option>';
  structures.forEach(function(s) {
    var services = s.services || [];
    if (services.length === 0) return;
    var options = services.map(function(svc) {
      return '<option value="' + svc.id + '">' + svc.nom + '</option>';
    }).join('');
    html += structureId ? options : '<optgroup label="' + s.nom + '">' + options + '</optgroup>';
  });
  select.innerHTML = html;

  var encoreDisponible = Array.from(select.options).some(function(o) { return o.value === ancienChoix; });
  select.value = encoreDisponible ? ancienChoix : '';
}

function appliquerFiltres() {
  var structureId = document.getElementById('filterStructure').value;
  var serviceId = document.getElementById('filterService').value;
  var statut = document.getElementById('filterStatut').value;
  var champRecherche = document.getElementById('delegationSearch');
  var recherche = champRecherche ? champRecherche.value.trim().toLowerCase() : '';

  var filtered = allDelegations;
  if (structureId) filtered = filtered.filter(function(d) { return d.structure_id == structureId; });
  if (serviceId) filtered = filtered.filter(function(d) { return d.service_id == serviceId; });
  if (statut) filtered = filtered.filter(function(d) { return d.statut === statut; });
  if (recherche) {
    filtered = filtered.filter(function(d) {
      return [d.reference, d.objet, d.structure ? d.structure.nom : '', d.service ? d.service.nom : '', d.statut]
        .join(' ').toLowerCase().indexOf(recherche) !== -1;
    });
  }

  currentPage = 1;
  renderTable(filtered);
  updateStats(filtered);
}

function setupEvents() {
  document.getElementById('btnNouvelleDelegation').addEventListener('click', function() {
    document.getElementById('modalDelegation').style.display = 'block';
    document.getElementById('formError').style.display = 'none';
    document.getElementById('formSuccess').style.display = 'none';
  });

  document.getElementById('btnCloseModal').addEventListener('click', closeModal);
  document.getElementById('btnAnnuler').addEventListener('click', closeModal);
  document.getElementById('btnCloseDetail').addEventListener('click', function() {
    document.getElementById('modalDetail').style.display = 'none';
  });

  document.getElementById('structure_id').addEventListener('change', function() {
    var structureId = this.value;
    var serviceSelect = document.getElementById('service_id');
    serviceSelect.innerHTML = '<option value="">-- Choisir --</option>';
    if (!structureId) return;
    var structure = allStructures.find(function(s) { return s.id == structureId; });
    if (structure && structure.services) {
      structure.services.forEach(function(svc) {
        serviceSelect.innerHTML += '<option value="' + svc.id + '">' + svc.nom + '</option>';
      });
    }
  });

  document.getElementById('filterStructure').addEventListener('change', function() {
    majServicesFiltre();
    appliquerFiltres();
  });

  ['filterService', 'filterStatut'].forEach(function(id) {
    document.getElementById(id).addEventListener('change', appliquerFiltres);
  });
  document.getElementById('btnFiltrer').addEventListener('click', appliquerFiltres);

  document.getElementById('btnReinitialiser').addEventListener('click', function() {
    document.getElementById('filterStructure').value = '';
    document.getElementById('filterStatut').value = '';
    document.getElementById('filterService').value = '';
    var recherche = document.getElementById('delegationSearch');
    if (recherche) recherche.value = '';
    majServicesFiltre();
    appliquerFiltres();
  });

  var champRecherche = document.getElementById('delegationSearch');
  if (champRecherche) champRecherche.addEventListener('input', appliquerFiltres);

  document.getElementById('btnCloseMontant').addEventListener('click', fermerMontant);
  document.getElementById('btnAnnulerMontant').addEventListener('click', fermerMontant);
  document.getElementById('nouveau_montant_disponible').addEventListener('input', calculerSoldeEstime);
  document.getElementById('formMontant').addEventListener('submit', soumettreMontant);

  document.getElementById('btnCloseEngager').addEventListener('click', fermerEngager);
  document.getElementById('btnAnnulerEngager').addEventListener('click', fermerEngager);
  document.getElementById('formEngager').addEventListener('submit', soumettreEngagement);

  document.getElementById('btnCloseHistorique').addEventListener('click', function() { document.getElementById('modalHistorique').style.display = 'none'; });
  document.getElementById('btnHistFiltrer').addEventListener('click', filtrerHistorique);

  document.getElementById('btnCloseAffectation').addEventListener('click', fermerAffectation);
  document.getElementById('btnAnnulerAffectation').addEventListener('click', fermerAffectation);
  document.getElementById('affectation_structure_id').addEventListener('change', function() {
    majServicesAffectation(null);
  });
  document.getElementById('formAffectation').addEventListener('submit', soumettreAffectation);

  document.getElementById('btnExporter').addEventListener('click', exporterCSV);

  document.getElementById('formDelegation').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.textContent = 'Création en cours...';

    var montantInitial = document.getElementById('montant_initial').value;
    var dateFin = document.getElementById('date_fin').value;

    var formData = {
      annee_academique: document.getElementById('annee_academique').value,
      reference: document.getElementById('reference').value,
      objet: document.getElementById('objet').value,
      structure_id: parseInt(document.getElementById('structure_id').value),
      service_id: parseInt(document.getElementById('service_id').value),
      montant_initial: montantInitial ? parseFloat(montantInitial) : null,
      montant_disponible: parseFloat(document.getElementById('montant_disponible').value),
      date_delegation: document.getElementById('date_delegation').value,
      date_fin: dateFin || null,
    };

    fetch(API + '/delegation-credits', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(formData)
    })
    .then(function(res) {
      return res.json().then(function(result) {
        if (!res.ok) {
          var errMsg = result.errors ? Object.values(result.errors).flat().join(', ') : result.message;
          document.getElementById('formError').textContent = errMsg;
          document.getElementById('formError').style.display = 'block';
          document.getElementById('formSuccess').style.display = 'none';
        } else {
          document.getElementById('formSuccess').textContent = 'Délégation créée avec succès !';
          document.getElementById('formSuccess').style.display = 'block';
          document.getElementById('formError').style.display = 'none';
          document.getElementById('formDelegation').reset();
          setTimeout(function() { closeModal(); loadDelegations(); }, 1000);
        }
        btn.disabled = false;
        btn.textContent = 'Créer la délégation';
      });
    })
    .catch(function(e) {
      document.getElementById('formError').textContent = 'Erreur de connexion au serveur.';
      document.getElementById('formError').style.display = 'block';
      btn.disabled = false;
      btn.textContent = 'Créer la délégation';
    });
  });
}

function closeModal() {
  document.getElementById('modalDelegation').style.display = 'none';
  document.getElementById('formDelegation').reset();
}

function exporterCSV() {
  var table = document.getElementById('delegationTable');
  var entetes = Array.from(table.querySelectorAll('thead th'))
    .filter(function(th) { return !th.classList.contains('actions-cell'); })
    .map(function(th) { return th.textContent.trim(); });

  var lignes = Array.from(table.querySelectorAll('tbody tr'))
    .map(function(tr) {
      return Array.from(tr.querySelectorAll('td'))
        .filter(function(td) { return !td.classList.contains('actions-cell'); })
        .map(function(td) { return td.textContent.trim(); });
    });

  if (lignes.length === 0) {
    alert('Aucune délégation à exporter.');
    return;
  }

  var echapper = function(valeur) { return '"' + String(valeur).replace(/"/g, '""') + '"'; };
  var csv = [entetes].concat(lignes)
    .map(function(ligne) { return ligne.map(echapper).join(';'); })
    .join('\r\n');

  var blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
  var url = URL.createObjectURL(blob);
  var lien = document.createElement('a');
  lien.href = url;
  lien.download = 'delegations-credit-' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(lien);
  lien.click();
  document.body.removeChild(lien);
  URL.revokeObjectURL(url);
}

function voirDetail(id) {
  fetch(API + '/delegation-credits/' + id + '/etat')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var paiementsHtml = '';
      if (data.paiements && data.paiements.length > 0) {
        paiementsHtml = '<h3 style="margin-top:24px; color:#087f5b;">Paiements associés</h3>' +
          '<table class="table" style="margin-top:8px;"><thead><tr><th>Agent</th><th>Mois</th><th>Montant</th><th>Date</th></tr></thead><tbody>' +
          data.paiements.map(function(p) {
            return '<tr><td>' + p.nom_agent + '</td><td>' + p.mois + '</td><td>' + formatMontant(p.montant) + '</td><td>' + formatDate(p.date_paiement) + '</td></tr>';
          }).join('') + '</tbody></table>';
      } else {
        paiementsHtml = '<p style="margin-top:16px; color:#868e96;">Aucun paiement associé.</p>';
      }

      document.getElementById('detailContent').innerHTML =
        '<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">' +
          '<div><strong>Référence :</strong> ' + data.reference + '</div>' +
          '<div><strong>Exercice budgétaire :</strong> ' + data.annee_academique + '</div>' +
          '<div style="grid-column:span 2;"><strong>Libellé :</strong> ' + (data.objet || '-') + '</div>' +
          '<div><strong>Montant initial :</strong> ' + (data.montant_initial ? formatMontant(data.montant_initial) : '-') + '</div>' +
          '<div><strong>Montant disponible :</strong> ' + formatMontant(data.montant_disponible) + '</div>' +
          '<div><strong>Montant engagé :</strong> ' + formatMontant(data.montant_engage) + '</div>' +
          '<div><strong>Montant consommé :</strong> ' + formatMontant(data.montant_consomme) + '</div>' +
          '<div><strong>Solde restant :</strong> <span style="color:' + (data.solde > 0 ? '#087f5b' : '#e03131') + '; font-weight:bold;">' + formatMontant(data.solde) + '</span></div>' +
          '<div><strong>Statut :</strong> ' + (data.statut || '-') + '</div>' +
          '<div><strong>Début validité :</strong> ' + formatDate(data.date_delegation) + '</div>' +
          '<div><strong>Fin validité :</strong> ' + (data.date_fin ? formatDate(data.date_fin) : '-') + '</div>' +
        '</div>' + paiementsHtml;
      document.getElementById('modalDetail').style.display = 'block';
    })
    .catch(function(e) {
      alert('Erreur lors du chargement des détails.');
    });
}

function ouvrirAffectation(id) {
  var delegation = allDelegations.find(function(d) { return d.id == id; });
  if (!delegation) return;

  document.getElementById('affectation_delegation_id').value = id;
  document.getElementById('affectationError').style.display = 'none';
  document.getElementById('affectationSuccess').style.display = 'none';

  document.getElementById('affectationInfo').innerHTML =
    '<strong>' + delegation.reference + '</strong> — ' + (delegation.objet || '') +
    '<br><small>Structure actuelle : <strong>' + (delegation.structure ? delegation.structure.nom : 'Aucune') + '</strong>' +
    ' | Service actuel : <strong>' + (delegation.service ? delegation.service.nom : 'Aucun') + '</strong></small>';

  var structSelect = document.getElementById('affectation_structure_id');
  structSelect.innerHTML = '<option value="">-- Choisir une structure --</option>';
  allStructures.forEach(function(s) {
    var selected = delegation.structure_id == s.id ? ' selected' : '';
    structSelect.innerHTML += '<option value="' + s.id + '"' + selected + '>' + s.nom + '</option>';
  });

  majServicesAffectation(delegation.service_id);

  document.getElementById('modalAffectation').style.display = 'block';
}

function majServicesAffectation(selectedServiceId) {
  var structureId = document.getElementById('affectation_structure_id').value;
  var serviceSelect = document.getElementById('affectation_service_id');
  serviceSelect.innerHTML = '<option value="">-- Aucun service --</option>';
  if (!structureId) return;
  var structure = allStructures.find(function(s) { return s.id == structureId; });
  if (structure && structure.services) {
    structure.services.forEach(function(svc) {
      var selected = selectedServiceId == svc.id ? ' selected' : '';
      serviceSelect.innerHTML += '<option value="' + svc.id + '"' + selected + '>' + svc.nom + '</option>';
    });
  }
}

function fermerAffectation() {
  document.getElementById('modalAffectation').style.display = 'none';
}

function soumettreAffectation(e) {
  e.preventDefault();
  var id = document.getElementById('affectation_delegation_id').value;
  var structureId = document.getElementById('affectation_structure_id').value;
  var serviceId = document.getElementById('affectation_service_id').value;
  var btn = document.getElementById('btnSubmitAffectation');

  if (!structureId) {
    document.getElementById('affectationError').textContent = 'Veuillez sélectionner une structure.';
    document.getElementById('affectationError').style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Affectation en cours...';

  var body = { structure_id: parseInt(structureId) };
  if (serviceId) body.service_id = parseInt(serviceId);

  fetch(API + '/delegation-credits/' + id + '/affectation', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(body)
  })
  .then(function(res) {
    return res.json().then(function(result) {
      if (!res.ok) {
        var errMsg = result.errors
          ? Object.values(result.errors).flat().join(', ')
          : result.message;
        document.getElementById('affectationError').textContent = errMsg;
        document.getElementById('affectationError').style.display = 'block';
        document.getElementById('affectationSuccess').style.display = 'none';
      } else {
        document.getElementById('affectationSuccess').textContent = result.message;
        document.getElementById('affectationSuccess').style.display = 'block';
        document.getElementById('affectationError').style.display = 'none';
        setTimeout(function() { fermerAffectation(); loadDelegations(); }, 1000);
      }
      btn.disabled = false;
      btn.textContent = 'Affecter';
    });
  })
  .catch(function() {
    document.getElementById('affectationError').textContent = 'Erreur de connexion au serveur.';
    document.getElementById('affectationError').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Affecter';
  });
}

var montantDelegationCache = null;

function ouvrirMontant(id) {
  var delegation = allDelegations.find(function(d) { return d.id == id; });
  if (!delegation) return;

  montantDelegationCache = delegation;
  document.getElementById('montant_delegation_id').value = id;
  document.getElementById('montantError').style.display = 'none';
  document.getElementById('montantSuccess').style.display = 'none';

  document.getElementById('montantInfo').innerHTML =
    '<strong>' + delegation.reference + '</strong> — ' + (delegation.objet || '') +
    '<br><small>Solde actuel : <strong style="color:#087f5b;">' + formatMontant(delegation.solde) + '</strong></small>';

  var mi = delegation.montant_initial || 0;
  var mc = delegation.montant_consomme || 0;
  document.getElementById('montant_initial_affiche').value = formatMontant(mi);
  document.getElementById('montant_consomme_affiche').value = formatMontant(mc);
  document.getElementById('nouveau_montant_disponible').value = delegation.montant_disponible || '';
  document.getElementById('montantHint').textContent = mi > 0
    ? 'Max : ' + formatMontant(mi) + ' | Min : ' + formatMontant(mc > 0 ? mc : 1)
    : '';

  calculerSoldeEstime();
  document.getElementById('modalMontant').style.display = 'block';
}

function fermerMontant() {
  document.getElementById('modalMontant').style.display = 'none';
  montantDelegationCache = null;
}

function calculerSoldeEstime() {
  var md = parseFloat(document.getElementById('nouveau_montant_disponible').value) || 0;
  var mc = montantDelegationCache ? (montantDelegationCache.montant_consomme || 0) : 0;
  var solde = md - mc;
  document.getElementById('solde_estime').value = formatMontant(solde);
  document.getElementById('solde_estime').style.color = solde >= 0 ? '#087f5b' : '#e03131';
  document.getElementById('solde_estime').style.background = solde >= 0 ? '#d3f9d8' : '#ffe3e3';
}

function soumettreMontant(e) {
  e.preventDefault();
  var id = document.getElementById('montant_delegation_id').value;
  var montant = parseFloat(document.getElementById('nouveau_montant_disponible').value);
  var btn = document.getElementById('btnSubmitMontant');

  if (!montant || montant <= 0) {
    document.getElementById('montantError').textContent = 'Le montant doit être supérieur à zéro.';
    document.getElementById('montantError').style.display = 'block';
    return;
  }

  if (montantDelegationCache && montantDelegationCache.montant_initial && montant > montantDelegationCache.montant_initial) {
    document.getElementById('montantError').textContent = 'Le montant ne peut pas dépasser le montant initial (' + formatMontant(montantDelegationCache.montant_initial) + ').';
    document.getElementById('montantError').style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Enregistrement...';

  fetch(API + '/delegation-credits/' + id + '/montant-disponible', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ montant_disponible: montant })
  })
  .then(function(res) {
    return res.json().then(function(result) {
      if (!res.ok) {
        var errMsg = result.errors
          ? Object.values(result.errors).flat().join(', ')
          : result.message;
        document.getElementById('montantError').textContent = errMsg;
        document.getElementById('montantError').style.display = 'block';
        document.getElementById('montantSuccess').style.display = 'none';
      } else {
        document.getElementById('montantSuccess').textContent = result.message;
        document.getElementById('montantSuccess').style.display = 'block';
        document.getElementById('montantError').style.display = 'none';
        setTimeout(function() { fermerMontant(); loadDelegations(); }, 1000);
      }
      btn.disabled = false;
      btn.textContent = 'Enregistrer';
    });
  })
  .catch(function() {
    document.getElementById('montantError').textContent = 'Erreur de connexion au serveur.';
    document.getElementById('montantError').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Enregistrer';
  });
}

function ouvrirEngager(id) {
  var delegation = allDelegations.find(function(d) { return d.id == id; });
  if (!delegation) return;

  document.getElementById('engager_delegation_id').value = id;
  document.getElementById('engagerError').style.display = 'none';
  document.getElementById('engagerSuccess').style.display = 'none';
  document.getElementById('engager_motif').value = '';
  document.getElementById('engager_montant').value = '';
  document.getElementById('engager_ref').value = '';
  document.getElementById('engager_date').value = new Date().toISOString().slice(0, 10);

  var creditDispo = delegation.montant_disponible - delegation.montant_engage;
  document.getElementById('engagerInfo').innerHTML =
    '<strong>' + delegation.reference + '</strong> — ' + (delegation.objet || '') +
    '<br><small>Disponible : <strong>' + formatMontant(delegation.montant_disponible) + '</strong>' +
    ' | Déjà engagé : <strong>' + formatMontant(delegation.montant_engage) + '</strong>' +
    ' | Reste : <strong style="color:#087f5b;">' + formatMontant(creditDispo) + '</strong></small>';

  document.getElementById('engagerHint').textContent = 'Maximum : ' + formatMontant(creditDispo);

  document.getElementById('modalEngager').style.display = 'block';
}

function fermerEngager() {
  document.getElementById('modalEngager').style.display = 'none';
}

function soumettreEngagement(e) {
  e.preventDefault();
  var id = document.getElementById('engager_delegation_id').value;
  var montant = parseFloat(document.getElementById('engager_montant').value);
  var motif = document.getElementById('engager_motif').value.trim();
  var btn = document.getElementById('btnSubmitEngager');

  if (!motif) {
    document.getElementById('engagerError').textContent = 'Le motif est obligatoire.';
    document.getElementById('engagerError').style.display = 'block';
    return;
  }
  if (!montant || montant <= 0) {
    document.getElementById('engagerError').textContent = 'Le montant doit être supérieur à zéro.';
    document.getElementById('engagerError').style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Enregistrement...';

  fetch(API + '/delegation-credits/' + id + '/engager', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      montant: montant,
      motif: motif,
      date_engagement: document.getElementById('engager_date').value || null,
      reference_operation: document.getElementById('engager_ref').value || null,
    })
  })
  .then(function(res) {
    return res.json().then(function(result) {
      if (!res.ok) {
        var errMsg = result.errors ? Object.values(result.errors).flat().join(', ') : result.message;
        document.getElementById('engagerError').textContent = errMsg;
        document.getElementById('engagerError').style.display = 'block';
        document.getElementById('engagerSuccess').style.display = 'none';
      } else {
        document.getElementById('engagerSuccess').textContent = result.message;
        document.getElementById('engagerSuccess').style.display = 'block';
        document.getElementById('engagerError').style.display = 'none';
        setTimeout(function() { fermerEngager(); loadDelegations(); }, 1000);
      }
      btn.disabled = false;
      btn.textContent = 'Engager';
    });
  })
  .catch(function() {
    document.getElementById('engagerError').textContent = 'Erreur de connexion au serveur.';
    document.getElementById('engagerError').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Engager';
  });
}

var historiqueDelegationId = null;

function ouvrirHistorique(id) {
  historiqueDelegationId = id;
  document.getElementById('histDateDebut').value = '';
  document.getElementById('histDateFin').value = '';
  chargerHistorique(id);
  document.getElementById('modalHistorique').style.display = 'block';
}

function filtrerHistorique() {
  if (historiqueDelegationId) chargerHistorique(historiqueDelegationId);
}

function chargerHistorique(id) {
  var params = [];
  var dd = document.getElementById('histDateDebut').value;
  var df = document.getElementById('histDateFin').value;
  if (dd) params.push('date_debut=' + dd);
  if (df) params.push('date_fin_filtre=' + df);
  var qs = params.length ? '?' + params.join('&') : '';

  fetch(API + '/delegation-credits/' + id + '/engagements' + qs)
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var d = data.delegation;
      var taux = d.taux_engagement || 0;
      var couleurTaux = taux > 80 ? '#e03131' : (taux > 50 ? '#e67700' : '#087f5b');

      document.getElementById('historiqueResume').innerHTML =
        '<div style="background:#e7f5ff; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Disponible</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#1971c2;">' + formatMontant(d.montant_disponible) + '</div></div>' +
        '<div style="background:#fff3bf; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Engagé</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#e67700;">' + formatMontant(d.montant_engage) + '</div>' +
          '<div style="font-size:0.75rem; color:' + couleurTaux + '; font-weight:600;">' + taux + '% engagé</div></div>' +
        '<div style="background:#d3f9d8; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Solde</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#087f5b;">' + formatMontant(d.solde) + '</div></div>';

      var engagements = data.engagements || [];
      var tbody = document.getElementById('historiqueBody');
      var empty = document.getElementById('historiqueEmpty');

      if (engagements.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
      } else {
        empty.style.display = 'none';
        tbody.innerHTML = engagements.map(function(e) {
          return '<tr>' +
            '<td>' + formatDate(e.date_engagement) + '</td>' +
            '<td>' + e.motif + '</td>' +
            '<td>' + (e.reference_operation || '-') + '</td>' +
            '<td style="font-weight:600;">' + formatMontant(e.montant) + '</td>' +
            '</tr>';
        }).join('');

        tbody.innerHTML += '<tr style="font-weight:bold; background:#f1f3f5;">' +
          '<td colspan="3">Total (' + data.nombre + ' opération(s))</td>' +
          '<td>' + formatMontant(data.total) + '</td></tr>';
      }
    })
    .catch(function(e) {
      console.error('Erreur historique:', e);
    });
}

function validerDelegation(id) {
  if (!confirm('Voulez-vous valider cette délégation ?')) return;
  fetch(API + '/delegation-credits/' + id, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ statut: 'Validée' })
  })
  .then(function() { loadDelegations(); })
  .catch(function() { alert('Erreur lors de la validation.'); });
}
</script>
@endpush
