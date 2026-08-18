@extends('layouts.app')

@section('title', 'SICORE - Édition des engagements')
@section('content')
<main class="main-content">
  <x-topbar
    title="Édition des engagements"
    subtitle="Gestion de la paie > Édition des engagements"
    icon="fa-solid fa-file-invoice-dollar"
    search-id="engagementSearch"
    search-placeholder="Rechercher…"
    filter-target="#engagementTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Consulter l'état des engagements par exercice, période et structure.</li>
        <li>Comparer les montants engagés avec la paie calculée.</li>
      </ul>
    </section>

    <!-- Filtres -->
    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filtreExercice">Exercice</label>
        <select class="form-control" id="filtreExercice">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtrePeriode">Période</label>
        <select class="form-control" id="filtrePeriode">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreIA">IA</label>
        <select class="form-control" id="filtreIA">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreIEF">IEF</label>
        <select class="form-control" id="filtreIEF">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreEtablissement">Établissement</label>
        <select class="form-control" id="filtreEtablissement">
          <option value="">Tous</option>
        </select>
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
          <p class="stat-label">Agents</p>
          <p class="stat-value" id="statAgents">0</p>
          <p class="stat-note">Nombre d'agents</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total engagé</p>
          <p class="stat-value" id="statEngage">0</p>
          <p class="stat-note">Montants engagés</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-coins" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total net payé</p>
          <p class="stat-value" id="statNet">0</p>
          <p class="stat-note">Paie calculée</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Écart</p>
          <p class="stat-value" id="statEcart">0</p>
          <p class="stat-note">Engagé − Payé</p>
        </div>
        <span class="stat-icon red"><i class="fa-solid fa-scale-unbalanced" aria-hidden="true"></i></span>
      </article>
    </div>

    <!-- Synthèse engagements vs paie -->
    <section class="table-card" id="synthesePanel" style="display:none;">
      <h3 style="margin:0 0 16px 0; color:#087f5b; font-size:1.1rem;">Synthèse engagements vs paie</h3>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Indicateur</th>
              <th>Engagements (délégations)</th>
              <th>Paie (bulletins)</th>
            </tr>
          </thead>
          <tbody id="syntheseBody"></tbody>
        </table>
      </div>
    </section>

    <!-- Actions -->
    <div class="actions-row" id="actionsPanel" style="display:none;">
      <p class="breadcrumb">Gestion de la paie > Édition des engagements</p>
      <div class="actions-group">
        <button class="btn-secondary" type="button" id="btnExporter">Exporter CSV</button>
        <button class="btn-secondary" type="button" id="btnImprimer">Imprimer</button>
      </div>
    </div>

    <!-- Détail par agent -->
    <section class="table-card" id="detailPanel" style="display:none;">
      <h3 style="margin:0 0 16px 0; color:#087f5b; font-size:1.1rem;">Détail par agent</h3>
      <div class="table-responsive">
        <table class="table" id="engagementTable">
          <thead>
            <tr>
              <th>Matricule</th>
              <th>Nom</th>
              <th>IA</th>
              <th>Période</th>
              <th>Brut</th>
              <th>Retenues</th>
              <th>Net</th>
              <th>Charges employeur</th>
            </tr>
          </thead>
          <tbody id="detailBody"></tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucune donnée trouvée.</p>
      <div class="pagination" id="paginationControls"></div>
    </section>
  </section>
</main>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';
var allLignes = [];
var currentPage = 1;
var perPage = 10;

document.addEventListener('DOMContentLoaded', function() {
  chargerFiltres();
  setupEvents();
});

function chargerFiltres() {
  fetch(API + '/edition-engagements/filtres')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var selExercice = document.getElementById('filtreExercice');
      (data.annees_academiques || []).forEach(function(a) {
        selExercice.innerHTML += '<option value="' + a + '">' + a + '</option>';
      });

      var selPeriode = document.getElementById('filtrePeriode');
      (data.periodes || []).forEach(function(p) {
        selPeriode.innerHTML += '<option value="' + p + '">' + p + '</option>';
      });

      var selIA = document.getElementById('filtreIA');
      (data.ias || []).forEach(function(ia) {
        selIA.innerHTML += '<option value="' + ia.id + '">' + ia.libelle + '</option>';
      });
    })
    .catch(function(e) {
      console.error('Erreur chargement filtres:', e);
    });
}

