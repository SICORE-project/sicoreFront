@extends('layouts.app')

@section('title', 'SICORE - Édition des engagements')
@section('content')
<main class="main-content">
  <x-topbar
    title="Édition des engagements par délégation"
    subtitle="Gestion de la paie > Édition des engagements"
    icon="fa-solid fa-clipboard-check"
    search-id="engagementSearch"
    search-placeholder="Rechercher…"
    filter-target="#engagementTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Consulter les engagements pris sur les crédits délégués, ligne par ligne.</li>
        <li>Suivre, pour chaque N° de carton, le montant délégué, le montant engagé et le reste.</li>
      </ul>
    </section>

    <!-- Filtres : période comptable de frmEditEngDelegation.aspx -->
    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filtreAnnee">Année académique</label>
        <select class="form-control" id="filtreAnnee">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtrePeriode">Période de paie</label>
        <select class="form-control" id="filtrePeriode">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreCorps">Corps d'enseignant</label>
        <select class="form-control" id="filtreCorps">
          <option value="">Tous</option>
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
        <label for="filtreCarton">N° Carton</label>
        <input class="form-control" id="filtreCarton" type="text" placeholder="ex. 25-DC1823">
      </div>
      <div class="form-group">
        <label for="filtreType">Type d'édition</label>
        <select class="form-control" id="filtreType">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnConsulter">Consulter</button>
        <button class="btn-secondary" type="button" id="btnResetFiltres">Réinitialiser</button>
      </div>
    </section>

    <!-- Cartes résumé -->
    <div class="stats-grid four" id="statsPanel" style="display:none;">
      <article class="stat-card">
        <div>
          <p class="stat-label">Lignes</p>
          <p class="stat-value" id="statLignes">0</p>
          <p class="stat-note" id="statDelegations">0 délégation(s)</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-list" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total délégué</p>
          <p class="stat-value" id="statMontant">0</p>
          <p class="stat-note">Crédits mis à disposition</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-coins" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total engagé</p>
          <p class="stat-value" id="statEngagement">0</p>
          <p class="stat-note" id="statTaux">0 % du délégué</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-file-signature" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Reste à engager</p>
          <p class="stat-value" id="statReste">0</p>
          <p class="stat-note">Délégué − engagé</p>
        </div>
        <span class="stat-icon red"><i class="fa-solid fa-scale-unbalanced" aria-hidden="true"></i></span>
      </article>
    </div>

    <!-- Actions -->
    <div class="actions-row" id="actionsPanel" style="display:none;">
      <p class="breadcrumb">Gestion de la paie > Édition des engagements</p>
      <div class="actions-group">
        <button class="btn-secondary" type="button" id="btnExporter">Exporter CSV</button>
        <button class="btn-secondary" type="button" id="btnImprimer">Imprimer</button>
      </div>
    </div>

    <!-- Grille des engagements, calquée sur frmDetailDelegation.aspx -->
    <section class="table-card" id="detailPanel" style="display:none;">
      <h3 style="margin:0 0 16px 0; color:#087f5b; font-size:1.1rem;">Engagements par carton</h3>
      <div class="table-responsive">
        <table class="table" id="engagementTable">
          <thead>
            <tr>
              <th>N° Carton</th>
              <th>Montant</th>
              <th>Engagement</th>
              <th>Reste</th>
              <th>Taux</th>
              <th>N° Autorisation</th>
              <th>Corps</th>
              <th>IA</th>
              <th>IEF</th>
            </tr>
          </thead>
          <tbody id="detailBody"></tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucune donnée trouvée.</p>
      <div class="pagination" id="paginationControls"></div>
    </section>

    <!-- Récapitulatif par délégation : ruptures d'état -->
    <section class="table-card" id="recapPanel" style="display:none;">
      <h3 style="margin:0 0 16px 0; color:#087f5b; font-size:1.1rem;">Récapitulatif par délégation</h3>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Objet</th>
              <th>Période</th>
              <th>Lignes</th>
              <th>Montant</th>
              <th>Engagement</th>
              <th>Reste</th>
              <th>Taux</th>
            </tr>
          </thead>
          <tbody id="recapBody"></tbody>
        </table>
      </div>
    </section>
  </section>
</main>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';
var currentPage = 1;
var lignesPage = [];

document.addEventListener('DOMContentLoaded', function() {
  chargerFiltres();
  setupEvents();
});

