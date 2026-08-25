@extends('layouts.app')

@section('title', 'SICORE - Rubriques de paie')

@section('content')
<main class="main-content">
  <x-topbar title="Rubriques de paie" subtitle="Paramétrage > Rubriques de paie" icon="fa-solid fa-list-ul" />

  <section class="content-area">
    <div class="stats-grid four">
      <article class="stat-card"><div><p class="stat-label">Total</p><p class="stat-value">{{ $statistics['total'] }}</p><p class="stat-note">Rubriques enregistrées</p></div><span class="stat-icon green"><i class="fa-solid fa-list-check"></i></span></article>
      <article class="stat-card"><div><p class="stat-label">Gains</p><p class="stat-value">{{ $statistics['gains'] }}</p><p class="stat-note">Éléments de rémunération</p></div><span class="stat-icon blue"><i class="fa-solid fa-arrow-trend-up"></i></span></article>
      <article class="stat-card"><div><p class="stat-label">Retenues</p><p class="stat-value">{{ $statistics['retenues'] }}</p><p class="stat-note">Éléments déduits</p></div><span class="stat-icon yellow"><i class="fa-solid fa-arrow-trend-down"></i></span></article>
      <article class="stat-card"><div><p class="stat-label">Actives</p><p class="stat-value">{{ $statistics['actives'] }}</p><p class="stat-note">Disponibles pour la paie</p></div><span class="stat-icon green"><i class="fa-solid fa-circle-check"></i></span></article>
    </div>

    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.index') }}">Paramétrage</a> &gt; Rubriques de paie</p>
      @if ($canManage)
        <button class="btn-primary" type="button" data-modal-open="rubrique-create-modal"><i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une rubrique</button>
      @endif
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif

    <form class="filter-panel rubrique-filters" method="GET" action="{{ route('parametres.rubriques-paie.index') }}" data-rubrique-filters>
      <div class="form-group rubrique-search">
        <label for="rubriqueSearch">Rechercher</label>
        <input class="form-control" id="rubriqueSearch" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code, libellé ou description…" autocomplete="off">
      </div>
      <div class="form-group">
        <label for="rubriqueType">Type</label>
        <select class="form-control" id="rubriqueType" name="type">
          <option value="">Tous les types</option>
          <option value="gain" @selected(($filters['type'] ?? '') === 'gain')>Gain</option>
          <option value="retenue" @selected(($filters['type'] ?? '') === 'retenue')>Retenue</option>
        </select>
      </div>
      <div class="form-group">
        <label for="rubriquePeriodicite">Périodicité</label>
        <select class="form-control" id="rubriquePeriodicite" name="periodicite">
          <option value="">Toutes les périodicités</option>
          <option value="mensuelle" @selected(($filters['periodicite'] ?? '') === 'mensuelle')>Mensuelle</option>
          <option value="ponctuelle" @selected(($filters['periodicite'] ?? '') === 'ponctuelle')>Ponctuelle</option>
          <option value="annuelle" @selected(($filters['periodicite'] ?? '') === 'annuelle')>Annuelle</option>
        </select>
      </div>
      <div class="form-group">
        <label for="rubriqueStatus">Statut</label>
        <select class="form-control" id="rubriqueStatus" name="est_actif">
          <option value="">Tous les statuts</option>
          <option value="1" @selected(($filters['est_actif'] ?? '') === '1')>Actif</option>
          <option value="0" @selected(($filters['est_actif'] ?? '') === '0')>Inactif</option>
        </select>
      </div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.rubriques-paie.index') }}">Réinitialiser</a></div>
      <span class="loading-indicator" role="status" hidden data-filter-loading>Chargement…</span>
    </form>

    <section class="table-card" aria-labelledby="rubriqueListTitle">
      <div class="table-card-header"><div><h2 id="rubriqueListTitle">Liste des rubriques de paie</h2><p class="table-card-subtitle">{{ $pagination['total'] }} résultat{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Code</th><th>Libellé</th><th>Type</th><th>Périodicité</th><th>Valeur par défaut</th><th>Statut</th>@if ($canManage)<th class="actions-cell">Actions</th>@endif</tr></thead>
          <tbody>
            @forelse ($items as $rubrique)
              @php
                $rubriqueId = data_get($rubrique, 'id');
                $type = data_get($rubrique, 'type');
                $montant = data_get($rubrique, 'montant_defaut');
                $taux = data_get($rubrique, 'taux_defaut');
                $active = (bool) data_get($rubrique, 'est_actif', false);
              @endphp
              <tr>
                <td><span class="rubrique-code">{{ data_get($rubrique, 'code', '—') }}</span></td>
                <td><strong>{{ data_get($rubrique, 'libelle', '—') }}</strong></td>
                <td><span class="rubrique-type rubrique-type--{{ $type === 'gain' ? 'gain' : 'retenue' }}"><i class="fa-solid {{ $type === 'gain' ? 'fa-arrow-up' : 'fa-arrow-down' }}" aria-hidden="true"></i> {{ $type === 'gain' ? 'Gain' : 'Retenue' }}</span></td>
                <td>{{ ucfirst((string) data_get($rubrique, 'periodicite', '—')) }}</td>
                <td>
                  @if ($montant !== null && $montant !== '')
                    <strong>{{ number_format((float) $montant, 0, ',', ' ') }} FCFA</strong>
                  @elseif ($taux !== null && $taux !== '')
                    <strong>{{ number_format((float) $taux, 2, ',', ' ') }} %</strong>
                  @else
                    —
                  @endif
                </td>
                <td><span class="badge {{ $active ? 'badge-active' : 'badge-inactive' }}">{{ $active ? 'Actif' : 'Inactif' }}</span></td>
                @if ($canManage)
                  <td class="actions-cell">
                    <button class="icon-action" type="button" data-modal-open="rubrique-update-modal" data-rubrique-edit='@json($rubrique)' title="Modifier" aria-label="Modifier {{ data_get($rubrique, 'libelle') }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                    <form class="inline-form" method="POST" action="{{ route('parametres.rubriques-paie.destroy', $rubriqueId) }}" data-rubrique-delete data-confirm-message="Supprimer définitivement la rubrique « {{ data_get($rubrique, 'libelle') }} » ?">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ data_get($rubrique, 'libelle') }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button></form>
                  </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ $canManage ? 7 : 6 }}" class="empty-message show">Aucune rubrique de paie trouvée.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($pagination['last_page'] > 1)
        <nav class="pagination" aria-label="Pagination des rubriques de paie">
          @for ($page = 1; $page <= $pagination['last_page']; $page++)
            <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.rubriques-paie.index', array_merge(request()->except('page'), ['page' => $page])) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
          @endfor
        </nav>
      @endif
    </section>
  </section>
