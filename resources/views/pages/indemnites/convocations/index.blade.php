@extends('layouts.app')

@section('title', 'SICORE - Convocations')
@section('content')
<main class="main-content">
    <x-topbar
      title="Convocations"
      subtitle="Indemnites > Convocations"
      icon="fa-solid fa-calendar-check"
      search-id="convocationSearch"
      search-placeholder="Rechercher une convocation…"
      filter-target="#convocationTable"
    />

    <section class="content-area">
      <div class="actions-row">
        <p class="breadcrumb">Indemnites &gt; Convocations</p>
        <div class="actions-group">
          <a class="btn-primary" href="{{ route('indemnites.convocations.create') }}">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Nouvelle convocation
          </a>
        </div>
      </div>

      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Total convocations</p><p class="stat-value">4</p><p class="stat-note">Session Aout 2025</p></div>
          <span class="stat-icon blue"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Emises</p><p class="stat-value">1</p><p class="stat-note neutral">En attente d'envoi</p></div>
          <span class="stat-icon purple"><i class="fa-solid fa-file-circle-check" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Envoyees</p><p class="stat-value">1</p><p class="stat-note neutral">Aux beneficiaires</p></div>
          <span class="stat-icon green"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Brouillons</p><p class="stat-value">1</p><p class="stat-note neutral">A completer</p></div>
          <span class="stat-icon yellow"><i class="fa-solid fa-pen" aria-hidden="true"></i></span>
        </article>
      </div>

      <section class="filter-panel" aria-label="Filtres de la page">
        <div class="form-group">
          <label for="filterStatut">Statut</label>
          <select class="form-control" id="filterStatut">
            <option value="">Tous</option>
            <option>Brouillon</option>
            <option>Emise</option>
            <option>Envoyee</option>
            <option>Cloturee</option>
          </select>
        </div>
        <div class="form-group">
          <label for="filterCentre">Centre d'examen</label>
          <select class="form-control" id="filterCentre">
            <option value="">Tous</option>
            <option>LTP FXN/THIES</option>
            <option>LTID</option>
            <option>LTAB Diourbel</option>
          </select>
        </div>
        <div class="actions-group">
          <button class="btn-secondary" type="button">Filtrer</button>
        </div>
      </section>

      <section class="table-card">
        <div class="table-responsive">
          <table class="table" id="convocationTable">
            <thead>
              <tr>
                <th>Objet</th>
                <th>Date d'emission</th>
                <th>Centre d'examen</th>
                <th>Beneficiaires</th>
                <th>Statut</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Certification BT - Jury 1</td>
                <td>23/07/2025</td>
                <td>LTP FXN/THIES</td>
                <td>7</td>
                <td><span class="badge badge-active">Envoyee</span></td>
                <td class="actions-cell">
                  <a class="icon-action" title="Voir" href="{{ route('indemnites.convocations.show', 1) }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                  <a class="icon-action" title="Modifier" href="{{ route('indemnites.convocations.edit', 1) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                  <button class="icon-action" title="Supprimer" type="button" data-confirm="Voulez-vous vraiment supprimer cette convocation ?" data-success-message="Convocation supprimee."><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </td>
              </tr>
              <tr>
                <td>Certification BTS Genie Civil</td>
                <td>15/07/2025</td>
                <td>LTID</td>
                <td>4</td>
                <td><span class="badge badge-primary">Emise</span></td>
                <td class="actions-cell">
                  <a class="icon-action" title="Voir" href="{{ route('indemnites.convocations.show', 2) }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                  <a class="icon-action" title="Modifier" href="{{ route('indemnites.convocations.edit', 2) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                  <button class="icon-action" title="Supprimer" type="button"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </td>
              </tr>
              <tr>
                <td>Correction epreuves Froid Climatisation</td>
                <td>01/08/2025</td>
                <td>LTAB Diourbel</td>
                <td>0</td>
                <td><span class="badge badge-inactive">Brouillon</span></td>
                <td class="actions-cell">
                  <a class="icon-action" title="Voir" href="{{ route('indemnites.convocations.show', 3) }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                  <a class="icon-action" title="Modifier" href="{{ route('indemnites.convocations.edit', 3) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                  <button class="icon-action" title="Supprimer" type="button"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </td>
              </tr>
              <tr>
                <td>Jury CAP Electricite</td>
                <td>10/06/2025</td>
                <td>LTP FXN/THIES</td>
                <td>6</td>
                <td><span class="badge badge-suspended">Cloturee</span></td>
                <td class="actions-cell">
                  <a class="icon-action" title="Voir" href="{{ route('indemnites.convocations.show', 4) }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                  <a class="icon-action" title="Modifier" href="{{ route('indemnites.convocations.edit', 4) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                  <button class="icon-action" title="Supprimer" type="button"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="empty-message">Aucune convocation trouvee.</p>
        <div class="pagination" aria-label="Pagination">
          <button class="page-btn" type="button" aria-label="Page precedente">←</button>
          <button class="page-btn active" type="button" data-page-number>1</button>
          <button class="page-btn" type="button" aria-label="Page suivante">→</button>
        </div>
      </section>
    </section>
  </main>
@endsection
