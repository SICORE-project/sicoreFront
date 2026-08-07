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
              <th>Structure</th>
              <th>Service</th>
              <th>Montant alloué</th>
              <th>Date</th>
              <th>Statut</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody id="delegationBody">
          </tbody>
        </table>
      </div>
      <p class="empty-message" id="emptyMsg" style="display:none;">Aucune délégation trouvée.</p>
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
          <input type="text" class="form-control" id="reference" name="reference" placeholder="DEL-2026-001" required>
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
          <label for="montant_disponible">Montant disponible (FCFA) *</label>
          <input type="number" class="form-control" id="montant_disponible" name="montant_disponible" min="0" step="1" required>
        </div>
        <div class="form-group">
          <label for="date_delegation">Date de délégation *</label>
          <input type="date" class="form-control" id="date_delegation" name="date_delegation" required>
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
const API = 'http://127.0.0.1:8000/api';
let allDelegations = [];
let allStructures = [];

document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([loadStructures(), loadDelegations()]);
  setupEvents();
});

async function loadStructures() {
  try {
    const res = await fetch(API + '/structures');
    allStructures = await res.json();
    const filterSelect = document.getElementById('filterStructure');
    const formSelect = document.getElementById('structure_id');
    allStructures.forEach(s => {
      filterSelect.innerHTML += `<option value="${s.id}">${s.nom}</option>`;
      formSelect.innerHTML += `<option value="${s.id}">${s.nom}</option>`;
    });
    // Sans cela, la liste Service reste vide tant qu'aucune structure n'est choisie.
    majServicesFiltre();
  } catch(e) {
    console.error('Erreur chargement structures:', e);
  }
}

async function loadDelegations() {
  try {
    const res = await fetch(API + '/delegation-credits');
    allDelegations = await res.json();
    renderTable(allDelegations);
    updateStats(allDelegations);
  } catch(e) {
    console.error('Erreur chargement délégations:', e);
    document.getElementById('emptyMsg').style.display = 'block';
    document.getElementById('emptyMsg').textContent = 'Erreur de connexion au serveur backend (port 8000).';
  }
}

function updateStats(data) {
  const total = data.length;
  const validees = data.filter(d => d.statut === 'Validée').length;
  const attente = data.filter(d => d.statut === 'En attente').length;
  const rejetees = data.filter(d => d.statut === 'Rejetée').length;

  document.getElementById('statTotal').textContent = total;
  document.getElementById('statValidees').textContent = validees;
  document.getElementById('statAttente').textContent = attente;
  document.getElementById('statRejetees').textContent = rejetees;
  document.getElementById('statValideesPct').textContent = total > 0 ? Math.round(validees/total*100) + '% traités' : '0% traités';
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR');
}

function badgeClass(statut) {
  if (statut === 'Validée') return 'badge-active';
  if (statut === 'Rejetée') return 'badge-rejected';
  return 'badge-pending';
}

function renderTable(data) {
  const tbody = document.getElementById('delegationBody');
  const empty = document.getElementById('emptyMsg');

  if (data.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    empty.textContent = 'Aucune délégation trouvée.';
    return;
  }
  empty.style.display = 'none';

  tbody.innerHTML = data.map(d => `
    <tr>
      <td>${d.reference}</td>
      <td>${d.structure ? d.structure.nom : '-'}</td>
      <td>${d.service ? d.service.nom : '-'}</td>
      <td>${formatMontant(d.montant_disponible)}</td>
      <td>${formatDate(d.date_delegation)}</td>
      <td><span class="badge ${badgeClass(d.statut)}">${d.statut}</span></td>
      <td class="actions-cell">
        <div class="table-actions-inline">
          <button class="table-action" type="button" onclick="voirDetail(${d.id})">Voir</button>
          ${d.statut === 'En attente' ? `<button class="table-action primary" type="button" onclick="validerDelegation(${d.id})">Valider</button>` : ''}
        </div>
      </td>
    </tr>
  `).join('');
}