function setupEvents() {
  document.getElementById('filtreIA').addEventListener('change', function() {
    var iaId = this.value;
    var selIEF = document.getElementById('filtreIEF');
    var selEtab = document.getElementById('filtreEtablissement');
    selIEF.innerHTML = '<option value="">Toutes</option>';
    selEtab.innerHTML = '<option value="">Tous</option>';
    if (!iaId) return;

    fetch(API + '/edition-engagements/iefs/' + iaId)
      .then(function(r) { return r.json(); })
      .then(function(iefs) {
        iefs.forEach(function(ief) {
          selIEF.innerHTML += '<option value="' + ief.id + '">' + ief.libelle + '</option>';
        });
      });
  });

  document.getElementById('filtreIEF').addEventListener('change', function() {
    var iefId = this.value;
    var selEtab = document.getElementById('filtreEtablissement');
    selEtab.innerHTML = '<option value="">Tous</option>';
    if (!iefId) return;

    fetch(API + '/edition-engagements/etablissements/' + iefId)
      .then(function(r) { return r.json(); })
      .then(function(etabs) {
        etabs.forEach(function(e) {
          selEtab.innerHTML += '<option value="' + e.id + '">' + e.libelle + '</option>';
        });
      });
  });

  document.getElementById('btnGenerer').addEventListener('click', genererEtat);

  document.getElementById('btnResetFiltres').addEventListener('click', function() {
    document.getElementById('filtreExercice').value = '';
    document.getElementById('filtrePeriode').value = '';
    document.getElementById('filtreIA').value = '';
    document.getElementById('filtreIEF').innerHTML = '<option value="">Toutes</option>';
    document.getElementById('filtreEtablissement').innerHTML = '<option value="">Tous</option>';
    ['statsPanel', 'synthesePanel', 'actionsPanel', 'detailPanel'].forEach(function(id) {
      document.getElementById(id).style.display = 'none';
    });
  });

  document.getElementById('btnExporter').addEventListener('click', exporterCSV);
  document.getElementById('btnImprimer').addEventListener('click', imprimerEtat);
}

