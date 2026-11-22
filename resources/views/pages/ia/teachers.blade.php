@extends('layouts.app')
@section('title', 'SICORE - Personnel de mon IA')
@section('content')
<main class="main-content">
    <x-topbar title="Gestion du personnel" subtitle="Enseignants de votre Inspection d’Académie" icon="fa-solid fa-users" />
    <section class="content-area">
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
        @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif

        <div class="actions-row teacher-list-actions">
            <p class="breadcrumb">Gestion du personnel &gt; Enseignants — Périmètre : {{ $scopeLabel }}</p>
            <div class="actions-group">
                <a class="btn-secondary" href="{{ route('ia.teachers.submissions') }}">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i> Mes soumissions
                </a>
                <button class="btn-secondary" type="button" data-modal-open="ia-teacher-import-modal">
                    <i class="fa-solid fa-file-import" aria-hidden="true"></i> Importer
                </button>
                <a class="btn-primary" href="{{ route('ia.teachers.create') }}">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Ajouter
                </a>
            </div>
        </div>

        @php
            $engagementLabels = ['non_fonctionnaires' => 'Contractuels et vacataires', 'contractuel' => 'Contractuels', 'vacataire' => 'Vacataires'];
        @endphp
        <form class="filter-panel teacher-filters" method="GET" action="{{ route('enseignants.index') }}">
            @if (isset($engagementLabels[$filters['engagement'] ?? '']))
                <input type="hidden" name="engagement" value="{{ $filters['engagement'] }}">
            @endif
            <div class="form-group"><label for="ia-filter-search">Prénom, nom ou matricule</label>
                <input class="form-control" id="ia-filter-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher par prénom, nom ou matricule…" maxlength="100"></div>
            <div class="form-group"><label for="ia-filter-ief">IEF</label>
                <select class="form-control" id="ia-filter-ief" name="ief_id"><option value="">Toutes les IEF de mon IA</option>
                    @foreach (($references['iefs'] ?? []) as $ief)<option value="{{ $ief['id'] }}" @selected(($filters['ief_id'] ?? '') == $ief['id'])>{{ $ief['libelle'] }}</option>@endforeach
                </select></div>
            <div class="form-group"><label for="ia-filter-lieu">Lieu de service</label>
                <select class="form-control" id="ia-filter-lieu" name="lieu_service_id"><option value="">Tous les lieux de mon IA</option>
                    @foreach (($references['etablissements'] ?? []) as $lieu)<option value="{{ $lieu['id'] }}" @selected(($filters['lieu_service_id'] ?? '') == $lieu['id'])>{{ $lieu['libelle'] }}</option>@endforeach
                </select></div>
            <div class="actions-group">
                <button class="btn-secondary" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrer</button>
                <a class="btn-secondary" href="{{ route('enseignants.index') }}"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Réinitialiser</a>
            </div>
        </form>

        <section class="table-card">
            <div class="panel-header">
                <div>
                    <h2>Liste des enseignants</h2>
                    <p>{{ $data['total'] ?? count($data['data'] ?? []) }} résultat(s){{ isset($engagementLabels[$filters['engagement'] ?? '']) ? ' — '.$engagementLabels[$filters['engagement']] : '' }}</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Enseignant</th><th>Matricule</th><th>Engagement</th><th>Statut</th><th class="actions-cell">Actions</th></tr></thead>
                    <tbody>
                    @php $canUpdate = \App\Support\SicoreAccess::can(request(), 'enseignants.update'); @endphp
                    @forelse (($data['data'] ?? []) as $teacher)
                        @php $teacherName = trim(($teacher['prenom'] ?? '').' '.($teacher['nom'] ?? '')); @endphp
                        <tr>
                            <td><span class="teacher-list-name">{{ $teacherName ?: '—' }}</span></td>
                            <td>{{ $teacher['matricule'] ?: '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', (string) ($teacher['type_engagement'] ?? ''))) ?: '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', (string) ($teacher['statut'] ?? ''))) ?: '—' }}</td>
                            <td class="actions-cell">
                                <a class="icon-action" href="{{ route('ia.teachers.show', $teacher['id']) }}" title="Consulter" aria-label="Consulter le dossier de {{ $teacherName }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                @if ($canUpdate)
                                    <a class="icon-action" href="{{ route('ia.teachers.edit', $teacher['id']) }}" title="Modifier" aria-label="Modifier la fiche de {{ $teacherName }}"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-message show">{{ $error ? 'Liste indisponible.' : 'Aucun enseignant trouvé dans votre IA.' }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @include('pages.ia.pagination', ['pagination' => $data])
        </section>
    </section>
</main>

<x-module-indemnite type="modal" id="ia-teacher-import-modal" title="Importer des enseignants" :open="$errors->has('csv')">
    <p>Excel ou CSV : 500 enseignants maximum, 2 Mo. Un bilan indique les lignes acceptées et bloquées.</p>
    <p>Les dossiers importés seront soumis à la DAGE pour validation.</p>
    <p>Renseignez les libellés des référentiels (statut, IEF, corps, catégorie), sans identifiants.</p>
    @include('pages.enseignants.import-error')
    <div class="ia-import-actions">
        <a class="btn-secondary" href="{{ route('enseignants.import.template') }}"><i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i> Télécharger le modèle Excel</a>
    </div>
    <form method="POST" action="{{ route('enseignants.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group"><label for="ia-teacher-import">Fichier Excel ou CSV *</label>
            <input class="form-control" id="ia-teacher-import" name="csv" type="file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required></div>
        <div class="form-actions">
            <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
            <button class="btn-primary" type="submit"><i class="fa-solid fa-file-import" aria-hidden="true"></i> Importer et soumettre à la DAGE</button>
        </div>
    </form>
</x-module-indemnite>
@endsection
@include('pages.ia.styles')
