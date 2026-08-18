@extends('layouts.app')

@section('title', 'SICORE - État des crédits utilisés pour la paie')
@section('content')
<main class="main-content">
  <x-topbar
    title="État des crédits utilisés"
    subtitle="Gestion de la paie > État des crédits utilisés pour la paie"
    icon="fa-solid fa-chart-pie"
    search-id="etatSearch"
    search-placeholder="Rechercher..."
    filter-target="#etatTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Disposer d'une synthèse de l'utilisation des crédits délégués pour la paie.</li>
        <li>Comparer les montants disponibles, engagés, consommés et les soldes restants.</li>
      </ul>
    </section>

    <!-- Filtres -->
    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filtreStructure">Structure</label>
        <select class="form-control" id="filtreStructure">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreService">Service</label>
        <select class="form-control" id="filtreService">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreDelegation">Délégation</label>
        <select class="form-control" id="filtreDelegation">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreStatut">Statut</label>
        <select class="form-control" id="filtreStatut">
          <option value="">Tous</option>
          <option value="En attente">En attente</option>
          <option value="Validée">Validée</option>
          <option value="Rejetée">Rejetée</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreDateDebut">Période du</label>
        <input type="date" class="form-control" id="filtreDateDebut">
      </div>
      <div class="form-group">
        <label for="filtreDateFin">Au</label>
        <input type="date" class="form-control" id="filtreDateFin">
      </div>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnGenerer">Générer l'état</button>
        <button class="btn-secondary" type="button" id="btnResetFiltres">Réinitialiser</button>
      </div>
    </section>

    <!-- Cartes résumé -->
    <div class="stats-grid four" id="statsPanel" style="display:none;">
      <article class="stat-card">
        <div>
          <p class="stat-label">Crédits disponibles</p>
          <p class="stat-value" id="statDisponible">0</p>
          <p class="stat-note" id="statNbDelegations">0 délégation(s)</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-coins" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Montant engagé</p>
          <p class="stat-value" id="statEngage">0</p>
          <p class="stat-note" id="statTauxEng">0% engagé</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Montant consommé</p>
          <p class="stat-value" id="statConsomme">0</p>
          <p class="stat-note" id="statTauxConso">0% consommé</p>
        </div>
        <span class="stat-icon red"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Solde global</p>
          <p class="stat-value" id="statSolde">0</p>
          <p class="stat-note">Reste à consommer</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-piggy-bank" aria-hidden="true"></i></span>
      </article>
    </div>

    <!-- Actions -->
    <div class="actions-row" id="actionsPanel" style="display:none;">
      <p class="breadcrumb">Gestion de la paie > État des crédits</p>
      <div class="actions-group">
        <button class="btn-secondary" type="button" id="btnExporterCSV"><i class="fa-solid fa-file-csv"></i> Exporter CSV</button>
        <button class="btn-secondary" type="button" id="btnImprimer"><i class="fa-solid fa-print"></i> Imprimer</button>
      </div>
    </div>

    <!-- Tableau principal -->
    <section class="table-card" id="tablePanel" style="display:none;">
      <h3 style="margin:0 0 16px 0; color:#087f5b; font-size:1.1rem;">Détail par délégation</h3>
      <div class="table-responsive">
        <table class="table" id="etatTable">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Objet</th>
              <th>Structure</th>
              <th>Service</th>
              <th>Disponible</th>
              <th>Engagé</th>
              <th>Consommé</th>
              <th>Solde</th>
              <th>Taux conso.</th>
              <th>Statut</th>
              <th class="actions-cell">Détail</th>
            </tr>
          </thead>
          <tbody id="etatBody"></tbody>
          <tfoot id="etatFoot" style="font-weight:bold; background:#f1f3f5;"></tfoot>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucune délégation trouvée.</p>
      <div class="pagination" id="paginationControls"></div>
    </section>
  </section>
</main>

<!-- Modal Détail paiements -->
<div id="modalDetailPaiements" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:800px; width:95%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;"><i class="fa-solid fa-receipt"></i> Détail des paiements</h2>
      <button type="button" id="btnCloseDetailPaiements" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="detailDelegationInfo" style="margin-bottom:16px; padding:12px; background:#f8f9fa; border-radius:8px;"></div>
    <div id="detailCoherenceAlert" style="display:none; margin-bottom:12px; padding:10px; border-radius:6px; font-size:0.88rem;"></div>
    <div id="detailResume" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:16px;"></div>
    <div style="overflow-x:auto;">
      <table class="table" style="font-size:0.85rem;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Agent</th>
            <th>Période</th>
            <th>Montant</th>
            <th>Bulletin</th>
            <th>Type</th>
          </tr>
        </thead>
        <tbody id="detailPaiementsBody"></tbody>
      </table>
    </div>
    <p id="detailPaiementsEmpty" style="display:none; text-align:center; color:#868e96; padding:20px;">Aucun paiement enregistré.</p>
  </div>
