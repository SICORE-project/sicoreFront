@extends('layouts.app')
@section('title', 'SICORE - Nouveaux enseignants vacataires')
@section('content')
<main class="main-content recruitment-page">
    @include('pages.recruitment.header', ['heading' => 'Nouveaux enseignants vacataires'])
    <section class="content-area">
        @include('pages.recruitment.search')
        <div class="actions-row">
            <div><h2>Listes des enseignants recrutés</h2><p>Importez les nouveaux enseignants vacataires, joignez leur ordre de service et suivez leur prise de service.</p></div>
            @if (app(\App\Services\Organisation\InterfaceAccess::class)->allowsRoute('recruitment.create'))
                <a class="btn-primary" href="{{ route('recruitment.create') }}"><i class="fa-solid fa-file-import" aria-hidden="true"></i> Importer une liste</a>
            @endif
        </div>
        @if ($error)<p class="recruitment-errors" role="alert">{{ $error }}</p>@endif
        @if (count($notices))
            <section class="panel recruitment-panel">
                <h2>Notifications et échéances</h2>
                <ul class="recruitment-notices">
                    @foreach ($notices as $notice)
                        <li><a href="{{ route('recruitment.show', $notice['batch_id']) }}">{{ $notice['message'] }}</a> <small>{{ $notice['created_at'] }}</small></li>
                    @endforeach
                </ul>
            </section>
        @endif
        <section class="panel recruitment-panel">
            <h2>Rechercher un lot</h2>
            <form method="GET" action="{{ route('recruitment.index') }}" class="recruitment-form">
                <div class="form-grid">
                    <div class="form-group"><label for="batch-year">Année de recrutement</label><input class="form-control" type="number" name="year" id="batch-year" min="1900" max="2100" placeholder="Ex. 2025" value="{{ request('year') }}"></div>
                    <div class="form-group"><label for="batch-reference">Nom ou référence du lot</label><input class="form-control" name="reference" id="batch-reference" maxlength="100" placeholder="Tout ou partie de la référence" value="{{ request('reference') }}"></div>
                </div>
                <p>L’année correspond à la date de recrutement, pas à la date d’import du fichier.</p>
                <button class="btn-primary" type="submit">Rechercher les lots</button>
                <a href="{{ route('recruitment.index') }}">Afficher tous les lots</a>
            </form>
        </section>
        <section class="table-card">
            <div class="panel-header"><h2>Liste des lots</h2><span>{{ count($batches) }} lot(s)</span></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Référence</th><th>Date de recrutement</th><th>Transmission DAGE</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr><td>{{ $batch['reference'] }}</td><td>{{ $batch['recruited_at'] }}</td>
                            <td><span class="badge {{ $batch['transmitted_at'] ? 'badge-active' : 'badge-suspended' }}">{{ $batch['transmitted_at'] ? 'Transmis' : 'À préparer' }}</span></td>
                            <td><a class="btn-secondary" href="{{ route('recruitment.show', $batch['id']) }}">Ouvrir le lot</a></td></tr>
                    @empty
                        <tr><td colspan="4"><x-table-empty-state>{{ $error ? 'Les lots ne sont pas disponibles actuellement.' : 'Aucun lot de recrutement dans votre périmètre.' }}</x-table-empty-state></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </section>
    </section>
</main>
@endsection