function chargerFiltres() {
  fetch(API + '/edition-engagements/filtres')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      remplir('filtreAnnee', data.annees_academiques, function(a) {
        return { value: a, label: a };
      });
      remplir('filtrePeriode', data.periodes_paie, function(p) {
        return { value: p, label: p };
      });
      remplir('filtreCorps', data.corps_enseignants, function(c) {
        return { value: c.id, label: c.libelle };
      });
      remplir('filtreIA', data.ias, function(ia) {
        return { value: ia.id, label: ia.code + ' - ' + ia.libelle };
      });
      remplir('filtreType', data.types, function(t) {
        return { value: t.value, label: t.label };
      });
    })
    .catch(function(e) {
      console.error('Erreur chargement filtres:', e);
    });
}

function remplir(selectId, items, mapper) {
  var sel = document.getElementById(selectId);
  (items || []).forEach(function(item) {
    var o = mapper(item);
    sel.innerHTML += '<option value="' + o.value + '">' + o.label + '</option>';
  });
}

function setupEvents() {
  // Cascade IA -> IEF
  document.getElementById('filtreIA').addEventListener('change', function() {
    var iaId = this.value;
    var selIEF = document.getElementById('filtreIEF');
    selIEF.innerHTML = '<option value="">Toutes</option>';
    if (!iaId) return;

    fetch(API + '/edition-engagements/iefs/' + iaId)
      .then(function(r) { return r.json(); })
      .then(function(iefs) {
        iefs.forEach(function(ief) {
          selIEF.innerHTML += '<option value="' + ief.id + '">' + ief.libelle + '</option>';
        });
      });
  });

  document.getElementById('btnConsulter').addEventListener('click', function() {
    currentPage = 1;
    consulter();
  });

  document.getElementById('btnResetFiltres').addEventListener('click', function() {
    ['filtreAnnee', 'filtrePeriode', 'filtreCorps', 'filtreIA', 'filtreType'].forEach(function(id) {
      document.getElementById(id).value = '';
    });
    document.getElementById('filtreCarton').value = '';
    document.getElementById('filtreIEF').innerHTML = '<option value="">Toutes</option>';
    ['statsPanel', 'actionsPanel', 'detailPanel', 'recapPanel'].forEach(function(id) {
      document.getElementById(id).style.display = 'none';
    });
  });

  document.getElementById('btnExporter').addEventListener('click', exporterCSV);
  document.getElementById('btnImprimer').addEventListener('click', function() { window.print(); });
}