</div>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';
var allLignes = [];
var allStructures = [];
var currentPage = 1;
var perPage = 10;

document.addEventListener('DOMContentLoaded', function() {
  chargerFiltres();
  setupEvents();
});

function chargerFiltres() {
  fetch(API + '/etat-credits/filtres')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      allStructures = data.structures || [];
      var selStructure = document.getElementById('filtreStructure');
      allStructures.forEach(function(s) {
        selStructure.innerHTML += '<option value="' + s.id + '">' + s.nom + '</option>';
      });

      var selDelegation = document.getElementById('filtreDelegation');
      (data.delegations || []).forEach(function(d) {
        selDelegation.innerHTML += '<option value="' + d.id + '">' + d.reference + ' — ' + (d.objet || '') + '</option>';
      });
    })
    .catch(function(e) { console.error('Erreur filtres:', e); });
}

function majServicesFiltres() {
  var structureId = document.getElementById('filtreStructure').value;
  var sel = document.getElementById('filtreService');
  sel.innerHTML = '<option value="">Tous</option>';
  if (!structureId) return;
  var structure = allStructures.find(function(s) { return s.id == structureId; });
  if (structure && structure.services) {
    structure.services.forEach(function(svc) {
      sel.innerHTML += '<option value="' + svc.id + '">' + svc.nom + '</option>';
    });
  }
}

function setupEvents() {
  document.getElementById('filtreStructure').addEventListener('change', majServicesFiltres);
  document.getElementById('btnGenerer').addEventListener('click', genererEtat);
  document.getElementById('btnResetFiltres').addEventListener('click', resetFiltres);
  document.getElementById('btnExporterCSV').addEventListener('click', exporterCSV);
  document.getElementById('btnImprimer').addEventListener('click', imprimerEtat);
  document.getElementById('btnCloseDetailPaiements').addEventListener('click', function() {
    document.getElementById('modalDetailPaiements').style.display = 'none';
  });

  var champRecherche = document.getElementById('etatSearch');
  if (champRecherche) champRecherche.addEventListener('input', function() {
    filtrerLocal();
  });
}

function resetFiltres() {
  document.getElementById('filtreStructure').value = '';
  document.getElementById('filtreService').innerHTML = '<option value="">Tous</option>';
  document.getElementById('filtreDelegation').value = '';
  document.getElementById('filtreStatut').value = '';
  document.getElementById('filtreDateDebut').value = '';
  document.getElementById('filtreDateFin').value = '';
  var champRecherche = document.getElementById('etatSearch');
  if (champRecherche) champRecherche.value = '';
  document.getElementById('statsPanel').style.display = 'none';
  document.getElementById('actionsPanel').style.display = 'none';
  document.getElementById('tablePanel').style.display = 'none';
}

function genererEtat() {
  var params = [];
  var structureId = document.getElementById('filtreStructure').value;
  var serviceId = document.getElementById('filtreService').value;
  var delegationId = document.getElementById('filtreDelegation').value;
  var statut = document.getElementById('filtreStatut').value;
  var dateDebut = document.getElementById('filtreDateDebut').value;
  var dateFin = document.getElementById('filtreDateFin').value;

  if (structureId) params.push('structure_id=' + structureId);
  if (serviceId) params.push('service_id=' + serviceId);
  if (delegationId) params.push('delegation_id=' + delegationId);
  if (statut) params.push('statut=' + encodeURIComponent(statut));
  if (dateDebut) params.push('date_debut=' + dateDebut);
  if (dateFin) params.push('date_fin=' + dateFin);

  var qs = params.length ? '?' + params.join('&') : '';
  var btn = document.getElementById('btnGenerer');
  btn.disabled = true;
  btn.textContent = 'Chargement...';

  fetch(API + '/etat-credits' + qs)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      allLignes = data.lignes || [];
      afficherStats(data.totaux);
      currentPage = 1;
      renderTable(allLignes);
      document.getElementById('statsPanel').style.display = '';
      document.getElementById('actionsPanel').style.display = '';
      document.getElementById('tablePanel').style.display = '';
    })
    .catch(function(e) {
      console.error('Erreur:', e);
      alert('Erreur lors de la génération de l\'état.');
    })
    .finally(function() {
      btn.disabled = false;
      btn.textContent = 'Générer l\'état';
    });
}

