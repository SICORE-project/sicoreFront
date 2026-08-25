@extends('layouts.app')

@section('title', 'SICORE - Années académiques')

@section('content')
<main class="main-content">
  <x-topbar title="Années académiques" subtitle="Paramétrage > Années académiques" icon="fa-solid fa-calendar-days" />

  <section class="content-area">
    <div class="stats-grid">
      <article class="stat-card"><div><p class="stat-label">Total</p><p class="stat-value">{{ $stats['total'] }}</p><p class="stat-note">Années enregistrées</p></div><span class="stat-icon green"><i class="fa-solid fa-calendar-days"></i></span></article>
      <article class="stat-card"><div><p class="stat-label">Année active</p><p class="stat-value">{{ $stats['active'] }}</p><p class="stat-note">Une seule année à la fois</p></div><span class="stat-icon blue"><i class="fa-solid fa-circle-check"></i></span></article>
      <article class="stat-card"><div><p class="stat-label">Clôturées</p><p class="stat-value">{{ $stats['closed'] }}</p><p class="stat-note">Années terminées</p></div><span class="stat-icon yellow"><i class="fa-solid fa-lock"></i></span></article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb">Paramétrage &gt; Années académiques</p>
      <button class="btn-primary" type="button" data-modal-open="annee-create-modal"><i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une année</button>
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif

    <form class="filter-panel" method="GET" action="{{ route('parametres.annees-academiques.index') }}" data-annee-filters>
      <div class="form-group">
        <label for="anneeSearch">Rechercher</label>
        <input class="form-control" id="anneeSearch" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher par libellé…" autocomplete="off">
      </div>
      <div class="form-group">
        <label for="anneeStatus">Statut</label>
        <select class="form-control" id="anneeStatus" name="statut">
          <option value="">Tous les statuts</option>
          <option value="active" @selected(($filters['statut'] ?? '') === 'active')>Active</option>
          <option value="inactive" @selected(($filters['statut'] ?? '') === 'inactive')>Inactive</option>
          <option value="closed" @selected(($filters['statut'] ?? '') === 'closed')>Clôturée</option>
        </select>
      </div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.annees-academiques.index') }}">Réinitialiser</a></div>
    </form>

    <section class="table-card">
      <div class="table-card-header"><div><h2>Liste des années académiques</h2><p class="table-card-subtitle">{{ $annees->total() }} résultat{{ $annees->total() > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Libellé</th><th>Période</th><th>Observations</th><th>Statut</th><th class="actions-cell">Actions</th></tr></thead>
          <tbody>
            @forelse ($annees as $annee)
              @php
                $anneeId = data_get($annee, 'id');
                $active = (bool) data_get($annee, 'est_active', false);
                $closed = (bool) data_get($annee, 'est_cloturee', false);
                $start = data_get($annee, 'date_debut');
                $end = data_get($annee, 'date_fin');
              @endphp
              <tr>
                <td><strong>{{ data_get($annee, 'libelle', '—') }}</strong></td>
                <td>{{ $start ? date('d/m/Y', strtotime($start)) : '—' }} au {{ $end ? date('d/m/Y', strtotime($end)) : '—' }}</td>
                <td class="annee-observation" title="{{ data_get($annee, 'observations') }}">{{ data_get($annee, 'observations', '—') ?: '—' }}</td>
                <td>
                  @if ($closed)<span class="badge badge-inactive">Clôturée</span>
                  @elseif ($active)<span class="badge badge-active">Active</span>
                  @else<span class="badge badge-pending">Inactive</span>@endif
                </td>
                <td class="actions-cell">
                  @if (! $closed)
                    <button class="icon-action" type="button" data-modal-open="annee-update-modal" data-annee-edit='@json($annee)' title="Modifier" aria-label="Modifier {{ data_get($annee, 'libelle') }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                    @if ($active)
                      <form class="inline-form" method="POST" action="{{ route('parametres.annees-academiques.deactivate', $anneeId) }}" data-annee-action-form data-confirm-message="Désactiver cette année académique ?">@csrf @method('PATCH')<button class="icon-action" type="submit" title="Désactiver"><i class="fa-solid fa-toggle-on" aria-hidden="true"></i></button></form>
                    @else
                      <form class="inline-form" method="POST" action="{{ route('parametres.annees-academiques.activate', $anneeId) }}" data-annee-action-form data-confirm-message="Activer cette année académique ? L’année actuellement active sera désactivée.">@csrf @method('PATCH')<button class="icon-action" type="submit" title="Activer"><i class="fa-solid fa-toggle-off" aria-hidden="true"></i></button></form>
                    @endif
                    <form class="inline-form" method="POST" action="{{ route('parametres.annees-academiques.close', $anneeId) }}" data-annee-action-form data-confirm-message="Clôturer définitivement cette année académique ?">@csrf @method('PATCH')<button class="icon-action" type="submit" title="Clôturer"><i class="fa-solid fa-lock" aria-hidden="true"></i></button></form>
                  @endif
                  @if (! $active)
                    <form class="inline-form" method="POST" action="{{ route('parametres.annees-academiques.destroy', $anneeId) }}" data-annee-action-form data-confirm-message="Supprimer définitivement cette année académique ?">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button></form>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="empty-message show">Aucune année académique trouvée.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($annees->hasPages())
        <nav class="pagination" aria-label="Pagination des années académiques">
          @foreach ($annees->getUrlRange(1, $annees->lastPage()) as $page => $url)
            <a class="page-btn {{ $page === $annees->currentPage() ? 'active' : '' }}" href="{{ $url }}" @if($page === $annees->currentPage()) aria-current="page" @endif>{{ $page }}</a>
          @endforeach
        </nav>
      @endif
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="annee-create-modal" title="Ajouter une année académique" :open="(bool) session('annee_create_form_open') || $errors->any()">
  <form class="teacher-form" id="anneeCreateForm" method="POST" action="{{ route('parametres.annees-academiques.store') }}">
    @csrf
    @if ($errors->has('api'))<div class="alert alert-error" role="alert">{{ $errors->first('api') }}</div>@endif
    <div class="form-grid form-grid--balanced">
      <div class="form-group full"><label for="anneeLibelle">Libellé <span class="required">*</span></label><input class="form-control" id="anneeLibelle" name="libelle" value="{{ old('libelle') }}" maxlength="100" placeholder="Ex. 2026-2027" required>@error('libelle')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="anneeDateDebut">Date de début <span class="required">*</span></label><input class="form-control" id="anneeDateDebut" name="date_debut" type="date" value="{{ old('date_debut') }}" required>@error('date_debut')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="anneeDateFin">Date de fin <span class="required">*</span></label><input class="form-control" id="anneeDateFin" name="date_fin" type="date" value="{{ old('date_fin') }}" required>@error('date_fin')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
      <div class="form-group full"><label for="anneeObservations">Observations</label><textarea class="form-control" id="anneeObservations" name="observations" rows="4" maxlength="1000">{{ old('observations') }}</textarea>@error('observations')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" data-create-submit>Ajouter</button></div>
  </form>
</x-module-indemnite>

<x-module-indemnite type="modal" id="annee-update-modal" title="Modifier l’année académique" :open="$errors->getBag('updateAnnee')->any() || (bool) session('annee_update_form_open')">
  <form class="teacher-form" id="anneeUpdateForm" method="POST" data-update-url="{{ route('parametres.annees-academiques.update', ['annee' => '__ID__']) }}">
    @csrf @method('PUT')
    @if ($errors->getBag('updateAnnee')->has('api'))<div class="alert alert-error" role="alert">{{ $errors->getBag('updateAnnee')->first('api') }}</div>@endif
    <div class="form-grid form-grid--balanced">
      <div class="form-group full"><label for="anneeUpdateLibelle">Libellé <span class="required">*</span></label><input class="form-control" id="anneeUpdateLibelle" name="libelle" maxlength="100" required>@error('libelle', 'updateAnnee')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="anneeUpdateDateDebut">Date de début <span class="required">*</span></label><input class="form-control" id="anneeUpdateDateDebut" name="date_debut" type="date" required>@error('date_debut', 'updateAnnee')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
      <div class="form-group"><label for="anneeUpdateDateFin">Date de fin <span class="required">*</span></label><input class="form-control" id="anneeUpdateDateFin" name="date_fin" type="date" required>@error('date_fin', 'updateAnnee')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
      <div class="form-group full"><label for="anneeUpdateObservations">Observations</label><textarea class="form-control" id="anneeUpdateObservations" name="observations" rows="4" maxlength="1000"></textarea>@error('observations', 'updateAnnee')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
    </div>
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" data-update-submit>Enregistrer</button></div>
  </form>
</x-module-indemnite>
@endsection

@push('styles')
<style>
  #annee-create-modal .modal-dialog, #annee-update-modal .modal-dialog { width: calc(100% - 32px); max-width: 900px; }
  #annee-create-modal .teacher-form, #annee-update-modal .teacher-form { padding-inline: 0; padding-bottom: 0; }
  #annee-create-modal .form-actions, #annee-update-modal .form-actions { padding-top: 18px; border-top: 1px solid #e2e8f0; }
  .annee-observation { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    var filterForm = document.querySelector('[data-annee-filters]');
    var searchInput = document.getElementById('anneeSearch');
    var statusInput = document.getElementById('anneeStatus');
    var timer;
    searchInput.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { filterForm.requestSubmit(); }, 400); });
    statusInput.addEventListener('change', function () { filterForm.requestSubmit(); });

    var updateForm = document.getElementById('anneeUpdateForm');
    function dateValue(value) { return value ? String(value).slice(0, 10) : ''; }
    function fillUpdateForm(item) {
      updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', encodeURIComponent(item.id));
      document.getElementById('anneeUpdateLibelle').value = item.libelle || '';
      document.getElementById('anneeUpdateDateDebut').value = dateValue(item.date_debut);
      document.getElementById('anneeUpdateDateFin').value = dateValue(item.date_fin);
      document.getElementById('anneeUpdateObservations').value = item.observations || '';
    }
    document.querySelectorAll('[data-annee-edit]').forEach(function (button) {
      button.addEventListener('click', function () { fillUpdateForm(JSON.parse(button.dataset.anneeEdit)); });
    });

    document.querySelectorAll('[data-annee-action-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!window.confirm(form.dataset.confirmMessage)) { event.preventDefault(); return; }
        form.querySelector('button').disabled = true;
      });
    });

    [document.getElementById('anneeCreateForm'), updateForm].forEach(function (form) {
      form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        var button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = 'Enregistrement…';
      });
    });

    @if ($errors->getBag('updateAnnee')->any() || session('annee_update_form_open'))
      updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', @json(session('annee_update_id')));
      document.getElementById('anneeUpdateLibelle').value = @json(old('libelle'));
      document.getElementById('anneeUpdateDateDebut').value = @json(old('date_debut'));
      document.getElementById('anneeUpdateDateFin').value = @json(old('date_fin'));
      document.getElementById('anneeUpdateObservations').value = @json(old('observations'));
    @endif
  }());
</script>
@endpush
