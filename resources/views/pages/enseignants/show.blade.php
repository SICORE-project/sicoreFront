@extends('layouts.app')

@section('title', 'SICORE - Dossier enseignant')

@section('content')
<main class="main-content">
  <x-topbar title="Dossier enseignant" subtitle="Administration > Enseignants > Dossier" icon="fa-solid fa-address-card" />
  <section class="content-area">
    <div class="actions-row"><div><p class="breadcrumb"><a href="{{ route('enseignants.index') }}">Enseignants</a> &gt; Dossier</p><h2>{{ trim(data_get($teacher, 'prenom').' '.data_get($teacher, 'nom')) ?: data_get($teacher, 'matricule', 'Enseignant') }}</h2></div></div>

    <section class="table-card" aria-labelledby="teacherDisciplinesTitle">
      <div class="table-card-header"><div><h2 id="teacherDisciplinesTitle">Disciplines</h2><p class="table-card-subtitle">Disciplines associées à l’enseignant</p></div>@if ($canAssociateDiscipline && count($availableDisciplines) > 0)<button class="btn-primary" type="button" data-modal-open="associate-discipline-modal">+ Associer une discipline</button>@endif</div>
      <div class="table-responsive"><table class="table"><thead><tr><th>Code</th><th>Libellé</th><th>Type</th><th>Statut</th></tr></thead><tbody>
        @forelse ($associatedDisciplines as $discipline)
          @php($principal = (bool) data_get($discipline, 'pivot.est_principale', data_get($discipline, 'est_principale', false)))
          <tr class="{{ $principal ? 'discipline-principale' : '' }}"><td>{{ data_get($discipline, 'code', '—') }}</td><td>{{ data_get($discipline, 'libelle', '—') }}</td><td>@if($principal)<span class="badge badge-active"><i class="fa-solid fa-star"></i> Principale</span>@else Secondaire @endif</td><td>{{ data_get($discipline, 'statut', '—') === 'actif' ? 'Active' : 'Inactive' }}</td></tr>
        @empty
          <tr><td colspan="4" class="empty-message show">Aucune discipline associée.</td></tr>
        @endforelse
      </tbody></table></div>
    </section>
  </section>
</main>

@if ($canAssociateDiscipline && count($availableDisciplines) > 0)
<x-module-indemnite type="modal" id="associate-discipline-modal" title="Associer une discipline">
  <form class="teacher-form" id="associateDisciplineForm" method="POST" action="{{ route('enseignants.disciplines.store', data_get($teacher, 'id', data_get($teacher, 'uuid'))) }}">@csrf
    <div class="form-group"><label for="teacherDiscipline">Discipline <span class="required">*</span></label><select class="form-control" id="teacherDiscipline" name="discipline_id" required><option value="">Sélectionner une discipline active</option>@foreach($availableDisciplines as $discipline)<option value="{{ data_get($discipline, 'id', data_get($discipline, 'uuid')) }}" @selected((string) old('discipline_id') === (string) data_get($discipline, 'id', data_get($discipline, 'uuid')))>{{ data_get($discipline, 'code') }} — {{ data_get($discipline, 'libelle') }}</option>@endforeach</select>@error('discipline_id', 'associateDiscipline')<span class="field-error">{{ $message }}</span>@enderror</div>
    <label class="check-label"><input type="checkbox" name="est_principale" value="1" @checked(old('est_principale'))> Discipline principale</label>
    @error('api', 'associateDiscipline')<div class="alert alert-error">{{ $message }}</div>@enderror
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-primary" type="submit" data-associate-submit>Associer</button></div>
  </form>
</x-module-indemnite>
@endif
@push('scripts')<script>
  var associationForm = document.getElementById('associateDisciplineForm');
  if (associationForm) associationForm.addEventListener('submit', function () { if (!associationForm.checkValidity()) return; var button = associationForm.querySelector('[data-associate-submit]'); button.disabled = true; button.setAttribute('aria-busy', 'true'); button.textContent = 'Enregistrement…'; });
  @if(session('discipline_association_form_open')) document.querySelector('[data-modal-open="associate-discipline-modal"]')?.click(); @endif
</script>@endpush
@endsection
