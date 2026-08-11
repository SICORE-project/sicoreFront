
@extends('layouts.app')

@section('title', 'SICORE - Suivi des envois')
@section('content')
<main class="main-content">
  <x-topbar
    title="Suivi des envois"
    subtitle="Indemnites > Convocations > Suivi"
    icon="fa-solid fa-list-check"
  />

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb">Indemnites &gt; Convocations &gt; #{{ $id }} &gt; Suivi</p>
      <div class="actions-group">
        <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          Retour a la fiche
        </a>
      </div>
    </div>

    <div class="stats-grid four">
      <article class="stat-card">
        <div><p class="stat-label">Total des envois</p><p class="stat-value">{{ $stats['total'] }}</p><p class="stat-note">Tentatives enregistrees</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Envoyes</p><p class="stat-value">{{ $stats['envoye'] }}</p><p class="stat-note neutral">Notifications delivrees</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Echecs</p><p class="stat-value">{{ $stats['echec'] }}</p><p class="stat-note neutral">A relancer</p></div>
        <span class="stat-icon yellow"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Taux de succes</p><p class="stat-value">{{ $stats['total'] > 0 ? round(($stats['envoye'] / $stats['total']) * 100) : 0 }}%</p><p class="stat-note neutral">Sur le total des envois</p></div>
        <span class="stat-icon purple"><i class="fa-solid fa-chart-simple" aria-hidden="true"></i></span>
      </article>
    </div>

    <section class="table-card">
      <div class="panel-header">
        <div>
          <h2>Historique des envois</h2>
          <p>Detail des notifications envoyees aux membres du jury</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Destinataire</th>
              <th>Canal</th>
              <th>Date d'envoi</th>
              <th>Statut</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($envois as $envoi)
              <tr>
                <td>{{ $envoi['destinataire'] ?? $envoi['enseignant']['nom'] ?? '—' }}</td>
                <td>{{ ucfirst($envoi['canal'] ?? '—') }}</td>
                <td>{{ isset($envoi['envoye_le']) ? \Illuminate\Support\Carbon::parse($envoi['envoye_le'])->format('d/m/Y H:i') : ($envoi['created_at'] ?? '—') }}</td>
                <td>
                  @if (($envoi['statut'] ?? null) === 'envoye')
                    <span class="badge badge-active">Envoye</span>
                  @elseif (($envoi['statut'] ?? null) === 'echec')
                    <span class="badge badge-inactive">Echec</span>
                  @else
                    <span class="badge badge-pending">{{ ucfirst($envoi['statut'] ?? 'Inconnu') }}</span>
                  @endif
                </td>
                <td>{{ $envoi['erreur'] ?? $envoi['message'] ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if (empty($envois))
        <p class="empty-message">Aucun envoi enregistre pour cette convocation.</p>
      @endif
    </section>

    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>Relancer les beneficiaires</h2>
          <p class="breadcrumb">Envoyer un nouveau rappel a ceux qui n'ont pas encore repondu</p>
        </div>
      </div>
      <form class="form-section" method="POST" action="{{ route('indemnites.convocations.relancer', $id) }}">
        @csrf
        <div class="form-grid">
          <div class="form-group full">
            <label for="relance_message">Message de relance (optionnel)</label>
            <textarea class="form-control" id="relance_message" name="message" rows="3" maxlength="2000" placeholder="Message de relance…"></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn-primary" type="submit">
            <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
            Relancer
          </button>
        </div>
      </form>
    </section>
  </section>
</main>
@endsection
