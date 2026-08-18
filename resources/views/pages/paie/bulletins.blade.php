@extends('layouts.app')

@section('title', 'SICORE - Bulletins de salaire')
@section('content')
<main class="main-content">
  <x-topbar
    title="Bulletins de salaire"
    subtitle="Gestion de la paie > Bulletins de salaire"
    icon="fa-solid fa-file-invoice-dollar"
    search-id="bulletinSearch"
    search-placeholder="Rechercher par nom, matricule, référence…"
    filter-target="#bulletinTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Générer automatiquement le bulletin de salaire d'un agent à partir des données de paie.</li>
        <li>Consulter, valider, exporter et imprimer les bulletins.</li>
      </ul>
    </section>

    <!-- Cartes statistiques -->
    <div class="stats-grid four">
      <article class="stat-card">
        <div>
          <p class="stat-label">Bulletins</p>
          <p class="stat-value" id="statTotal">0</p>
          <p class="stat-note">Total générés</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Validés</p>
          <p class="stat-value" id="statValides">0</p>
          <p class="stat-note" id="statValidesPct">0%</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total brut</p>
          <p class="stat-value" id="statBrut">0</p>
          <p class="stat-note">Masse salariale</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-coins" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total net</p>
          <p class="stat-value" id="statNet">0</p>
          <p class="stat-note">À payer</p>
        </div>
        <span class="stat-icon red"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i></span>
      </article>
    </div>

    <!-- Actions -->
    <div class="actions-row">
      <p class="breadcrumb">Gestion de la paie > Bulletins de salaire</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnNouveauBulletin">
          <i class="fa-solid fa-plus"></i> Générer un bulletin
        </button>
        <button class="btn-secondary" type="button" id="btnGenererMasse">
          <i class="fa-solid fa-users"></i> Génération en masse
        </button>
        <button class="btn-secondary" type="button" id="btnExporterCSV">
          <i class="fa-solid fa-download"></i> Exporter CSV
        </button>
      </div>
    </div>

    <!-- Filtres -->
    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filtreMois">Mois</label>
        <select class="form-control" id="filtreMois">
          <option value="">Tous</option>
          <option value="1">Janvier</option><option value="2">Février</option>
          <option value="3">Mars</option><option value="4">Avril</option>
          <option value="5">Mai</option><option value="6">Juin</option>
          <option value="7">Juillet</option><option value="8">Août</option>
          <option value="9">Septembre</option><option value="10">Octobre</option>
          <option value="11">Novembre</option><option value="12">Décembre</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreAnnee">Année</label>
        <select class="form-control" id="filtreAnnee">
          <option value="">Toutes</option>
          <option value="2026" selected>2026</option>
          <option value="2025">2025</option>
          <option value="2024">2024</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtreStatut">Statut</label>
        <select class="form-control" id="filtreStatut">
          <option value="">Tous</option>
          <option value="genere">Généré</option>
          <option value="valide">Validé</option>
          <option value="paye">Payé</option>
          <option value="annule">Annulé</option>
        </select>
      </div>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnFiltrer">Filtrer</button>
        <button class="btn-secondary" type="button" id="btnResetFiltres">Réinitialiser</button>
      </div>
    </section>

    <!-- Tableau des bulletins -->
    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="bulletinTable">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Agent</th>
              <th>Corps</th>
              <th>Période</th>
              <th>Brut</th>
              <th>Retenues</th>
              <th>Net</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="bulletinBody"></tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucun bulletin trouvé.</p>
      <div class="pagination" id="paginationControls"></div>
    </section>
  </section>
</main>

