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

    {{-- Axe de recherche FINPRONET : période comptable puis corps / IA / IEF.
         Corps, IA et IEF sont portés par les ventilations : une délégation
         ressort dès qu'une de ses lignes correspond. --}}
    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filterAnnee">Année académique</label>
        <select class="form-control" id="filterAnnee">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterPeriode">Période de paie</label>
        <select class="form-control" id="filterPeriode">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterCorps">Corps d'enseignant</label>
        <select class="form-control" id="filterCorps">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterIA">IA</label>
        <select class="form-control" id="filterIA">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filterIEF">IEF</label>
        <select class="form-control" id="filterIEF">
          <option value="">Toutes</option>
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
      <div class="form-group">
        <label for="filterRecherche">Référence ou objet</label>
        <input class="form-control" id="filterRecherche" type="text" placeholder="ex. 15/10/2025">
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
              <th>Période</th>
              <th>Ventilations</th>
              <th>Montant initial</th>
              <th>Disponible</th>
              <th>Engagé</th>
              <th>Consommé</th>
              <th>Solde</th>
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
      <h2 id="modalDelegationTitre" style="margin:0; color:#087f5b; font-size:1.25rem;">Nouvelle délégation de crédit</h2>
      <button type="button" id="btnCloseModal" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <form id="formDelegation">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="form-group">
          <label for="annee_academique">Année académique *</label>
          <input type="text" class="form-control" id="annee_academique" name="annee_academique" placeholder="2025-2026" required>
        </div>
        <div class="form-group">
          <label for="reference">Référence lettre *</label>
          <input type="text" class="form-control" id="reference" name="reference" placeholder="15/10/2025" required>
        </div>
        <div class="form-group" style="grid-column:span 2;">
          <label for="objet">Objet *</label>
          <input type="text" class="form-control" id="objet" name="objet" placeholder="SALAIRE PC" required>
        </div>
        <div class="form-group">
          <label for="periode_paie">Période</label>
          <input type="text" class="form-control" id="periode_paie" name="periode_paie" placeholder="octobre">
        </div>
        <div class="form-group">
          <label for="structure_id">Structure</label>
          <select class="form-control" id="structure_id" name="structure_id">
            <option value="">-- Aucune --</option>
          </select>
        </div>
        <div class="form-group">
          <label for="service_id">Service</label>
          <select class="form-control" id="service_id" name="service_id">
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

<!-- Modal Consommer (enregistrer un paiement) -->
<div id="modalConsommer" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Enregistrer un paiement</h2>
      <button type="button" id="btnCloseConsommer" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="consommerInfo" style="background:#f1f3f5; border-radius:8px; padding:16px; margin-bottom:20px;"></div>
    <form id="formConsommer">
      <input type="hidden" id="consommer_delegation_id">
      <div style="display:grid; gap:16px;">
        <div class="form-group">
          <label for="consommer_agent">Nom de l'agent *</label>
          <input type="text" class="form-control" id="consommer_agent" placeholder="Ex : Diallo Mamadou" required>
        </div>
        <div class="form-group">
          <label for="consommer_mois">Mois *</label>
          <input type="text" class="form-control" id="consommer_mois" placeholder="Ex : Juin 2026" required>
        </div>
        <div class="form-group">
          <label for="consommer_montant">Montant (FCFA) *</label>
          <input type="number" class="form-control" id="consommer_montant" min="1" step="1" required>
          <small id="consommerHint" style="color:#868e96; font-size:0.82rem;"></small>
        </div>
        <div class="form-group">
          <label for="consommer_date">Date du paiement</label>
          <input type="date" class="form-control" id="consommer_date">
        </div>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
        <button type="button" class="btn-secondary" id="btnAnnulerConsommer">Annuler</button>
        <button type="submit" class="btn-primary" id="btnSubmitConsommer">Enregistrer le paiement</button>
      </div>
      <p id="consommerError" style="color:#e03131; margin-top:12px; display:none;"></p>
      <p id="consommerSuccess" style="color:#087f5b; margin-top:12px; display:none;"></p>
    </form>
  </div>
</div>

<!-- Modal Historique Consommations -->
<div id="modalHistConso" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:750px; width:95%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;">Suivi des consommations</h2>
      <button type="button" id="btnCloseHistConso" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="histConsoResume" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:20px;"></div>
    <div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; align-items:flex-end;">
      <div class="form-group" style="margin:0;">
        <label for="consoDateDebut" style="font-size:0.82rem;">Date début</label>
        <input type="date" class="form-control" id="consoDateDebut" style="padding:6px 10px;">
      </div>
      <div class="form-group" style="margin:0;">
        <label for="consoDateFin" style="font-size:0.82rem;">Date fin</label>
        <input type="date" class="form-control" id="consoDateFin" style="padding:6px 10px;">
      </div>
      <div class="form-group" style="margin:0;">
        <label for="consoAgent" style="font-size:0.82rem;">Agent</label>
        <input type="text" class="form-control" id="consoAgent" placeholder="Nom…" style="padding:6px 10px; width:140px;">
      </div>
      <button class="btn-secondary" type="button" id="btnConsoFiltrer" style="padding:7px 14px;">Filtrer</button>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Agent</th>
            <th>Mois</th>
            <th>Montant</th>
          </tr>
        </thead>
        <tbody id="histConsoBody"></tbody>
      </table>
    </div>
    <p id="histConsoEmpty" style="display:none; color:#868e96; text-align:center; margin-top:12px;">Aucun paiement enregistré.</p>
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

<!-- Modal Solde -->
<div id="modalSolde" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:650px; width:90%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;"><i class="fa-solid fa-wallet"></i> Suivi du solde</h2>
      <button type="button" id="btnCloseSolde" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="soldeAlerteBanner" style="display:none; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-weight:600;"></div>
    <div id="soldeInfo" style="margin-bottom:16px; padding:12px; background:#f8f9fa; border-radius:8px;"></div>
    <div id="soldeBarreContainer" style="margin-bottom:20px;">
      <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#868e96; margin-bottom:4px;">
        <span>Consommation du crédit</span>
        <span id="soldePctLabel">0%</span>
      </div>
      <div style="background:#e9ecef; border-radius:8px; height:24px; overflow:hidden; position:relative;">
        <div id="barreEngage" style="position:absolute; left:0; top:0; height:100%; background:#ffd43b; border-radius:8px 0 0 8px; transition:width 0.5s;"></div>
        <div id="barreConsomme" style="position:absolute; left:0; top:0; height:100%; background:#ff6b6b; border-radius:8px 0 0 8px; transition:width 0.5s;"></div>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:0.72rem; color:#868e96; margin-top:4px;">
        <span><span style="display:inline-block;width:10px;height:10px;background:#ff6b6b;border-radius:2px;margin-right:4px;"></span>Consommé</span>
        <span><span style="display:inline-block;width:10px;height:10px;background:#ffd43b;border-radius:2px;margin-right:4px;"></span>Engagé</span>
        <span><span style="display:inline-block;width:10px;height:10px;background:#e9ecef;border-radius:2px;margin-right:4px;"></span>Solde libre</span>
      </div>
    </div>
    <div id="soldeDetails" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;"></div>
  </div>
</div>

<!-- Modal Associer Bulletin -->
<div id="modalAssocier" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:750px; width:95%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;"><i class="fa-solid fa-link"></i> Associer un bulletin de salaire</h2>
      <button type="button" id="btnCloseAssocier" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="associerInfo" style="margin-bottom:16px; padding:12px; background:#f8f9fa; border-radius:8px;"></div>
    <p id="associerError" style="display:none; background:#ffe3e3; color:#c92a2a; padding:10px; border-radius:6px; font-size:0.88rem;"></p>
    <p id="associerSuccess" style="display:none; background:#d3f9d8; color:#087f5b; padding:10px; border-radius:6px; font-size:0.88rem;"></p>
    <input type="hidden" id="associer_delegation_id">
    <div style="margin-bottom:16px;">
      <label for="associerSearch" style="font-weight:600; font-size:0.88rem;">Rechercher un bulletin :</label>
      <div style="display:flex; gap:8px; margin-top:6px;">
        <input type="text" class="form-control" id="associerSearch" placeholder="Nom, matricule ou référence..." style="flex:1;">
        <button type="button" class="btn-secondary" id="btnAssocierRechercher">Rechercher</button>
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table class="table" id="associerTable" style="font-size:0.85rem;">
        <thead>
          <tr>
            <th>Référence</th>
            <th>Agent</th>
            <th>Période</th>
            <th>Net à payer</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="associerBody"></tbody>
      </table>
    </div>
    <p id="associerEmpty" style="display:none; text-align:center; color:#868e96; padding:20px;">Aucun bulletin validé disponible.</p>
  </div>
</div>

<!-- Modal Historique Associations -->
<div id="modalHistAssoc" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="background:#fff; border-radius:12px; padding:32px; max-width:750px; width:95%; max-height:90vh; overflow-y:auto; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="margin:0; color:#087f5b; font-size:1.25rem;"><i class="fa-solid fa-clock-rotate-left"></i> Bulletins associés</h2>
      <button type="button" id="btnCloseHistAssoc" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
    </div>
    <div id="histAssocResume" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;"></div>
    <div style="display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap;">
      <input type="date" class="form-control" id="histAssocDateDebut" style="max-width:150px;">
      <input type="date" class="form-control" id="histAssocDateFin" style="max-width:150px;">
      <input type="text" class="form-control" id="histAssocAgent" placeholder="Nom agent..." style="max-width:180px;">
      <button type="button" class="btn-secondary" id="btnHistAssocFiltrer">Filtrer</button>
    </div>
    <div style="overflow-x:auto;">
      <table class="table" style="font-size:0.85rem;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Bulletin</th>
            <th>Agent</th>
            <th>Période</th>
            <th>Montant</th>
          </tr>
        </thead>
        <tbody id="histAssocBody"></tbody>
      </table>
    </div>
    <p id="histAssocEmpty" style="display:none; text-align:center; color:#868e96; padding:20px;">Aucune association trouvée.</p>
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
// id de la délégation en cours de modification, null en création
var delegationEnEdition = null;

document.addEventListener('DOMContentLoaded', function() {
  Promise.all([loadStructures(), loadFiltres(), loadDelegations()]).then(function() {
    setupEvents();
  });
});

// Les structures n'alimentent plus que le formulaire de saisie : l'axe de
// recherche est désormais celui de FINPRONET (corps / IA / IEF).
function loadStructures() {
  return fetch(API + '/structures')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      allStructures = data;
      var formSelect = document.getElementById('structure_id');
      allStructures.forEach(function(s) {
        formSelect.innerHTML += '<option value="' + s.id + '">' + s.nom + '</option>';
      });
    })
    .catch(function(e) {
      console.error('Erreur chargement structures:', e);
    });
}

