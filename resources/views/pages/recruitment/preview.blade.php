@extends('layouts.app')
@section('title', 'SICORE - Vérification du lot')
@section('content')
<main class="main-content recruitment-page">
    @include('pages.recruitment.header', ['heading' => 'Confirmer le lot de recrutement'])
    <section class="content-area">
        <section class="panel recruitment-panel">
            <h2>{{ $pending['reference'] }}</h2>
            <p>Date de recrutement : {{ $pending['recruited_at'] }} · <strong>{{ $total }} enseignant(s) vérifié(s)</strong></p>
            <p>Après confirmation, tous les recrutés seront <strong>inactifs</strong>. L’IA enregistrera leur prise de service sur présentation du certificat.</p>
            <p>L’aperçu est valable 30 minutes. Les {{ count($rows) }} premières lignes sont affichées ; la validation concerne la totalité du fichier.</p>
        </section>
        <section class="table-card"><div class="table-responsive"><table class="table">
            <thead><tr><th>Matricule</th><th>Prénom</th><th>Nom</th><th>Catégorie</th><th>IA</th><th>IEF</th><th>Établissement</th></tr></thead>
            <tbody>@foreach ($rows as $row)<tr>
                <td>{{ $row['matricule'] ?: 'Non attribué' }}</td><td>{{ $row['prenom'] }}</td><td>{{ $row['nom'] }}</td><td>{{ ucfirst($row['type_engagement']) }}</td>
                <td>{{ $row['ia_id'] ?? '—' }}</td><td>{{ $row['ief_id'] ?? '—' }}</td><td>{{ $row['lieu_service_id'] ?? '—' }}</td>
            </tr>@endforeach</tbody>
        </table></div></section>
        <div class="actions-row">
            <form method="POST" action="{{ route('recruitment.cancel') }}">@csrf<button class="btn-secondary" type="submit">Annuler et corriger le fichier</button></form>
            <form method="POST" action="{{ route('recruitment.store') }}">@csrf<input type="hidden" name="token" value="{{ $pending['token'] }}"><button class="btn-primary" type="submit">Confirmer l’import de {{ $total }} recruté(s)</button></form>
        </div>
    </section>
</main>
@endsection
