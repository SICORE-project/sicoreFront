@extends('layouts.app')

@section('title', 'SICORE - Édition des délégations de crédits')
@section('content')
<main class="main-content">
  <x-topbar
    title="Édition des délégations de crédits"
    subtitle="Gestion de la paie > Édition des délégations de crédits"
    icon="fa-solid fa-file-signature"
    search-id="editionSearch"
    search-placeholder="Rechercher…"
    filter-target="#editionTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Définir le montant disponible.</li>
        <li>Suivre le montant engagé.</li>
        <li>Suivre le montant consommé.</li>
        <li>Suivre le solde restant.</li>
      </ul>
    </section>

    <div class="stats-grid four">
      <article class="stat-card">
        <div>
          <p class="stat-label">Montant disponible</p>
          <p class="stat-value" id="statDisponible">0</p>
          <p class="stat-note">FCFA</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Montant engagé</p>
          <p class="stat-value" id="statEngage">0</p>
          <p class="stat-note">FCFA</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-file-contract" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Montant consommé</p>
          <p class="stat-value" id="statConsomme">0</p>
          <p class="stat-note">FCFA</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-coins" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Solde restant</p>
          <p class="stat-value" id="statSolde">0</p>
          <p class="stat-note">FCFA</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></span>
      </article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb">Gestion de la paie > Édition des délégations de crédits</p>
      <div class="actions-group">
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
        <table class="table" id="editionTable">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Structure</th>
              <th>Montant disponible</th>
              <th>Engagé</th>
              <th>Consommé</th>
              <th>Solde</th>
              <th>Statut</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody id="editionBody"></tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucune délégation trouvée.</p>
    </section>
  </section>
</main>

<!-- Modal Engager / Consommer -->
<div id="modalMontant" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:450px; width:90%; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;" id="modalMontantTitle">Engager un montant</h2>
      <button type="button" id="btnCloseMontant" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <form id="formMontant">
      <input type="hidden" id="montantDelegationId">
      <input type="hidden" id="montantAction">
      <div class="form-group">
        <label for="montantValue">Montant (FCFA)</label>
        <input type="number" class="form-control" id="montantValue" min="0" step="1" required>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:16px;">
        <button type="button" class="btn-secondary" id="btnAnnulerMontant">Annuler</button>
        <button type="submit" class="btn-primary" id="btnSubmitMontant">Confirmer</button>
      </div>
      <p id="montantError" style="color:#e03131; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const API = 'http://127.0.0.1:8000/api';
let allDelegations = [];

document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([loadStructures(), loadDelegations()]);
  setupEvents();
});

async function loadStructures() {
  try {
    const res = await fetch(API + '/structures');
    const structures = await res.json();
    const sel = document.getElementById('filterStructure');
    structures.forEach(s => {
      sel.innerHTML += `<option value="${s.id}">${s.nom}</option>`;
    });
  } catch(e) { console.error(e); }
}