function loadFiltres() {
  return fetch(API + '/delegation-credits/filtres')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      remplirSelect('filterAnnee', data.annees_academiques, function(a) {
        return { value: a, label: a };
      });
      remplirSelect('filterPeriode', data.periodes_paie, function(p) {
        return { value: p, label: p };
      });
      remplirSelect('filterCorps', data.corps_enseignants, function(c) {
        return { value: c.id, label: c.libelle };
      });
      remplirSelect('filterIA', data.ias, function(ia) {
        return { value: ia.id, label: ia.code + ' - ' + ia.libelle };
      });
    })
    .catch(function(e) {
      console.error('Erreur chargement filtres:', e);
    });
}

function remplirSelect(selectId, items, mapper) {
  var sel = document.getElementById(selectId);
  if (!sel) return;
  (items || []).forEach(function(item) {
    var o = mapper(item);
    sel.innerHTML += '<option value="' + o.value + '">' + o.label + '</option>';
  });
}

function majIefs() {
  var iaId = document.getElementById('filterIA').value;
  var select = document.getElementById('filterIEF');
  select.innerHTML = '<option value="">Toutes</option>';
  if (!iaId) return Promise.resolve();

  return fetch(API + '/delegation-credits/iefs/' + iaId)
    .then(function(res) { return res.json(); })
    .then(function(iefs) {
      iefs.forEach(function(ief) {
        select.innerHTML += '<option value="' + ief.id + '">' + ief.libelle + '</option>';
      });
    })
    .catch(function(e) { console.error('Erreur chargement IEF:', e); });
}