</main>

@if ($canManage)
  <x-module-indemnite type="modal" id="rubrique-create-modal" title="Ajouter une rubrique de paie" :open="(bool) session('rubrique_create_form_open')">
    <form class="teacher-form rubrique-form" id="rubriqueCreateForm" method="POST" action="{{ route('parametres.rubriques-paie.store') }}">
      @csrf
      @if ($errors->has('api'))<div class="alert alert-error" role="alert">{{ $errors->first('api') }}</div>@endif
      @include('pages.parametres.partials.rubrique-paie-fields', ['prefix' => 'rubrique', 'bag' => null, 'values' => []])
      <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit"><span data-submit-label>Ajouter</span></button></div>
    </form>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="rubrique-update-modal" title="Modifier une rubrique de paie" :open="(bool) session('rubrique_update_form_open') || $errors->getBag('updateRubrique')->any()">
    <form class="teacher-form rubrique-form" id="rubriqueUpdateForm" method="POST" data-update-url="{{ route('parametres.rubriques-paie.update', ['rubrique' => '__ID__']) }}">
      @csrf @method('PUT')
      @if ($errors->getBag('updateRubrique')->has('api'))<div class="alert alert-error" role="alert">{{ $errors->getBag('updateRubrique')->first('api') }}</div>@endif
      @include('pages.parametres.partials.rubrique-paie-fields', ['prefix' => 'rubriqueUpdate', 'bag' => 'updateRubrique', 'values' => []])
      <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit"><span data-submit-label>Enregistrer</span></button></div>
    </form>
  </x-module-indemnite>
@endif
@endsection

