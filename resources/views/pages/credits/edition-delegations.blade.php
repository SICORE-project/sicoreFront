@extends('layouts.app')

@section('title', 'SICORE - Edition des delegations de credits')
@section('content')
<main class="main-content">
  <x-topbar
    title="Édition des délégations de crédits"
    subtitle="Gestion de la paie > Délégation de crédit > Édition des délégations"
    icon="fa-solid fa-file-signature"
    search-id="editionSearch"
    search-placeholder="Rechercher un carton…"
    filter-target="#editionTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Éditer l'état des délégations de crédits sur une période comptable.</li>
        <li>Distinguer l'état sur salaire de l'état sur prime scolaire.</li>
        <li>Suivre le montant engagé et le reste par carton et par délégation.</li>
      </ul>
    </section>

    <p class="breadcrumb">Gestion de la paie &gt; Délégation de crédit &gt; Édition des délégations</p>

    {{-- Periode comptable : reprend frmEditDetailDelegation.aspx --}}
    <section class="table-card">
      <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
        <i class="fa-solid fa-calendar-days"></i> Période comptable
      </h2>

      <form id="formEdition">
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr); gap:16px;">
          <div class="form-group">
            <label for="annee_academique">Année académique</label>
            <select class="form-control" id="annee_academique">
              <option value="">Toutes</option>
            </select>
          </div>
          <div class="form-group">
            <label for="corps_enseignant_id">Corps d'enseignant</label>
            <select class="form-control" id="corps_enseignant_id">
              <option value="">Tous</option>
            </select>
          </div>
          <div class="form-group">
            <label for="ia_id">IA</label>
            <select class="form-control" id="ia_id">
              <option value="">Toutes</option>
            </select>
          </div>
          <div class="form-group">
            <label for="ief_id">IEF</label>
            <select class="form-control" id="ief_id">
              <option value="">Toutes</option>
            </select>
          </div>
          <div class="form-group">
            <label for="date_debut">Période du</label>
            <input class="form-control" id="date_debut" type="date">
          </div>
          <div class="form-group">
            <label for="date_fin">au</label>
            <input class="form-control" id="date_fin" type="date">
          </div>
        </div>

        <fieldset style="margin-top:16px; border:1px solid #dee2e6; border-radius:8px; padding:16px;">
          <legend style="padding:0 8px; font-weight:600; color:#087f5b;">Type d'édition</legend>
          <label style="display:block; margin-bottom:8px; font-weight:400;">
            <input type="radio" name="type" value="salaire" checked> État sur salaire
          </label>
          <label style="display:block; font-weight:400;">
            <input type="radio" name="type" value="prime_scolaire"> État sur prime scolaire
          </label>
        </fieldset>

        <p class="empty-message" id="formError" style="display:none; color:#c92a2a;"></p>

        <div class="actions-group" style="margin-top:16px;">
          <button class="btn-primary"   type="submit" id="btnConsulter">Consulter</button>
          <button class="btn-secondary" type="button" id="btnReinitialiser">Réinitialiser</button>
          <button class="btn-secondary" type="button" id="btnExporter" disabled>Exporter CSV</button>
          <button class="btn-secondary" type="button" id="btnImprimer" disabled>Imprimer</button>
        </div>
      </form>
    </section>

    {{-- Resultats --}}
    <div id="zoneResultats" style="display:none;">
      <div class="stats-grid four">
        <article class="stat-card">
          <div>
            <p class="stat-label">Délégations</p>
            <p class="stat-value" id="statDelegations">0</p>
            <p class="stat-note">Sur la période</p>
          </div>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Montant délégué</p>
            <p class="stat-value" id="statMontant">0</p>
            <p class="stat-note">FCFA</p>
          </div>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Montant engagé</p>
            <p class="stat-value" id="statEngage">0</p>
            <p class="stat-note">FCFA</p>
          </div>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Reste</p>
            <p class="stat-value" id="statReste">0</p>
            <p class="stat-note">FCFA</p>
          </div>
        </article>
      </div>

      <section class="table-card">
        <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
          <i class="fa-solid fa-list-ul"></i> Détail par carton
          <span id="libelleType" style="font-weight:400; font-size:0.95rem; color:#495057;"></span>
        </h2>
        <div class="table-responsive">
          <table class="table" id="editionTable">
            <thead>
              <tr>
                <th>N° Carton</th>
                <th>Référence</th>
                <th>Corps d'enseignant</th>
                <th>IA</th>
                <th>IEF</th>
                <th>Imputation budgétaire</th>
                <th style="text-align:right;">Montant</th>
                <th style="text-align:right;">Engagement</th>
                <th style="text-align:right;">Reste</th>
              </tr>
            </thead>
            <tbody id="editionBody"></tbody>
            <tfoot>
              <tr style="font-weight:700; background:#f1f3f5;">
                <td colspan="6">Total général</td>
                <td style="text-align:right;" id="totalMontant">0</td>
                <td style="text-align:right;" id="totalEngagement">0</td>
                <td style="text-align:right;" id="totalReste">0</td>
              </tr>
            </tfoot>
          </table>
        </div>
        <p class="empty-message" id="emptyMsg" style="display:none;">Aucune ligne pour ces critères.</p>
      </section>

      <section class="table-card">
        <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
          <i class="fa-solid fa-table-list"></i> Récapitulatif par délégation
        </h2>
        <div class="table-responsive">
          <table class="table" id="recapTable">
            <thead>
              <tr>
                <th>Référence</th>
                <th>Objet</th>
                <th>Période</th>
                <th style="text-align:right;">Lignes</th>
                <th style="text-align:right;">Montant</th>
                <th style="text-align:right;">Engagement</th>
                <th style="text-align:right;">Reste</th>
              </tr>
            </thead>
            <tbody id="recapBody"></tbody>
          </table>
        </div>
      </section>
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';
var derniersResultats = null;
var tousLesIefs = [];

