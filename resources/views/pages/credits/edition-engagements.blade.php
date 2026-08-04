@extends('layouts.app')

@section('title', 'SICORE - Édition des engagements')
@section('content')
<main class="main-content">
  <x-topbar
    title="Édition des engagements"
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
        <li>Associer les paiements de salaires à une délégation de crédit.</li>
        <li>Générer l'état des crédits utilisés pour la paie.</li>
      </ul>
    </section>

    <div class="stats-grid four">
      <article class="stat-card">
        <div>
          <p class="stat-label">Paiements</p>
          <p class="stat-value" id="statPaiements">0</p>
          <p class="stat-note">Total enregistrés</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total payé</p>
          <p class="stat-value" id="statTotalPaye">0</p>
          <p class="stat-note">FCFA</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Délégations utilisées</p>
          <p class="stat-value" id="statDelegationsUtilisees">0</p>
          <p class="stat-note">Avec paiements</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Solde total</p>
          <p class="stat-value" id="statSoldeTotal">0</p>
          <p class="stat-note">FCFA restants</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></span>
      </article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb">Gestion de la paie > Édition des engagements</p>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnNouveauPaiement">Nouveau paiement</button>
        <button class="btn-secondary" type="button" id="btnExportPDF">Export PDF</button>
      </div>
    </div>

    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filterDelegation">Délégation</label>
        <select class="form-control" id="filterDelegation">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="actions-group">
        <button class="btn-secondary" type="button" id="btnFiltrer">Filtrer</button>
      </div>
    </section>

    <!-- État des crédits par délégation -->
    <section class="table-card" id="etatSection">
      <h3 style="padding:16px 16px 0; color:#087f5b;">État des crédits utilisés pour la paie</h3>
      <div class="table-responsive">
        <table class="table" id="etatTable">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Structure</th>
              <th>Montant disponible</th>
              <th>Engagé</th>
              <th>Consommé</th>
              <th>Solde</th>
              <th>Nb paiements</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody id="etatBody"></tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyEtat" style="display:none;">Aucune délégation trouvée.</p>
    </section>

    <!-- Détail paiements par délégation sélectionnée -->
    <section class="table-card" id="paiementsSection" style="display:none;">
      <div style="display:flex; justify-content:space-between; align-items:center; padding:16px;">
        <h3 style="color:#087f5b; margin:0;" id="paiementsTitre">Paiements</h3>
        <button class="btn-secondary" type="button" id="btnRetourEtat">Retour</button>
      </div>
      <div class="table-responsive">
        <table class="table" id="engagementTable">
          <thead>
            <tr>
              <th>Agent</th>
              <th>Mois</th>
              <th>Montant</th>
              <th>Date paiement</th>
            </tr>
          </thead>
          <tbody id="paiementsBody"></tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyPaiements" style="display:none;">Aucun paiement pour cette délégation.</p>
    </section>
  </section>
</main>

<!-- Modal Nouveau Paiement -->
<div id="modalPaiement" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:500px; width:90%; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Associer un paiement de salaire</h2>
      <button type="button" id="btnClosePaiement" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <form id="formPaiement">
      <div class="form-group">
        <label for="paiDelegation">Délégation de crédit *</label>
        <select class="form-control" id="paiDelegation" required>
          <option value="">-- Choisir --</option>
        </select>
        <small id="paiSoldeInfo" style="color:#868e96;"></small>
      </div>
      <div class="form-group">
        <label for="paiAgent">Nom de l'agent *</label>
        <input type="text" class="form-control" id="paiAgent" required>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="form-group">
          <label for="paiMois">Mois *</label>
          <select class="form-control" id="paiMois" required>
            <option value="">-- Choisir --</option>
            <option>Janvier</option><option>Février</option><option>Mars</option>
            <option>Avril</option><option>Mai</option><option>Juin</option>
            <option>Juillet</option><option>Août</option><option>Septembre</option>
            <option>Octobre</option><option>Novembre</option><option>Décembre</option>
          </select>
        </div>
        <div class="form-group">
          <label for="paiDate">Date paiement *</label>
          <input type="date" class="form-control" id="paiDate" required>
        </div>
      </div>
      <div class="form-group">
        <label for="paiMontant">Montant (FCFA) *</label>
        <input type="number" class="form-control" id="paiMontant" min="0" step="1" required>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:16px;">
        <button type="button" class="btn-secondary" id="btnAnnulerPaiement">Annuler</button>
        <button type="submit" class="btn-primary">Enregistrer le paiement</button>
      </div>
      <p id="paiError" style="color:#e03131; margin-top:12px; display:none;"></p>
      <p id="paiSuccess" style="color:#087f5b; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const API = 'http://127.0.0.1:8000/api';