// Remplit la liste Service : tous les services (groupes par structure) si aucune
// structure n'est selectionnee, sinon ceux de la structure choisie.
function majServicesFiltre() {
  const structureId = document.getElementById('filterStructure').value;
  const select = document.getElementById('filterService');
  const ancienChoix = select.value;
  const structures = structureId
    ? allStructures.filter(s => s.id == structureId)
    : allStructures;

  select.innerHTML = '<option value="">Tous</option>' + structures.map(s => {
    const services = s.services || [];
    if (services.length === 0) return '';
    const options = services.map(svc => `<option value="${svc.id}">${svc.nom}</option>`).join('');
    // Sans structure choisie, on groupe pour distinguer les services homonymes.
    return structureId ? options : `<optgroup label="${s.nom}">${options}</optgroup>`;
  }).join('');

  const encoreDisponible = [...select.options].some(o => o.value === ancienChoix);
  select.value = encoreDisponible ? ancienChoix : '';
}

function appliquerFiltres() {
  const structureId = document.getElementById('filterStructure').value;
  const serviceId = document.getElementById('filterService').value;
  const statut = document.getElementById('filterStatut').value;
  const champRecherche = document.getElementById('delegationSearch');
  const recherche = champRecherche ? champRecherche.value.trim().toLowerCase() : '';

  let filtered = allDelegations;
  if (structureId) filtered = filtered.filter(d => d.structure_id == structureId);
  if (serviceId) filtered = filtered.filter(d => d.service_id == serviceId);
  if (statut) filtered = filtered.filter(d => d.statut === statut);
  if (recherche) {
    filtered = filtered.filter(d => [
      d.reference,
      d.objet,
      d.structure ? d.structure.nom : '',
      d.service ? d.service.nom : '',
      d.statut
    ].join(' ').toLowerCase().includes(recherche));
  }

  renderTable(filtered);
  updateStats(filtered);
}

function setupEvents() {
  document.getElementById('btnNouvelleDelegation').addEventListener('click', () => {
    document.getElementById('modalDelegation').style.display = 'block';
    document.getElementById('formError').style.display = 'none';
    document.getElementById('formSuccess').style.display = 'none';
  });

  document.getElementById('btnCloseModal').addEventListener('click', closeModal);
  document.getElementById('btnAnnuler').addEventListener('click', closeModal);
  document.getElementById('btnCloseDetail').addEventListener('click', () => {
    document.getElementById('modalDetail').style.display = 'none';
  });

  document.getElementById('structure_id').addEventListener('change', function() {
    const structureId = this.value;
    const serviceSelect = document.getElementById('service_id');
    serviceSelect.innerHTML = '<option value="">-- Choisir --</option>';
    if (!structureId) return;
    const structure = allStructures.find(s => s.id == structureId);
    if (structure && structure.services) {
      structure.services.forEach(svc => {
        serviceSelect.innerHTML += `<option value="${svc.id}">${svc.nom}</option>`;
      });
    }
  });

  document.getElementById('filterStructure').addEventListener('change', () => {
    majServicesFiltre();
    appliquerFiltres();
  });

  // Le filtrage est immediat : le bouton Filtrer reste disponible mais n'est plus indispensable.
  ['filterService', 'filterStatut'].forEach(id => {
    document.getElementById(id).addEventListener('change', appliquerFiltres);
  });
  document.getElementById('btnFiltrer').addEventListener('click', appliquerFiltres);

  document.getElementById('btnReinitialiser').addEventListener('click', () => {
    document.getElementById('filterStructure').value = '';
    document.getElementById('filterStatut').value = '';
    // Vide avant majServicesFiltre(), qui conserve sinon le service deja choisi.
    document.getElementById('filterService').value = '';
    const recherche = document.getElementById('delegationSearch');
    if (recherche) recherche.value = '';
    majServicesFiltre();
    appliquerFiltres();
  });

  // La recherche du bandeau passe par le meme filtrage, sinon un clic sur Filtrer
  // reconstruit le tableau et fait perdre le terme recherche.
  const champRecherche = document.getElementById('delegationSearch');
  if (champRecherche) champRecherche.addEventListener('input', appliquerFiltres);

  document.getElementById('btnExporter').addEventListener('click', exporterCSV);

  document.getElementById('formDelegation').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.textContent = 'Création en cours...';

    const formData = {
      annee_academique: document.getElementById('annee_academique').value,
      reference: document.getElementById('reference').value,
      objet: document.getElementById('objet').value,
      structure_id: parseInt(document.getElementById('structure_id').value),
      service_id: parseInt(document.getElementById('service_id').value),
      montant_disponible: parseFloat(document.getElementById('montant_disponible').value),
      date_delegation: document.getElementById('date_delegation').value,
    };

    try {
      const res = await fetch(API + '/delegation-credits', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(formData)
      });

      const result = await res.json();

      if (!res.ok) {
        const errMsg = result.errors ? Object.values(result.errors).flat().join(', ') : result.message;
        document.getElementById('formError').textContent = errMsg;
        document.getElementById('formError').style.display = 'block';
        document.getElementById('formSuccess').style.display = 'none';
      } else {
        document.getElementById('formSuccess').textContent = 'Délégation créée avec succès !';
        document.getElementById('formSuccess').style.display = 'block';
        document.getElementById('formError').style.display = 'none';
        document.getElementById('formDelegation').reset();
        setTimeout(() => { closeModal(); loadDelegations(); }, 1000);
      }
    } catch(e) {
      document.getElementById('formError').textContent = 'Erreur de connexion au serveur.';
      document.getElementById('formError').style.display = 'block';
    }

    btn.disabled = false;
    btn.textContent = 'Créer la délégation';
  });
}