document.addEventListener('DOMContentLoaded', function () {
  chargerFiltres().then(setupEvents);
});

function chargerFiltres() {
  return fetch(API + '/edition-delegations/filtres')
    .then(function (res) { return res.json(); })
    .then(function (data) {
      var selectAnnee = document.getElementById('annee_academique');
      (data.annees_academiques || []).forEach(function (a) {
        selectAnnee.innerHTML += '<option value="' + a + '">' + a + '</option>';
      });
      remplirSelect('corps_enseignant_id', data.corps_enseignants, 'libelle');
      remplirSelect('ia_id', data.ias, 'libelle', 'code');
      tousLesIefs = data.iefs || [];
      majIefs();
    })
    .catch(function (e) {
      console.error('Erreur chargement filtres:', e);
      afficherErreur('Erreur de connexion au serveur backend (port 8000).');
    });
}

/* L'IEF depend de l'IA choisie, comme dans FINPRONET. */
function majIefs() {
  var iaId = document.getElementById('ia_id').value;
  var select = document.getElementById('ief_id');
  var actuel = select.value;
  select.innerHTML = '<option value="">Toutes</option>';
  tousLesIefs
    .filter(function (ief) { return !iaId || String(ief.ia_id) === String(iaId); })
    .forEach(function (ief) {
      select.innerHTML += '<option value="' + ief.id + '">' + ief.code + ' — ' + ief.libelle + '</option>';
    });
  select.value = actuel;
}

function setupEvents() {
  document.getElementById('ia_id').addEventListener('change', majIefs);
  document.getElementById('formEdition').addEventListener('submit', consulter);
  document.getElementById('btnReinitialiser').addEventListener('click', reinitialiser);
  document.getElementById('btnExporter').addEventListener('click', exporterCSV);
  document.getElementById('btnImprimer').addEventListener('click', function () { window.print(); });
}

function consulter(e) {
  e.preventDefault();
  cacherErreur();

  var params = [];
  ajouterParam(params, 'annee_academique', 'annee_academique');
  ajouterParam(params, 'corps_enseignant_id', 'corps_enseignant_id');
  ajouterParam(params, 'ia_id', 'ia_id');
  ajouterParam(params, 'ief_id', 'ief_id');
  ajouterParam(params, 'date_debut', 'date_debut');
  ajouterParam(params, 'date_fin', 'date_fin');
  params.push('type=' + typeChoisi());

  fetch(API + '/edition-delegations?' + params.join('&'))
    .then(function (res) { return res.json().then(function (b) { return { ok: res.ok, body: b }; }); })
    .then(function (r) {
      if (!r.ok) return afficherErreur(messageErreur(r.body));
      derniersResultats = r.body;
      afficherResultats(r.body);
    })
    .catch(function (e) {
      console.error(e);
      afficherErreur('Erreur de connexion au serveur backend (port 8000).');
    });
}