function buildParams(perPage) {
  var params = [];
  var champs = {
    annee_academique: document.getElementById('filtreAnnee').value,
    periode_paie: document.getElementById('filtrePeriode').value,
    corps_enseignant_id: document.getElementById('filtreCorps').value,
    ia_id: document.getElementById('filtreIA').value,
    ief_id: document.getElementById('filtreIEF').value,
    numero_carton: document.getElementById('filtreCarton').value,
    type: document.getElementById('filtreType').value
  };

  Object.keys(champs).forEach(function(cle) {
    if (champs[cle]) params.push(cle + '=' + encodeURIComponent(champs[cle]));
  });

  if (perPage) params.push('per_page=' + perPage);
  if (currentPage > 1) params.push('page=' + currentPage);

  return params.length ? '?' + params.join('&') : '';
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function formatMontantCourt(val) {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
  if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
  return String(val);
}

function consulter() {
  var btn = document.getElementById('btnConsulter');
  btn.disabled = true;
  btn.textContent = 'Chargement...';

  fetch(API + '/edition-engagements' + buildParams())
    .then(function(r) { return r.json(); })
    .then(function(data) {
      afficherStats(data.totaux);
      afficherLignes(data.lignes, data.pagination);
      afficherRecap(data.recapitulatif);

      ['statsPanel', 'actionsPanel', 'detailPanel', 'recapPanel'].forEach(function(id) {
        document.getElementById(id).style.display = '';
      });

      btn.disabled = false;
      btn.textContent = 'Consulter';
    })
    .catch(function(e) {
      console.error('Erreur:', e);
      alert('Erreur lors de la consultation de l\'état.');
      btn.disabled = false;
      btn.textContent = 'Consulter';
    });
}

function afficherStats(t) {
  document.getElementById('statLignes').textContent = t.nombre_lignes;
  document.getElementById('statDelegations').textContent = t.nombre_delegations + ' délégation(s)';
  document.getElementById('statMontant').textContent = formatMontantCourt(t.total_montant);
  document.getElementById('statEngagement').textContent = formatMontantCourt(t.total_engagement);
  document.getElementById('statTaux').textContent = t.taux_engagement + ' % du délégué';
  document.getElementById('statReste').textContent = formatMontantCourt(t.total_reste);
}

function afficherLignes(lignes, pagination) {
  lignesPage = lignes || [];
  var tbody = document.getElementById('detailBody');
  var empty = document.getElementById('emptyMsg');

  if (lignesPage.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    document.getElementById('paginationControls').innerHTML = '';
    return;
  }
  empty.style.display = 'none';

  tbody.innerHTML = lignesPage.map(function(l) {
    return '<tr>' +
      '<td>' + (l.numero_carton || '-') + '</td>' +
      '<td>' + formatMontant(l.montant) + '</td>' +
      '<td>' + formatMontant(l.engagement) + '</td>' +
      '<td>' + formatMontant(l.reste) + '</td>' +
      '<td>' + l.taux_engagement + ' %</td>' +
      '<td>' + (l.numero_autorisation || '-') + '</td>' +
      '<td>' + (l.corps_enseignant || '-') + '</td>' +
      '<td>' + (l.ia || '-') + '</td>' +
      '<td>' + (l.ief || '-') + '</td>' +
      '</tr>';
  }).join('');

  renderPagination(pagination);
}

function afficherRecap(recap) {
  document.getElementById('recapBody').innerHTML = (recap || []).map(function(r) {
    return '<tr>' +
      '<td>' + (r.reference || '-') + '</td>' +
      '<td>' + (r.objet || '-') + '</td>' +
      '<td>' + (r.periode_paie || '-') + '</td>' +
      '<td>' + r.nombre_lignes + '</td>' +
      '<td>' + formatMontant(r.total_montant) + '</td>' +
      '<td>' + formatMontant(r.total_engagement) + '</td>' +
      '<td>' + formatMontant(r.total_reste) + '</td>' +
      '<td>' + r.taux_engagement + ' %</td>' +
      '</tr>';
  }).join('');
}

// Pagination serveur : chaque page est une requete, les totaux restent
// calcules sur l'integralite du perimetre filtre.
function renderPagination(p) {
  var container = document.getElementById('paginationControls');
  if (!p || p.last_page <= 1) { container.innerHTML = ''; return; }

  var html = '<button class="page-btn" ' + (p.current_page === 1 ? 'disabled' : '') +
    ' onclick="allerPage(' + (p.current_page - 1) + ')">&laquo;</button>';

  for (var i = 1; i <= p.last_page; i++) {
    html += '<button class="page-btn' + (i === p.current_page ? ' active' : '') +
      '" onclick="allerPage(' + i + ')">' + i + '</button>';
  }

  html += '<button class="page-btn" ' + (p.current_page === p.last_page ? 'disabled' : '') +
    ' onclick="allerPage(' + (p.current_page + 1) + ')">&raquo;</button>';

  container.innerHTML = html;
}

function allerPage(page) {
  currentPage = page;
  consulter();
}

// L'export porte sur tout le perimetre filtre, pas sur la page affichee :
// on redemande l'etat complet au serveur.
function exporterCSV() {
  var btn = document.getElementById('btnExporter');
  btn.disabled = true;
  btn.textContent = 'Export...';

  var pageAvant = currentPage;
  currentPage = 1;
  var url = API + '/edition-engagements' + buildParams(1000);
  currentPage = pageAvant;

  fetch(url)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var lignes = data.lignes || [];
      if (lignes.length === 0) { alert('Aucune donnée à exporter.'); return; }

      var entetes = ['N° Carton', 'Montant', 'Engagement', 'Reste', 'Taux %',
                     'N° Autorisation', 'Corps', 'IA', 'IEF', 'Référence', 'Période'];

      var corps = lignes.map(function(l) {
        return [l.numero_carton, l.montant, l.engagement, l.reste, l.taux_engagement,
                l.numero_autorisation, l.corps_enseignant, l.ia, l.ief,
                l.reference, l.periode_paie];
      });

      var echapper = function(v) { return '"' + String(v === null ? '' : v).replace(/"/g, '""') + '"'; };
      var csv = [entetes].concat(corps)
        .map(function(ligne) { return ligne.map(echapper).join(';'); })
        .join('\r\n');

      var blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
      var lien = document.createElement('a');
      lien.href = URL.createObjectURL(blob);
      lien.download = 'etat-engagements-' + new Date().toISOString().slice(0, 10) + '.csv';
      document.body.appendChild(lien);
      lien.click();
      document.body.removeChild(lien);
      URL.revokeObjectURL(lien.href);

      if (data.pagination && data.pagination.total > lignes.length) {
        alert('Export limité à ' + lignes.length + ' lignes sur ' + data.pagination.total +
              '. Affinez les filtres pour exporter le reste.');
      }
    })
    .catch(function(e) {
      console.error('Erreur export:', e);
      alert('Erreur lors de l\'export.');
    })
    .finally(function() {
      btn.disabled = false;
      btn.textContent = 'Exporter CSV';
    });
}
</script>
@endpush