// Le filtrage se fait côté serveur : corps / IA / IEF sont portés par les
// ventilations, ils ne sont pas filtrables depuis la liste déjà chargée.
function paramsFiltres() {
  var params = [];
  var champs = {
    annee_academique: valeurDe('filterAnnee'),
    periode_paie: valeurDe('filterPeriode'),
    corps_enseignant_id: valeurDe('filterCorps'),
    ia_id: valeurDe('filterIA'),
    ief_id: valeurDe('filterIEF'),
    statut: valeurDe('filterStatut'),
    search: valeurDe('filterRecherche')
  };

  Object.keys(champs).forEach(function(cle) {
    if (champs[cle]) params.push(cle + '=' + encodeURIComponent(champs[cle]));
  });

  params.push('per_page=200');

  return '?' + params.join('&');
}

function valeurDe(id) {
  var el = document.getElementById(id);
  return el ? el.value.trim() : '';
}

function loadDelegations() {
  return fetch(API + '/delegation-credits' + paramsFiltres())
    .then(function(res) { return res.json(); })
    .then(function(response) {
      allDelegations = response.data || response;
      currentPage = 1;
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
    // Période comptable FINPRONET et poids réel de la délégation : les montants
    // vivent dans les ventilations, pas dans l'en-tête.
    var periode = [d.annee_academique, d.periode_paie].filter(Boolean).join(' · ') || '-';
    var nbVent = (d.nombre_ventilations !== undefined && d.nombre_ventilations !== null)
      ? d.nombre_ventilations : 0;
    var ventile = (d.montant_ventile !== undefined && d.montant_ventile !== null)
      ? formatMontant(d.montant_ventile) : '-';
    var cellVentilations = nbVent > 0
      ? nbVent + ' ligne(s)<br><small style="color:#868e96;">' + ventile + '</small>'
      : '<span style="color:#868e96;">aucune</span>';
    var actions = '<button class="table-action" type="button" onclick="voirDetail(' + d.id + ')">Voir</button>';
    // Cycle de saisie de FINPRONET : Ajouter / Modifier / Supprimer
    actions += '<button class="table-action" type="button" onclick="modifierDelegation(' + d.id + ')">Modifier</button>';
    // Equivalent du bouton "Ventilations" de FINPRONET (frmDelegation.aspx)
    actions += '<button class="table-action primary" type="button" onclick="ouvrirVentilations(' + d.id + ')">Ventilations</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirMontant(' + d.id + ')">Montant</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirEngager(' + d.id + ')">Engager</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirHistorique(' + d.id + ')">Suivi</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirConsommer(' + d.id + ')">Payer</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirHistConso(' + d.id + ')">Conso.</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirSolde(' + d.id + ')">Solde</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirAssocier(' + d.id + ')">Associer</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirHistAssoc(' + d.id + ')">Assoc.</button>';
    actions += '<button class="table-action" type="button" onclick="ouvrirAffectation(' + d.id + ')">Affecter</button>';
    if (d.statut === 'En attente') {
      actions += '<button class="table-action primary" type="button" onclick="validerDelegation(' + d.id + ')">Valider</button>';
    }
    // Suppression en dernier, et refusée côté serveur si la délégation porte
    // des ventilations ou des engagements.
    actions += '<button class="table-action danger" type="button" onclick="supprimerDelegation('
      + d.id + ', \'' + String(d.reference || '').replace(/'/g, "\\'") + '\')">Supprimer</button>';

    var solde = parseFloat(d.solde) || 0;
    var dispo = parseFloat(d.montant_disponible) || 0;
    var pctSolde = dispo > 0 ? (solde / dispo) * 100 : 0;
    var soldeColor = pctSolde <= 0 ? '#e03131' : (pctSolde <= 20 ? '#e67700' : '#087f5b');
    var soldeBg = pctSolde <= 0 ? '#ffe3e3' : (pctSolde <= 20 ? '#fff3bf' : '#d3f9d8');
    var alerteIcon = pctSolde <= 20 ? ' <i class="fa-solid fa-triangle-exclamation" style="color:' + soldeColor + ';"></i>' : '';

    return '<tr>' +
      '<td>' + d.reference + '</td>' +
      '<td>' + (d.objet || '-') + '</td>' +
      '<td>' + periode + '</td>' +
      '<td>' + cellVentilations + '</td>' +
      '<td>' + (d.montant_initial ? formatMontant(d.montant_initial) : '-') + '</td>' +
      '<td>' + formatMontant(d.montant_disponible) + '</td>' +
      '<td style="color:#e67700; font-weight:600;">' + formatMontant(d.montant_engage) + '</td>' +
      '<td style="color:#c92a2a; font-weight:600;">' + formatMontant(d.montant_consomme) + '</td>' +
      '<td><span style="background:' + soldeBg + '; color:' + soldeColor + '; font-weight:bold; padding:2px 8px; border-radius:4px;">' + formatMontant(solde) + alerteIcon + '</span></td>' +
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

function appliquerFiltres() {
  return loadDelegations();
}

// La recherche libre de la topbar reste un affinage local sur la page
// courante ; le champ « Référence ou objet » du panneau, lui, interroge
// le serveur.
function rechercheLocale() {
  var champRecherche = document.getElementById('delegationSearch');
  var recherche = champRecherche ? champRecherche.value.trim().toLowerCase() : '';

  if (!recherche) {
    currentPage = 1;
    renderTable(allDelegations);
    updateStats(allDelegations);
    return;
  }

  var filtered = allDelegations.filter(function(d) {
    return [d.reference, d.objet, d.statut, d.periode_paie, d.annee_academique]
      .join(' ').toLowerCase().indexOf(recherche) !== -1;
  });

  currentPage = 1;
  renderTable(filtered);
  updateStats(filtered);
}

function setupEvents() {
  document.getElementById('btnNouvelleDelegation').addEventListener('click', function() {
    // Le formulaire est partagé création / modification : on repart d'un état
    // propre, sinon on modifierait la dernière délégation ouverte.
    document.getElementById('formDelegation').reset();
    quitterModeEdition();
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

  // Cascade IA -> IEF, puis relance du filtrage serveur.
  document.getElementById('filterIA').addEventListener('change', function() {
    majIefs().then(appliquerFiltres);
  });

  ['filterAnnee', 'filterPeriode', 'filterCorps', 'filterIEF', 'filterStatut'].forEach(function(id) {
    document.getElementById(id).addEventListener('change', appliquerFiltres);
  });

  document.getElementById('filterRecherche').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') appliquerFiltres();
  });

  document.getElementById('btnFiltrer').addEventListener('click', appliquerFiltres);

  document.getElementById('btnReinitialiser').addEventListener('click', function() {
    ['filterAnnee', 'filterPeriode', 'filterCorps', 'filterIA', 'filterStatut'].forEach(function(id) {
      document.getElementById(id).value = '';
    });
    document.getElementById('filterRecherche').value = '';
    document.getElementById('filterIEF').innerHTML = '<option value="">Toutes</option>';
    var recherche = document.getElementById('delegationSearch');
    if (recherche) recherche.value = '';
    appliquerFiltres();
  });

  // Frappe au clavier : affinage local, pas un appel serveur par caractère.
  var champRecherche = document.getElementById('delegationSearch');
  if (champRecherche) champRecherche.addEventListener('input', rechercheLocale);

  document.getElementById('btnCloseMontant').addEventListener('click', fermerMontant);
  document.getElementById('btnAnnulerMontant').addEventListener('click', fermerMontant);
  document.getElementById('nouveau_montant_disponible').addEventListener('input', calculerSoldeEstime);
  document.getElementById('formMontant').addEventListener('submit', soumettreMontant);

  document.getElementById('btnCloseEngager').addEventListener('click', fermerEngager);
  document.getElementById('btnAnnulerEngager').addEventListener('click', fermerEngager);
  document.getElementById('formEngager').addEventListener('submit', soumettreEngagement);

  document.getElementById('btnCloseConsommer').addEventListener('click', fermerConsommer);
  document.getElementById('btnAnnulerConsommer').addEventListener('click', fermerConsommer);
  document.getElementById('formConsommer').addEventListener('submit', soumettreConsommation);

  document.getElementById('btnCloseHistConso').addEventListener('click', function() { document.getElementById('modalHistConso').style.display = 'none'; });
  document.getElementById('btnConsoFiltrer').addEventListener('click', filtrerHistConso);

  document.getElementById('btnCloseHistorique').addEventListener('click', function() { document.getElementById('modalHistorique').style.display = 'none'; });
  document.getElementById('btnHistFiltrer').addEventListener('click', filtrerHistorique);

  document.getElementById('btnCloseAffectation').addEventListener('click', fermerAffectation);
  document.getElementById('btnAnnulerAffectation').addEventListener('click', fermerAffectation);
  document.getElementById('affectation_structure_id').addEventListener('change', function() {
    majServicesAffectation(null);
  });
  document.getElementById('formAffectation').addEventListener('submit', soumettreAffectation);

  document.getElementById('btnCloseSolde').addEventListener('click', function() { document.getElementById('modalSolde').style.display = 'none'; });

  document.getElementById('btnCloseAssocier').addEventListener('click', function() { document.getElementById('modalAssocier').style.display = 'none'; });
  document.getElementById('btnAssocierRechercher').addEventListener('click', function() {
    var id = document.getElementById('associer_delegation_id').value;
    if (id) chargerBulletinsDisponibles(id);
  });
  document.getElementById('associerSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var id = document.getElementById('associer_delegation_id').value;
      if (id) chargerBulletinsDisponibles(id);
    }
  });

  document.getElementById('btnCloseHistAssoc').addEventListener('click', function() { document.getElementById('modalHistAssoc').style.display = 'none'; });
  document.getElementById('btnHistAssocFiltrer').addEventListener('click', function() {
    if (histAssocDelegationId) chargerHistAssoc(histAssocDelegationId);
  });

  document.getElementById('btnExporter').addEventListener('click', exporterCSV);

  document.getElementById('formDelegation').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnSubmit');
    var edition = delegationEnEdition !== null;
    btn.disabled = true;
    btn.textContent = edition ? 'Modification en cours...' : 'Création en cours...';

    var montantInitial = document.getElementById('montant_initial').value;
    var dateFin = document.getElementById('date_fin').value;

    var structureId = document.getElementById('structure_id').value;
    var serviceId = document.getElementById('service_id').value;

    var formData = {
      annee_academique: document.getElementById('annee_academique').value,
      periode_paie: document.getElementById('periode_paie').value || null,
      reference: document.getElementById('reference').value,
      objet: document.getElementById('objet').value,
      structure_id: structureId ? parseInt(structureId) : null,
      service_id: serviceId ? parseInt(serviceId) : null,
      montant_initial: montantInitial ? parseFloat(montantInitial) : null,
      montant_disponible: parseFloat(document.getElementById('montant_disponible').value),
      date_delegation: document.getElementById('date_delegation').value,
      date_fin: dateFin || null,
    };

    var url = edition
      ? API + '/delegation-credits/' + delegationEnEdition
      : API + '/delegation-credits';

    fetch(url, {
      method: edition ? 'PUT' : 'POST',
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
          document.getElementById('formSuccess').textContent = edition
            ? 'Délégation modifiée avec succès !'
            : 'Délégation créée avec succès !';
          document.getElementById('formSuccess').style.display = 'block';
          document.getElementById('formError').style.display = 'none';
          document.getElementById('formDelegation').reset();
          setTimeout(function() { closeModal(); loadDelegations(); }, 1000);
        }
        btn.disabled = false;
        btn.textContent = edition ? 'Enregistrer les modifications' : 'Créer la délégation';
      });
    })
    .catch(function(e) {
      document.getElementById('formError').textContent = 'Erreur de connexion au serveur.';
      document.getElementById('formError').style.display = 'block';
      btn.disabled = false;
      btn.textContent = edition ? 'Enregistrer les modifications' : 'Créer la délégation';
    });
  });
}

