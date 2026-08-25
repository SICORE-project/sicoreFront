@extends('layouts.app')

@section('title', 'SICORE - Périodes de paie')

@section('content')
<main class="main-content">
  <x-topbar title="Périodes de paie" subtitle="Paramétrage > Périodes de paie" icon="fa-solid fa-calendar-week" />

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb">Paramétrage &gt; Périodes de paie</p>
      @if ($canManage)
        <button class="btn-primary" type="button" data-modal-open="periode-create-modal"><i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une période</button>
      @endif
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
    @if ($error)<div class="alert alert-error" role="alert">{{ $error }}</div>@endif

    <form class="filter-panel periode-filters" method="GET" action="{{ route('parametres.periodes-paie.index') }}" data-periode-filters>
      <div class="form-group periode-search">
        <label for="periodeSearch">Rechercher</label>
        <input class="form-control" id="periodeSearch" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Code ou libellé…" autocomplete="off">
      </div>
      <div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.periodes-paie.index') }}">Réinitialiser</a></div>
      <span class="loading-indicator" role="status" hidden data-filter-loading>Chargement…</span>
    </form>

    <section class="table-card" aria-labelledby="periodeListTitle">
      <div class="table-card-header"><div><h2 id="periodeListTitle">Liste des périodes de paie</h2><p class="table-card-subtitle">{{ $pagination['total'] }} résultat{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Code</th><th>Libellé</th>@if ($canManage)<th class="actions-cell">Actions</th>@endif</tr></thead>
          <tbody>
            @forelse ($items as $periode)
              <tr>
                <td><span class="periode-code">{{ data_get($periode, 'code', '—') }}</span></td>
                <td><strong>{{ data_get($periode, 'libelle', '—') }}</strong></td>
                @if ($canManage)
                  <td class="actions-cell">
                    <button class="icon-action" type="button" data-modal-open="periode-update-modal" data-periode-edit='@json($periode)' title="Modifier" aria-label="Modifier {{ data_get($periode, 'libelle') }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                    <form class="inline-form" method="POST" action="{{ route('parametres.periodes-paie.destroy', data_get($periode, 'id')) }}" data-periode-delete data-confirm-message="Supprimer définitivement la période « {{ data_get($periode, 'libelle') }} » ?">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button></form>
                  </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ $canManage ? 3 : 2 }}" class="empty-message show">Aucune période de paie trouvée.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($pagination['last_page'] > 1)
        <nav class="pagination" aria-label="Pagination des périodes de paie">
          @for ($page = 1; $page <= $pagination['last_page']; $page++)
            <a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.periodes-paie.index', array_merge(request()->except('page'), ['page' => $page])) }}" @if ($page === $pagination['current_page']) aria-current="page" @endif>{{ $page }}</a>
          @endfor
        </nav>
      @endif
    </section>
  </section>
</main>

@if ($canManage)
  <x-module-indemnite type="modal" id="periode-create-modal" title="Ajouter une période de paie" :open="(bool) session('periode_create_form_open')">
    <form class="teacher-form periode-form" method="POST" action="{{ route('parametres.periodes-paie.store') }}">@csrf
      @if ($errors->has('api'))<div class="alert alert-error" role="alert">{{ $errors->first('api') }}</div>@endif
      <div class="form-grid two-columns">
        <div class="form-group"><label for="periodeCode">Code <span aria-hidden="true">*</span></label><input class="form-control" id="periodeCode" name="code" value="{{ old('code') }}" maxlength="20" required>@error('code')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-group"><label for="periodeLibelle">Libellé <span aria-hidden="true">*</span></label><input class="form-control" id="periodeLibelle" name="libelle" value="{{ old('libelle') }}" maxlength="100" required>@error('libelle')<small class="field-error">{{ $message }}</small>@enderror</div>
      </div>
      <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Ajouter</button></div>
    </form>
  </x-module-indemnite>

  <x-module-indemnite type="modal" id="periode-update-modal" title="Modifier une période de paie" :open="(bool) session('periode_update_form_open') || $errors->getBag('updatePeriode')->any()">
    <form class="teacher-form periode-form" id="periodeUpdateForm" method="POST" data-update-url="{{ route('parametres.periodes-paie.update', ['periode' => '__ID__']) }}">@csrf @method('PUT')
      @if ($errors->getBag('updatePeriode')->has('api'))<div class="alert alert-error" role="alert">{{ $errors->getBag('updatePeriode')->first('api') }}</div>@endif
      <div class="form-grid two-columns">
        <div class="form-group"><label for="periodeUpdateCode">Code <span aria-hidden="true">*</span></label><input class="form-control" id="periodeUpdateCode" name="code" maxlength="20" required>@error('code', 'updatePeriode')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-group"><label for="periodeUpdateLibelle">Libellé <span aria-hidden="true">*</span></label><input class="form-control" id="periodeUpdateLibelle" name="libelle" maxlength="100" required>@error('libelle', 'updatePeriode')<small class="field-error">{{ $message }}</small>@enderror</div>
      </div>
      <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer</button></div>
    </form>
  </x-module-indemnite>
@endif
@endsection

@push('styles')
<style>
  #periode-create-modal .modal-dialog, #periode-update-modal .modal-dialog { width: calc(100% - 32px); max-width: 720px; }
  .periode-filters { align-items: end; }
  .periode-search { min-width: 260px; flex: 1 1 400px; }
  .periode-code { display: inline-flex; padding: 5px 9px; border-radius: 8px; background: #f1f5f9; color: #334155; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; }
  .periode-form .form-actions { margin-top: 22px; padding-top: 18px; border-top: 1px solid #e2e8f0; }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    var filterForm = document.querySelector('[data-periode-filters]');
    var timer;
    document.getElementById('periodeSearch')?.addEventListener('input', function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(function () { filterForm.querySelector('[data-filter-loading]').hidden = false; filterForm.requestSubmit(); }, 400);
    });
    ['periodeCode', 'periodeUpdateCode'].forEach(function (id) {
      document.getElementById(id)?.addEventListener('input', function (event) { event.target.value = event.target.value.toUpperCase(); });
    });
    var updateForm = document.getElementById('periodeUpdateForm');
    document.querySelectorAll('[data-periode-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var item = JSON.parse(button.dataset.periodeEdit);
        updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', encodeURIComponent(item.id));
        document.getElementById('periodeUpdateCode').value = item.code || '';
        document.getElementById('periodeUpdateLibelle').value = item.libelle || '';
      });
    });
    document.querySelectorAll('[data-periode-delete]').forEach(function (form) {
      form.addEventListener('submit', function (event) { if (!window.confirm(form.dataset.confirmMessage)) event.preventDefault(); });
    });
    @if (session('periode_update_form_open') || $errors->getBag('updatePeriode')->any())
      updateForm.action = updateForm.dataset.updateUrl.replace('__ID__', @json(session('periode_update_id')));
      document.getElementById('periodeUpdateCode').value = @json(old('code'));
      document.getElementById('periodeUpdateLibelle').value = @json(old('libelle'));
    @endif
  }());
</script>
@endpush
