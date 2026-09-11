@extends('layouts.app')
@section('title', 'SICORE - Corps enseignants')
@section('content')
<main class="main-content">
  <header class="topbar"><div class="page-title-wrap"><button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">&#9776;</button><span class="title-icon"><i class="fa-solid fa-users-line"></i></span><div><h1>Corps enseignants</h1><p>Gestion du référentiel des corps</p></div></div></header>
  <section class="content-area">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    <p class="breadcrumb">Paramétrage &gt; Corps</p>
    <div class="corps-toolbar">
      <form id="corpsFilterForm" method="GET" action="{{ route('parametres.corps.index') }}">
        <label class="sr-only" for="corpsSearch">Rechercher un corps</label>
        <input class="form-control" id="corpsSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Rechercher par libellé…">
      </form>
      @if(request('search'))<a class="btn-secondary" href="{{ route('parametres.corps.index') }}">Réinitialiser</a>@endif
      <button class="btn-primary" type="button" data-modal-open="corps-create-modal"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nouveau corps</button>
    </div>
    @if($error) <div class="alert alert-error">{{ $error }}</div> @endif
    <section class="table-card"><div class="table-card-header"><div><h2>Liste des corps</h2><p class="table-card-subtitle">{{ $pagination['total'] }} enregistrement{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div><div class="table-responsive"><table class="table"><thead><tr><th>Libellé</th><th class="actions-cell">Actions</th></tr></thead><tbody>
      @forelse($items as $item)<tr><td>{{ data_get($item,'libelle','—') }}</td><td class="actions-cell"><button class="icon-action" type="button" data-modal-open="corps-edit-modal" data-corps-edit='@json($item)' title="Modifier"><i class="fa-solid fa-pen-to-square"></i></button>@if(in_array(session('sicore_user.role_slug'),['admin','super_admin'],true))<form class="inline-form" method="POST" action="{{ route('parametres.corps.destroy',data_get($item,'id')) }}">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button></form>@endif</td></tr>
      @empty<tr><td colspan="2" class="empty-message"><x-table-empty-state>Aucun corps trouvé.</x-table-empty-state></td></tr>@endforelse
    </tbody></table></div><nav class="pagination">@for($page=1;$page<=$pagination['last_page'];$page++)<a class="page-btn {{ $page===$pagination['current_page']?'active':'' }}" href="{{ route('parametres.corps.index',array_merge(request()->except('page'),['page'=>$page])) }}">{{ $page }}</a>@endfor</nav></section>
  </section>
</main>
<x-module-indemnite type="modal" id="corps-create-modal" title="Créer un corps enseignant"><form class="teacher-form" method="POST" action="{{ route('parametres.corps.store') }}">@csrf<div class="form-grid form-grid--balanced"><div class="form-group"><label for="corpsLibelle">Libellé *</label><input class="form-control" id="corpsLibelle" name="libelle" maxlength="100" required></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Créer</button></div></form></x-module-indemnite>
<x-module-indemnite type="modal" id="corps-edit-modal" title="Modifier un corps enseignant"><form class="teacher-form" id="corpsEditForm" method="POST">@csrf @method('PUT')<div class="form-grid form-grid--balanced"><div class="form-group"><label for="corpsEditLibelle">Libellé *</label><input class="form-control" id="corpsEditLibelle" name="libelle" maxlength="100" required></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer</button></div></form></x-module-indemnite>
@endsection
@push('styles')
<style>
  .corps-toolbar { display: flex; align-items: center; width: 100%; gap: 20px; padding: 16px; border: 1px solid var(--border); border-radius: var(--radius-card); background: #fff; }
  #corpsFilterForm { flex: 1; min-width: 0; margin: 0; }
  .corps-toolbar > button, .corps-toolbar > a { flex-shrink: 0; white-space: nowrap; }
  @media (max-width: 600px) {
    .corps-toolbar { flex-wrap: wrap; }
    #corpsFilterForm { flex-basis: 100%; }
    .corps-toolbar > button { flex: 1; justify-content: center; }
  }
  #corpsFilterForm #corpsSearch { width: 100%; }
  #corps-create-modal .modal-dialog,
  #corps-edit-modal .modal-dialog { width: calc(100% - 32px); max-width: 920px; }
  #corps-create-modal .form-grid, #corps-edit-modal .form-grid { grid-template-columns: minmax(0, 1fr); }
  #corps-create-modal textarea,
  #corps-edit-modal textarea { min-height: 130px; resize: vertical; }
</style>
@endpush
@push('scripts')
<script>(function(){var form=document.getElementById('corpsFilterForm'),search=document.getElementById('corpsSearch'),timer,updateUrl=@json(route('parametres.corps.update',['corps'=>'__ID__']));search.addEventListener('input',function(){clearTimeout(timer);timer=setTimeout(function(){form.requestSubmit();},400);});document.querySelectorAll('[data-corps-edit]').forEach(function(button){button.addEventListener('click',function(){var item=JSON.parse(button.dataset.corpsEdit);document.getElementById('corpsEditForm').action=updateUrl.replace('__ID__',item.id);document.getElementById('corpsEditLibelle').value=item.libelle||'';});});}());</script>
@endpush
