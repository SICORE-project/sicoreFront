@extends('layouts.app')
@section('title', 'SICORE - Personnel de mon IA')
@section('content')
<main class="main-content">
    <x-topbar title="Gestion du personnel" subtitle="Enseignants de votre Inspection d’Académie" icon="fa-solid fa-users" />
    <section class="content-area">
        <section class="objective-card"><h2>{{ $scopeLabel }}</h2><p>Périmètre : {{ $scopeLabel }}</p></section>
        @if ($error)<p class="alert alert-warning" role="alert">{{ $error }}</p>@endif
        <section class="panel">
            <div class="panel-header"><h2>Enseignants</h2></div>
            <form method="get" class="ia-filters">
                @if (($filters['engagement'] ?? '') === 'non_fonctionnaires')
                    <input type="hidden" name="engagement" value="non_fonctionnaires">
                    <p>Contractuels et vacataires</p>
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
