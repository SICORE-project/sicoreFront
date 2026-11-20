@extends('layouts.app')
@section('title', 'SICORE - Espace DECPC')
@section('content')
@php
    $nombre = fn ($valeur) => is_numeric($valeur) ? number_format($valeur, 0, ',', ' ') : '—';
    $statutsConvocation = ['brouillon' => 'Brouillon', 'emise' => 'Émise', 'envoyee' => 'Envoyée', 'cloturee' => 'Clôturée'];
    $statutsIndemnite = ['brouillon' => 'Brouillon', 'calcule' => 'Calculée', 'valide' => 'Validée', 'rejete' => 'Rejetée'];
    $convocations = $metrics['convocations'] ?? null;
    $pieces = $metrics['pieces'] ?? null;
@endphp
<main class="main-content decpc-dashboard">
    <x-topbar title="Tableau de bord DECPC" :subtitle="'Périmètre : '.$scopeLabel" icon="fa-solid fa-gauge-high" />
    <section class="content-area">
        @if ($error)
            <div class="decpc-alert" role="alert"><p>{{ $error }}</p><a href="{{ route('dashboard') }}">Réessayer</a></div>
        @endif

        @if ($canConsultConvocations && is_array($convocations))
            <h2 class="decpc-section-title">Convocations</h2>
            <div class="stats-grid four">
                @foreach ([
                    ['total', 'Convocations', 'Toutes les convocations', 'fa-calendar-check', 'green'],
                    ['brouillons', 'Brouillons', 'À finaliser', 'fa-pen-to-square', 'yellow'],
                    ['envoyees', 'Envoyées', 'Transmises à la DAGE', 'fa-paper-plane', 'blue'],
                    ['cloturees', 'Clôturées', 'Sessions terminées', 'fa-circle-check', 'purple'],
                ] as [$key, $label, $note, $icon, $color])
                    <a class="stat-card decpc-stat-link" href="{{ route('indemnites.convocations') }}" aria-label="{{ 'Consulter : '.$label }}">
                        <div><p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ $nombre($convocations[$key] ?? null) }}</p>
                            <p class="stat-note">{{ $note }}</p>
                        </div>
                        <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    </a>
                @endforeach
            </div>

            <div class="dashboard-grid">
                <section class="panel">
                    <div class="panel-header"><h2>Convocations par statut</h2></div>
                    @php
                        $repartition = collect(['brouillons' => 'Brouillon', 'emises' => 'Émise', 'envoyees' => 'Envoyée', 'cloturees' => 'Clôturée'])
                            ->map(fn ($libelle, $key) => ['libelle' => $libelle, 'total' => max(0, (int) ($convocations[$key] ?? 0))]);
                        $maximum = max(1, (int) $repartition->max('total'));
                    @endphp
                    <div class="decpc-distribution">
                        @if ($repartition->sum('total') > 0)
                            <ul class="decpc-bar-chart" aria-label="Convocations par statut">
                                @foreach ($repartition as $item)
                                    <li>
                                        <div class="decpc-bar-label"><span>{{ $item['libelle'] }}</span><strong>{{ $nombre($item['total']) }}</strong></div>
                                        <div class="decpc-bar-track" aria-hidden="true"><span class="decpc-bar-fill" style="width: {{ round($item['total'] / $maximum * 100, 2) }}%"></span></div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="decpc-empty">
                                <i class="fa-solid fa-chart-bar" aria-hidden="true"></i>
                                <p>Aucune convocation enregistrée.</p>
                                <small>Le diagramme apparaîtra dès la création d’une convocation.</small>
                            </div>
                        @endif
                    </div>
                </section>
                <section class="panel">
                    <div class="panel-header"><h2>Dernières convocations</h2></div>
                    <div class="table-responsive"><table class="table">
                        <thead><tr><th scope="col">Objet</th><th scope="col">Statut</th><th scope="col">Émise le</th></tr></thead>
                        <tbody>
                            @forelse (($convocations['dernieres'] ?? []) as $convocation)
                                <tr>
                                    <td>{{ data_get($convocation, 'objet') ?: '—' }}</td>
                                    <td>{{ $statutsConvocation[data_get($convocation, 'statut')] ?? (data_get($convocation, 'statut') ?: '—') }}</td>
                                    <td>{{ data_get($convocation, 'date_emission') ? \Illuminate\Support\Carbon::parse($convocation['date_emission'])->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Aucune convocation disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table></div>
                </section>
            </div>
        @endif

        @if ($canConsultPieces && is_array($pieces))
            <h2 class="decpc-section-title">Pièces justificatives</h2>
            <div class="stats-grid four">
                @foreach ([
                    ['total', 'Pièces déposées', 'Toutes les pièces', 'fa-folder-open', 'green'],
                    ['a_verifier', 'À vérifier', 'En attente de validation', 'fa-hourglass-half', 'yellow'],
                    ['validees', 'Validées', 'Pièces conformes', 'fa-circle-check', 'blue'],
                    ['rejetees', 'Rejetées', 'À redéposer', 'fa-circle-xmark', 'red'],
                ] as [$key, $label, $note, $icon, $color])
                    <a class="stat-card decpc-stat-link" href="{{ route('indemnites.pieces-justificatives') }}" aria-label="{{ 'Consulter : '.$label }}">
                        <div><p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ $nombre($pieces[$key] ?? null) }}</p>
                            <p class="stat-note">{{ $note }}</p>
                        </div>
                        <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($canConsultPersonnel || $canConsultIndemnites)
            <h2 class="decpc-section-title">Personnel et indemnités</h2>
            <div class="stats-grid">
                @foreach ([
                    'total_agents' => ['Nombre total d’agents concernés', $canConsultPersonnel, 'fa-users', 'green'],
                    'dossiers_en_attente' => ['Dossiers d’indemnité en attente', $canConsultIndemnites, 'fa-clock', 'yellow'],
                    'dossiers_valides' => ['Dossiers d’indemnité validés', $canConsultIndemnites, 'fa-circle-check', 'blue'],
                    'dossiers_rejetes_retournes' => ['Dossiers rejetés ou retournés', $canConsultIndemnites, 'fa-circle-xmark', 'red'],
                    'montant_en_cours' => ['Montant total des indemnités en cours', $canConsultIndemnites, 'fa-coins', 'purple'],
                ] as $key => [$label, $allowed, $icon, $color])
                    @if ($allowed)
                        <section class="stat-card">
                            <div><p class="stat-label">{{ $label }}</p>
                                <p class="stat-value">{{ $nombre($metrics[$key] ?? null) }}{{ $key === 'montant_en_cours' && is_numeric($metrics[$key] ?? null) ? ' FCFA' : '' }}</p>
                            </div>
                            <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        </section>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($canConsultIndemnites)
            <div class="dashboard-grid">
                <section class="panel">
                    <div class="panel-header"><h2>Répartition des indemnités par type</h2></div>
                    <div class="table-responsive"><table class="table">
                        <thead><tr><th scope="col">Type</th><th scope="col">Dossiers</th><th scope="col">Montant (FCFA)</th></tr></thead>
                        <tbody>
                            @forelse (($metrics['indemnites_par_type'] ?? []) as $type)
                                <tr><td>{{ data_get($type, 'libelle') ?: 'Non renseigné' }}</td><td>{{ $nombre(data_get($type, 'total')) }}</td><td>{{ $nombre(data_get($type, 'montant')) }}</td></tr>
                            @empty
                                <tr><td colspan="3">Aucune répartition disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table></div>
                </section>
                <section class="panel">
                    <div class="panel-header"><h2>Derniers dossiers reçus ou traités</h2></div>
                    <div class="table-responsive"><table class="table">
                        <thead><tr><th scope="col">Type</th><th scope="col">Statut</th><th scope="col">Montant (FCFA)</th><th scope="col">Mis à jour</th></tr></thead>
                        <tbody>
                            @forelse (($metrics['derniers_dossiers'] ?? []) as $dossier)
                                <tr>
                                    <td>{{ data_get($dossier, 'type') ?: '—' }}</td>
                                    <td>{{ $statutsIndemnite[data_get($dossier, 'statut')] ?? (data_get($dossier, 'statut') ?: '—') }}</td>
                                    <td>{{ $nombre(data_get($dossier, 'montant_total')) }}</td>
                                    <td>{{ data_get($dossier, 'updated_at') ? \Illuminate\Support\Carbon::parse($dossier['updated_at'])->format('d/m/Y à H:i') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">Aucun dossier récent disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table></div>
                </section>
            </div>
        @endif
    </section>
</main>
@endsection

@push('styles')
<style>
    .decpc-dashboard .decpc-section-title { margin: 8px 0 -4px; font-size: 18px; color: #1e293b; }
    .decpc-dashboard .decpc-stat-link { color: inherit; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    .decpc-dashboard .decpc-stat-link:hover { border-color: #176637; box-shadow: 0 6px 20px #17663720; }
    .decpc-dashboard .decpc-stat-link:focus-visible { outline: 3px solid #176637; outline-offset: 3px; }
    .decpc-dashboard .decpc-alert { display: flex; align-items: center; justify-content: space-between; gap: 16px; border: 1px solid #ead397; border-radius: 12px; background: #fff8e7; padding: 16px 22px; color: #705114; }
    .decpc-dashboard .decpc-alert p { margin: 0; }
    .decpc-dashboard .decpc-alert a { color: inherit; font-weight: 700; }
    .decpc-dashboard .decpc-distribution { padding: 24px; }
    .decpc-dashboard .decpc-bar-chart { list-style: none; margin: 0; padding: 0; display: grid; gap: 22px; }
    .decpc-dashboard .decpc-bar-label { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 8px; font-size: 14px; }
    .decpc-dashboard .decpc-bar-label strong { color: #176637; font-variant-numeric: tabular-nums; }
    .decpc-dashboard .decpc-bar-track { height: 16px; border-radius: 6px; background: #eef4f1; overflow: hidden; }
    .decpc-dashboard .decpc-bar-fill { display: block; height: 100%; background: #176637; border-radius: inherit; }
    .decpc-dashboard .decpc-empty { min-height: 170px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #64748b; gap: 12px; }
    .decpc-dashboard .decpc-empty i { font-size: 32px; color: #176637; }
    .decpc-dashboard .decpc-empty p { margin: 0; color: #334155; }
    .decpc-dashboard .dashboard-grid > *, .decpc-dashboard .stat-card > div { min-width: 0; }
    .decpc-dashboard .stat-value { overflow-wrap: anywhere; }
    @media (max-width: 900px) { .decpc-dashboard .stats-grid, .decpc-dashboard .dashboard-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .decpc-dashboard .decpc-alert { align-items: flex-start; flex-direction: column; } }
</style>
@endpush