let allDelegations = [];

document.addEventListener('DOMContentLoaded', async () => {
  await loadDelegations();
  setupEvents();
});

async function loadDelegations() {
  try {
    const res = await fetch(API + '/delegation-credits');
    allDelegations = await res.json();
    renderEtatTable(allDelegations);
    updateStats();
    populateDelegationSelects();
  } catch(e) {
    console.error(e);
    document.getElementById('emptyEtat').style.display = 'block';
    document.getElementById('emptyEtat').textContent = 'Erreur de connexion au serveur backend.';
  }
}

function shortMontant(val) {
  val = parseFloat(val);
  if (val >= 1000000000) return (val / 1000000000).toFixed(1) + 'Md';
  if (val >= 1000000) return Math.round(val / 1000000) + 'M';
  if (val >= 1000) return Math.round(val / 1000) + 'K';
  return val.toString();
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val);
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('fr-FR');
}

function updateStats() {
  const totalPaiements = allDelegations.reduce((s, d) => s + parseFloat(d.montant_consomme), 0);
  const delegationsAvecPaiements = allDelegations.filter(d => parseFloat(d.montant_consomme) > 0).length;
  const soldeTotal = allDelegations.reduce((s, d) => s + parseFloat(d.solde), 0);

  document.getElementById('statPaiements').textContent = allDelegations.length;
  document.getElementById('statTotalPaye').textContent = shortMontant(totalPaiements);
  document.getElementById('statDelegationsUtilisees').textContent = delegationsAvecPaiements;
  document.getElementById('statSoldeTotal').textContent = shortMontant(soldeTotal);
}

function populateDelegationSelects() {
  const filterSel = document.getElementById('filterDelegation');
  const formSel = document.getElementById('paiDelegation');
  filterSel.innerHTML = '<option value="">Toutes</option>';
  formSel.innerHTML = '<option value="">-- Choisir --</option>';
  allDelegations.forEach(d => {
    const label = d.reference + ' - ' + (d.structure ? d.structure.nom : '');
    filterSel.innerHTML += `<option value="${d.id}">${label}</option>`;
    formSel.innerHTML += `<option value="${d.id}" data-solde="${d.solde}">${label} (Solde: ${formatMontant(d.solde)} FCFA)</option>`;
  });
}

