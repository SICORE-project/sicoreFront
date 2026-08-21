@extends('layouts.app')

@section('title', 'SICORE - Mes projections')
@section('content')
<main class="main-content">
  <x-topbar
    title="Mes projections"
    subtitle="Gestion de la paie > Délégation de crédit > Mes projections"
    icon="fa-solid fa-chart-line"
    search-id="projectionSearch"
    search-placeholder="Rechercher…"
    filter-target="#echeancierTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Projeter le besoin en crédits sur plusieurs mois.</li>
        <li>Partir des crédits réellement engagés sur une période de référence.</li>
        <li>Appliquer un taux de majoration et les options tabaski.</li>
      </ul>
    </section>

    <p class="breadcrumb">Gestion de la paie &gt; Délégation de crédit &gt; Mes projections</p>

    {{-- Periode comptable : reprend frmEditExpressionDel.aspx --}}
    <section class="table-card">
      <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
        <i class="fa-solid fa-calendar-days"></i> Période comptable
      </h2>

      <form id="formProjection">
        <div class="stats-grid" style="grid-template-columns:repeat(2,1fr); gap:16px;">
          <div class="form-group">
            <label for="annee_academique">Année académique</label>
            <select class="form-control" id="annee_academique"><option value="">—</option></select>
          </div>
          <div class="form-group">
            <label for="annee_reference">Année de référence</label>
            <select class="form-control" id="annee_reference"><option value="">Toutes</option></select>
          </div>
          <div class="form-group">
            <label for="periode_reference">Période de référence</label>
            <select class="form-control" id="periode_reference"><option value="">Toutes</option></select>
          </div>
          <div class="form-group">
            <label for="corps_enseignant_id">Corps d'enseignant</label>
            <select class="form-control" id="corps_enseignant_id"><option value="">Tous</option></select>
          </div>
          <div class="form-group">
            <label for="taux_majoration">Taux de majoration sur salaire (%)</label>
            <input class="form-control" id="taux_majoration" type="number" min="0" max="100" step="0.01" value="0.00">
          </div>
          <div class="form-group">
            <label for="nombre_mois">Projection des crédits sur (mois) *</label>
            <input class="form-control" id="nombre_mois" type="number" min="1" max="60" step="1" value="12" required>
          </div>
        </div>

        <fieldset style="margin-top:16px; border:1px solid #dee2e6; border-radius:8px; padding:16px;">
          <legend style="padding:0 8px; font-weight:600; color:#087f5b;">Options tabaski</legend>

          <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap;">
            <label style="font-weight:400; min-width:260px;">
              <input type="checkbox" id="chk_avance"> Prise en compte avance tabaski
            </label>
            <input class="form-control" id="avance_tabaski" type="number" min="0" step="1" value="0" disabled style="max-width:220px;">
          </div>

          <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <label style="font-weight:400; min-width:260px;">
              <input type="checkbox" id="chk_sans_retenue"> Salaire sans retenue tabaski
            </label>
            <input class="form-control" id="sans_retenue_tabaski" type="number" min="0" step="1" value="0" disabled style="max-width:220px;">
          </div>

          <label style="display:block; margin-top:14px; font-weight:400;">
            <input type="checkbox" id="exclure_dakar"> Ne pas prendre en compte DAKAR
          </label>
        </fieldset>

        <p class="empty-message" id="formError" style="display:none; color:#c92a2a;"></p>

        <div class="actions-group" style="margin-top:16px;">
          <button class="btn-primary"   type="submit" id="btnConsulter">Consulter</button>
          <button class="btn-secondary" type="button" id="btnReinitialiser">Réinitialiser</button>
          <button class="btn-secondary" type="button" id="btnExporter" disabled>Exporter CSV</button>
        </div>
      </form>
    </section>

    <div id="zoneResultats" style="display:none;">
      <div class="stats-grid four">
        <article class="stat-card">
          <div>
            <p class="stat-label">Base de référence</p>
            <p class="stat-value" id="statBase">0</p>
            <p class="stat-note">Crédits engagés</p>
          </div>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Mensuel majoré</p>
            <p class="stat-value" id="statMensuel">0</p>
            <p class="stat-note" id="statTaux">+0 %</p>
          </div>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Durée</p>
            <p class="stat-value" id="statMois">0</p>
            <p class="stat-note">mois projetés</p>
          </div>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Projection totale</p>
            <p class="stat-value" id="statProjection">0</p>
            <p class="stat-note">FCFA</p>
          </div>
        </article>
      </div>

      {{-- Le detail du calcul, etape par etape, pour qu'il reste verifiable --}}
      <section class="table-card">
        <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
          <i class="fa-solid fa-calculator"></i> Détail du calcul
        </h2>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr><th>Étape</th><th style="text-align:right;">Montant (FCFA)</th></tr>
            </thead>
            <tbody id="calculBody"></tbody>
          </table>
        </div>
        <p class="empty-message" style="display:block; text-align:left; color:#495057;">
          Formule appliquée : <strong>(base de référence − salaire sans retenue tabaski + avance tabaski)
          × (1 + taux) × nombre de mois</strong>. À faire valider par le métier.
        </p>
      </section>

      <section class="table-card">
        <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
          <i class="fa-solid fa-location-dot"></i> Origine de la base, par IA
        </h2>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr><th>IA</th><th style="text-align:right;">Lignes</th><th style="text-align:right;">Engagement</th></tr>
            </thead>
            <tbody id="parIaBody"></tbody>
          </table>
        </div>
      </section>

      <section class="table-card">
        <h2 style="margin:0 0 16px; color:#087f5b; font-size:1.25rem;">
          <i class="fa-solid fa-timeline"></i> Échéancier
        </h2>
        <div class="table-responsive">
          <table class="table" id="echeancierTable">
            <thead>
              <tr><th>Mois</th><th style="text-align:right;">Montant</th><th style="text-align:right;">Cumul</th></tr>
            </thead>
            <tbody id="echeancierBody"></tbody>
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
var dernier = null;

document.addEventListener('DOMContentLoaded', function () {
  chargerFiltres().then(setupEvents);
});

function chargerFiltres() {
  return fetch(API + '/projections-delegation/filtres')
    .then(function (res) { return res.json(); })
    .then(function (data) {
      (data.annees_academiques || []).forEach(function (a) {
        document.getElementById('annee_academique').innerHTML += '<option value="' + a + '">' + a + '</option>';
        document.getElementById('annee_reference').innerHTML += '<option value="' + a + '">' + a + '</option>';
      });
      (data.periodes || []).forEach(function (p) {
        document.getElementById('periode_reference').innerHTML += '<option value="' + p + '">' + p + '</option>';
      });
      (data.corps_enseignants || []).forEach(function (c) {
        document.getElementById('corps_enseignant_id').innerHTML += '<option value="' + c.id + '">' + c.libelle + '</option>';
      });
    })
    .catch(function (e) {
      console.error(e);
      afficherErreur('Erreur de connexion au serveur backend (port 8000).');
    });
}

function setupEvents() {
  basculer('chk_avance', 'avance_tabaski');
  basculer('chk_sans_retenue', 'sans_retenue_tabaski');
  document.getElementById('formProjection').addEventListener('submit', consulter);
  document.getElementById('btnReinitialiser').addEventListener('click', reinitialiser);
  document.getElementById('btnExporter').addEventListener('click', exporterCSV);
}

/* Le montant n'est saisissable que si la case est cochee, comme dans FINPRONET. */
function basculer(idCase, idMontant) {
  var c = document.getElementById(idCase);
  var m = document.getElementById(idMontant);
  c.addEventListener('change', function () {
    m.disabled = !c.checked;
    if (!c.checked) m.value = 0;
  });
}

function consulter(e) {
  e.preventDefault();
  cacherErreur();

  var params = [];
  ajouter(params, 'annee_academique', 'annee_academique');
  ajouter(params, 'annee_reference', 'annee_reference');
  ajouter(params, 'periode_reference', 'periode_reference');
  ajouter(params, 'corps_enseignant_id', 'corps_enseignant_id');
  params.push('taux_majoration=' + (document.getElementById('taux_majoration').value || 0));
  params.push('nombre_mois=' + (document.getElementById('nombre_mois').value || 1));

  if (document.getElementById('chk_avance').checked) {
    params.push('avance_tabaski=' + (document.getElementById('avance_tabaski').value || 0));
  }
  if (document.getElementById('chk_sans_retenue').checked) {
    params.push('sans_retenue_tabaski=' + (document.getElementById('sans_retenue_tabaski').value || 0));
  }
  if (document.getElementById('exclure_dakar').checked) {
    params.push('exclure_dakar=1');
  }

  fetch(API + '/projections-delegation?' + params.join('&'))
    .then(function (res) { return res.json().then(function (b) { return { ok: res.ok, body: b }; }); })
    .then(function (r) {
      if (!r.ok) return afficherErreur(messageErreur(r.body));
      dernier = r.body;
      afficher(r.body);
    })
    .catch(function (e) {
      console.error(e);
      afficherErreur('Erreur de connexion au serveur backend (port 8000).');
    });
}

function afficher(data) {
  document.getElementById('zoneResultats').style.display = 'block';
  document.getElementById('btnExporter').disabled = false;

  var c = data.calcul;
  document.getElementById('statBase').textContent = court(c.base_reference);
  document.getElementById('statMensuel').textContent = court(c.mensuel_majore);
  document.getElementById('statTaux').textContent = '+' + c.taux_applique + ' %';
  document.getElementById('statMois').textContent = c.nombre_mois;
  document.getElementById('statProjection').textContent = court(c.projection_totale);

  var etapes = [
    ['Base de référence — crédits engagés', c.base_reference],
    ['− Salaire sans retenue tabaski', -c.moins_sans_retenue],
    ['+ Avance tabaski', c.plus_avance_tabaski],
    ['= Base ajustée', c.base_ajustee],
    ['× Majoration de ' + c.taux_applique + ' % → mensuel majoré', c.mensuel_majore],
    ['× ' + c.nombre_mois + ' mois → projection totale', c.projection_totale]
  ];

  document.getElementById('calculBody').innerHTML = etapes.map(function (e, i) {
    var fort = (i === etapes.length - 1);
    return '<tr' + (fort ? ' style="font-weight:700; background:#f1f3f5;"' : '') + '>' +
           '<td>' + e[0] + '</td>' +
           '<td style="text-align:right;">' + montant(e[1]) + '</td></tr>';
  }).join('');

  document.getElementById('parIaBody').innerHTML = data.source.par_ia.map(function (ia) {
    return '<tr><td>' + ia.ia + '</td>' +
           '<td style="text-align:right;">' + ia.nombre_lignes + '</td>' +
           '<td style="text-align:right;">' + montant(ia.engagement) + '</td></tr>';
  }).join('') || '<tr><td colspan="3">Aucune ventilation sur cette période de référence.</td></tr>';

  document.getElementById('echeancierBody').innerHTML = data.echeancier.map(function (l) {
    return '<tr><td>Mois ' + l.mois + '</td>' +
           '<td style="text-align:right;">' + montant(l.montant) + '</td>' +
           '<td style="text-align:right;">' + montant(l.cumul) + '</td></tr>';
  }).join('');
}

function reinitialiser() {
  ['annee_academique', 'annee_reference', 'periode_reference', 'corps_enseignant_id'].forEach(function (id) {
    document.getElementById(id).value = '';
  });
  document.getElementById('taux_majoration').value = '0.00';
  document.getElementById('nombre_mois').value = 12;
  ['chk_avance', 'chk_sans_retenue', 'exclure_dakar'].forEach(function (id) {
    document.getElementById(id).checked = false;
  });
  ['avance_tabaski', 'sans_retenue_tabaski'].forEach(function (id) {
    document.getElementById(id).value = 0;
    document.getElementById(id).disabled = true;
  });
  cacherErreur();
  document.getElementById('zoneResultats').style.display = 'none';
  document.getElementById('btnExporter').disabled = true;
  dernier = null;
}

function exporterCSV() {
  if (!dernier) return;
  var lignes = [['Mois', 'Montant', 'Cumul']].concat(
    dernier.echeancier.map(function (l) { return [l.mois, l.montant, l.cumul]; })
  );
  var csv = lignes.map(function (l) {
    return l.map(function (c) { return '"' + String(c) + '"'; }).join(';');
  }).join('\n');

  var blob = new Blob(["﻿" + csv], { type: 'text/csv;charset=utf-8;' });
  var lien = document.createElement('a');
  lien.href = URL.createObjectURL(blob);
  lien.download = 'projection-credits.csv';
  lien.click();
  URL.revokeObjectURL(lien.href);
}

/* ---------------------------------------------------------------- utilitaires */

function ajouter(params, cle, id) {
  var v = document.getElementById(id).value;
  if (v) params.push(cle + '=' + encodeURIComponent(v));
}

function montant(v) {
  return new Intl.NumberFormat('fr-FR').format(Math.round(Number(v) || 0));
}

function court(v) {
  var n = Number(v) || 0;
  if (n >= 1000000000) return (n / 1000000000).toFixed(2).replace('.00', '') + ' Md';
  if (n >= 1000000) return (n / 1000000).toFixed(1).replace('.0', '') + 'M';
  if (n >= 1000) return Math.round(n / 1000) + 'K';
  return String(Math.round(n));
}

function messageErreur(body) {
  if (body && body.errors) {
    var cles = Object.keys(body.errors);
    if (cles.length) return body.errors[cles[0]][0];
  }
  return (body && body.message) ? body.message : 'Une erreur est survenue.';
}

function afficherErreur(m) {
  var p = document.getElementById('formError');
  p.textContent = m;
  p.style.display = 'block';
}

function cacherErreur() {
  document.getElementById('formError').style.display = 'none';
}
</script>
@endpush
