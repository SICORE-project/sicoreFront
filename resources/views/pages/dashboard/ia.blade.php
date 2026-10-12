@extends('layouts.app')

@section('title', 'SICORE - Tableau de bord IA')

@section('content')
<main class="main-content ia-dashboard">
    <x-topbar title="Tableau de bord IA" :subtitle="'Périmètre : '.$scopeLabel" icon="fa-solid fa-gauge-high" />
    <section class="content-area">
        @if ($error)
            <div class="ia-alert" role="alert"><p>{{ $error }}</p><a href="{{ route('dashboard') }}">Réessayer</a></div>
        @endif

        @if ($canConsultPersonnel)
            <div class="stats-grid ia-personnel-stats">
                @foreach ([
                    ['agents', 'Enseignants', 'fa-users', 'green'],
                    ['non_fonctionnaires', 'Contractuels et vacataires', 'fa-chalkboard-user', 'yellow'],
                ] as [$key, $label, $icon, $color])
                    <a class="stat-card ia-stat-link" href="{{ route('enseignants.index', $key === 'non_fonctionnaires' ? ['engagement' => 'non_fonctionnaires'] : []) }}" aria-label="{{ 'Consulter : '.$label }}">
                        <div><p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ is_numeric(data_get($metrics, 'indicateurs.'.$key)) ? number_format(data_get($metrics, 'indicateurs.'.$key), 0, ',', ' ') : '—' }}</p>
                            <p class="stat-note">{{ isset($metrics['indicateurs'][$key]) ? $scopeLabel : 'Indicateur indisponible' }}</p>
                        </div>
                        <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($canConsultPayroll && (app(\App\Services\Organisation\InterfaceAccess::class)->allows('paie.sommes_percues.read') || ($canConsultSalaryMass && isset($metrics['indicateurs']['masse_salariale']))))
            <section class="panel">
                <div class="panel-header"><div><h2>Suivi de la paie</h2><p>{{ data_get($metrics, 'periode.code') ?: 'Aucune période disponible' }}</p></div></div>
                <div class="ia-payroll">
                    @foreach (['sommes_percues' => 'Sommes perçues'] as $key => $label)
                        @if (app(\App\Services\Organisation\InterfaceAccess::class)->allows('paie.sommes_percues.read'))
                            <div><p class="stat-label">{{ $label }}</p><p class="stat-value">{{ number_format($metrics['indicateurs'][$key] ?? 0, 0, ',', ' ') }}{{ $key === 'sommes_percues' ? ' FCFA' : '' }}</p></div>
                        @endif
                    @endforeach
                    @if ($canConsultSalaryMass && isset($metrics['indicateurs']['masse_salariale']))
                        <div><p class="stat-label">Masse salariale brute</p><p class="stat-value">{{ number_format($metrics['indicateurs']['masse_salariale'], 0, ',', ' ') }} <small>FCFA</small></p></div>
                    @endif
                </div>
            </section>
        @endif

        @if ($canConsultPersonnel)
            <div class="dashboard-grid">
                @foreach (['iefs' => 'Enseignants par IEF', 'lieu_de_services' => 'Enseignants par lieu de service'] as $key => $title)
                    <section class="panel">
                        <div class="panel-header"><h2>{{ $title }}</h2></div>
                        @php
                            $distribution = collect($metrics[$key] ?? [])->sortByDesc('total')->values();
                            $maximum = max(1, (int) $distribution->max('total'));
                        @endphp
                        <div class="ia-distribution">
                            @if ($distribution->isNotEmpty())
                                <p class="ia-chart-caption">Effectif des enseignants</p>
                                <ul class="ia-bar-chart" aria-label="{{ $title }}">
                                    @foreach ($distribution as $item)
                                        @php($effectif = max(0, (int) $item['total']))
                                        <li class="ia-bar-row">
                                            <div class="ia-bar-label">
                                                <span>{{ $item['libelle'] ?: 'Non renseigné' }}</span>
                                                <strong>{{ number_format($effectif, 0, ',', ' ') }}</strong>
                                            </div>
                                            <div class="ia-bar-track" aria-hidden="true">
                                                <span class="ia-bar-fill {{ $key === 'iefs' ? '' : 'ia-bar-fill-teal' }}" style="width: {{ round($effectif / $maximum * 100, 2) }}%"></span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="ia-chart-empty">
                                    <i class="fa-solid fa-chart-bar" aria-hidden="true"></i>
                                    <p>Aucune répartition disponible.</p>
                                    <small>Le diagramme apparaîtra dès que des enseignants seront rattachés.</small>
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        @if ($canConsultPayroll)
            <section class="panel">
                <div class="panel-header"><div><h2>Dernières opérations de paie</h2><p>Les 10 derniers bulletins mis à jour sur la période</p></div></div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th scope="col">Enseignant</th><th scope="col">Statut du paiement</th><th scope="col">Dernière modification</th></tr></thead>
                    <tbody>
                        @forelse (($metrics['dernieres_operations'] ?? []) as $operation)
                            <tr>
                                <td>{{ $operation['prenom'] }} {{ $operation['nom'] }}</td>
                                <td>{{ ['paid' => 'Payé', 'pending' => 'En attente', 'rejected' => 'Rejeté'][$operation['payment_status'] ?? ''] ?? ($operation['payment_status'] ?? '—') }}</td>
                                <td>{{ $operation['updated_at'] ? \Illuminate\Support\Carbon::parse($operation['updated_at'])->format('d/m/Y à H:i') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Aucune opération disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </section>
        @endif

    </section>
</main>
@endsection

@push('styles')
<style>
    .ia-dashboard .ia-personnel-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ia-dashboard .ia-stat-link { color: inherit; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    .ia-dashboard .ia-stat-link:hover { border-color: #176637; box-shadow: 0 6px 20px #17663720; }
    .ia-dashboard .ia-stat-link:focus-visible { outline: 3px solid #176637; outline-offset: 3px; }
    @media (max-width: 640px) { .ia-dashboard .ia-personnel-stats { grid-template-columns: 1fr; } }

    .ia-dashboard .ia-alert { display: flex; align-items: center; justify-content: space-between; gap: 16px; border: 1px solid #ead397; border-radius: 12px; background: #fff8e7; padding: 16px 22px; color: #705114; }
    .ia-dashboard .ia-alert p { margin: 0; }
    .ia-dashboard .ia-alert a { color: inherit; font-weight: 700; }
    .ia-dashboard .ia-payroll { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 24px; padding: 24px; }
    .ia-dashboard .ia-payroll small { font-size: 14px; font-weight: 500; }
    .ia-dashboard .ia-distribution { max-height: 420px; overflow-y: auto; padding: 24px; }
    .ia-dashboard .ia-chart-caption { margin: 0 0 20px; color: #64748b; font-size: 13px; }
    .ia-dashboard .ia-bar-chart { list-style: none; margin: 0; padding: 0; display: grid; gap: 22px; }
    .ia-dashboard .ia-bar-label { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 8px; font-size: 14px; }
    .ia-dashboard .ia-bar-label span { min-width: 0; overflow-wrap: anywhere; }
    .ia-dashboard .ia-bar-label strong { color: #176637; flex-shrink: 0; font-variant-numeric: tabular-nums; }
    .ia-dashboard .ia-bar-track { height: 16px; border-radius: 6px; background: #eef4f1; overflow: hidden; }
    .ia-dashboard .ia-bar-fill { display: block; height: 100%; background: #176637; border-radius: inherit; }
    .ia-dashboard .ia-bar-fill-teal { background: #168477; }
    .ia-dashboard .ia-chart-empty { min-height: 170px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #64748b; gap: 12px; }
    .ia-dashboard .ia-chart-empty i { font-size: 32px; color: #176637; }
    .ia-dashboard .ia-chart-empty p { margin: 0; color: #334155; }
    .ia-dashboard .ia-chart-empty small { max-width: 320px; }
    .ia-dashboard .dashboard-grid > *, .ia-dashboard .stat-card > div { min-width: 0; }
    .ia-dashboard .stat-value { overflow-wrap: anywhere; }
    @media (max-width: 900px) { .ia-dashboard .stats-grid, .ia-dashboard .dashboard-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .ia-dashboard .ia-alert { align-items: flex-start; flex-direction: column; } }
</style>
@endpush
