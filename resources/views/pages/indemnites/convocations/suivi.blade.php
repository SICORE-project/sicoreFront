@extends('layouts.app')

@section('title', 'SICORE - Suivi des envois')
@section('content')
<main class="main-content">
    <x-topbar
      title="Suivi des envois"
      subtitle="{{ 'Indemnites > Convocations > Convocation #'.$id.' > Suivi' }}"
      icon="fa-solid fa-list-check"
    />

    <section class="content-area">
      <div class="actions-row">
        <p class="breadcrumb">Certification BT - Jury 1 &nbsp;·&nbsp; LTP FXN/THIES</p>
        <div class="actions-group">
          <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Retour a la convocation
          </a>
        </div>
      </div>

      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Total envois</p><p class="stat-value">7</p><p class="stat-note">Beneficiaires convoques</p></div>
          <span class="stat-icon blue"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Envoyes</p><p class="stat-value">6</p><p class="stat-note neutral">86% du total</p></div>
          <span class="stat-icon green"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Echecs</p><p class="stat-value">1</p><p class="stat-note neutral">A relancer</p></div>
          <span class="stat-icon yellow"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Canal principal</p><p class="stat-value">Email</p><p class="stat-note neutral">5 sur 7</p></div>
          <span class="stat-icon purple"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
        </article>
      </div>

      <section class="table-card">
        <div class="table-responsive">
          <table class="table" id="suiviTable">
            <thead>
              <tr>
                <th>Beneficiaire</th>
                <th>Canal</th>
                <th>Statut</th>
                <th>Date d'envoi</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Gueye Adama</td>
                <td>Email</td>
                <td><span class="badge badge-active">Envoye</span></td>
                <td>23/07/2025 09:12</td>
                <td class="actions-cell">-</td>
              </tr>
              <tr>
                <td>Sarr Nfaly</td>
                <td>Email</td>
                <td><span class="badge badge-active">Envoye</span></td>
                <td>23/07/2025 09:12</td>
                <td class="actions-cell">-</td>
              </tr>
              <tr>
                <td>Sarr Papa Alioune Badara</td>
                <td>SMS</td>
                <td><span class="badge badge-active">Envoye</span></td>
                <td>23/07/2025 09:13</td>
                <td class="actions-cell">-</td>
              </tr>
              <tr>
                <td>Wone Mamadou Moustapha</td>
                <td>Email</td>
                <td><span class="badge badge-suspended">Echec</span></td>
                <td>23/07/2025 09:13</td>
                <td class="actions-cell">
                  <button class="icon-action" title="Relancer" type="button" data-confirm="Relancer ce beneficiaire ?" data-success-message="Relance envoyee."><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
                </td>
              </tr>
              <tr>
                <td>Mbaye Gueye</td>
                <td>Email</td>
                <td><span class="badge badge-active">Envoye</span></td>
                <td>23/07/2025 09:14</td>
                <td class="actions-cell">-</td>
              </tr>
              <tr>
                <td>Thiam Assane</td>
                <td>Courrier</td>
                <td><span class="badge badge-active">Envoye</span></td>
                <td>23/07/2025 09:14</td>
                <td class="actions-cell">-</td>
              </tr>
              <tr>
                <td>Faye El Hadji</td>
                <td>Email</td>
                <td><span class="badge badge-active">Envoye</span></td>
                <td>23/07/2025 09:15</td>
                <td class="actions-cell">-</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="empty-message">Aucun envoi enregistre.</p>
      </section>
    </section>
  </main>
@endsection