function afficherStats(t) {
  document.getElementById('statDisponible').textContent = formatMontant(t.montant_disponible);
  document.getElementById('statNbDelegations').textContent = t.nombre_delegations + ' délégation(s)';
  document.getElementById('statEngage').textContent = formatMontant(t.montant_engage);
  document.getElementById('statTauxEng').textContent = t.taux_engagement + '% engagé';
  document.getElementById('statConsomme').textContent = formatMontant(t.montant_consomme);
  document.getElementById('statTauxConso').textContent = t.taux_consommation + '% consommé';
  document.getElementById('statSolde').textContent = formatMontant(t.solde);
}

function filtrerLocal() {
  var champRecherche = document.getElementById('etatSearch');
  var recherche = champRecherche ? champRecherche.value.trim().toLowerCase() : '';
  if (!recherche) {
    currentPage = 1;
    renderTable(allLignes);
    return;
  }
  var filtered = allLignes.filter(function(l) {
    return [l.reference, l.objet, l.structure, l.service, l.statut]
      .join(' ').toLowerCase().indexOf(recherche) !== -1;
  });
  currentPage = 1;
  renderTable(filtered);
}

function renderTable(lignes) {
  var totalPages = Math.ceil(lignes.length / perPage) || 1;
  var start = (currentPage - 1) * perPage;
  var page = lignes.slice(start, start + perPage);

  var tbody = document.getElementById('etatBody');
  var empty = document.getElementById('emptyMsg');

  if (lignes.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    document.getElementById('etatFoot').innerHTML = '';
    document.getElementById('paginationControls').innerHTML = '';
    return;
  }
  empty.style.display = 'none';

  tbody.innerHTML = page.map(function(l) {
    var soldeColor = l.pct_solde <= 0 ? '#e03131' : (l.pct_solde <= 20 ? '#e67700' : '#087f5b');
    var soldeBg = l.pct_solde <= 0 ? '#ffe3e3' : (l.pct_solde <= 20 ? '#fff3bf' : '#d3f9d8');
    var tauxColor = l.taux_consommation > 80 ? '#e03131' : (l.taux_consommation > 50 ? '#e67700' : '#087f5b');
    var alerteIcon = l.pct_solde <= 20 ? ' <i class="fa-solid fa-triangle-exclamation" style="color:' + soldeColor + ';"></i>' : '';
    var badgeCls = l.statut === 'Validée' || l.statut === 'Validee' ? 'badge-active' :
                   (l.statut === 'Rejetée' || l.statut === 'Rejetee' ? 'badge-rejected' : 'badge-pending');

    return '<tr>' +
      '<td>' + l.reference + '</td>' +
      '<td>' + (l.objet || '-') + '</td>' +
      '<td>' + l.structure + '</td>' +
      '<td>' + l.service + '</td>' +
      '<td>' + formatMontant(l.montant_disponible) + '</td>' +
      '<td style="color:#e67700; font-weight:600;">' + formatMontant(l.montant_engage) + '</td>' +
      '<td style="color:#c92a2a; font-weight:600;">' + formatMontant(l.montant_consomme) + '</td>' +
      '<td><span style="background:' + soldeBg + '; color:' + soldeColor + '; font-weight:bold; padding:2px 8px; border-radius:4px;">' + formatMontant(l.solde) + alerteIcon + '</span></td>' +
      '<td style="color:' + tauxColor + '; font-weight:600;">' + l.taux_consommation + '%</td>' +
      '<td><span class="badge ' + badgeCls + '">' + l.statut + '</span></td>' +
      '<td class="actions-cell"><button class="table-action" type="button" onclick="voirDetailPaiements(' + l.id + ')">Détail</button></td>' +
      '</tr>';
  }).join('');

  var totDisp = lignes.reduce(function(s, l) { return s + l.montant_disponible; }, 0);
  var totEng = lignes.reduce(function(s, l) { return s + l.montant_engage; }, 0);
  var totCons = lignes.reduce(function(s, l) { return s + l.montant_consomme; }, 0);
  var totSolde = lignes.reduce(function(s, l) { return s + l.solde; }, 0);
  var tauxGlobal = totDisp > 0 ? Math.round(totCons / totDisp * 1000) / 10 : 0;

  document.getElementById('etatFoot').innerHTML =
    '<tr>' +
    '<td colspan="4">TOTAUX (' + lignes.length + ' délégation(s))</td>' +
    '<td>' + formatMontant(totDisp) + '</td>' +
    '<td style="color:#e67700;">' + formatMontant(totEng) + '</td>' +
    '<td style="color:#c92a2a;">' + formatMontant(totCons) + '</td>' +
    '<td style="color:#087f5b; font-weight:bold;">' + formatMontant(totSolde) + '</td>' +
    '<td>' + tauxGlobal + '%</td>' +
    '<td colspan="2"></td>' +
    '</tr>';

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
  filtrerLocal();
}

