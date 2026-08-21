@extends('layouts.app')
@section('title', 'SICORE - Lieux de service')
@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon"><i class="fa-solid fa-location-dot"></i></span>
      <div><h1>Lieux de service</h1><p>Structures d’affectation enregistrées dans SICORE</p></div>
    </div>
    <div class="search-wrap"><label class="sr-only" for="lieuSearch">Rechercher</label><input class="search-input" id="lieuSearch" type="search" placeholder="Code, libellé, IA ou IEF..." data-table-filter="#lieuxTable"></div>
  </header>
  <section class="content-area">
    <div class="stats-grid four">
      <article class="stat-card"><div><p class="stat-label">Lieux de service</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Tous les lieux</p></div></article>
      <article class="stat-card"><div><p class="stat-label">Actifs</p><p class="stat-value">{{ $activeCount }}</p><p class="stat-note">Disponibles</p></div></article>
      <article class="stat-card"><div><p class="stat-label">Inactifs</p><p class="stat-value">{{ $inactiveCount }}</p><p class="stat-note">Non disponibles</p></div></article>
      <article class="stat-card"><div><p class="stat-label">Incohérences</p><p class="stat-value">{{ $inconsistentCount }}</p><p class="stat-note">Entre IA et IEF</p></div></article>
    </div>
    <div class="actions-row"><p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Lieux de service</p><a class="btn-secondary" href="{{ route('parametres.lieux-service.index') }}">Actualiser</a></div>
    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif
    @if ($inconsistentCount)<div class="alert alert-warning" role="alert">Des lieux présentent une incohérence entre l’IA et l’IEF.</div>@endif
    <section class="table-card" aria-labelledby="lieuxTitle">
      <div class="table-card-header"><div><h2 id="lieuxTitle">Liste des lieux de service</h2><p class="table-card-subtitle">{{ $pagination['total'] }} résultat{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive"><table class="table" id="lieuxTable">
        <thead><tr><th>Code</th><th>Libellé</th><th>Type</th><th>Inspection d’académie</th><th>IEF</th><th>Adresse</th><th>Statut</th><th>Cohérence</th></tr></thead>
        <tbody>@foreach ($items as $lieu)
          @php
            $status = data_get($lieu, 'statut', data_get($lieu, 'status', data_get($lieu, 'est_actif', data_get($lieu, 'actif'))));
            $status = is_string($status) ? mb_strtolower(trim($status)) : $status;
            $active = in_array($status, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
            $iaId = data_get($lieu, 'ia.id', data_get($lieu, 'inspection_academie.id', data_get($lieu, 'inspection_academie_id')));
            $iefIaId = data_get($lieu, 'ief.inspection_academie_id', data_get($lieu, 'ief.ia_id', data_get($lieu, 'ief.ia.id')));
            $consistent = $iaId === null || $iefIaId === null || (string) $iaId === (string) $iefIaId;
          @endphp
          <tr>
            <td>{{ data_get($lieu, 'code', '—') }}</td><td>{{ data_get($lieu, 'libelle', data_get($lieu, 'nom', '—')) }}</td><td>{{ data_get($lieu, 'type.libelle', data_get($lieu, 'type', '—')) }}</td>
            <td>{{ data_get($lieu, 'ia.libelle', data_get($lieu, 'inspection_academie.libelle', '—')) }}</td><td>{{ data_get($lieu, 'ief.libelle', data_get($lieu, 'inspection_education_formation.libelle', '—')) }}</td><td>{{ data_get($lieu, 'adresse', data_get($lieu, 'localisation', '—')) }}</td>
            <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td><td><span class="badge {{ $consistent ? 'badge-active' : 'badge-suspended' }}">{{ $consistent ? 'Conforme' : 'À vérifier' }}</span></td>
          </tr>
        @endforeach</tbody>
      </table></div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}">Aucun lieu de service trouvé.</p>
      <nav class="pagination" aria-label="Pagination">@for ($page = 1; $page <= $pagination['last_page']; $page++)<a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.lieux-service.index', ['page' => $page]) }}">{{ $page }}</a>@endfor</nav>
    </section>
  </section>
</main>
@endsection