@push('styles')
<style>
  #rubrique-create-modal .modal-dialog, #rubrique-update-modal .modal-dialog { width: calc(100% - 32px); max-width: 980px; }
  .rubrique-filters { align-items: end; }
  .rubrique-filters .rubrique-search { min-width: 260px; flex: 1 1 300px; }
  .rubrique-code { display: inline-flex; padding: 5px 9px; border-radius: 8px; background: #f1f5f9; color: #334155; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; }
  .rubrique-type { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; font-size: .82rem; font-weight: 700; }
  .rubrique-type--gain { background: #dcfce7; color: #166534; }
  .rubrique-type--retenue { background: #fee2e2; color: #991b1b; }
  .rubrique-form { padding-inline: 0; padding-bottom: 0; }
  .rubrique-form .form-actions { margin-top: 22px; padding-top: 18px; border-top: 1px solid #e2e8f0; }
  .rubrique-flags { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
  .rubrique-flag { display: flex; align-items: flex-start; gap: 10px; min-height: 76px; padding: 14px; border: 1px solid #dce3ec; border-radius: 12px; background: #f8fafc; cursor: pointer; }
  .rubrique-flag:hover { border-color: #9bbdaf; background: #f3f8f6; }
  .rubrique-flag input { width: 18px; height: 18px; margin-top: 2px; accent-color: #26775a; }
  .rubrique-flag strong, .rubrique-flag small { display: block; }
  .rubrique-flag small { margin-top: 3px; color: #64748b; line-height: 1.35; }
  @media (max-width: 760px) { .rubrique-flags { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    var filterForm = document.querySelector('[data-rubrique-filters]');
    var filterTimer;
    function submitFilters() {
      filterForm.querySelector('[data-filter-loading]').hidden = false;
      filterForm.requestSubmit();
    }
    document.getElementById('rubriqueSearch')?.addEventListener('input', function () {
      window.clearTimeout(filterTimer);
      filterTimer = window.setTimeout(submitFilters, 400);
    });
    ['rubriqueType', 'rubriquePeriodicite', 'rubriqueStatus'].forEach(function (id) {
      document.getElementById(id)?.addEventListener('change', submitFilters);
    });

    function isTrue(value) { return value === true || value === 1 || value === '1' || value === 'true'; }
    function uppercaseCode(input) {
      input?.addEventListener('input', function () { input.value = input.value.toUpperCase(); });
    }
    uppercaseCode(document.getElementById('rubriqueCode'));
    uppercaseCode(document.getElementById('rubriqueUpdateCode'));

    var updateForm = document.getElementById('rubriqueUpdateForm');
    function fillUpdateForm(item) {
      updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', encodeURIComponent(item.id));
      document.getElementById('rubriqueUpdateCode').value = item.code || '';
      document.getElementById('rubriqueUpdateLibelle').value = item.libelle || '';
      document.getElementById('rubriqueUpdateType').value = item.type || 'gain';
      document.getElementById('rubriqueUpdatePeriodicite').value = item.periodicite || 'mensuelle';
      document.getElementById('rubriqueUpdateMontant').value = item.montant_defaut ?? '';
      document.getElementById('rubriqueUpdateTaux').value = item.taux_defaut ?? '';
      document.getElementById('rubriqueUpdateFormule').value = item.formule_calcul || '';
      document.getElementById('rubriqueUpdateDescription').value = item.description || '';
      document.getElementById('rubriqueUpdateCotisable').checked = isTrue(item.est_cotisable);
      document.getElementById('rubriqueUpdateImposable').checked = isTrue(item.est_imposable);
      document.getElementById('rubriqueUpdateBulletin').checked = isTrue(item.est_afficher_bulletin);
      document.getElementById('rubriqueUpdateActif').value = isTrue(item.est_actif) ? '1' : '0';
    }
    document.querySelectorAll('[data-rubrique-edit]').forEach(function (button) {
      button.addEventListener('click', function () { fillUpdateForm(JSON.parse(button.dataset.rubriqueEdit)); });
    });

    document.querySelectorAll('[data-rubrique-delete]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!window.confirm(form.dataset.confirmMessage)) { event.preventDefault(); return; }
        var button = form.querySelector('button');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
      });
    });

    document.querySelectorAll('.rubrique-form').forEach(function (form) {
      form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        var button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.querySelector('[data-submit-label]').textContent = 'Enregistrement…';
      });
    });

    @if (session('rubrique_update_form_open') || $errors->getBag('updateRubrique')->any())
      updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', @json(session('rubrique_update_id')));
      document.getElementById('rubriqueUpdateCode').value = @json(old('code'));
      document.getElementById('rubriqueUpdateLibelle').value = @json(old('libelle'));
      document.getElementById('rubriqueUpdateType').value = @json(old('type', 'gain'));
      document.getElementById('rubriqueUpdatePeriodicite').value = @json(old('periodicite', 'mensuelle'));
      document.getElementById('rubriqueUpdateMontant').value = @json(old('montant_defaut'));
      document.getElementById('rubriqueUpdateTaux').value = @json(old('taux_defaut'));
      document.getElementById('rubriqueUpdateFormule').value = @json(old('formule_calcul'));
      document.getElementById('rubriqueUpdateDescription').value = @json(old('description'));
      document.getElementById('rubriqueUpdateCotisable').checked = isTrue(@json(old('est_cotisable')));
      document.getElementById('rubriqueUpdateImposable').checked = isTrue(@json(old('est_imposable')));
      document.getElementById('rubriqueUpdateBulletin').checked = isTrue(@json(old('est_afficher_bulletin')));
      document.getElementById('rubriqueUpdateActif').value = @json(old('est_actif', '1'));
    @endif
  }());
</script>
@endpush
