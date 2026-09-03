@extends('layouts.app')
<<<<<<< HEAD
@section('title', 'SICORE - IEF')
@section('content')
<main class="main-content">
  <header class="topbar"><div class="page-title-wrap"><button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button><span class="title-icon"><i class="fa-solid fa-sitemap"></i></span><div><h1>Inspections de l’éducation et de la formation</h1><p>Gestion des IEF rattachées aux inspections d’académie</p></div></div></header>
  <section class="content-area">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    <div class="stats-grid">
      <article class="stat-card"><div><p class="stat-label">Total IEF</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">IEF enregistrées</p></div><span class="stat-icon green"><i class="fa-solid fa-sitemap"></i></span></article>
      <article class="stat-card"><div><p class="stat-label">IA disponibles</p><p class="stat-value">{{ count($ias) }}</p><p class="stat-note">Rattachements possibles</p></div><span class="stat-icon blue"><i class="fa-solid fa-building-columns"></i></span></article>
    </div>
    <div class="actions-row"><p class="breadcrumb">Paramétrage &gt; IEF</p><div class="actions-group"><button class="btn-primary" type="button" data-modal-open="ief-create-modal">+ Nouvelle IEF</button></div></div>
    <form class="filter-panel" id="iefFilterForm" method="GET" action="{{ route('parametres.ief.index') }}">
      <div class="form-group"><label for="iefSearch">Rechercher</label><input class="form-control" id="iefSearch" name="search" value="{{ request('search') }}" placeholder="Code ou libellé"></div>
      <div class="form-group"><label for="iefFilterIa">IA de rattachement</label><select class="form-control" id="iefFilterIa" name="ia_id"><option value="">Toutes les IA</option>@foreach ($ias as $ia)<option value="{{ data_get($ia, 'id') }}" @selected((string) request('ia_id') === (string) data_get($ia, 'id'))>{{ data_get($ia, 'code') }} — {{ data_get($ia, 'libelle') }}</option>@endforeach</select></div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.ief.index') }}">Réinitialiser</a></div>
    </form>
    @if ($error) <div class="alert alert-error">{{ $error }}</div> @endif
    <section class="table-card">
      <div class="table-card-header"><div><h2>Liste des IEF</h2><p class="table-card-subtitle">{{ $pagination['total'] }} enregistrement{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive"><table class="table" id="iefTable"><thead><tr><th>Code</th><th>Libellé</th><th>Inspection d’académie</th><th class="actions-cell">Actions</th></tr></thead><tbody>
        @forelse ($items as $ief)
          @php($iefId = data_get($ief, 'id'))
          <tr><td>{{ data_get($ief, 'code', '—') }}</td><td>{{ data_get($ief, 'libelle', '—') }}</td><td>{{ data_get($ief, 'ia.code', '—') }} — {{ data_get($ief, 'ia.libelle', '—') }}</td><td class="actions-cell">
            <button class="icon-action" type="button" data-modal-open="ief-edit-modal" data-ief-edit='@json($ief)' title="Modifier"><i class="fa-solid fa-pen-to-square"></i></button>
            @if ($iefId && in_array(session('sicore_user.role_slug'), ['admin', 'super_admin'], true))
              <form class="inline-form" method="POST" action="{{ route('parametres.ief.destroy', $iefId) }}" onsubmit="return confirm('Supprimer cette IEF ?');">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button></form>
            @endif
          </td></tr>
        @empty <tr><td colspan="4" class="empty-message">Aucune IEF trouvée.</td></tr> @endforelse
      </tbody></table></div>
      <nav class="pagination">@for ($page = 1; $page <= $pagination['last_page']; $page++)<a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.ief.index', array_merge(request()->except('page'), ['page' => $page])) }}">{{ $page }}</a>@endfor</nav>
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="ief-create-modal" title="Créer une IEF">
  <form class="teacher-form" method="POST" action="{{ route('parametres.ief.store') }}">@csrf
    <div class="form-grid form-grid--balanced"><div class="form-group"><label for="iefCode">Code *</label><input class="form-control" id="iefCode" name="code" maxlength="20" value="{{ old('code') }}" required></div><div class="form-group"><label for="iefLibelle">Libellé *</label><input class="form-control" id="iefLibelle" name="libelle" maxlength="100" value="{{ old('libelle') }}" required></div><div class="form-group"><label for="iefIa">Inspection d’académie *</label><select class="form-control" id="iefIa" name="ia_id" required><option value="">Sélectionner une IA</option>@foreach ($ias as $ia)<option value="{{ data_get($ia, 'id') }}">{{ data_get($ia, 'code') }} — {{ data_get($ia, 'libelle') }}</option>@endforeach</select></div></div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Créer</button></div>
  </form>
</x-module-indemnite>
<x-module-indemnite type="modal" id="ief-edit-modal" title="Modifier une IEF">
  <form class="teacher-form" id="iefEditForm" method="POST">@csrf @method('PUT')
    <div class="form-grid form-grid--balanced"><div class="form-group"><label for="iefEditCode">Code *</label><input class="form-control" id="iefEditCode" name="code" maxlength="20" required></div><div class="form-group"><label for="iefEditLibelle">Libellé *</label><input class="form-control" id="iefEditLibelle" name="libelle" maxlength="100" required></div><div class="form-group"><label for="iefEditIa">Inspection d’académie *</label><select class="form-control" id="iefEditIa" name="ia_id" required>@foreach ($ias as $ia)<option value="{{ data_get($ia, 'id') }}">{{ data_get($ia, 'code') }} — {{ data_get($ia, 'libelle') }}</option>@endforeach</select></div></div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer</button></div>
  </form>
</x-module-indemnite>
@endsection
@push('scripts')
<script>
  (function () {
    var updateUrl = @json(route('parametres.ief.update', ['ief' => '__IEF__']));
    var filterForm = document.getElementById('iefFilterForm');
    var searchInput = document.getElementById('iefSearch');
    var iaFilter = document.getElementById('iefFilterIa');
    var searchTimer;

    searchInput.addEventListener('input', function () {
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () {
        filterForm.requestSubmit();
      }, 400);
    });

    iaFilter.addEventListener('change', function () {
      filterForm.requestSubmit();
    });

    document.querySelectorAll('[data-ief-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var ief = JSON.parse(button.getAttribute('data-ief-edit'));
        document.getElementById('iefEditForm').action = updateUrl.replace('__IEF__', ief.id);
        document.getElementById('iefEditCode').value = ief.code || '';
        document.getElementById('iefEditLibelle').value = ief.libelle || '';
        document.getElementById('iefEditIa').value = ief.ia_id || '';
      });
    });

    document.addEventListener('DOMContentLoaded', function () {
      @if (session('error')) window.showToast?.('error', @json(session('error'))); @endif
      @if (session('success')) window.showToast?.('success', @json(session('success'))); @endif
    });
  }());
