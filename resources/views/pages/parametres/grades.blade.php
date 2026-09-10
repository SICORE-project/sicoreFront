@extends('layouts.app')
@section('title', 'SICORE - Grades')
@section('content')
<main class="main-content">
  <x-topbar title="Grades" subtitle="Paramétrage > Grades" icon="fa-solid fa-ranking-star" />
  <section class="content-area">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    <div class="actions-row"><p class="breadcrumb">Paramétrage &gt; Grades</p><div class="actions-group"><button class="btn-primary" type="button" data-modal-open="grade-create-modal">+ Nouveau grade</button></div></div>
    <form class="filter-panel" id="gradeFilterForm" method="GET" action="{{ route('parametres.grades.index') }}">
      <div class="form-group"><label for="gradeSearch">Rechercher</label><input class="form-control" id="gradeSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Libellé ou description"></div>
      @if(request('search'))<div class="actions-group"><a class="btn-secondary" href="{{ route('parametres.grades.index') }}">Réinitialiser</a></div>@endif
    </form>
    @if($error) <div class="alert alert-error">{{ $error }}</div> @endif
    <section class="table-card">
      <div class="table-card-header"><div><h2>Liste des grades</h2><p class="table-card-subtitle">{{ $pagination['total'] }} enregistrement{{ $pagination['total'] > 1 ? 's' : '' }}</p></div></div>
      <div class="table-responsive"><table class="table"><thead><tr><th>Libellé</th><th>Description</th><th class="actions-cell">Actions</th></tr></thead><tbody>
        @forelse($items as $item)
          <tr><td>{{ data_get($item, 'libelle', '—') }}</td><td>{{ data_get($item, 'description') ?: '—' }}</td><td class="actions-cell"><button class="icon-action" type="button" data-modal-open="grade-edit-modal" data-grade-edit='@json($item)' title="Modifier"><i class="fa-solid fa-pen-to-square"></i></button><form class="inline-form" method="POST" action="{{ route('parametres.grades.destroy', data_get($item, 'id')) }}" onsubmit="return confirm('Supprimer ce grade ?');">@csrf @method('DELETE')<button class="icon-action delete" type="submit" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button></form></td></tr>
        @empty
          <tr><td colspan="3" class="empty-message">Aucun grade trouvé.</td></tr>
        @endforelse
      </tbody></table></div>
      @if($pagination['last_page'] > 1)<nav class="pagination">@for($page = 1; $page <= $pagination['last_page']; $page++)<a class="page-btn {{ $page === $pagination['current_page'] ? 'active' : '' }}" href="{{ route('parametres.grades.index', array_merge(request()->except('page'), ['page' => $page])) }}">{{ $page }}</a>@endfor</nav>@endif
    </section>
  </section>
</main>

<x-module-indemnite type="modal" id="grade-create-modal" title="Créer un grade">
  <form class="teacher-form" method="POST" action="{{ route('parametres.grades.store') }}">@csrf<div class="form-grid"><div class="form-group"><label for="gradeLibelle">Libellé *</label><input class="form-control" id="gradeLibelle" name="libelle" maxlength="100" required></div><div class="form-group full"><label for="gradeDescription">Description</label><textarea class="form-control" id="gradeDescription" name="description" rows="5" maxlength="1000"></textarea></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Créer</button></div></form>
</x-module-indemnite>
<x-module-indemnite type="modal" id="grade-edit-modal" title="Modifier un grade">
  <form class="teacher-form" id="gradeEditForm" method="POST">@csrf @method('PUT')<div class="form-grid"><div class="form-group"><label for="gradeEditLibelle">Libellé *</label><input class="form-control" id="gradeEditLibelle" name="libelle" maxlength="100" required></div><div class="form-group full"><label for="gradeEditDescription">Description</label><textarea class="form-control" id="gradeEditDescription" name="description" rows="5" maxlength="1000"></textarea></div></div><div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit">Enregistrer</button></div></form>
</x-module-indemnite>
@endsection
@push('scripts')
<script>
(function () {
  var form = document.getElementById('gradeFilterForm');
  var search = document.getElementById('gradeSearch');
  var timer;
  var updateUrl = @json(route('parametres.grades.update', ['grade' => '__ID__']));
  search.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { form.requestSubmit(); }, 400); });
  document.querySelectorAll('[data-grade-edit]').forEach(function (button) {
    button.addEventListener('click', function () {
      var item = JSON.parse(button.dataset.gradeEdit);
      document.getElementById('gradeEditForm').action = updateUrl.replace('__ID__', item.id);
      document.getElementById('gradeEditLibelle').value = item.libelle || '';
      document.getElementById('gradeEditDescription').value = item.description || '';
    });
  });
}());
</script>
@endpush