function closeModal() {
  document.getElementById('modalDelegation').style.display = 'none';
  document.getElementById('formDelegation').reset();
  quitterModeEdition();
}

// === MODIFICATION ===
// Le formulaire de saisie sert aussi à la modification : même bloc
// Identification que FINPRONET, on ne duplique pas l'écran.
function quitterModeEdition() {
  delegationEnEdition = null;
  var titre = document.getElementById('modalDelegationTitre');
  if (titre) titre.textContent = 'Nouvelle délégation de crédit';
  document.getElementById('btnSubmit').textContent = 'Créer la délégation';
  document.getElementById('formError').style.display = 'none';
  document.getElementById('formSuccess').style.display = 'none';
}

function modifierDelegation(id) {
  fetch(API + '/delegation-credits/' + id)
    .then(function(res) { return res.json(); })
    .then(function(reponse) {
      var d = reponse.data || reponse;

      delegationEnEdition = id;

      var valeurs = {
        annee_academique: d.annee_academique || '',
        periode_paie: d.periode_paie || '',
        reference: d.reference || '',
        objet: d.objet || '',
        montant_initial: d.montant_initial || '',
        montant_disponible: d.montant_disponible || '',
        date_delegation: d.date_delegation || '',
        date_fin: d.date_fin || ''
      };

      Object.keys(valeurs).forEach(function(champ) {
        var el = document.getElementById(champ);
        if (el) el.value = valeurs[champ];
      });

      var selStructure = document.getElementById('structure_id');
      selStructure.value = d.structure_id || '';
      selStructure.dispatchEvent(new Event('change'));

      // Le service dépend de la structure : on attend que la liste soit
      // reconstruite avant de repositionner la valeur.
      setTimeout(function() {
        var selService = document.getElementById('service_id');
        if (selService) selService.value = d.service_id || '';
      }, 0);

      var titre = document.getElementById('modalDelegationTitre');
      if (titre) titre.textContent = 'Modifier la délégation ' + (d.reference || '');
      document.getElementById('btnSubmit').textContent = 'Enregistrer les modifications';
      document.getElementById('formError').style.display = 'none';
      document.getElementById('formSuccess').style.display = 'none';
      document.getElementById('modalDelegation').style.display = 'block';
    })
    .catch(function(e) {
      console.error('Erreur chargement délégation:', e);
      alert('Impossible de charger cette délégation.');
    });
}