async function loadDelegations() {
  try {
    const res = await fetch(API + '/delegation-credits');
    allDelegations = await res.json();
    renderTable(allDelegations);
    updateStats(allDelegations);
  } catch(e) {
    console.error(e);
    document.getElementById('emptyMsg').style.display = 'block';
    document.getElementById('emptyMsg').textContent = 'Erreur de connexion au serveur backend.';
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

function updateStats(data) {
  const disponible = data.reduce((s, d) => s + parseFloat(d.montant_disponible), 0);
  const engage = data.reduce((s, d) => s + parseFloat(d.montant_engage), 0);
  const consomme = data.reduce((s, d) => s + parseFloat(d.montant_consomme), 0);
  const solde = data.reduce((s, d) => s + parseFloat(d.solde), 0);

  document.getElementById('statDisponible').textContent = shortMontant(disponible);
  document.getElementById('statEngage').textContent = shortMontant(engage);
  document.getElementById('statConsomme').textContent = shortMontant(consomme);
  document.getElementById('statSolde').textContent = shortMontant(solde);
}

function badgeClass(statut) {
  if (statut === 'Validée') return 'badge-active';
  if (statut === 'Rejetée') return 'badge-rejected';
  return 'badge-pending';
}

function renderTable(data) {
  const tbody = document.getElementById('editionBody');
  const empty = document.getElementById('emptyMsg');

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
      <td><span class="badge ${badgeClass(d.statut)}">${d.statut}</span></td>
      <td class="actions-cell">
        <div class="table-actions-inline">
          <button class="table-action" type="button" onclick="openMontant(${d.id}, 'engager')">Engager</button>
          <button class="table-action" type="button" onclick="openMontant(${d.id}, 'consommer')">Consommer</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function appliquerFiltres() {
  const structureId = document.getElementById('filterStructure').value;
  const statut = document.getElementById('filterStatut').value;
  const champRecherche = document.getElementById('editionSearch');
  const recherche = champRecherche ? champRecherche.value.trim().toLowerCase() : '';

  let filtered = allDelegations;
  if (structureId) filtered = filtered.filter(d => d.structure_id == structureId);
  if (statut) filtered = filtered.filter(d => d.statut === statut);
  if (recherche) {
    filtered = filtered.filter(d => [
      d.reference,
      d.objet,
      d.structure ? d.structure.nom : '',
      d.statut
    ].join(' ').toLowerCase().includes(recherche));
  }

  renderTable(filtered);
  updateStats(filtered);
}

// Exporte les lignes actuellement affichees du tableau (filtres et recherche compris).
function exporterCSV() {
  const table = document.getElementById('editionTable');

  const entetes = [...table.querySelectorAll('thead th')]
    .filter(th => !th.classList.contains('actions-cell'))
    .map(th => th.textContent.trim());

  const lignes = [...table.querySelectorAll('tbody tr')]
    .filter(tr => !tr.classList.contains('is-hidden'))
    .map(tr => [...tr.querySelectorAll('td')]
      .filter(td => !td.classList.contains('actions-cell'))
      .map(td => td.textContent.trim()));

  if (lignes.length === 0) {
    alert('Aucune délégation à exporter.');
    return;
  }

  const echapper = valeur => '"' + String(valeur).replace(/"/g, '""') + '"';
  const csv = [entetes, ...lignes]
    .map(ligne => ligne.map(echapper).join(';'))
    .join('\r\n');

  // BOM UTF-8 : sans lui, Excel affiche mal les accents.
  const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const lien = document.createElement('a');
  lien.href = url;
  lien.download = 'edition-delegations-' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(lien);
  lien.click();
  document.body.removeChild(lien);
  URL.revokeObjectURL(url);
}

function setupEvents() {
  // Le filtrage est immediat : le bouton Filtrer reste disponible mais n'est plus indispensable.
  ['filterStructure', 'filterStatut'].forEach(id => {
    document.getElementById(id).addEventListener('change', appliquerFiltres);
  });
  document.getElementById('btnFiltrer').addEventListener('click', appliquerFiltres);

  document.getElementById('btnReinitialiser').addEventListener('click', () => {
    document.getElementById('filterStructure').value = '';
    document.getElementById('filterStatut').value = '';
    const recherche = document.getElementById('editionSearch');
    if (recherche) recherche.value = '';
    appliquerFiltres();
  });

  // La recherche du bandeau passe par le meme filtrage, sinon un clic sur Filtrer
  // reconstruit le tableau et fait perdre le terme recherche.
  const champRecherche = document.getElementById('editionSearch');
  if (champRecherche) champRecherche.addEventListener('input', appliquerFiltres);

  document.getElementById('btnExporter').addEventListener('click', exporterCSV);

  document.getElementById('btnCloseMontant').addEventListener('click', closeMontant);
  document.getElementById('btnAnnulerMontant').addEventListener('click', closeMontant);

  document.getElementById('formMontant').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('montantDelegationId').value;
    const action = document.getElementById('montantAction').value;
    const montant = parseFloat(document.getElementById('montantValue').value);

    try {
      const res = await fetch(API + '/delegation-credits/' + id + '/' + action, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ montant })
      });
      const result = await res.json();
      if (!res.ok) {
        document.getElementById('montantError').textContent = result.message || 'Erreur';
        document.getElementById('montantError').style.display = 'block';
      } else {
        closeMontant();
        loadDelegations();
      }
    } catch(e) {
      document.getElementById('montantError').textContent = 'Erreur de connexion.';
      document.getElementById('montantError').style.display = 'block';
    }
  });
}

function openMontant(id, action) {
  document.getElementById('montantDelegationId').value = id;
  document.getElementById('montantAction').value = action;
  document.getElementById('modalMontantTitle').textContent = action === 'engager' ? 'Engager un montant' : 'Consommer un montant';
  document.getElementById('montantValue').value = '';
  document.getElementById('montantError').style.display = 'none';
  document.getElementById('modalMontant').style.display = 'block';
}

function closeMontant() {
  document.getElementById('modalMontant').style.display = 'none';
}
</script>
@endpush