<!-- Modal Générer un bulletin -->
<div class="modal-overlay" id="modalGenerer" style="display:none;">
  <div class="modal-container" style="max-width:720px;">
    <div class="modal-header">
      <h3>Générer un bulletin de salaire</h3>
      <button class="modal-close" id="btnFermerGenerer">&times;</button>
    </div>
    <div class="modal-body">
      <!-- Étape 1 : Sélection agent + période -->
      <div id="genStep1">
        <div class="form-grid two-cols">
          <div class="form-group" style="grid-column:1/-1;">
            <label for="genSearch">Rechercher un agent</label>
            <input type="text" class="form-control" id="genSearch" placeholder="Nom ou prénom…">
            <div id="genAgentsList" class="agent-results" style="display:none;"></div>
          </div>
          <div class="form-group" id="genAgentInfo" style="display:none; grid-column:1/-1;">
            <div class="info-card" style="background:#f1f3f5; padding:12px; border-radius:8px;">
              <p><strong>Agent sélectionné :</strong> <span id="genAgentNom">-</span></p>
              <p><strong>Matricule :</strong> <span id="genAgentMat">-</span></p>
              <p><strong>Indice :</strong> <span id="genAgentIndice">-</span> | <strong>Corps :</strong> <span id="genAgentCorps">-</span></p>
              <input type="hidden" id="genEnseignantId">
            </div>
          </div>
          <div class="form-group">
            <label for="genMois">Mois</label>
            <select class="form-control" id="genMois">
              <option value="1">Janvier</option><option value="2">Février</option>
              <option value="3">Mars</option><option value="4">Avril</option>
              <option value="5">Mai</option><option value="6">Juin</option>
              <option value="7">Juillet</option><option value="8">Août</option>
              <option value="9">Septembre</option><option value="10">Octobre</option>
              <option value="11">Novembre</option><option value="12">Décembre</option>
            </select>
          </div>
          <div class="form-group">
            <label for="genAnnee">Année</label>
            <input type="number" class="form-control" id="genAnnee" value="2026" min="2020">
          </div>
        </div>
        <div class="actions-group" style="justify-content:flex-end; margin-top:16px;">
          <button class="btn-primary" type="button" id="btnApercu">Aperçu du bulletin</button>
        </div>
      </div>
      <!-- Étape 2 : Aperçu -->
      <div id="genStep2" style="display:none;">
        <h4 style="margin:0 0 12px; color:#087f5b;">Aperçu du bulletin</h4>
        <div class="table-responsive">
          <table class="table" id="apercuTable">
            <thead>
              <tr>
                <th>Rubrique</th>
                <th>Gains</th>
                <th>Retenues</th>
              </tr>
            </thead>
            <tbody id="apercuBody"></tbody>
            <tfoot id="apercuFooter"></tfoot>
          </table>
        </div>
        <div id="apercuPresence" style="display:none; margin-top:12px; padding:8px 12px; background:#fff3bf; border-radius:6px; font-size:0.9rem;"></div>
        <div class="actions-group" style="justify-content:flex-end; margin-top:16px;">
          <button class="btn-secondary" type="button" id="btnRetourStep1">Retour</button>
          <button class="btn-primary" type="button" id="btnConfirmerGenerer">Confirmer et générer</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Génération en masse -->
<div class="modal-overlay" id="modalMasse" style="display:none;">
  <div class="modal-container" style="max-width:500px;">
    <div class="modal-header">
      <h3>Génération en masse</h3>
      <button class="modal-close" id="btnFermerMasse">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin:0 0 16px; color:#495057; font-size:0.95rem;">Générer les bulletins pour tous les agents qui n'en ont pas encore pour la période sélectionnée.</p>
      <div class="form-grid two-cols">
        <div class="form-group">
          <label for="masseMois">Mois</label>
          <select class="form-control" id="masseMois">
            <option value="1">Janvier</option><option value="2">Février</option>
            <option value="3">Mars</option><option value="4">Avril</option>
            <option value="5">Mai</option><option value="6">Juin</option>
            <option value="7">Juillet</option><option value="8">Août</option>
            <option value="9">Septembre</option><option value="10">Octobre</option>
            <option value="11">Novembre</option><option value="12">Décembre</option>
          </select>
        </div>
        <div class="form-group">
          <label for="masseAnnee">Année</label>
          <input type="number" class="form-control" id="masseAnnee" value="2026" min="2020">
        </div>
      </div>
      <div id="masseResultat" style="display:none; margin-top:16px; padding:12px; border-radius:8px; background:#f1f3f5;"></div>
      <div class="actions-group" style="justify-content:flex-end; margin-top:16px;">
        <button class="btn-primary" type="button" id="btnLancerMasse">Lancer la génération</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Détail bulletin -->