// === SUPPRESSION ===
// Le backend refuse (409) si la délégation porte des ventilations ou des
// engagements : on affiche son message plutôt qu'une erreur générique.
function supprimerDelegation(id, reference) {
  var confirmation = window.confirm(
    'Supprimer définitivement la délégation ' + reference + ' ?\n\n' +
    'Cette action est irréversible.'
  );
  if (!confirmation) return;

  fetch(API + '/delegation-credits/' + id, {
    method: 'DELETE',
    headers: { 'Accept': 'application/json' }
  })
  .then(function(res) {
    return res.json().then(function(result) {
      if (!res.ok) {
        alert(result.message || 'Suppression impossible.');
        return;
      }
      loadDelegations();
    });
  })
  .catch(function(e) {
    console.error('Erreur suppression:', e);
    alert('Erreur de connexion au serveur.');
  });
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

// Ouvre l'ecran Ventilations sur la delegation choisie,
// comme le bouton "Ventilations" de frmDelegation.aspx.
function ouvrirVentilations(id) {
  window.location.href = '{{ route('credits.ventilations') }}?delegation=' + id;
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

function ouvrirConsommer(id) {
  var delegation = allDelegations.find(function(d) { return d.id == id; });
  if (!delegation) return;

  document.getElementById('consommer_delegation_id').value = id;
  document.getElementById('consommerError').style.display = 'none';
  document.getElementById('consommerSuccess').style.display = 'none';
  document.getElementById('consommer_agent').value = '';
  document.getElementById('consommer_mois').value = '';
  document.getElementById('consommer_montant').value = '';
  document.getElementById('consommer_date').value = new Date().toISOString().slice(0, 10);

  var resteSolde = delegation.montant_disponible - delegation.montant_consomme;
  document.getElementById('consommerInfo').innerHTML =
    '<strong>' + delegation.reference + '</strong> — ' + (delegation.objet || '') +
    '<br><small>Disponible : <strong>' + formatMontant(delegation.montant_disponible) + '</strong>' +
    ' | Consommé : <strong>' + formatMontant(delegation.montant_consomme) + '</strong>' +
    ' | Solde : <strong style="color:' + (resteSolde > 0 ? '#087f5b' : '#e03131') + ';">' + formatMontant(resteSolde) + '</strong></small>';

  document.getElementById('consommerHint').textContent = 'Maximum : ' + formatMontant(resteSolde);

  document.getElementById('modalConsommer').style.display = 'block';
}

function fermerConsommer() {
  document.getElementById('modalConsommer').style.display = 'none';
}

function soumettreConsommation(e) {
  e.preventDefault();
  var id = document.getElementById('consommer_delegation_id').value;
  var btn = document.getElementById('btnSubmitConsommer');
  var montant = parseFloat(document.getElementById('consommer_montant').value);
  var agent = document.getElementById('consommer_agent').value.trim();
  var mois = document.getElementById('consommer_mois').value.trim();

  if (!agent || !mois) {
    document.getElementById('consommerError').textContent = 'L\'agent et le mois sont obligatoires.';
    document.getElementById('consommerError').style.display = 'block';
    return;
  }
  if (!montant || montant <= 0) {
    document.getElementById('consommerError').textContent = 'Le montant doit être supérieur à zéro.';
    document.getElementById('consommerError').style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Enregistrement...';

  fetch(API + '/delegation-credits/' + id + '/consommer', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      montant: montant,
      nom_agent: agent,
      mois: mois,
      date_paiement: document.getElementById('consommer_date').value || null,
    })
  })
  .then(function(res) {
    return res.json().then(function(result) {
      if (!res.ok) {
        var errMsg = result.errors ? Object.values(result.errors).flat().join(', ') : result.message;
        document.getElementById('consommerError').textContent = errMsg;
        document.getElementById('consommerError').style.display = 'block';
        document.getElementById('consommerSuccess').style.display = 'none';
      } else {
        document.getElementById('consommerSuccess').textContent = result.message;
        document.getElementById('consommerSuccess').style.display = 'block';
        document.getElementById('consommerError').style.display = 'none';
        setTimeout(function() { fermerConsommer(); loadDelegations(); }, 1000);
      }
      btn.disabled = false;
      btn.textContent = 'Enregistrer le paiement';
    });
  })
  .catch(function() {
    document.getElementById('consommerError').textContent = 'Erreur de connexion au serveur.';
    document.getElementById('consommerError').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Enregistrer le paiement';
  });
}

