@extends('layouts.app')
@section('title', 'SICORE - Lot de recrutement')
@section('content')
@php
    $access = app(\App\Services\Organisation\InterfaceAccess::class);
    $activeCount = collect($members)->filter(fn ($member) => filter_var($member['est_actif'], FILTER_VALIDATE_BOOLEAN))->count();
    $dueCount = collect($members)->where('due', true)->count();
    $eventLabels = ['recrutement' => 'Recrutement', 'ordre_service' => 'Ordre de service', 'transmission_dage' => 'Transmission à la DAGE', 'prise_service' => 'Prise de service', 'changement_statut' => 'Changement de catégorie', 'consultation' => 'Consultation du lot', 'consultation_document' => 'Consultation du justificatif'];
@endphp
<main class="main-content recruitment-page">
    @include('pages.recruitment.header', ['heading' => 'Lot '.$batch['reference']])
    <section class="content-area">
        <a href="{{ route('recruitment.index') }}">← Retour aux lots</a>
        <div class="stats-grid four">
            @foreach (['Recrutés du périmètre' => count($members), 'Actifs' => $activeCount, 'En attente de prise de service' => count($members) - $activeCount, 'Échéances à examiner' => $dueCount] as $label => $value)
                <article class="stat-card"><div><p class="stat-label">{{ $label }}</p><p class="stat-value">{{ $value }}</p></div></article>
            @endforeach
        </div>
        <section class="panel recruitment-panel">
            <h2>Ordre de service du lot</h2>
            <p>Recrutement du {{ $batch['recruited_at'] }} · {{ $batch['transmitted_at'] ? 'Transmis à la DAGE le '.$batch['transmitted_at'] : 'Lot en préparation' }}</p>
            @php($osEvent = collect($history)->first(fn ($event) => $event['action'] === 'ordre_service' && $event['has_document']))
            @if ($osEvent)<a class="btn-secondary" href="{{ route('recruitment.document', $osEvent['id']) }}">Télécharger l’OS</a>
            @elseif (!$batch['has_os'])<p>Aucun ordre de service joint.</p>@endif
            @if (!$batch['transmitted_at'] && $access->allowsRoute('recruitment.os'))
                <form method="POST" action="{{ route('recruitment.os', $batch['id']) }}" enctype="multipart/form-data" class="recruitment-form">
                    @csrf<label for="os-document">{{ $batch['has_os'] ? 'Remplacer l’OS' : 'Joindre l’OS' }} — PDF, 10 Mo maximum</label>
                    <input class="form-control" type="file" id="os-document" name="document" accept="application/pdf" required>
                    <button class="btn-primary" type="submit">Enregistrer l’OS</button>
                </form>
            @endif
            @if (!$batch['transmitted_at'] && $batch['has_os'] && $access->allowsRoute('recruitment.transmit'))
                <form method="POST" action="{{ route('recruitment.transmit', $batch['id']) }}" class="recruitment-form">@csrf
                    <p>La transmission rend le lot disponible pour la suite du traitement et verrouille son OS.</p>
                    <button class="btn-primary" type="submit">Transmettre le lot à la DAGE</button>
                </form>
            @endif
        </section>
        @if ($dueCount)<p class="recruitment-alert" role="status">{{ $dueCount }} dossier(s) ont atteint deux ans depuis la prise de service. Une décision justificative est nécessaire pour valider le passage à contractuel.</p>@endif
        <section class="table-card">
            <div class="panel-header"><h2>Liste des recrutés</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Enseignant</th><th>Catégorie</th><th>État</th><th>Prise de service</th><th>Échéance des deux ans</th><th>Actions et historique</th></tr></thead>
                <tbody>@forelse ($members as $member)
                    <tr id="agent-{{ $member['id'] }}">
                        <td><strong>{{ $member['prenom'] }} {{ $member['nom'] }}</strong><br><small>{{ str_starts_with($member['matricule'] ?? '', 'TMP') ? 'Matricule non attribué' : $member['matricule'] }}</small></td>
                        <td>{{ ucfirst($member['engagement']) }}</td>
                        <td><span class="badge {{ filter_var($member['est_actif'], FILTER_VALIDATE_BOOLEAN) ? 'badge-active' : 'badge-suspended' }}">{{ filter_var($member['est_actif'], FILTER_VALIDATE_BOOLEAN) ? 'Actif' : 'Inactif' }}</span></td>
                        <td>{{ $member['service_date'] ?? 'En attente' }}</td>
                        <td>{{ $member['due_at'] ?? '—' }}@if ($member['due'])<br><strong class="recruitment-due">À examiner</strong>@endif</td>
                        <td>
                            @if (!$member['service_date'] && $batch['transmitted_at'] && $access->allowsRoute('recruitment.service'))
                                <a class="btn-secondary" href="{{ route('recruitment.service-form', [$batch['id'], $member['id']]) }}">Enregistrer la prise de service</a>
                            @endif
                            @if ($member['due'] && $access->allowsRoute('recruitment.transition'))
                                <details class="recruitment-details"><summary>Valider le passage à contractuel</summary>
                                    <form method="POST" action="{{ route('recruitment.transition', [$batch['id'], $member['id']]) }}" enctype="multipart/form-data" class="recruitment-form">
                                        @csrf
                                        <label for="date-{{ $member['id'] }}">Date d’effet de la décision *</label><input class="form-control" type="date" id="date-{{ $member['id'] }}" name="effective_date" min="{{ $member['due_at'] }}" max="{{ now()->toDateString() }}" required>
                                        <label for="decision-{{ $member['id'] }}">Décision justificative — PDF, 10 Mo *</label><input class="form-control" type="file" id="decision-{{ $member['id'] }}" name="document" accept="application/pdf" required>
                                        <button class="btn-primary" type="submit">Valider la décision</button>
                                    </form>
                                </details>
                            @endif
                            <details class="recruitment-details"><summary>Historique de carrière</summary>
                                <ul class="recruitment-history">
                                    @forelse (collect($history)->where('member_id', $member['id'])->whereIn('action', ['recrutement', 'prise_service', 'changement_statut']) as $event)
                                        <li><strong>{{ $eventLabels[$event['action']] }}</strong> — {{ $event['effective_date'] ?? $event['created_at'] }}
                                            <p>{{ $event['previous_status'] ? ucfirst($event['previous_status']).' → ' : '' }}{{ ucfirst($event['new_status'] ?? '') }}</p>
                                            <small>Enregistré le {{ $event['created_at'] }} par l’agent n° {{ $event['user_id'] }}</small>
                                            @if ($event['has_document'])<p><a href="{{ route('recruitment.document', $event['id']) }}">Télécharger le justificatif</a></p>@endif
                                        </li>
                                    @empty<li>Aucun événement disponible.</li>@endforelse
                                </ul>
                            </details>
                        </td>
                    </tr>
                @empty<tr><td colspan="6"><x-table-empty-state>Aucun recruté visible dans ce lot.</x-table-empty-state></td></tr>@endforelse</tbody>
            </table></div>
        </section>
        <details class="panel recruitment-panel"><summary>Journal du lot</summary>
            <ul class="recruitment-history">@foreach (collect($history)->whereNull('member_id') as $event)
                <li>{{ $eventLabels[$event['action']] ?? $event['action'] }} — {{ $event['created_at'] }} — agent n° {{ $event['user_id'] }}
                    @if ($event['has_document'])<a href="{{ route('recruitment.document', $event['id']) }}">Justificatif</a>@endif</li>
            @endforeach</ul>
        </details>
    </section>
</main>
@endsection