function voirDetailPaiements(id) {
  fetch(API + '/etat-credits/' + id + '/detail')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var d = data.delegation;

      document.getElementById('detailDelegationInfo').innerHTML =
        '<strong>' + d.reference + '</strong> — ' + (d.objet || '') +
        '<br><small>Structure : ' + d.structure + ' | Service : ' + d.service + ' | Statut : ' + d.statut + '</small>';

      var alertDiv = document.getElementById('detailCoherenceAlert');
      if (data.coherence) {
        alertDiv.style.display = 'block';
        alertDiv.style.background = '#d3f9d8';
        alertDiv.style.color = '#087f5b';
        alertDiv.innerHTML = '<i class="fa-solid fa-circle-check"></i> Données cohérentes : montant consommé = somme des paiements.';
      } else {
        alertDiv.style.display = 'block';
        alertDiv.style.background = '#ffe3e3';
        alertDiv.style.color = '#c92a2a';
        alertDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Incohérence détectée : montant consommé (' + formatMontant(d.montant_consomme) + ') ≠ somme des paiements (' + formatMontant(data.total_paiements) + ').';
      }

      document.getElementById('detailResume').innerHTML =
        '<div style="background:#e7f5ff; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Disponible</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#1971c2;">' + formatMontant(d.montant_disponible) + '</div></div>' +
        '<div style="background:#ffe3e3; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Consommé</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#c92a2a;">' + formatMontant(d.montant_consomme) + '</div>' +
          '<div style="font-size:0.75rem; color:#868e96;">' + data.nombre_paiements + ' paiement(s)</div></div>' +
        '<div style="background:#d3f9d8; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Solde</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#087f5b;">' + formatMontant(d.solde) + '</div></div>';

      var paiements = data.paiements || [];
      var tbody = document.getElementById('detailPaiementsBody');
      var empty = document.getElementById('detailPaiementsEmpty');

      if (paiements.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
      } else {
        empty.style.display = 'none';
        tbody.innerHTML = paiements.map(function(p) {
          var typeBadge = p.type === 'bulletin'
            ? '<span style="background:#d3f9d8; color:#087f5b; padding:2px 6px; border-radius:4px; font-size:0.78rem;">Bulletin</span>'
            : '<span style="background:#e7f5ff; color:#1971c2; padding:2px 6px; border-radius:4px; font-size:0.78rem;">Manuel</span>';
          return '<tr>' +
            '<td>' + formatDate(p.date_paiement) + '</td>' +
            '<td>' + p.nom_agent + '</td>' +
            '<td>' + p.mois + '</td>' +
            '<td style="font-weight:600;">' + formatMontant(p.montant) + '</td>' +
            '<td>' + p.bulletin_ref + '</td>' +
            '<td>' + typeBadge + '</td>' +
            '</tr>';
        }).join('');

        tbody.innerHTML += '<tr style="font-weight:bold; background:#f1f3f5;">' +
          '<td colspan="3">Total</td>' +
          '<td>' + formatMontant(data.total_paiements) + '</td>' +
          '<td colspan="2"></td></tr>';
      }

      document.getElementById('modalDetailPaiements').style.display = 'block';
    })
    .catch(function(e) {
      console.error('Erreur détail:', e);
      alert('Erreur lors du chargement du détail.');
    });
}