var histConsoDelegationId = null;

function ouvrirHistConso(id) {
  histConsoDelegationId = id;
  document.getElementById('consoDateDebut').value = '';
  document.getElementById('consoDateFin').value = '';
  document.getElementById('consoAgent').value = '';
  chargerHistConso(id);
  document.getElementById('modalHistConso').style.display = 'block';
}

function filtrerHistConso() {
  if (histConsoDelegationId) chargerHistConso(histConsoDelegationId);
}

function chargerHistConso(id) {
  var params = [];
  var dd = document.getElementById('consoDateDebut').value;
  var df = document.getElementById('consoDateFin').value;
  var ag = document.getElementById('consoAgent').value.trim();
  if (dd) params.push('date_debut=' + dd);
  if (df) params.push('date_fin_filtre=' + df);
  if (ag) params.push('nom_agent=' + encodeURIComponent(ag));
  var qs = params.length ? '?' + params.join('&') : '';

  fetch(API + '/delegation-credits/' + id + '/consommations' + qs)
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var d = data.delegation;
      var taux = d.taux_consommation || 0;
      var couleurTaux = taux > 80 ? '#e03131' : (taux > 50 ? '#e67700' : '#087f5b');

      document.getElementById('histConsoResume').innerHTML =
        '<div style="background:#e7f5ff; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Disponible</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#1971c2;">' + formatMontant(d.montant_disponible) + '</div></div>' +
        '<div style="background:#ffe3e3; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Consommé</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#c92a2a;">' + formatMontant(d.montant_consomme) + '</div>' +
          '<div style="font-size:0.75rem; color:' + couleurTaux + '; font-weight:600;">' + taux + '% consommé</div></div>' +
        '<div style="background:#d3f9d8; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Solde</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#087f5b;">' + formatMontant(d.solde) + '</div></div>';

      var paiements = data.paiements || [];
      var tbody = document.getElementById('histConsoBody');
      var empty = document.getElementById('histConsoEmpty');

      if (paiements.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
      } else {
        empty.style.display = 'none';
        tbody.innerHTML = paiements.map(function(p) {
          return '<tr>' +
            '<td>' + formatDate(p.date_paiement) + '</td>' +
            '<td>' + p.nom_agent + '</td>' +
            '<td>' + p.mois + '</td>' +
            '<td style="font-weight:600;">' + formatMontant(p.montant) + '</td>' +
            '</tr>';
        }).join('');

        tbody.innerHTML += '<tr style="font-weight:bold; background:#f1f3f5;">' +
          '<td colspan="3">Total (' + data.nombre + ' paiement(s))</td>' +
          '<td>' + formatMontant(data.total) + '</td></tr>';
      }
    })
    .catch(function(e) {
      console.error('Erreur historique conso:', e);
    });
}

