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

        @if ($canConsultPayroll)
            <section class="panel">
                <div class="panel-header"><div><h2>Suivi de la paie</h2><p>{{ data_get($metrics, 'periode.code') ?: 'Aucune période disponible' }}</p></div></div>
                <div class="ia-payroll">
                    @foreach (['bulletins_generes' => 'Bulletins générés', 'bulletins_restants' => 'Bulletins restants'] as $key => $label)
                        <div><p class="stat-label">{{ $label }}</p><p class="stat-value">{{ is_numeric(data_get($metrics, 'indicateurs.'.$key)) ? number_format(data_get($metrics, 'indicateurs.'.$key), 0, ',', ' ') : '—' }}</p></div>
                    @endforeach
                    @foreach (['bulletins_payes' => 'Bulletins payés', 'sommes_percues' => 'Sommes perçues'] as $key => $label)
                        @if (app(\App\Services\Organisation\InterfaceAccess::class)->allows('paie.sommes_percues.read'))
                            <div><p class="stat-label">{{ $label }}</p><p class="stat-value">{{ isset($metrics['indicateurs'][$key]) ? number_format($metrics['indicateurs'][$key], 0, ',', ' ') : '—' }}{{ $key === 'sommes_percues' ? ' FCFA' : '' }}</p></div>
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
                        <div class="table-responsive ia-distribution"><table class="table">
                            <thead><tr><th scope="col">{{ $key === 'iefs' ? 'IEF' : 'Lieu de service' }}</th><th scope="col">Effectif</th></tr></thead>
                            <tbody>
                                @forelse (($metrics[$key] ?? []) as $item)
                                    <tr><td>{{ $item['libelle'] ?: 'Non renseigné' }}</td><td>{{ number_format($item['total'], 0, ',', ' ') }}</td></tr>
                                @empty
                                    <tr><td colspan="2">Aucune répartition disponible.</td></tr>
                                @endforelse
                            </tbody>
                        </table></div>
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
    .ia-dashboard .ia-distribution { max-height: 360px; overflow: auto; }
    .ia-dashboard .dashboard-grid > *, .ia-dashboard .stat-card > div { min-width: 0; }
    .ia-dashboard .stat-value { overflow-wrap: anywhere; }
    @media (max-width: 900px) { .ia-dashboard .stats-grid, .ia-dashboard .dashboard-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .ia-dashboard .ia-alert { align-items: flex-start; flex-direction: column; } }
</style>
@endpush