function exporterCSV() {
  if (allLignes.length === 0) {
    alert('Aucune donnée à exporter. Générez d\'abord l\'état.');
    return;
  }

  var entetes = ['Référence', 'Objet', 'Structure', 'Service', 'Montant disponible',
    'Montant engagé', 'Montant consommé', 'Solde', 'Taux consommation (%)', 'Statut'];

  var lignes = allLignes.map(function(l) {
    return [l.reference, l.objet || '', l.structure, l.service,
      l.montant_disponible, l.montant_engage, l.montant_consomme,
      l.solde, l.taux_consommation, l.statut];
  });

  var totDisp = allLignes.reduce(function(s, l) { return s + l.montant_disponible; }, 0);
  var totEng = allLignes.reduce(function(s, l) { return s + l.montant_engage; }, 0);
  var totCons = allLignes.reduce(function(s, l) { return s + l.montant_consomme; }, 0);
  var totSolde = allLignes.reduce(function(s, l) { return s + l.solde; }, 0);
  lignes.push(['TOTAUX', '', '', '', totDisp, totEng, totCons, totSolde, '', '']);

  var echapper = function(v) { return '"' + String(v).replace(/"/g, '""') + '"'; };
  var csv = [entetes].concat(lignes)
    .map(function(ligne) { return ligne.map(echapper).join(';'); })
    .join('\r\n');

  var blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
  var url = URL.createObjectURL(blob);
  var lien = document.createElement('a');
  lien.href = url;
  lien.download = 'etat-credits-paie-' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(lien);
  lien.click();
  document.body.removeChild(lien);
  URL.revokeObjectURL(url);
}

function imprimerEtat() {
  if (allLignes.length === 0) {
    alert('Aucune donnée à imprimer. Générez d\'abord l\'état.');
    return;
  }

  var totDisp = allLignes.reduce(function(s, l) { return s + l.montant_disponible; }, 0);
  var totEng = allLignes.reduce(function(s, l) { return s + l.montant_engage; }, 0);
  var totCons = allLignes.reduce(function(s, l) { return s + l.montant_consomme; }, 0);
  var totSolde = allLignes.reduce(function(s, l) { return s + l.solde; }, 0);

  var html = '<html><head><title>État des crédits utilisés pour la paie</title>' +
    '<style>body{font-family:Arial,sans-serif;padding:20px;font-size:12px;}' +
    'h1{font-size:16px;color:#087f5b;text-align:center;margin-bottom:5px;}' +
    'h2{font-size:12px;color:#868e96;text-align:center;margin-bottom:20px;}' +
    'table{width:100%;border-collapse:collapse;margin-top:10px;}' +
    'th,td{border:1px solid #dee2e6;padding:6px 8px;text-align:left;font-size:11px;}' +
    'th{background:#087f5b;color:#fff;}' +
    'tfoot td{font-weight:bold;background:#f1f3f5;}' +
    '.right{text-align:right;}' +
    '.footer{margin-top:20px;font-size:10px;color:#868e96;text-align:center;}' +
    '</style></head><body>' +
    '<h1>ÉTAT DES CRÉDITS UTILISÉS POUR LA PAIE</h1>' +
    '<h2>Généré le ' + new Date().toLocaleDateString('fr-FR') + '</h2>' +
    '<table><thead><tr>' +
    '<th>Référence</th><th>Objet</th><th>Structure</th><th>Service</th>' +
    '<th>Disponible</th><th>Engagé</th><th>Consommé</th><th>Solde</th><th>Taux</th><th>Statut</th>' +
    '</tr></thead><tbody>';

  allLignes.forEach(function(l) {
    html += '<tr>' +
      '<td>' + l.reference + '</td>' +
      '<td>' + (l.objet || '-') + '</td>' +
      '<td>' + l.structure + '</td>' +
      '<td>' + l.service + '</td>' +
      '<td class="right">' + formatMontantSimple(l.montant_disponible) + '</td>' +
      '<td class="right">' + formatMontantSimple(l.montant_engage) + '</td>' +
      '<td class="right">' + formatMontantSimple(l.montant_consomme) + '</td>' +
      '<td class="right">' + formatMontantSimple(l.solde) + '</td>' +
      '<td class="right">' + l.taux_consommation + '%</td>' +
      '<td>' + l.statut + '</td>' +
      '</tr>';
  });

  html += '</tbody><tfoot><tr>' +
    '<td colspan="4">TOTAUX (' + allLignes.length + ' délégation(s))</td>' +
    '<td class="right">' + formatMontantSimple(totDisp) + '</td>' +
    '<td class="right">' + formatMontantSimple(totEng) + '</td>' +
    '<td class="right">' + formatMontantSimple(totCons) + '</td>' +
    '<td class="right">' + formatMontantSimple(totSolde) + '</td>' +
    '<td colspan="2"></td>' +
    '</tr></tfoot></table>' +
    '<div class="footer">SICORE — Système de gestion de la paie</div>' +
    '</body></html>';

  var win = window.open('', '_blank');
  win.document.write(html);
  win.document.close();
  win.print();
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function formatMontantSimple(val) {
  return new Intl.NumberFormat('fr-FR').format(val);
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  var d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR');
}
</script>
@endpush