<div class="modal-overlay" id="modalDetail" style="display:none;">
  <div class="modal-container" style="max-width:720px;">
    <div class="modal-header">
      <h3 id="detailTitle">Bulletin de salaire</h3>
      <button class="modal-close" id="btnFermerDetail">&times;</button>
    </div>
    <div class="modal-body" id="detailContent"></div>
    <div class="modal-footer" style="display:flex; gap:8px; justify-content:flex-end; padding:16px 24px; border-top:1px solid #e9ecef;">
      <button class="btn-secondary" type="button" id="btnImprimerBulletin"><i class="fa-solid fa-print"></i> Imprimer</button>
      <button class="btn-primary" type="button" id="btnValiderBulletin" style="display:none;"><i class="fa-solid fa-check"></i> Valider</button>
      <button class="btn-danger" type="button" id="btnAnnulerBulletin" style="display:none;"><i class="fa-solid fa-ban"></i> Annuler</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<style>
.agent-results { position:absolute; z-index:10; background:#fff; border:1px solid #dee2e6; border-radius:6px; max-height:200px; overflow-y:auto; width:100%; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.agent-results .agent-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f3f5; font-size:0.9rem; }
.agent-results .agent-item:hover { background:#e7f5ff; }
.agent-results .agent-item:last-child { border-bottom:none; }
.badge { display:inline-block; padding:4px 10px; border-radius:12px; font-size:0.78rem; font-weight:600; }
.badge-genere { background:#e7f5ff; color:#1971c2; }
.badge-valide { background:#d3f9d8; color:#2b8a3e; }
.badge-paye { background:#fff3bf; color:#e67700; }
.badge-annule { background:#ffe3e3; color:#c92a2a; }
.btn-danger { background:#e03131; color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-size:0.9rem; }
.btn-danger:hover { background:#c92a2a; }
.bulletin-print { font-family:'Segoe UI',sans-serif; }
.bulletin-print h4 { color:#087f5b; margin:16px 0 8px; }
.bulletin-print .info-row { display:flex; gap:24px; flex-wrap:wrap; margin-bottom:8px; }
.bulletin-print .info-row span { font-size:0.9rem; }
.form-group { position:relative; }
@media print {
  .main-content, .modal-overlay { display:none !important; }
  #modalDetail { display:flex !important; }
  #modalDetail .modal-container { box-shadow:none; max-width:100%; }
  #modalDetail .modal-footer { display:none !important; }
}
</style>
<script>
var API = 'http://127.0.0.1:8000/api';
var currentPage = 1;
var perPage = 10;
var totalPages = 1;
var currentBulletinId = null;
var searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
  loadBulletins();
  loadStats();
  setupEvents();
  document.getElementById('genMois').value = new Date().getMonth() + 1;
  document.getElementById('masseMois').value = new Date().getMonth() + 1;
});

function setupEvents() {
  document.getElementById('btnFiltrer').addEventListener('click', function() { currentPage = 1; loadBulletins(); loadStats(); });
  document.getElementById('btnResetFiltres').addEventListener('click', function() {
    document.getElementById('filtreMois').value = '';
    document.getElementById('filtreAnnee').value = '';
    document.getElementById('filtreStatut').value = '';
    currentPage = 1; loadBulletins(); loadStats();
  });
  document.getElementById('btnNouveauBulletin').addEventListener('click', function() {
    resetGenForm(); show('modalGenerer');
  });
  document.getElementById('btnFermerGenerer').addEventListener('click', function() { hide('modalGenerer'); });
  document.getElementById('btnGenererMasse').addEventListener('click', function() {
    document.getElementById('masseResultat').style.display = 'none';
    show('modalMasse');
  });
  document.getElementById('btnFermerMasse').addEventListener('click', function() { hide('modalMasse'); });
  document.getElementById('btnFermerDetail').addEventListener('click', function() { hide('modalDetail'); });

  document.getElementById('genSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    var q = this.value.trim();
    if (q.length < 2) { hide('genAgentsList'); return; }
    searchTimeout = setTimeout(function() { rechercherAgents(q); }, 300);
  });

  document.getElementById('btnApercu').addEventListener('click', genererApercu);
  document.getElementById('btnRetourStep1').addEventListener('click', function() {
    show('genStep1'); hide('genStep2');
  });
  document.getElementById('btnConfirmerGenerer').addEventListener('click', confirmerGeneration);
  document.getElementById('btnLancerMasse').addEventListener('click', lancerMasse);
  document.getElementById('btnImprimerBulletin').addEventListener('click', function() { window.print(); });
  document.getElementById('btnValiderBulletin').addEventListener('click', function() { changerStatut('valider'); });
  document.getElementById('btnAnnulerBulletin').addEventListener('click', function() { changerStatut('annuler'); });
  document.getElementById('btnExporterCSV').addEventListener('click', exporterCSV);

  var searchInput = document.getElementById('bulletinSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() { currentPage = 1; loadBulletins(); }, 400);
    });
  }
}

function show(id) { document.getElementById(id).style.display = ''; }
function hide(id) { document.getElementById(id).style.display = 'none'; }

function fmt(val) { return new Intl.NumberFormat('fr-FR').format(Math.round(val)) + ' FCFA'; }
function fmtCourt(val) {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
  if (val >= 1000) return Math.round(val / 1000) + 'K';
  return val.toString();
}

function badgeStatut(s) {
  var labels = { genere:'Généré', valide:'Validé', paye:'Payé', annule:'Annulé' };
  return '<span class="badge badge-' + s + '">' + (labels[s] || s) + '</span>';
}

function buildParams() {
  var p = [];
  var m = document.getElementById('filtreMois').value;
  var a = document.getElementById('filtreAnnee').value;
  var s = document.getElementById('filtreStatut').value;
  var q = document.getElementById('bulletinSearch');
  if (m) p.push('mois=' + m);
  if (a) p.push('annee=' + a);
  if (s) p.push('statut=' + s);
  if (q && q.value.trim()) p.push('search=' + encodeURIComponent(q.value.trim()));
  p.push('page=' + currentPage);
  p.push('per_page=' + perPage);
  return '?' + p.join('&');
}

function loadBulletins() {
  fetch(API + '/bulletins-salaire' + buildParams())
    .then(function(r) { return r.json(); })
    .then(function(resp) {
      var data = resp.data || [];
      var pag = resp.pagination || {};
      totalPages = pag.last_page || 1;
      currentPage = pag.current_page || 1;

      var tbody = document.getElementById('bulletinBody');
      var empty = document.getElementById('emptyMsg');

      if (data.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
        document.getElementById('paginationControls').innerHTML = '';
        return;
      }
      empty.style.display = 'none';

      tbody.innerHTML = data.map(function(b) {
        return '<tr>' +
          '<td>' + (b.reference || b.numero_ordre || '-') + '</td>' +
          '<td>' + b.nom + '</td>' +
          '<td>' + b.corps + '</td>' +
          '<td>' + (b.mois_validite || '-') + '</td>' +
          '<td>' + fmt(b.salaire_brut) + '</td>' +
          '<td>' + fmt(b.total_retenues) + '</td>' +
          '<td>' + fmt(b.net_a_payer) + '</td>' +
          '<td>' + badgeStatut(b.statut || 'genere') + '</td>' +
          '<td><button class="btn-icon" title="Voir" onclick="voirBulletin(' + b.id + ')"><i class="fa-solid fa-eye"></i></button></td>' +
          '</tr>';
      }).join('');

      renderPagination();
    })
    .catch(function(e) { console.error('Erreur chargement bulletins:', e); });
}

function loadStats() {
  var p = [];
  var m = document.getElementById('filtreMois').value;
  var a = document.getElementById('filtreAnnee').value;
  if (m) p.push('mois=' + m);
  if (a) p.push('annee=' + a);
  var qs = p.length ? '?' + p.join('&') : '';

  fetch(API + '/bulletins-salaire/statistiques' + qs)
    .then(function(r) { return r.json(); })
    .then(function(s) {
      document.getElementById('statTotal').textContent = s.total_bulletins;
      document.getElementById('statValides').textContent = s.valides;
      var pct = s.total_bulletins > 0 ? Math.round((s.valides / s.total_bulletins) * 100) : 0;
      document.getElementById('statValidesPct').textContent = pct + '% validés';
      document.getElementById('statBrut').textContent = fmtCourt(s.total_brut);
      document.getElementById('statNet').textContent = fmtCourt(s.total_net);
    })
    .catch(function(e) { console.error('Erreur stats:', e); });
}

function renderPagination() {
  var c = document.getElementById('paginationControls');
  if (totalPages <= 1) { c.innerHTML = ''; return; }
  var html = '<button class="page-btn" ' + (currentPage === 1 ? 'disabled' : '') +
    ' onclick="goPage(' + (currentPage - 1) + ')">&laquo;</button>';
  for (var i = 1; i <= totalPages; i++) {
    html += '<button class="page-btn' + (i === currentPage ? ' active' : '') +
      '" onclick="goPage(' + i + ')">' + i + '</button>';
  }
  html += '<button class="page-btn" ' + (currentPage === totalPages ? 'disabled' : '') +
    ' onclick="goPage(' + (currentPage + 1) + ')">&raquo;</button>';
  c.innerHTML = html;
}

function goPage(p) { currentPage = p; loadBulletins(); }

function rechercherAgents(q) {
  fetch(API + '/bulletins-salaire/agents?q=' + encodeURIComponent(q))
    .then(function(r) { return r.json(); })
    .then(function(agents) {
      var el = document.getElementById('genAgentsList');
      if (agents.length === 0) { el.innerHTML = '<div class="agent-item">Aucun agent trouvé</div>'; show('genAgentsList'); return; }
      el.innerHTML = agents.map(function(a) {
        return '<div class="agent-item" onclick="selectAgent(' + JSON.stringify(a).replace(/"/g, '&quot;') + ')">' +
          '<strong>' + a.nom + ' ' + a.prenom + '</strong> — ' + a.matricule +
          '<br><small>Indice: ' + a.indice + ' | ' + a.corps + '</small></div>';
      }).join('');
      show('genAgentsList');
    });
}

function selectAgent(agent) {
  document.getElementById('genEnseignantId').value = agent.id;
  document.getElementById('genAgentNom').textContent = agent.nom + ' ' + agent.prenom;
  document.getElementById('genAgentMat').textContent = agent.matricule;
  document.getElementById('genAgentIndice').textContent = agent.indice;
  document.getElementById('genAgentCorps').textContent = agent.corps;
  document.getElementById('genSearch').value = agent.nom + ' ' + agent.prenom;
  hide('genAgentsList');
  show('genAgentInfo');
}

function resetGenForm() {
  document.getElementById('genSearch').value = '';
  document.getElementById('genEnseignantId').value = '';
  hide('genAgentInfo');
  hide('genAgentsList');
  show('genStep1');
  hide('genStep2');
}

function genererApercu() {
  var enseignantId = document.getElementById('genEnseignantId').value;
  if (!enseignantId) { alert('Veuillez sélectionner un agent.'); return; }

  var btn = document.getElementById('btnApercu');
  btn.disabled = true; btn.textContent = 'Chargement…';

  fetch(API + '/bulletins-salaire/apercu', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      enseignant_id: parseInt(enseignantId),
      mois: parseInt(document.getElementById('genMois').value),
      annee: parseInt(document.getElementById('genAnnee').value),
    })
  })
  .then(function(r) {
    if (!r.ok) return r.json().then(function(e) { throw new Error(e.message || 'Erreur'); });
    return r.json();
  })
  .then(function(data) {
    afficherApercu(data);
    hide('genStep1');
    show('genStep2');
    btn.disabled = false; btn.textContent = 'Aperçu du bulletin';
  })
  .catch(function(e) {
    alert(e.message);
    btn.disabled = false; btn.textContent = 'Aperçu du bulletin';
  });
}

function afficherApercu(data) {
  var c = data.calcul;
  var tbody = document.getElementById('apercuBody');
  tbody.innerHTML = c.lignes.map(function(l) {
    return '<tr>' +
      '<td>' + l.rubrique + '</td>' +
      '<td>' + (l.type === 'gain' ? fmt(l.montant) : '') + '</td>' +
      '<td>' + (l.type === 'retenue' ? fmt(l.montant) : '') + '</td>' +
      '</tr>';
  }).join('');

  document.getElementById('apercuFooter').innerHTML =
    '<tr style="font-weight:bold; background:#f1f3f5;">' +
    '<td>TOTAUX</td><td>' + fmt(c.brut) + '</td><td>' + fmt(c.total_retenues) + '</td></tr>' +
    '<tr style="font-weight:bold; background:#d3f9d8;">' +
    '<td>NET À PAYER</td><td colspan="2" style="text-align:center; font-size:1.1rem;">' + fmt(c.net) + '</td></tr>';

  var presDiv = document.getElementById('apercuPresence');
  if (c.presence) {
    presDiv.innerHTML = '<strong>Présence :</strong> ' + c.presence.nombre_absences + ' absence(s), ' +
      c.presence.nombre_retards + ' retard(s)';
    presDiv.style.display = '';
  } else {
    presDiv.style.display = 'none';
  }
}

function confirmerGeneration() {
  var btn = document.getElementById('btnConfirmerGenerer');
  btn.disabled = true; btn.textContent = 'Génération…';

  fetch(API + '/bulletins-salaire/generer', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      enseignant_id: parseInt(document.getElementById('genEnseignantId').value),
      mois: parseInt(document.getElementById('genMois').value),
      annee: parseInt(document.getElementById('genAnnee').value),
    })
  })
  .then(function(r) {
    if (!r.ok) return r.json().then(function(e) { throw new Error(e.message || 'Erreur'); });
    return r.json();
  })
  .then(function(data) {
    alert(data.message);
    hide('modalGenerer');
    loadBulletins(); loadStats();
    btn.disabled = false; btn.textContent = 'Confirmer et générer';
  })
  .catch(function(e) {
    alert(e.message);
    btn.disabled = false; btn.textContent = 'Confirmer et générer';
  });
}

