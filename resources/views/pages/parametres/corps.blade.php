@extends('layouts.app')
@section('title', 'SICORE - Corps enseignants')
@section('content')
<main class="main-content">
  <header class="topbar"><div class="page-title-wrap"><button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button><span class="title-icon"><i class="fa-solid fa-users-line"></i></span><div><h1>Corps enseignants</h1><p>Gestion du référentiel des corps</p></div></div></header>
  <section class="content-area">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    <div class="stats-grid"><article class="stat-card"><div><p class="stat-label">Corps enseignants</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Enregistrements disponibles</p></div><span class="stat-icon green"><i class="fa-solid fa-users-line"></i></span></article></div>
    <div class="actions-row"><p class="breadcrumb">Paramétrage &gt; Corps</p><div class="actions-group"><button class="btn-primary" type="button" data-modal-open="corps-create-modal">+ Nouveau corps</button></div></div>
    <form class="filter-panel" id="corpsFilterForm" method="GET" action="{{ route('parametres.corps.index') }}"><div class="form-group"><label for="corpsSearch">Rechercher</label><input class="form-control" id="corpsSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Code ou libellé"></div>@if(request('search'))<div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.corps.index') }}">Réinitialiser</a></div>@endif</form>
    @if($error) <div class="alert alert-error">{{ $error }}</div> @endif
    <section class="table-card"><div class="table-card-header"><div><h2>Liste des corps</h2><p class="table-card-subtitle">{{ $pagination['total'] }} enregistrement{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div><div class="table-responsive"><table class="table"><thead><tr><th>Code</th><th>Libellé</th><th>Description</th><th class="actions-cell">Actions</th></tr></thead><tbody>
      @forelse($items as $item)<tr><td>{{ data_get($item,'code','—') }}</td><td>{{ data_get($item,'libelle','—') }}</td><td>{{ data_get($item,'description','—') ?: '—' }}</td><td class="actions-cell"><button class="icon-action" type="button" data-modal-open="corps-edit-modal" data-corps-edit='@json($item)' title="Modifier"><i class="fa-solid fa-pen-to-square"></i></button>@if(in_array(session('sicore_user.role_slug'),['admin','super_admin'],true))<form class="inline-form" method="POST" action="{{ route('parametres.corps.destroy',data_get($item,'id')) }}" onsubmit="return confirm('Supprimer ce corps enseignant ?');">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button></form>@endif</td></tr>
      @empty<tr><td colspan="4" class="empty-message">Aucun corps trouvé.</td></tr>@endforelse
    </tbody></table></div><nav class="pagination">@for($page=1;$page<=$pagination['last_page'];$page++)<a class="page-btn {{ $page===$pagination['current_page']?'active':'' }}" href="{{ route('parametres.corps.index',array_merge(request()->except('page'),['page'=>$page])) }}">{{ $page }}</a>@endfor</nav></section>
  </section>
</main>
<x-module-indemnite type="modal" id="corps-create-modal" title="Créer un corps enseignant"><form class="teacher-form" method="POST" action="{{ route('parametres.corps.store') }}">@csrf<div class="form-grid form-grid--balanced"><div class="form-group"><label for="corpsCode">Code *</label><input class="form-control" id="corpsCode" name="code" maxlength="20" required></div><div class="form-group"><label for="corpsLibelle">Libellé *</label><input class="form-control" id="corpsLibelle" name="libelle" maxlength="100" required></div><div class="form-group corps-description-field"><label for="corpsDescription">Description</label><textarea class="form-control" id="corpsDescription" name="description" rows="5" maxlength="255"></textarea></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Créer</button></div></form></x-module-indemnite>
<x-module-indemnite type="modal" id="corps-edit-modal" title="Modifier un corps enseignant"><form class="teacher-form" id="corpsEditForm" method="POST">@csrf @method('PUT')<div class="form-grid form-grid--balanced"><div class="form-group"><label for="corpsEditCode">Code *</label><input class="form-control" id="corpsEditCode" name="code" maxlength="20" required></div><div class="form-group"><label for="corpsEditLibelle">Libellé *</label><input class="form-control" id="corpsEditLibelle" name="libelle" maxlength="100" required></div><div class="form-group corps-description-field"><label for="corpsEditDescription">Description</label><textarea class="form-control" id="corpsEditDescription" name="description" rows="5" maxlength="255"></textarea></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer</button></div></form></x-module-indemnite>
@endsection
@push('styles')
<style>
  #corpsFilterForm .form-group { width: min(100%, 620px); flex: 0 1 620px; }
  #corpsFilterForm #corpsSearch { width: 100%; }
  #corps-create-modal .modal-dialog,
  #corps-edit-modal .modal-dialog { width: calc(100% - 32px); max-width: 920px; }
  #corps-create-modal .corps-description-field,
  #corps-edit-modal .corps-description-field { grid-column: 1 / -1; }
  #corps-create-modal textarea,
  #corps-edit-modal textarea { min-height: 130px; resize: vertical; }
</style>
@endpush
@push('scripts')
<script>(function(){var form=document.getElementById('corpsFilterForm'),search=document.getElementById('corpsSearch'),timer,updateUrl=@json(route('parametres.corps.update',['corps'=>'__ID__']));search.addEventListener('input',function(){clearTimeout(timer);timer=setTimeout(function(){form.requestSubmit();},400);});document.querySelectorAll('[data-corps-edit]').forEach(function(button){button.addEventListener('click',function(){var item=JSON.parse(button.dataset.corpsEdit);document.getElementById('corpsEditForm').action=updateUrl.replace('__ID__',item.id);document.getElementById('corpsEditCode').value=item.code||'';document.getElementById('corpsEditLibelle').value=item.libelle||'';document.getElementById('corpsEditDescription').value=item.description||'';});});}());</script>
@endpush
