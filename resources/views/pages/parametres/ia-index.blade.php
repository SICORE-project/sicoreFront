@extends('layouts.app')

@section('title', 'SICORE - Inspections d’académie')

@section('content')
<main class="main-content">
  <header class="topbar">
    <div class="page-title-wrap">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button>
      <span class="title-icon" aria-hidden="true"><i class="fa-solid fa-building-columns"></i></span>
      <div><h1>Inspections d’académie</h1><p>Structures académiques enregistrées dans SICORE</p></div>
    </div>
    <div class="search-wrap">
      <label class="sr-only" for="iaSearch">Rechercher une IA</label>
      <input class="search-input" id="iaSearch" type="search" placeholder="Code, libellé, région..." data-table-filter="#iaTable">
    </div>
  </header>

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Inspections d’académie</p>
      <div class="actions-group">
        <a class="btn-primary" href="{{ route('parametres.ia.create') }}">+ Nouvelle IA</a>
        <a class="btn-secondary" href="{{ route('parametres.ia.index') }}">Actualiser</a>
      </div>
    </div>

    @if ($error)
      <div class="alert alert-error" role="alert">{{ $error }}</div>
    @endif

    <section class="table-card" aria-labelledby="iaListTitle">
      <div class="table-card-header">
        <div>
          <h2 id="iaListTitle">Liste des inspections d’académie</h2>
          <p class="table-card-subtitle">{{ $pagination['total'] }} IA enregistrée{{ $pagination['total'] > 1 ? 's' : '' }}</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table" id="iaTable">
          <thead><tr>
            <th>Code</th><th>Libellé</th><th>Région</th><th>Responsable</th>
            <th>Téléphone</th><th>E-mail</th><th>Statut</th>
          </tr></thead>
          <tbody>
            @foreach ($items as $ia)
              @php
                $status = data_get($ia, 'statut', data_get($ia, 'status', data_get($ia, 'est_actif', data_get($ia, 'actif'))));
                $normalizedStatus = is_string($status) ? mb_strtolower(trim($status)) : $status;
                $active = in_array($normalizedStatus, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
                $responsable = data_get($ia, 'responsable.nom_complet', data_get($ia, 'responsable.nom', data_get($ia, 'responsable', '—')));
              @endphp
              <tr>
                <td>{{ data_get($ia, 'code', '—') }}</td>
                <td>{{ data_get($ia, 'libelle', data_get($ia, 'nom', '—')) }}</td>
                <td>{{ data_get($ia, 'region.libelle', data_get($ia, 'region.nom', data_get($ia, 'region', '—'))) }}</td>
                <td>{{ is_string($responsable) ? $responsable : '—' }}</td>
                <td>{{ data_get($ia, 'telephone', data_get($ia, 'contact.telephone', '—')) }}</td>
                <td>{{ data_get($ia, 'email', data_get($ia, 'contact.email', '—')) }}</td>
                <td><span class="badge {{ $active ? 'badge-active' : 'badge-suspended' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="empty-message {{ empty($items) ? 'show' : '' }}" role="status">Aucune inspection d’académie trouvée.</p>

      <nav class="pagination" aria-label="Pagination">
        @for ($page = 1; $page <= $pagination['last_page']; $page++)
          <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.ia.index', ['page' => $page]) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
        @endfor
      </nav>
    </section>
  </section>
</main>
@endsection
