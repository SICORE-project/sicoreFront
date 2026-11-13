@extends('layouts.app')
@section('title', 'SICORE - Personnel de mon IA')
@section('content')
<main class="main-content">
    <x-topbar title="Gestion du personnel" subtitle="Enseignants de votre Inspection d’Académie" icon="fa-solid fa-users" />
    <section class="content-area">
        <section class="objective-card"><h2>{{ $scopeLabel }}</h2><p>Périmètre : {{ $scopeLabel }}</p></section>
        @if ($error)<p class="alert alert-warning" role="alert">{{ $error }}</p>@endif
        @if (!empty($data['stats']))
          {{-- 6 cartes sur 3 colonnes : 2 lignes complètes. --}}
          <div class="stats-grid three">
            <article class="stat-card">
              <div><p class="stat-label">Enseignants de l’IA</p><p class="stat-value">{{ number_format((int) ($data['stats']['total'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Tous statuts confondus</p></div>
              <span class="stat-icon green"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span>
            </article>
            <article class="stat-card">
              <div><p class="stat-label">En activité</p><p class="stat-value">{{ number_format((int) ($data['stats']['en_activite'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Statut en activité</p></div>
              <span class="stat-icon blue"><i class="fa-solid fa-user-check" aria-hidden="true"></i></span>
            </article>
            <article class="stat-card">
              <div><p class="stat-label">Fonctionnaires</p><p class="stat-value">{{ number_format((int) ($data['stats']['fonctionnaires'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Statut fonctionnaire</p></div>
              <span class="stat-icon green"><i class="fa-solid fa-user-tie" aria-hidden="true"></i></span>
            </article>
            <article class="stat-card">
              <div><p class="stat-label">Contractuels</p><p class="stat-value">{{ number_format((int) ($data['stats']['contractuels'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Statut contractuel</p></div>
              <span class="stat-icon yellow"><i class="fa-solid fa-file-signature" aria-hidden="true"></i></span>
            </article>
            <article class="stat-card">
              <div><p class="stat-label">Vacataires</p><p class="stat-value">{{ number_format((int) ($data['stats']['vacataires'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Statut vacataire</p></div>
              <span class="stat-icon blue"><i class="fa-solid fa-user-clock" aria-hidden="true"></i></span>
            </article>
            <article class="stat-card">
              <div><p class="stat-label">IEF couvertes</p><p class="stat-value">{{ number_format((int) ($data['stats']['ief'] ?? 0), 0, ',', ' ') }}</p><p class="stat-note">Dans votre IA</p></div>
              <span class="stat-icon purple"><i class="fa-solid fa-sitemap" aria-hidden="true"></i></span>
            </article>
          </div>
        @endif
        @include('pages.enseignants.import-report')
      @if(session('success'))<p class="alert alert-success" role="status">{{ session('success') }}</p>@endif
        <section class="panel">
            <h2>Importer des enseignants</h2>
            <p>Excel ou CSV : 500 enseignants maximum, 2 Mo. Les dossiers seront soumis à la DAGE.</p>
            <p>Renseignez les libellés des référentiels, sans identifiants.</p>
            <a class="btn-secondary" href="{{ route('enseignants.import.template') }}">Télécharger le modèle Excel</a>
            @include('pages.enseignants.import-error')
            <form method="POST" action="{{ route('enseignants.import.store') }}" enctype="multipart/form-data">
                @csrf
                <label for="ia-teacher-import">Liste des enseignants (Excel ou CSV)</label>
                <input class="form-control" id="ia-teacher-import" name="csv" type="file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required>
                <button class="btn-primary" type="submit">Importer et soumettre à la DAGE</button>
            </form>
        </section>
        <section class="panel">
            <div class="panel-header"><h2>Enseignants</h2><div class="actions-group"><a class="btn-primary" href="{{ route('ia.teachers.create') }}">Ajouter un enseignant</a><a class="btn-secondary" href="{{ route('ia.teachers.submissions') }}">Mes soumissions</a></div></div>
            <form method="get" class="ia-filters">
                @php
                    $engagementLabels = ['non_fonctionnaires' => 'Contractuels et vacataires', 'contractuel' => 'Contractuels', 'vacataire' => 'Vacataires'];
                @endphp
                @if (isset($engagementLabels[$filters['engagement'] ?? '']))
                    <input type="hidden" name="engagement" value="{{ $filters['engagement'] }}">
                    <p>{{ $engagementLabels[$filters['engagement']] }}</p>
                @endif
                <label>Rechercher<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom, prénom ou matricule"></label>
                <label>IEF<select name="ief_id"><option value="">Toutes les IEF de mon IA</option>
                    @foreach (($references['iefs'] ?? []) as $ief)<option value="{{ $ief['id'] }}" @selected(($filters['ief_id'] ?? '') == $ief['id'])>{{ $ief['libelle'] }}</option>@endforeach
                </select></label>
                <label>Lieu de service<select name="lieu_service_id"><option value="">Tous les lieux de mon IA</option>
                    @foreach (($references['etablissements'] ?? []) as $lieu)<option value="{{ $lieu['id'] }}" @selected(($filters['lieu_service_id'] ?? '') == $lieu['id'])>{{ $lieu['libelle'] }}</option>@endforeach
                </select></label>
                <button class="btn btn-primary" type="submit">Filtrer</button><a href="{{ route('enseignants.index') }}">Réinitialiser</a>
            </form>
            <div class="table-responsive"><table class="table"><thead><tr><th>Matricule</th><th>Enseignant</th><th>Engagement</th><th>Statut</th><th>Action</th></tr></thead><tbody>
                @forelse (($data['data'] ?? []) as $teacher)
                    <tr><td>{{ $teacher['matricule'] }}</td><td>{{ $teacher['prenom'] }} {{ $teacher['nom'] }}</td><td>{{ ucfirst(str_replace('_', ' ', $teacher['type_engagement'])) }}</td><td>{{ ucfirst(str_replace('_', ' ', $teacher['statut'])) }}</td><td><a href="{{ route('ia.teachers.show', $teacher['id']) }}">Consulter</a></td></tr>
                @empty<tr><td colspan="5">{{ $error ? 'Liste indisponible.' : 'Aucun enseignant trouvé dans votre IA.' }}</td></tr>@endforelse
            </tbody></table></div>
            @include('pages.ia.pagination', ['pagination' => $data])
        </section>
    </section>
</main>
@endsection
@include('pages.ia.styles')
