@extends('layouts.app')

@section('title', 'SICORE - Engagements vs paie')
@section('content')
<main class="main-content">
  <x-topbar
    title="Engagements vs paie"
    subtitle="Gestion de la paie > Engagements vs paie"
    icon="fa-solid fa-scale-unbalanced"
    search-id="comparaisonSearch"
    search-placeholder="Rechercher…"
    filter-target="#comparaisonTable"
  />

  <section class="content-area">
    <section class="objective-card">
      <h2>Objectifs métier</h2>
      <ul class="objective-list">
        <li>Rapprocher les crédits engagés sur les délégations et la paie réellement versée.</li>
        <li>Faire apparaître l'écart, et les conditions dans lesquelles il est interprétable.</li>
      </ul>
    </section>

    <section class="filter-panel" aria-label="Filtres">
      <div class="form-group">
        <label for="filtreAnnee">Année académique</label>
        <select class="form-control" id="filtreAnnee">
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
        <label for="filtrePeriodePaie">Période de délégation</label>
        <select class="form-control" id="filtrePeriodePaie">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="filtrePeriodeBulletin">Période de bulletin</label>
        <select class="form-control" id="filtrePeriodeBulletin">
          <option value="">Toutes</option>
        </select>
      </div>
      <div class="actions-group">
        <button class="btn-primary" type="button" id="btnComparer">Comparer</button>
        <button class="btn-secondary" type="button" id="btnReset">Réinitialiser</button>
      </div>
    </section>

    <!-- Avertissements : conditions d'interprétation de l'écart -->
    <section class="objective-card sensitive-panel" id="avertPanel" style="display:none;">
      <h2>À lire avant d'interpréter l'écart</h2>
      <ul class="objective-list" id="avertList"></ul>
    </section>

    <div class="stats-grid four" id="statsPanel" style="display:none;">
      <article class="stat-card">
        <div>
          <p class="stat-label">Total engagé</p>
          <p class="stat-value" id="statEngage">0</p>
          <p class="stat-note" id="statLignes">0 ligne(s)</p>
        </div>
        <span class="stat-icon green"><i class="fa-solid fa-file-signature" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Total net payé</p>
          <p class="stat-value" id="statNet">0</p>
          <p class="stat-note" id="statAgents">0 agent(s)</p>
        </div>
        <span class="stat-icon blue"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Écart</p>
          <p class="stat-value" id="statEcart">0</p>
          <p class="stat-note" id="statSens">Engagé − payé</p>
        </div>
        <span class="stat-icon red"><i class="fa-solid fa-scale-unbalanced" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div>
          <p class="stat-label">Charges employeur</p>
          <p class="stat-value" id="statCharges">0</p>
          <p class="stat-note" id="statTauxCharges">—</p>
        </div>
        <span class="stat-icon yellow"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
      </article>
    </div>

    <section class="table-card" id="detailPanel" style="display:none;">
      <h3 style="margin:0 0 16px 0; color:#087f5b; font-size:1.1rem;">Détail du rapprochement</h3>
      <div class="table-responsive">
        <table class="table" id="comparaisonTable">
          <thead>
            <tr>
              <th>Indicateur</th>
              <th>Engagements (délégations)</th>
              <th>Paie (bulletins)</th>
            </tr>
          </thead>
          <tbody id="comparaisonBody"></tbody>
        </table>
      </div>
    </section>
  </section>
</main>
@endsection

@push('scripts')
<script>
var API = 'http://127.0.0.1:8000/api';

document.addEventListener('DOMContentLoaded', function() {
  chargerFiltres();

  document.getElementById('btnComparer').addEventListener('click', comparer);

  document.getElementById('btnReset').addEventListener('click', function() {
    ['filtreAnnee', 'filtreIA', 'filtrePeriodePaie', 'filtrePeriodeBulletin'].forEach(function(id) {
      document.getElementById(id).value = '';
    });
    ['avertPanel', 'statsPanel', 'detailPanel'].forEach(function(id) {
      document.getElementById(id).style.display = 'none';
    });
  });
});

function chargerFiltres() {
  fetch(API + '/comparaison-engagements-paie/filtres')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      remplir('filtreAnnee', data.annees_academiques, function(a) { return { value: a, label: a }; });
      remplir('filtrePeriodePaie', data.periodes_paie, function(p) { return { value: p, label: p }; });
      remplir('filtrePeriodeBulletin', data.periodes_bulletin, function(p) { return { value: p, label: p }; });
      remplir('filtreIA', data.ias, function(ia) {
        return { value: ia.id, label: ia.code + ' - ' + ia.libelle };
      });
    })
    .catch(function(e) { console.error('Erreur chargement filtres:', e); });
}