function lancerMasse() {
  var btn = document.getElementById('btnLancerMasse');
  btn.disabled = true; btn.textContent = 'En cours…';

  fetch(API + '/bulletins-salaire/generer-masse', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      mois: parseInt(document.getElementById('masseMois').value),
      annee: parseInt(document.getElementById('masseAnnee').value),
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    var div = document.getElementById('masseResultat');
    var html = '<p><strong>' + data.message + '</strong></p>';
    if (data.erreurs && data.erreurs.length > 0) {
      html += '<ul style="color:#c92a2a; margin:8px 0 0; padding-left:20px;">';
      data.erreurs.forEach(function(e) { html += '<li>' + e + '</li>'; });
      html += '</ul>';
    }
    div.innerHTML = html;
    div.style.display = '';
    loadBulletins(); loadStats();
    btn.disabled = false; btn.textContent = 'Lancer la génération';
  })
  .catch(function(e) {
    alert('Erreur: ' + e.message);
    btn.disabled = false; btn.textContent = 'Lancer la génération';
  });
}

function voirBulletin(id) {
  currentBulletinId = id;
  fetch(API + '/bulletins-salaire/' + id)
    .then(function(r) { return r.json(); })
    .then(function(b) {
      document.getElementById('detailTitle').textContent = 'Bulletin ' + (b.reference || '');

      var html = '<div class="bulletin-print">' +
        '<h4>Informations agent</h4>' +
        '<div class="info-row">' +
        '<span><strong>Nom :</strong> ' + b.nom + '</span>' +
        '<span><strong>Matricule :</strong> ' + b.matricule + '</span>' +
        '<span><strong>Corps :</strong> ' + b.corps + '</span>' +
        '<span><strong>Indice :</strong> ' + (b.enseignant?.indice || '-') + '</span>' +
        '</div>' +
        '<div class="info-row">' +
        '<span><strong>Période :</strong> ' + (b.mois_validite || '-') + '</span>' +
        '<span><strong>IA :</strong> ' + b.ia + '</span>' +
        '<span><strong>Date :</strong> ' + (b.date_enregistrement || '-') + '</span>' +
        '</div>' +
        '<h4>Détail du bulletin</h4>' +
        '<table class="table"><thead><tr><th>Rubrique</th><th>Gains</th><th>Retenues</th></tr></thead><tbody>';

      (b.gains || []).forEach(function(g) {
        html += '<tr><td>' + g.rubrique + '</td><td>' + fmt(g.montant) + '</td><td></td></tr>';
      });
      (b.retenues || []).forEach(function(r) {
        html += '<tr><td>' + r.rubrique + '</td><td></td><td>' + fmt(r.montant) + '</td></tr>';
      });

      html += '</tbody><tfoot>' +
        '<tr style="font-weight:bold; background:#f1f3f5;"><td>TOTAUX</td><td>' + fmt(b.salaire_brut) + '</td><td>' + fmt(b.total_retenues) + '</td></tr>' +
        '<tr style="font-weight:bold; background:#d3f9d8;"><td>NET À PAYER</td><td colspan="2" style="text-align:center; font-size:1.15rem;">' + fmt(b.net_a_payer) + '</td></tr>' +
        '</tfoot></table></div>';

      document.getElementById('detailContent').innerHTML = html;

      var btnVal = document.getElementById('btnValiderBulletin');
      var btnAnn = document.getElementById('btnAnnulerBulletin');
      btnVal.style.display = b.statut === 'genere' ? '' : 'none';
      btnAnn.style.display = (b.statut === 'genere' || b.statut === 'valide') ? '' : 'none';

      show('modalDetail');
    });
}

