@extends('layouts.app')
@section('title', 'SICORE - Soumissions des enseignants')
@section('content')
<main class="main-content">
  <x-topbar title="Dossiers enseignants soumis" subtitle="Validation par la DAGE" icon="fa-solid fa-user-check" />
  <section class="content-area">
    <div class="actions-group"><a class="btn-secondary" href="{{ route('enseignants.index') }}">Retour aux enseignants</a>
      @unless($reviewing)<a class="btn-primary" href="{{ route('ia.teachers.create') }}">Ajouter un enseignant</a>@endunless
    </div>
    @if(session('success'))<p class="alert alert-success" role="status">{{ session('success') }}</p>@endif
    @if(session('error') || $error)<p class="alert alert-danger" role="alert">{{ session('error') ?: $error }}</p>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    @if (!empty($stats))
      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Dossiers soumis</p><p class="stat-value">{{ number_format((int) ($stats['total'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Tous les dossiers</p></div>
          <span class="stat-icon green"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">En attente de validation</p><p class="stat-value">{{ number_format((int) ($stats['en_attente'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">À traiter par la DAGE</p></div>
          <span class="stat-icon yellow"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Dossiers validés</p><p class="stat-value">{{ number_format((int) ($stats['valides'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Ajoutés aux enseignants</p></div>
          <span class="stat-icon blue"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">IA concernées</p><p class="stat-value">{{ number_format((int) ($stats['ia'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Ayant soumis des dossiers</p></div>
          <span class="stat-icon purple"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
        </article>
      </div>
    @endif
    <p>{{ $reviewing ? 'Consultez les dossiers transmis par les IA avant de les valider.' : 'Les dossiers en attente ne figurent pas encore dans la liste des enseignants.' }}</p>
    <section class="table-card"><div class="table-responsive"><table class="table">
      <thead><tr><th>Enseignant</th><th>Matricule</th><th>IA</th><th>Statut</th><th>Actions</th></tr></thead>
      <tbody>@forelse(($data['data'] ?? []) as $submission)
        <tr><td>{{ data_get($submission, 'payload.prenom') }} {{ data_get($submission, 'payload.nom') }}</td>
          <td>{{ $submission['matricule'] }}</td><td>{{ data_get($submission, 'payload.ia.libelle') }}</td>
          <td>{{ $submission['status'] === 'approved' ? 'Validé par la DAGE' : 'En attente de validation' }}</td>
          <td><div class="actions-group"><button class="btn-secondary" type="button" data-modal-open="teacher-view-modal" data-view-teacher='@json($submission["payload"])'>Consulter</button>
          @if($reviewing && $submission['status'] === 'pending')
            <form method="POST" action="{{ route('enseignants.submissions.approve', $submission['id']) }}" onsubmit="return confirm('Valider ce dossier et ajouter cet enseignant à la liste ?');">@csrf<button class="btn-primary" type="submit">Valider</button></form>
          @endif</div></td>
        </tr>
      @empty<tr><td colspan="5">Aucun dossier {{ $reviewing ? 'à valider' : 'soumis' }}.</td></tr>@endforelse</tbody>
    </table></div></section>
    @if(($data['last_page'] ?? 1) > 1)
      <nav class="pagination" aria-label="Pagination">
        @if($data['current_page'] > 1)<a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['page' => $data['current_page'] - 1]) }}">Précédent</a>@endif
        <span>{{ $data['current_page'] }} / {{ $data['last_page'] }}</span>
        @if($data['current_page'] < $data['last_page'])<a class="btn-secondary" href="{{ request()->fullUrlWithQuery(['page' => $data['current_page'] + 1]) }}">Suivant</a>@endif
      </nav>
    @endif
  </section>
</main>
<x-module-indemnite type="modal" id="teacher-view-modal" title="Dossier soumis">
  @include('pages.enseignants.partials.profile')
  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-profile.css') }}?v={{ filemtime(public_path('assets/css/teacher-profile.css')) }}">
<style>#teacher-view-modal .modal-dialog { width: min(920px, calc(100% - 32px)); }</style>
@endpush
@push('scripts')
<script src="{{ asset('assets/js/teacher-profile.js') }}?v={{ filemtime(public_path('assets/js/teacher-profile.js')) }}"></script>
@endpush