function buildParams() {
  var params = [];
  var exercice = document.getElementById('filtreExercice').value;
  var periode = document.getElementById('filtrePeriode').value;
  var iaId = document.getElementById('filtreIA').value;
  var iefId = document.getElementById('filtreIEF').value;
  var etabId = document.getElementById('filtreEtablissement').value;

  if (exercice) params.push('annee_academique=' + encodeURIComponent(exercice));
  if (periode) params.push('periode=' + encodeURIComponent(periode));
  if (iaId) params.push('ia_id=' + iaId);
  if (iefId) params.push('ief_id=' + iefId);
  if (etabId) params.push('etablissement_id=' + etabId);

  return params.length ? '?' + params.join('&') : '';
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function formatMontantCourt(val) {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
  if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
  return val.toString();
}

function genererEtat() {
  var btn = document.getElementById('btnGenerer');
  btn.disabled = true;
  btn.textContent = 'Chargement...';
  var params = buildParams();

  Promise.all([
    fetch(API + '/edition-engagements' + params).then(function(r) { return r.json(); }),
    fetch(API + '/edition-engagements/details' + params).then(function(r) { return r.json(); })
  ])
  .then(function(results) {
    var synthese = results[0];
    var details = results[1];

    afficherStats(synthese);
    afficherSynthese(synthese);
    afficherDetails(details);

    ['statsPanel', 'synthesePanel', 'actionsPanel', 'detailPanel'].forEach(function(id) {
      document.getElementById(id).style.display = '';
    });

    btn.disabled = false;
    btn.textContent = 'Générer l\'état';
  })
  .catch(function(e) {
    console.error('Erreur:', e);
    alert('Erreur lors de la génération de l\'état.');
    btn.disabled = false;
    btn.textContent = 'Générer l\'état';
  });
}

function afficherStats(data) {
  document.getElementById('statAgents').textContent = data.paie.nombre_agents;
  document.getElementById('statEngage').textContent = formatMontantCourt(data.engagements.total_engage);
  document.getElementById('statNet').textContent = formatMontantCourt(data.paie.total_net);

  var ecart = data.comparaison.ecart;
  var elEcart = document.getElementById('statEcart');
  elEcart.textContent = formatMontantCourt(Math.abs(ecart));
  elEcart.style.color = ecart >= 0 ? '#087f5b' : '#e03131';
}

function afficherSynthese(data) {
  var eng = data.engagements;
  var paie = data.paie;

  document.getElementById('syntheseBody').innerHTML =
    '<tr><td>Nombre</td><td>' + eng.nombre_delegations + ' délégation(s)</td><td>' + paie.nombre_agents + ' agent(s)</td></tr>' +
    '<tr><td>Montant brut</td><td>-</td><td>' + formatMontant(paie.total_brut) + '</td></tr>' +
    '<tr><td>Retenues</td><td>-</td><td>' + formatMontant(paie.total_retenues) + '</td></tr>' +
    '<tr><td>Net</td><td>-</td><td>' + formatMontant(paie.total_net) + '</td></tr>' +
    '<tr><td>Charges employeur</td><td>-</td><td>' + formatMontant(paie.charges_employeur) + '</td></tr>' +
    '<tr><td>Total engagé</td><td>' + formatMontant(eng.total_engage) + '</td><td>-</td></tr>' +
    '<tr><td>Total consommé</td><td>' + formatMontant(eng.total_consomme) + '</td><td>-</td></tr>' +
    '<tr><td>Solde restant</td><td>' + formatMontant(eng.total_solde) + '</td><td>-</td></tr>' +
    '<tr style="font-weight:bold; background:#f1f3f5;"><td>Écart (Engagé − Net payé)</td><td colspan="2" style="text-align:center; color:' +
    (data.comparaison.ecart >= 0 ? '#087f5b' : '#e03131') + ';">' +
    formatMontant(data.comparaison.ecart) + '</td></tr>';
}

function afficherDetails(data) {
  allLignes = data.lignes || [];
  currentPage = 1;
  renderDetailTable();
}

function renderDetailTable() {
  var tbody = document.getElementById('detailBody');
  var empty = document.getElementById('emptyMsg');
  var totalPages = Math.ceil(allLignes.length / perPage);
  if (currentPage > totalPages) currentPage = totalPages || 1;

  if (allLignes.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    document.getElementById('paginationControls').innerHTML = '';
    return;
  }
  empty.style.display = 'none';

  var start = (currentPage - 1) * perPage;
  var pageData = allLignes.slice(start, start + perPage);

  tbody.innerHTML = pageData.map(function(l) {
    return '<tr>' +
      '<td>' + l.matricule + '</td>' +
      '<td>' + l.nom + '</td>' +
      '<td>' + l.ia + '</td>' +
      '<td>' + (l.periode || '-') + '</td>' +
      '<td>' + formatMontant(l.brut) + '</td>' +
      '<td>' + formatMontant(l.retenues) + '</td>' +
      '<td>' + formatMontant(l.net) + '</td>' +
      '<td>' + formatMontant(l.charges_employeur) + '</td>' +
      '</tr>';
  }).join('');

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  var container = document.getElementById('paginationControls');
  if (totalPages <= 1) { container.innerHTML = ''; return; }
  var html = '<button class="page-btn" ' + (currentPage === 1 ? 'disabled' : '') +
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
  renderDetailTable();
}

function exporterCSV() {
  if (allLignes.length === 0) { alert('Aucune donnée à exporter.'); return; }

  var entetes = ['Matricule', 'Nom', 'IA', 'Période', 'Brut', 'Retenues', 'Net', 'Charges employeur'];
  var lignes = allLignes.map(function(l) {
    return [l.matricule, l.nom, l.ia, l.periode, l.brut, l.retenues, l.net, l.charges_employeur];
  });

  var echapper = function(v) { return '"' + String(v).replace(/"/g, '""') + '"'; };
  var csv = [entetes].concat(lignes)
    .map(function(ligne) { return ligne.map(echapper).join(';'); })
    .join('\r\n');

  var blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
  var url = URL.createObjectURL(blob);
  var lien = document.createElement('a');
  lien.href = url;
  lien.download = 'etat-engagements-' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(lien);
  lien.click();
  document.body.removeChild(lien);
  URL.revokeObjectURL(url);
}

function imprimerEtat() {
  window.print();
}
</script>
@endpush