function ouvrirAssocier(id) {
  var delegation = allDelegations.find(function(d) { return d.id == id; });
  if (!delegation) return;

  document.getElementById('associer_delegation_id').value = id;
  document.getElementById('associerError').style.display = 'none';
  document.getElementById('associerSuccess').style.display = 'none';
  document.getElementById('associerSearch').value = '';

  var soldeRestant = delegation.montant_disponible - delegation.montant_consomme;
  document.getElementById('associerInfo').innerHTML =
    '<strong>' + delegation.reference + '</strong> — ' + (delegation.objet || '') +
    '<br><small>Solde restant : <strong style="color:' + (soldeRestant > 0 ? '#087f5b' : '#e03131') + ';">' + formatMontant(soldeRestant) + '</strong>' +
    ' | Statut : <strong>' + delegation.statut + '</strong></small>';

  chargerBulletinsDisponibles(id);
  document.getElementById('modalAssocier').style.display = 'block';
}

function chargerBulletinsDisponibles(id) {
  var search = document.getElementById('associerSearch').value.trim();
  var qs = search ? '?search=' + encodeURIComponent(search) : '';

  fetch(API + '/delegation-credits/' + id + '/bulletins-disponibles' + qs)
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var bulletins = data.bulletins || [];
      var tbody = document.getElementById('associerBody');
      var empty = document.getElementById('associerEmpty');

      if (bulletins.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
      } else {
        empty.style.display = 'none';
        tbody.innerHTML = bulletins.map(function(b) {
          var depStyle = b.depassement ? ' style="color:#e03131; font-weight:bold;"' : ' style="font-weight:600;"';
          var btnDisabled = b.depassement ? ' disabled title="Solde insuffisant"' : '';
          var btnClass = b.depassement ? 'table-action" style="opacity:0.5; cursor:not-allowed;"' : 'table-action primary"';
          return '<tr>' +
            '<td>' + (b.reference || '-') + '</td>' +
            '<td>' + b.nom_complet + '<br><small style="color:#868e96;">' + b.matricule + ' | ' + b.corps + '</small></td>' +
            '<td>' + (b.mois_validite || '-') + '</td>' +
            '<td' + depStyle + '>' + formatMontant(b.net_a_payer) + (b.depassement ? ' <i class="fa-solid fa-triangle-exclamation" style="color:#e03131;"></i>' : '') + '</td>' +
            '<td><button class="' + btnClass + ' type="button"' + btnDisabled + ' onclick="confirmerAssociation(' + id + ', ' + b.id + ', \'' + (b.reference || '') + '\', ' + b.net_a_payer + ')">Associer</button></td>' +
            '</tr>';
        }).join('');
      }
    })
    .catch(function(e) {
      console.error('Erreur bulletins:', e);
    });
}

function confirmerAssociation(delegationId, bultinId, refBulletin, montant) {
  if (!confirm('Associer le bulletin ' + refBulletin + ' (' + formatMontant(montant) + ') à cette délégation ?')) return;

  document.getElementById('associerError').style.display = 'none';
  document.getElementById('associerSuccess').style.display = 'none';

  fetch(API + '/delegation-credits/' + delegationId + '/associer-bulletin', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ bultin_id: bultinId })
  })
  .then(function(res) {
    return res.json().then(function(result) {
      if (!res.ok) {
        document.getElementById('associerError').textContent = result.message || 'Erreur lors de l\'association.';
        document.getElementById('associerError').style.display = 'block';
      } else {
        document.getElementById('associerSuccess').textContent = result.message;
        document.getElementById('associerSuccess').style.display = 'block';
        chargerBulletinsDisponibles(delegationId);
        setTimeout(function() { loadDelegations(); }, 1500);
      }
    });
  })
  .catch(function() {
    document.getElementById('associerError').textContent = 'Erreur de connexion au serveur.';
    document.getElementById('associerError').style.display = 'block';
  });
}

var histAssocDelegationId = null;

function ouvrirHistAssoc(id) {
  histAssocDelegationId = id;
  document.getElementById('histAssocDateDebut').value = '';
  document.getElementById('histAssocDateFin').value = '';
  document.getElementById('histAssocAgent').value = '';
  chargerHistAssoc(id);
  document.getElementById('modalHistAssoc').style.display = 'block';
}

