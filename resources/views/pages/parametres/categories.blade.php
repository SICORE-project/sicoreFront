@extends('layouts.app')
@section('title','SICORE - Catégories')
@section('content')
<main class="main-content"><header class="topbar"><div class="page-title-wrap"><button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button><span class="title-icon"><i class="fa-solid fa-layer-group"></i></span><div><h1>Catégories</h1><p>Gestion des catégories rattachées aux corps enseignants</p></div></div></header>
<section class="content-area">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
  <div class="stats-grid"><article class="stat-card"><div><p class="stat-label">Catégories</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Enregistrements disponibles</p></div><span class="stat-icon green"><i class="fa-solid fa-layer-group"></i></span></article></div>
  <div class="actions-row"><p class="breadcrumb">Paramétrage &gt; Catégories</p><div class="actions-group"><button class="btn-primary" type="button" data-modal-open="categorie-create-modal">+ Nouvelle catégorie</button></div></div>
  <form class="filter-panel" id="categorieFilterForm" method="GET" action="{{ route('parametres.categories.index') }}"><div class="form-group"><label for="categorieSearch">Rechercher par libellé</label><input class="form-control" id="categorieSearch" name="search" value="{{ request('search') }}" placeholder="Libellé"></div></form>
  @if($error)<div class="alert alert-error">{{ $error }}</div>@endif
  <section class="table-card"><div class="table-card-header"><div><h2>Liste des catégories</h2><p class="table-card-subtitle">{{ $pagination['total'] }} enregistrement{{ $pagination['total']>1?'s':'' }}</p></div></div><div class="table-responsive"><table class="table"><thead><tr><th>Libellé</th><th>Corps</th><th class="actions-cell">Actions</th></tr></thead><tbody>
  @forelse($items as $item)<tr><td>{{ data_get($item,'libelle','—') }}</td><td>{{ data_get($item,'corps.code','—') }} — {{ data_get($item,'corps.libelle','—') }}</td><td class="actions-cell"><button class="icon-action" type="button" data-modal-open="categorie-edit-modal" data-categorie-edit='@json($item)' title="Modifier"><i class="fa-solid fa-pen-to-square"></i></button><form class="inline-form" method="POST" action="{{ route('parametres.categories.destroy',data_get($item,'id')) }}" onsubmit="return confirm('Supprimer cette catégorie ?');">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button></form></td></tr>
  @empty<tr><td colspan="3" class="empty-message">Aucune catégorie trouvée.</td></tr>@endforelse
  </tbody></table></div><nav class="pagination">@for($page=1;$page<=$pagination['last_page'];$page++)<a class="page-btn {{ $page===$pagination['current_page']?'active':'' }}" href="{{ route('parametres.categories.index',array_merge(request()->only('search'),['page'=>$page])) }}">{{ $page }}</a>@endfor</nav></section>
</section></main>
<x-module-indemnite type="modal" id="categorie-create-modal" title="Créer une catégorie"><form class="teacher-form" method="POST" action="{{ route('parametres.categories.store') }}">@csrf<div class="form-grid form-grid--balanced"><div class="form-group"><label for="categorieLibelle">Libellé *</label><input class="form-control" id="categorieLibelle" name="libelle" maxlength="255" required></div><div class="form-group"><label for="categorieCorps">Corps *</label><select class="form-control" id="categorieCorps" name="corps_id" required><option value="">Sélectionner</option>@foreach($corpsOptions as $corps)<option value="{{ data_get($corps,'id') }}" @selected((string) old('corps_id', $defaultCorpsId) === (string) data_get($corps,'id'))>{{ data_get($corps,'code') }} — {{ data_get($corps,'libelle') }}</option>@endforeach</select></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Créer</button></div></form></x-module-indemnite>
<x-module-indemnite type="modal" id="categorie-edit-modal" title="Modifier une catégorie"><form class="teacher-form" id="categorieEditForm" method="POST">@csrf @method('PUT')<div class="form-grid form-grid--balanced"><div class="form-group"><label for="categorieEditLibelle">Libellé *</label><input class="form-control" id="categorieEditLibelle" name="libelle" maxlength="255" required></div><div class="form-group"><label for="categorieEditCorps">Corps *</label><select class="form-control" id="categorieEditCorps" name="corps_id" required>@foreach($corpsOptions as $corps)<option value="{{ data_get($corps,'id') }}">{{ data_get($corps,'code') }} — {{ data_get($corps,'libelle') }}</option>@endforeach</select></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer</button></div></form></x-module-indemnite>
@endsection
@push('styles')<style>#categorieFilterForm{grid-template-columns:minmax(0,1fr)}#categorieFilterForm .form-group{width:100%;min-width:0;flex:1}#categorie-create-modal .modal-dialog,#categorie-edit-modal .modal-dialog{width:calc(100% - 32px);max-width:920px}</style>@endpush
@push('scripts')
<script>
(function () {
  var form = document.getElementById('categorieFilterForm');
  var search = document.getElementById('categorieSearch');
  var timer;
  var focusKey = 'categorie-search-focus';

  try {
    var savedFocus = JSON.parse(sessionStorage.getItem(focusKey) || 'null');
    sessionStorage.removeItem(focusKey);
    if (savedFocus && savedFocus.value === search.value) {
      search.focus();
      search.setSelectionRange(savedFocus.start, savedFocus.end);
    }
  } catch (error) {}
  var updateUrl = @json(route('parametres.categories.update', ['category' => '__ID__']));
  var defaultCorpsId = @json($defaultCorpsId);

  search.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { form.requestSubmit(); }, 400);
  });
  form.addEventListener('submit', function () {
    clearTimeout(timer);
    if (document.activeElement === search) {
      try {
        sessionStorage.setItem(focusKey, JSON.stringify({
          value: search.value,
          start: search.selectionStart,
          end: search.selectionEnd
        }));
      } catch (error) {}
    }
  });

  document.querySelectorAll('[data-categorie-edit]').forEach(function (button) {
    button.addEventListener('click', function () {
      var item = JSON.parse(button.dataset.categorieEdit);
      document.getElementById('categorieEditForm').action = updateUrl.replace('__ID__', item.id);
      document.getElementById('categorieEditLibelle').value = item.libelle || '';
      document.getElementById('categorieEditCorps').value = item.corps_id || (item.corps && item.corps.id) || defaultCorpsId;
    });
  });
}());
</script>
@endpush
