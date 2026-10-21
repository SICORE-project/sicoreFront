@extends('layouts.app')

{{--
  PAGE : Paramétrage général — URL /parametres dans routes/web.php.
  Layout/menu : layouts/app.blade.php et components/sidebar.blade.php.
  Recherche du tableau : public/assets/js/app.js via data-table-filter.
  Les ancres du menu, dont #periode-paie, sont déclarées dans navigation.php.
--}}
@section('title', 'SICORE - Param&eacute;trage')
@section('content')
<main class="main-content">
    {{-- En-tête local de la page de paramétrage. --}}
    <header class="topbar">
      <div class="page-title-wrap">
        <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
        <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-gears"></i></span>
        <div>
          <h1>Param&eacute;trage</h1>
          <p>Gestion des param&egrave;tres syst&egrave;me</p>
        </div>
      </div>
      <div class="search-wrap">
        <label class="sr-only" for="settingsSearch">Rechercher un param&egrave;tre</label>
        <input class="search-input" id="settingsSearch" type="search" placeholder="Rechercher un param&egrave;tre..." data-table-filter="#settingsTable">
      </div>
    </header>

    <section class="content-area">
      <div class="actions-row">
        <div>
          <p class="breadcrumb">Administration &gt; Param&eacute;trage &gt; R&eacute;f&eacute;rentiels</p>
        </div>
        <div class="actions-group">
          <button class="btn-primary" type="button">+ Nouveau param&egrave;tre</button>
          <button class="btn-secondary" type="button">Importer</button>
          <button class="btn-secondary" type="button">Exporter</button>
        </div>
      </div>

      {{-- Tableau des paramètres de présentation du système. --}}
      <section class="table-card">
        <div class="table-responsive">
          <table class="table" id="settingsTable" data-paginated-table>
            <thead>
              <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Statut</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr data-table-row id="ia">
                <td>IA</td>
                <td>Inspection d&rsquo;Acad&eacute;mie</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="diplomes">
                <td>DIP</td>
                <td>Dipl&ocirc;mes</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="corps">
                <td>COR</td>
                <td>Corps</td>
                <td><span class="badge badge-pending">En attente</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="categories">
                <td>CAT</td>
                <td>Cat&eacute;gories</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="institutions-financieres">
                <td>IF</td>
                <td>Institutions financi&egrave;res</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="discipline">
                <td>DIS</td>
                <td>Spécialité</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="syndicats">
                <td>SYN</td>
                <td>Syndicats</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="annee-academique">
                <td>AA</td>
                <td>Ann&eacute;e acad&eacute;mique</td>
                <td><span class="badge badge-pending">En attente</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="periode-paie">
                <td>PP</td>
                <td>P&eacute;riode de paie</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="rubrique-paie">
                <td>RP</td>
                <td>Rubrique de paie</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="rubriques-corps">
                <td>RC</td>
                <td>Rubriques par corps</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr data-table-row id="lieu-service">
                <td>LS</td>
                <td>Établissement</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="empty-message"><x-table-empty-state>Aucun param&egrave;tre trouv&eacute;.</x-table-empty-state></p>
              <nav
        class="pagination"
        data-table-pagination
        data-table-target="#settingsTable"
        data-current-page="1"
        aria-label="Pagination du tableau"
      >
        <p class="pagination-summary" data-pagination-summary aria-live="polite"></p>
        <div class="pagination-controls">
          <button class="page-btn page-btn-direction" type="button" data-page-action="previous" aria-label="Page précédente">Précédent</button>
          <span class="page-numbers" data-page-numbers></span>
          <button class="page-btn page-btn-direction" type="button" data-page-action="next" aria-label="Page suivante">Suivant</button>
          <label class="visually-hidden" for="settings-page-size">Nombre de lignes par page</label>
          <select class="page-size-select" id="settings-page-size" data-page-size aria-label="Nombre de lignes par page">
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
        </div>
      </nav>
      </section>
    </section>
  </main>
@endsection