function changerStatut(action) {
  if (!currentBulletinId) return;
  var msg = action === 'valider' ? 'Valider ce bulletin ?' : 'Annuler ce bulletin ?';
  if (!confirm(msg)) return;

  fetch(API + '/bulletins-salaire/' + currentBulletinId + '/' + action, {
    method: 'PUT',
    headers: { 'Accept': 'application/json' },
  })
  .then(function(r) {
    if (!r.ok) return r.json().then(function(e) { throw new Error(e.message || 'Erreur'); });
    return r.json();
  })
  .then(function(data) {
    alert(data.message);
    hide('modalDetail');
    loadBulletins(); loadStats();
  })
  .catch(function(e) { alert(e.message); });
}

function exporterCSV() {
  var rows = document.querySelectorAll('#bulletinBody tr');
  if (rows.length === 0) { alert('Aucune donnée à exporter.'); return; }

  var entetes = ['Référence', 'Agent', 'Corps', 'Période', 'Brut', 'Retenues', 'Net', 'Statut'];
  var lignes = [];
  rows.forEach(function(tr) {
    var cells = tr.querySelectorAll('td');
    var ligne = [];
    for (var i = 0; i < cells.length - 1; i++) {
      ligne.push(cells[i].textContent.trim());
    }
    lignes.push(ligne);
  });

  var esc = function(v) { return '"' + String(v).replace(/"/g, '""') + '"'; };
  var csv = [entetes].concat(lignes)
    .map(function(l) { return l.map(esc).join(';'); })
    .join('\r\n');

  var blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
  var url = URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.href = url;
  a.download = 'bulletins-salaire-' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
</script>
@endpush
