@extends('layouts.app')

@section('title', 'SICORE - IEF')
@section('content')
<main class="main-content">
    <header class="topbar">
      <div class="page-title-wrap">
        <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
        <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-sitemap"></i></span>
        <div>
          <h1>IEF (Inspection de l&#39;Education)</h1>
          <p>Administration &gt; IEF &gt; Liste des IEF</p>
        </div>
      </div>
      <div class="search-wrap">
        <label class="sr-only" for="iefSearch">Rechercher une IEF</label>
        <input class="search-input" id="iefSearch" type="search" placeholder="Rechercher une IEF..." data-table-filter="#iefTable">
      </div>
    </header>

    <section class="content-area">
      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Total IEF</p><p class="stat-value">48</p><p class="stat-note neutral">12 par region</p></div>
          <span class="stat-icon green">IEF</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">IA rattachees</p><p class="stat-value">24</p><p class="stat-note neutral">2 IEF par IA</p></div>
          <span class="stat-icon blue">IA</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Enseignants</p><p class="stat-value">4 832</p><p class="stat-note neutral">100 par IEF</p></div>
          <span class="stat-icon purple">EN</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">IEF actives</p><p class="stat-value">44</p><p class="stat-note">4 en creation</p></div>
          <span class="stat-icon yellow">OK</span>
        </article>
      </div>

      <div class="actions-row">
        <p class="breadcrumb">Administration &gt; IEF &gt; Liste des IEF</p>
        <div class="actions-group">
          <button class="btn-primary" type="button" data-modal-open="ief-create-modal">+ Nouvelle IEF</button>
          <button class="btn-secondary" type="button">Importer</button>
          <button class="btn-secondary" type="button">Exporter</button>
        </div>
      </div>

      <form class="filter-panel" id="iefFilterForm" aria-label="Filtres des IEF">
        <div class="form-group">
          <label for="iefFilterIa">IA rattachée</label>
          <select class="form-control" id="iefFilterIa" name="ia">
            <option value="">Toutes les IA</option>
            <option value="dakar">IA Dakar</option>
            <option value="thies">IA Thiès</option>
            <option value="saint-louis">IA Saint-Louis</option>
          </select>
        </div>
        <div class="form-group">
          <label for="iefFilterStatus">Statut</label>
          <select class="form-control" id="iefFilterStatus" name="statut">
            <option value="">Tous les statuts</option>
            <option value="actif">Actif</option>
            <option value="suspendue">Suspendue</option>
          </select>
        </div>
        <div class="actions-group">
          <button class="btn-secondary" id="iefFilterReset" type="button">Réinitialiser</button>
          <button class="btn-primary" type="submit">Filtrer</button>
        </div>
      </form>

      <section class="table-card">
        <div class="table-responsive">
          <table class="table" id="iefTable">
            <thead>
              <tr data-ief-row data-ief-ia="dakar" data-ief-status="actif">
                <th>Code</th>
                <th>Nom</th>
                <th>IA rattachee</th>
                <th>Responsable</th>
                <th>Contact</th>
                <th>Statut</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>IEF001</td>
                <td>IEF de Dakar Nord</td>
                <td>IA Dakar</td>
                <td>Mamadou DIOP</td>
                <td>33 123 45 67</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="table-action" type="button">Voir</button><button class="table-action" type="button" data-modal-open="ief-edit-modal" data-ief-edit>Modifier</button><button class="table-action" type="button" data-modal-open="ief-transfer-modal" data-ief-transfer>Transférer</button><button class="table-action" type="button" data-ief-delete>Supprimer</button></td>
              </tr>
              <tr data-ief-row data-ief-ia="dakar" data-ief-status="actif">
                <td>IEF002</td>
                <td>IEF de Dakar Sud</td>
                <td>IA Dakar</td>
                <td>Aissatou FALL</td>
                <td>33 234 56 78</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="table-action" type="button">Voir</button><button class="table-action" type="button" data-modal-open="ief-edit-modal" data-ief-edit>Modifier</button><button class="table-action" type="button" data-modal-open="ief-transfer-modal" data-ief-transfer>Transférer</button><button class="table-action" type="button" data-ief-delete>Supprimer</button></td>
              </tr>
              <tr data-ief-row data-ief-ia="thies" data-ief-status="actif">
                <td>IEF003</td>
                <td>IEF de Thies</td>
                <td>IA Thies</td>
                <td>Ibrahima SOW</td>
                <td>33 345 67 89</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="table-action" type="button">Voir</button><button class="table-action" type="button" data-modal-open="ief-edit-modal" data-ief-edit>Modifier</button><button class="table-action" type="button" data-modal-open="ief-transfer-modal" data-ief-transfer>Transférer</button><button class="table-action" type="button" data-ief-delete>Supprimer</button></td>
              </tr>
              <tr data-ief-row data-ief-ia="saint-louis" data-ief-status="suspendue">
                <td>IEF004</td>
                <td>IEF de Saint-Louis</td>
                <td>IA Saint-Louis</td>
                <td>Mariama GUEYE</td>
                <td>33 456 78 90</td>
                <td><span class="badge badge-suspended">Suspendue</span></td>
                <td class="actions-cell"><button class="table-action" type="button">Voir</button><button class="table-action" type="button" data-modal-open="ief-edit-modal" data-ief-edit>Modifier</button><button class="table-action" type="button" data-modal-open="ief-transfer-modal" data-ief-transfer>Transférer</button><button class="table-action" type="button" data-ief-delete>Supprimer</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="empty-message">Aucune IEF trouvee.</p>
        <div class="pagination">
          <button class="page-btn" type="button">&#8592;</button>
          <button class="page-btn active" type="button" data-page-number>1</button>
          <button class="page-btn" type="button" data-page-number>2</button>
          <button class="page-btn" type="button">&#8594;</button>
        </div>
      </section>
    </section>
  </main>

  <x-module-indemnite type="modal" id="ief-create-modal" title="Créer une IEF">
    <form class="teacher-form" id="iefCreateForm">
      <div class="alert alert-success" id="iefFormFeedback" role="status" hidden>L’IEF a été ajoutée à la maquette frontend.</div>
      <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>
      <div class="form-grid form-grid--balanced">
        <div class="form-group">
          <label for="iefCode">Code <span class="required">*</span></label>
          <input class="form-control" id="iefCode" name="code" type="text" maxlength="20" required autocomplete="off" placeholder="Ex. IEF-DKR-NORD">
        </div>
        <div class="form-group">
          <label for="iefLibelle">Libellé <span class="required">*</span></label>
          <input class="form-control" id="iefLibelle" name="libelle" type="text" maxlength="150" required placeholder="Ex. IEF de Dakar Nord">
        </div>
        <div class="form-group">
          <label for="iefIa">Inspection d’académie (IA) <span class="required">*</span></label>
          <select class="form-control" id="iefIa" name="inspection_academie_id" required>
            <option value="">Sélectionner une IA</option>
            <option value="1">IA-DKR — Inspection d’académie de Dakar</option>
            <option value="2">IA-THS — Inspection d’académie de Thiès</option>
            <option value="3">IA-SLG — Inspection d’académie de Saint-Louis</option>
          </select>
          <small>Données temporaires utilisées en attendant la connexion au backend.</small>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit">Créer l’IEF</button>
      </div>
    </form>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="ief-transfer-modal" title="Rattacher l’IEF à une IA">
    <form class="teacher-form" id="iefTransferForm">
      <div class="alert alert-success" id="iefTransferFeedback" role="status" hidden></div>
      <div class="alert alert-error" id="iefTransferError" role="alert" hidden>La nouvelle IA doit être différente de l’IA actuelle.</div>
      <p class="breadcrumb" id="iefTransferSubject"></p>
      <div class="form-grid form-grid--balanced">
        <div class="form-group">
          <label for="iefCurrentIa">IA actuelle</label>
          <input class="form-control" id="iefCurrentIa" type="text" readonly>
        </div>
        <div class="form-group">
          <label for="iefDestinationIa">Nouvelle IA active <span class="required">*</span></label>
          <select class="form-control" id="iefDestinationIa" name="inspection_academie_id" required>
            <option value="">Sélectionner une IA active</option>
            <option value="dakar">IA Dakar</option>
            <option value="thies">IA Thiès</option>
          </select>
          <small>Seules les inspections d’académie actives sont proposées.</small>
        </div>
      </div>
      <div class="alert alert-warning" role="note">Le transfert des données rattachées et la journalisation seront exécutés par le backend lors de son intégration.</div>
      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit">Confirmer le transfert</button>
      </div>
    </form>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="ief-edit-modal" title="Modifier une IEF">
    <form class="teacher-form" id="iefEditForm">
      <div class="alert alert-success" id="iefEditFeedback" role="status" hidden>Les modifications ont été appliquées à la maquette frontend.</div>
      <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>
      <div class="form-grid form-grid--balanced">
        <div class="form-group">
          <label for="iefEditCode">Code <span class="required">*</span></label>
          <input class="form-control" id="iefEditCode" name="code" type="text" maxlength="20" required autocomplete="off">
        </div>
        <div class="form-group">
          <label for="iefEditLibelle">Libellé <span class="required">*</span></label>
          <input class="form-control" id="iefEditLibelle" name="libelle" type="text" maxlength="150" required>
        </div>
        <div class="form-group">
          <label for="iefEditIa">Inspection d’académie (IA) <span class="required">*</span></label>
          <select class="form-control" id="iefEditIa" name="inspection_academie_id" required>
            <option value="dakar">IA Dakar</option>
            <option value="thies">IA Thiès</option>
            <option value="saint-louis">IA Saint-Louis</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit">Enregistrer les modifications</button>
      </div>
    </form>
  </x-module-indemnite>

  @push('scripts')
  <script>
    document.getElementById('iefCreateForm').addEventListener('submit', function (event) {
      event.preventDefault();
      if (!this.reportValidity()) return;
      document.getElementById('iefFormFeedback').hidden = false;
    });

    (function () {
      var selectedRow = null;
      var currentIaKey = '';
      var transferForm = document.getElementById('iefTransferForm');
      var currentIaInput = document.getElementById('iefCurrentIa');
      var destinationSelect = document.getElementById('iefDestinationIa');
      var subject = document.getElementById('iefTransferSubject');
      var error = document.getElementById('iefTransferError');
      var feedback = document.getElementById('iefTransferFeedback');
      var iaLabels = {
        'dakar': 'IA Dakar',
        'thies': 'IA Thies',
        'saint-louis': 'IA Saint-Louis'
      };

      document.querySelectorAll('[data-ief-transfer]').forEach(function (button) {
        button.addEventListener('click', function () {
          selectedRow = button.closest('[data-ief-row]');
          currentIaKey = selectedRow.dataset.iefIa;
          var cells = selectedRow.querySelectorAll('td');

          subject.textContent = cells[0].textContent.trim() + ' — ' + cells[1].textContent.trim();
          currentIaInput.value = cells[2].textContent.trim();
          destinationSelect.value = '';
          destinationSelect.querySelectorAll('option').forEach(function (option) {
            option.disabled = option.value !== '' && option.value === currentIaKey;
          });
          error.hidden = true;
          feedback.hidden = true;
        });
      });

      transferForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!transferForm.reportValidity() || !selectedRow) return;

        if (destinationSelect.value === currentIaKey) {
          error.hidden = false;
          return;
        }

        var oldIa = currentIaInput.value;
        var newIa = iaLabels[destinationSelect.value];
        if (!window.confirm('Transférer cette IEF de ' + oldIa + ' vers ' + newIa + ' ?')) return;

        selectedRow.dataset.iefIa = destinationSelect.value;
        selectedRow.querySelectorAll('td')[2].textContent = newIa;
        currentIaKey = destinationSelect.value;
        currentIaInput.value = newIa;
        feedback.textContent = 'Transfert simulé : ' + oldIa + ' → ' + newIa + '. Ancien et nouveau rattachements conservés dans ce résumé.';
        feedback.hidden = false;
        error.hidden = true;
      });
    }());

    (function () {
      var selectedRow = null;
      var editForm = document.getElementById('iefEditForm');
      var codeInput = document.getElementById('iefEditCode');
      var libelleInput = document.getElementById('iefEditLibelle');
      var iaSelect = document.getElementById('iefEditIa');
      var feedback = document.getElementById('iefEditFeedback');
      var iaLabels = {
        'dakar': 'IA Dakar',
        'thies': 'IA Thies',
        'saint-louis': 'IA Saint-Louis'
      };

      document.querySelectorAll('[data-ief-edit]').forEach(function (button) {
        button.addEventListener('click', function () {
          selectedRow = button.closest('[data-ief-row]');
          var cells = selectedRow.querySelectorAll('td');
          codeInput.value = cells[0].textContent.trim();
          libelleInput.value = cells[1].textContent.trim();
          iaSelect.value = selectedRow.dataset.iefIa;
          feedback.hidden = true;
        });
      });

      editForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!editForm.reportValidity() || !selectedRow) return;

        var cells = selectedRow.querySelectorAll('td');
        cells[0].textContent = codeInput.value.trim();
        cells[1].textContent = libelleInput.value.trim();
        cells[2].textContent = iaLabels[iaSelect.value];
        selectedRow.dataset.iefIa = iaSelect.value;
        feedback.hidden = false;
      });
    }());

    (function () {
      var filterForm = document.getElementById('iefFilterForm');
      var iaFilter = document.getElementById('iefFilterIa');
      var statusFilter = document.getElementById('iefFilterStatus');
      var rows = document.querySelectorAll('[data-ief-row]');
      var emptyMessage = document.querySelector('#iefTable').closest('.table-card').querySelector('.empty-message');

      function applyFilters() {
        var visible = 0;
        rows.forEach(function (row) {
          var matchesIa = !iaFilter.value || row.dataset.iefIa === iaFilter.value;
          var matchesStatus = !statusFilter.value || row.dataset.iefStatus === statusFilter.value;
          var matches = matchesIa && matchesStatus;
          row.classList.toggle('is-hidden', !matches);
          if (matches) visible += 1;
        });
        emptyMessage.classList.toggle('show', visible === 0);
      }

      filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        applyFilters();
      });

      document.getElementById('iefFilterReset').addEventListener('click', function () {
        filterForm.reset();
        applyFilters();
      });

      document.querySelectorAll('[data-ief-delete]').forEach(function (button) {
        button.addEventListener('click', function () {
          if (window.confirm('Supprimer cette IEF de la maquette frontend ?')) {
            button.closest('[data-ief-row]').remove();
          }
        });
      });
    }());
  </script>
  @endpush
@endsection