function remplir(selectId, items, mapper) {
  var sel = document.getElementById(selectId);
  (items || []).forEach(function(item) {
    var o = mapper(item);
    sel.innerHTML += '<option value="' + o.value + '">' + o.label + '</option>';
  });
}

function formatMontant(val) {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function formatMontantCourt(val) {
  var abs = Math.abs(val);
  if (abs >= 1000000) return (val / 1000000).toFixed(1) + 'M';
  if (abs >= 1000) return (val / 1000).toFixed(0) + 'K';
  return String(val);
}

function comparer() {
  var btn = document.getElementById('btnComparer');
  btn.disabled = true;
  btn.textContent = 'Chargement...';

  var params = [];
  var champs = {
    annee_academique: document.getElementById('filtreAnnee').value,
    ia_id: document.getElementById('filtreIA').value,
    periode_paie: document.getElementById('filtrePeriodePaie').value,
    periode_bulletin: document.getElementById('filtrePeriodeBulletin').value
  };
  Object.keys(champs).forEach(function(cle) {
    if (champs[cle]) params.push(cle + '=' + encodeURIComponent(champs[cle]));
  });

  fetch(API + '/comparaison-engagements-paie' + (params.length ? '?' + params.join('&') : ''))
    .then(function(r) { return r.json(); })
    .then(function(data) {
      afficherAvertissements(data.avertissements);
      afficherStats(data);
      afficherDetail(data);

      ['avertPanel', 'statsPanel', 'detailPanel'].forEach(function(id) {
        document.getElementById(id).style.display = '';
      });

      btn.disabled = false;
      btn.textContent = 'Comparer';
    })
    .catch(function(e) {
      console.error('Erreur:', e);
      alert('Erreur lors de la comparaison.');
      btn.disabled = false;
      btn.textContent = 'Comparer';
    });
}

function afficherAvertissements(messages) {
  document.getElementById('avertList').innerHTML = (messages || []).map(function(m) {
    return '<li>' + m + '</li>';
  }).join('');
}

function afficherStats(data) {
  var eng = data.engagements;
  var paie = data.paie;
  var comp = data.comparaison;

  document.getElementById('statEngage').textContent = formatMontantCourt(eng.total_engagement);
  document.getElementById('statLignes').textContent = eng.nombre_lignes + ' ligne(s)';
  document.getElementById('statNet').textContent = formatMontantCourt(paie.total_net);
  document.getElementById('statAgents').textContent = paie.nombre_agents + ' agent(s)';

  var elEcart = document.getElementById('statEcart');
  elEcart.textContent = formatMontantCourt(comp.ecart);
  elEcart.style.color = comp.ecart >= 0 ? '#087f5b' : '#e03131';

  document.getElementById('statSens').textContent =
    comp.sens === 'sur_engagement' ? 'Engagé > payé' : 'Payé > engagé';

  document.getElementById('statCharges').textContent = formatMontantCourt(paie.charges_employeur);
  document.getElementById('statTauxCharges').textContent = 'Taux appliqué : ' + paie.taux_charges + ' %';
}

function afficherDetail(data) {
  var eng = data.engagements;
  var paie = data.paie;
  var comp = data.comparaison;

  var ligne = function(label, gauche, droite) {
    return '<tr><td>' + label + '</td><td>' + gauche + '</td><td>' + droite + '</td></tr>';
  };

  document.getElementById('comparaisonBody').innerHTML =
    ligne('Volume', eng.nombre_delegations + ' délégation(s), ' + eng.nombre_lignes + ' ligne(s)',
                    paie.nombre_bulletins + ' bulletin(s), ' + paie.nombre_agents + ' agent(s)') +
    ligne('Montant délégué', formatMontant(eng.total_montant), '—') +
    ligne('Montant engagé', formatMontant(eng.total_engagement), '—') +
    ligne('Reste à engager', formatMontant(eng.total_reste), '—') +
    ligne('Brut', '—', formatMontant(paie.total_brut)) +
    ligne('Retenues', '—', formatMontant(paie.total_retenues)) +
    ligne('Net payé', '—', formatMontant(paie.total_net)) +
    ligne('Charges employeur', '—', formatMontant(paie.charges_employeur)) +
    '<tr style="font-weight:bold; background:#f1f3f5;">' +
      '<td>Écart (engagé − net payé)</td>' +
      '<td colspan="2" style="text-align:center; color:' +
        (comp.ecart >= 0 ? '#087f5b' : '#e03131') + ';">' +
        formatMontant(comp.ecart) +
      '</td>' +
    '</tr>';
}
</script>
@endpush