function chargerHistAssoc(id) {
  var params = [];
  var dd = document.getElementById('histAssocDateDebut').value;
  var df = document.getElementById('histAssocDateFin').value;
  var ag = document.getElementById('histAssocAgent').value.trim();
  if (dd) params.push('date_debut=' + dd);
  if (df) params.push('date_fin_filtre=' + df);
  if (ag) params.push('nom_agent=' + encodeURIComponent(ag));
  var qs = params.length ? '?' + params.join('&') : '';

  fetch(API + '/delegation-credits/' + id + '/associations' + qs)
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var d = data.delegation;
      var taux = d.montant_disponible > 0
        ? Math.round((d.montant_consomme / d.montant_disponible) * 100 * 10) / 10
        : 0;
      var couleurTaux = taux > 80 ? '#e03131' : (taux > 50 ? '#e67700' : '#087f5b');

      document.getElementById('histAssocResume').innerHTML =
        '<div style="background:#e7f5ff; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Disponible</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#1971c2;">' + formatMontant(d.montant_disponible) + '</div></div>' +
        '<div style="background:#ffe3e3; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Consommé (bulletins)</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#c92a2a;">' + formatMontant(d.montant_consomme) + '</div>' +
          '<div style="font-size:0.75rem; color:' + couleurTaux + '; font-weight:600;">' + taux + '%</div></div>' +
        '<div style="background:#d3f9d8; border-radius:8px; padding:12px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Solde</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#087f5b;">' + formatMontant(d.solde) + '</div></div>';

      var associations = data.associations || [];
      var tbody = document.getElementById('histAssocBody');
      var empty = document.getElementById('histAssocEmpty');

      if (associations.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
      } else {
        empty.style.display = 'none';
        tbody.innerHTML = associations.map(function(a) {
          return '<tr>' +
            '<td>' + formatDate(a.date_paiement) + '</td>' +
            '<td>' + a.bulletin_reference + '</td>' +
            '<td>' + a.nom_agent + '</td>' +
            '<td>' + a.mois + '</td>' +
            '<td style="font-weight:600;">' + formatMontant(a.montant) + '</td>' +
            '</tr>';
        }).join('');

        tbody.innerHTML += '<tr style="font-weight:bold; background:#f1f3f5;">' +
          '<td colspan="4">Total (' + data.nombre + ' bulletin(s))</td>' +
          '<td>' + formatMontant(data.total) + '</td></tr>';
      }
    })
    .catch(function(e) {
      console.error('Erreur historique associations:', e);
    });
}

function ouvrirSolde(id) {
  fetch(API + '/delegation-credits/' + id + '/solde')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var banner = document.getElementById('soldeAlerteBanner');
      if (data.alerte) {
        var bgAlert = data.alerte.niveau === 'critique' ? '#ffe3e3' : '#fff3bf';
        var colorAlert = data.alerte.niveau === 'critique' ? '#c92a2a' : '#e67700';
        var iconAlert = data.alerte.niveau === 'critique' ? 'fa-circle-xmark' : 'fa-triangle-exclamation';
        banner.style.display = 'block';
        banner.style.background = bgAlert;
        banner.style.color = colorAlert;
        banner.innerHTML = '<i class="fa-solid ' + iconAlert + '"></i> ' + data.alerte.message;
      } else {
        banner.style.display = 'none';
      }

      document.getElementById('soldeInfo').innerHTML =
        '<strong>' + data.reference + '</strong>' + (data.objet ? ' — ' + data.objet : '') +
        (data.structure ? '<br><small>Structure : ' + data.structure + (data.service ? ' | Service : ' + data.service : '') + '</small>' : '');

      var tauxConso = data.taux_consommation || 0;
      var tauxEng = data.taux_engagement || 0;
      var pctSolde = data.pct_solde || 0;

      document.getElementById('soldePctLabel').textContent = tauxConso + '% consommé';
      document.getElementById('barreConsomme').style.width = Math.min(tauxConso, 100) + '%';
      document.getElementById('barreEngage').style.width = Math.min(tauxEng, 100) + '%';

      var soldeColor = pctSolde <= 0 ? '#c92a2a' : (pctSolde <= 20 ? '#e67700' : '#087f5b');

      document.getElementById('soldeDetails').innerHTML =
        '<div style="background:#e7f5ff; border-radius:8px; padding:14px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Montant initial</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#1971c2;">' + formatMontant(data.montant_initial) + '</div></div>' +
        '<div style="background:#d3f9d8; border-radius:8px; padding:14px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Montant disponible</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#087f5b;">' + formatMontant(data.montant_disponible) + '</div></div>' +
        '<div style="background:#fff3bf; border-radius:8px; padding:14px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Montant engagé</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#e67700;">' + formatMontant(data.montant_engage) + '</div>' +
          '<div style="font-size:0.75rem; color:#868e96;">' + tauxEng + '% du disponible</div></div>' +
        '<div style="background:#ffe3e3; border-radius:8px; padding:14px; text-align:center;">' +
          '<div style="font-size:0.78rem; color:#495057;">Montant consommé</div>' +
          '<div style="font-size:1.1rem; font-weight:bold; color:#c92a2a;">' + formatMontant(data.montant_consomme) + '</div>' +
          '<div style="font-size:0.75rem; color:#868e96;">' + tauxConso + '% du disponible</div></div>' +
        '<div style="grid-column:span 2; background:linear-gradient(135deg, ' + (pctSolde <= 20 ? '#fff3bf' : '#d3f9d8') + ', #fff); border-radius:8px; padding:16px; text-align:center; border:2px solid ' + soldeColor + ';">' +
          '<div style="font-size:0.85rem; color:#495057;">Solde restant</div>' +
          '<div style="font-size:1.5rem; font-weight:bold; color:' + soldeColor + ';">' + formatMontant(data.solde) + '</div>' +
          '<div style="font-size:0.8rem; color:' + soldeColor + ';">' + pctSolde + '% du crédit disponible</div></div>';

      document.getElementById('modalSolde').style.display = 'block';
    })
    .catch(function(e) {
      console.error('Erreur solde:', e);
      alert('Erreur lors du chargement du solde.');
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