function closeModal() {
  document.getElementById('modalDelegation').style.display = 'none';
  document.getElementById('formDelegation').reset();
}

// Exporte les lignes actuellement affichees du tableau (filtres et recherche compris).
function exporterCSV() {
  const table = document.getElementById('delegationTable');

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
  lien.download = 'delegations-credit-' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(lien);
  lien.click();
  document.body.removeChild(lien);
  URL.revokeObjectURL(url);
}

async function voirDetail(id) {
  try {
    const res = await fetch(API + '/delegation-credits/' + id + '/etat');
    const data = await res.json();

    document.getElementById('detailContent').innerHTML = `
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div><strong>Référence :</strong> ${data.reference}</div>
        <div><strong>Année académique :</strong> ${data.annee_academique}</div>
        <div><strong>Montant disponible :</strong> ${formatMontant(data.montant_disponible)}</div>
        <div><strong>Montant engagé :</strong> ${formatMontant(data.montant_engage)}</div>
        <div><strong>Montant consommé :</strong> ${formatMontant(data.montant_consomme)}</div>
        <div><strong>Solde restant :</strong> <span style="color:${data.solde > 0 ? '#087f5b' : '#e03131'}; font-weight:bold;">${formatMontant(data.solde)}</span></div>
      </div>
      ${data.paiements && data.paiements.length > 0 ? `
        <h3 style="margin-top:24px; color:#087f5b;">Paiements associés</h3>
        <table class="table" style="margin-top:8px;">
          <thead><tr><th>Agent</th><th>Mois</th><th>Montant</th><th>Date</th></tr></thead>
          <tbody>
            ${data.paiements.map(p => `<tr><td>${p.nom_agent}</td><td>${p.mois}</td><td>${formatMontant(p.montant)}</td><td>${formatDate(p.date_paiement)}</td></tr>`).join('')}
          </tbody>
        </table>
      ` : '<p style="margin-top:16px; color:#868e96;">Aucun paiement associé.</p>'}
    `;
    document.getElementById('modalDetail').style.display = 'block';
  } catch(e) {
    alert('Erreur lors du chargement des détails.');
  }
}

async function validerDelegation(id) {
  if (!confirm('Voulez-vous valider cette délégation ?')) return;
  try {
    await fetch(API + '/delegation-credits/' + id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ statut: 'Validée' })
    });
    loadDelegations();
  } catch(e) {
    alert('Erreur lors de la validation.');
  }
}
</script>
@endpush