function afficherResultats(data) {
  document.getElementById('zoneResultats').style.display = 'block';
  document.getElementById('btnExporter').disabled = false;
  document.getElementById('btnImprimer').disabled = false;

  var t = data.totaux;
  document.getElementById('statDelegations').textContent = t.nombre_delegations;
  document.getElementById('statMontant').textContent = formatCourt(t.total_montant);
  document.getElementById('statEngage').textContent = formatCourt(t.total_engagement);
  document.getElementById('statReste').textContent = formatCourt(t.total_reste);

  document.getElementById('libelleType').textContent =
    '— ' + (typeChoisi() === 'salaire' ? 'État sur salaire' : 'État sur prime scolaire');

  var tbody = document.getElementById('editionBody');
  tbody.innerHTML = '';
  var empty = document.getElementById('emptyMsg');

  if (!data.lignes.length) {
    empty.style.display = 'block';
  } else {
    empty.style.display = 'none';
    data.lignes.forEach(function (l) {
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td><strong>' + (l.numero_carton || '-') + '</strong></td>' +
        '<td>' + (l.reference || '-') + '</td>' +
        '<td>' + l.corps_enseignant + '</td>' +
        '<td>' + l.ia + '</td>' +
        '<td>' + l.ief + '</td>' +
        '<td style="font-family:monospace; font-size:0.85rem;">' + (l.imputation_budgetaire || '-') + '</td>' +
        '<td style="text-align:right;">' + formatMontant(l.montant) + '</td>' +
        '<td style="text-align:right;">' + formatMontant(l.engagement) + '</td>' +
        '<td style="text-align:right;' + (l.reste < 0 ? ' color:#c92a2a;' : '') + '">' + formatMontant(l.reste) + '</td>';
      tbody.appendChild(tr);
    });
  }

  document.getElementById('totalMontant').textContent = formatMontant(t.total_montant);
  document.getElementById('totalEngagement').textContent = formatMontant(t.total_engagement);
  document.getElementById('totalReste').textContent = formatMontant(t.total_reste);

  var recap = document.getElementById('recapBody');
  recap.innerHTML = '';
  data.recapitulatif.forEach(function (r) {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><strong>' + (r.reference || '-') + '</strong></td>' +
      '<td>' + (r.objet || '-') + '</td>' +
      '<td>' + (r.periode_paie || '-') + '</td>' +
      '<td style="text-align:right;">' + r.nombre_lignes + '</td>' +
      '<td style="text-align:right;">' + formatMontant(r.total_montant) + '</td>' +
      '<td style="text-align:right;">' + formatMontant(r.total_engagement) + '</td>' +
      '<td style="text-align:right;">' + formatMontant(r.total_reste) + '</td>';
    recap.appendChild(tr);
  });
}

function reinitialiser() {
  ['annee_academique', 'corps_enseignant_id', 'ia_id', 'ief_id', 'date_debut', 'date_fin'].forEach(function (id) {
    document.getElementById(id).value = '';
  });
  document.querySelector('input[name="type"][value="salaire"]').checked = true;
  majIefs();
  cacherErreur();
  document.getElementById('zoneResultats').style.display = 'none';
  document.getElementById('btnExporter').disabled = true;
  document.getElementById('btnImprimer').disabled = true;
  derniersResultats = null;
}

function exporterCSV() {
  if (!derniersResultats) return;

  var entetes = ['N° Carton', 'Référence', 'Corps', 'IA', 'IEF', 'Imputation', 'Montant', 'Engagement', 'Reste'];
  var lignes = derniersResultats.lignes.map(function (l) {
    return [l.numero_carton, l.reference, l.corps_enseignant, l.ia, l.ief,
            l.imputation_budgetaire, l.montant, l.engagement, l.reste];
  });

  var csv = [entetes].concat(lignes)
    .map(function (ligne) {
      return ligne.map(function (c) { return '"' + String(c === null ? '' : c).replace(/"/g, '""') + '"'; }).join(';');
    })
    .join('\n');

  var blob = new Blob(["﻿" + csv], { type: 'text/csv;charset=utf-8;' });
  var lien = document.createElement('a');
  lien.href = URL.createObjectURL(blob);
  lien.download = 'edition-delegations-' + typeChoisi() + '.csv';
  lien.click();
  URL.revokeObjectURL(lien.href);
}

/* ---------------------------------------------------------------- utilitaires */

function typeChoisi() {
  return document.querySelector('input[name="type"]:checked').value;
}

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
</script>
@endpush