function renderEtatTable(data) {
  const tbody = document.getElementById('etatBody');
  const empty = document.getElementById('emptyEtat');

  if (data.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';

  tbody.innerHTML = data.map(d => `
    <tr>
      <td>${d.reference}</td>
      <td>${d.structure ? d.structure.nom : '-'}</td>
      <td>${formatMontant(d.montant_disponible)}</td>
      <td>${formatMontant(d.montant_engage)}</td>
      <td>${formatMontant(d.montant_consomme)}</td>
      <td style="font-weight:bold; color:${parseFloat(d.solde) > 0 ? '#087f5b' : '#e03131'};">${formatMontant(d.solde)}</td>
      <td>${parseFloat(d.montant_consomme) > 0 ? '<span class="badge badge-active">Utilisée</span>' : '<span class="badge badge-pending">Non utilisée</span>'}</td>
      <td class="actions-cell">
        <div class="table-actions-inline">
          <button class="table-action" type="button" onclick="voirPaiements(${d.id})">Détails</button>
        </div>
      </td>
    </tr>
  `).join('');
}

async function voirPaiements(id) {
  try {
    const res = await fetch(API + '/delegation-credits/' + id + '/etat');
    const data = await res.json();

    document.getElementById('paiementsTitre').textContent = 'Paiements - ' + data.reference;
    document.getElementById('etatSection').style.display = 'none';
    document.getElementById('paiementsSection').style.display = 'block';

    const tbody = document.getElementById('paiementsBody');
    const empty = document.getElementById('emptyPaiements');

    if (!data.paiements || data.paiements.length === 0) {
      tbody.innerHTML = '';
      empty.style.display = 'block';
      return;
    }
    empty.style.display = 'none';

    tbody.innerHTML = data.paiements.map(p => `
      <tr>
        <td>${p.nom_agent}</td>
        <td>${p.mois}</td>
        <td>${formatMontant(p.montant)} FCFA</td>
        <td>${formatDate(p.date_paiement)}</td>
      </tr>
    `).join('');
  } catch(e) {
    alert('Erreur lors du chargement.');
  }
}

function setupEvents() {
  document.getElementById('btnRetourEtat').addEventListener('click', () => {
    document.getElementById('etatSection').style.display = 'block';
    document.getElementById('paiementsSection').style.display = 'none';
  });

  document.getElementById('btnFiltrer').addEventListener('click', () => {
    const delId = document.getElementById('filterDelegation').value;
    if (delId) {
      renderEtatTable(allDelegations.filter(d => d.id == delId));
    } else {
      renderEtatTable(allDelegations);
    }
  });

  document.getElementById('btnNouveauPaiement').addEventListener('click', () => {
    document.getElementById('modalPaiement').style.display = 'block';
    document.getElementById('paiError').style.display = 'none';
    document.getElementById('paiSuccess').style.display = 'none';
  });

  document.getElementById('btnClosePaiement').addEventListener('click', closePaiement);
  document.getElementById('btnAnnulerPaiement').addEventListener('click', closePaiement);

  document.getElementById('paiDelegation').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const solde = opt.getAttribute('data-solde');
    document.getElementById('paiSoldeInfo').textContent = solde ? 'Solde disponible : ' + formatMontant(solde) + ' FCFA' : '';
  });

  document.getElementById('formPaiement').addEventListener('submit', async (e) => {
    e.preventDefault();

    const delegationId = document.getElementById('paiDelegation').value;
    const payload = {
      nom_agent: document.getElementById('paiAgent').value,
      mois: document.getElementById('paiMois').value,
      montant: parseFloat(document.getElementById('paiMontant').value),
      date_paiement: document.getElementById('paiDate').value,
    };

    try {
      const res = await fetch(API + '/delegation-credits/' + delegationId + '/paiements', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await res.json();

      if (!res.ok) {
        const errMsg = result.errors ? Object.values(result.errors).flat().join(', ') : result.message;
        document.getElementById('paiError').textContent = errMsg;
        document.getElementById('paiError').style.display = 'block';
        document.getElementById('paiSuccess').style.display = 'none';
      } else {
        document.getElementById('paiSuccess').textContent = 'Paiement enregistré avec succès !';
        document.getElementById('paiSuccess').style.display = 'block';
        document.getElementById('paiError').style.display = 'none';
        document.getElementById('formPaiement').reset();
        document.getElementById('paiSoldeInfo').textContent = '';
        setTimeout(() => { closePaiement(); loadDelegations(); }, 1000);
      }
    } catch(e) {
      document.getElementById('paiError').textContent = 'Erreur de connexion au serveur.';
      document.getElementById('paiError').style.display = 'block';
    }
  });
}

function closePaiement() {
  document.getElementById('modalPaiement').style.display = 'none';
  document.getElementById('formPaiement').reset();
  document.getElementById('paiSoldeInfo').textContent = '';
}
</script>
@endpush