</script>
@endpush
=======

{{--
  PAGE : Paramétrage des IEF — URL /parametres/ief dans routes/web.php.
  Layout/menu : layouts/app.blade.php et components/sidebar.blade.php.
  Recherche du tableau : public/assets/js/app.js via data-table-filter.
  Les données sont actuellement de présentation ; la persistance appartient au
  backend du module Paramétrage.
--}}
@section('title', 'SICORE - IEF')
@section('content')
<main class="main-content">
    {{-- En-tête écrit localement ; il peut être migré plus tard vers <x-topbar>. --}}
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
          <button class="btn-primary" type="button">+ Nouvelle IEF</button>
          <button class="btn-secondary" type="button">Importer</button>
          <button class="btn-secondary" type="button">Exporter</button>
          <button class="btn-secondary" type="button">Filtrer</button>
        </div>
      </div>

<<<<<<< HEAD
=======
      {{-- Liste des IEF avec actions de présentation. --}}
>>>>>>> origin/module-paie
      <section class="table-card">
        <div class="table-responsive">
          <table class="table" id="iefTable">
            <thead>
              <tr>
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
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>IEF002</td>
                <td>IEF de Dakar Sud</td>
                <td>IA Dakar</td>
                <td>Aissatou FALL</td>
                <td>33 234 56 78</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>IEF003</td>
                <td>IEF de Thies</td>
                <td>IA Thies</td>
                <td>Ibrahima SOW</td>
                <td>33 345 67 89</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>IEF004</td>
                <td>IEF de Saint-Louis</td>
                <td>IA Saint-Louis</td>
                <td>Mariama GUEYE</td>
                <td>33 456 78 90</td>
                <td><span class="badge badge-suspended">Suspendue</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
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
@endsection

