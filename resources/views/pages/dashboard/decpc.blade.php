@extends('layouts.app')
@section('title', 'SICORE - Espace DECPC')
@section('content')
<main class="main-content">
    <x-topbar title="Tableau de bord DECPC" subtitle="Suivi du personnel et des indemnités" icon="fa-solid fa-coins" />
    <section class="content-area">
        <p>{{ $scopeLabel }}</p>
        @if ($error)
            <p class="alert alert-warning" role="alert">{{ $error }}</p>
        @endif
        <div class="stats-grid">
            @foreach ([
                'total_agents' => ['Nombre total d’agents concernés', $canConsultPersonnel],
                'dossiers_en_attente' => ['Dossiers d’indemnité en attente', $canConsultIndemnites],
                'dossiers_valides' => ['Dossiers d’indemnité validés', $canConsultIndemnites],
                'dossiers_rejetes_retournes' => ['Dossiers rejetés ou retournés', $canConsultIndemnites],
                'montant_en_cours' => ['Montant total des indemnités en cours', $canConsultIndemnites],
            ] as $key => [$label, $allowed])
                @if ($allowed)
                    <section class="stat-card">
                        <div><p class="stat-label">{{ $label }}</p>
                            <p class="stat-value">{{ isset($metrics[$key]) && is_numeric($metrics[$key]) ? number_format($metrics[$key], 0, ',', ' ').($key === 'montant_en_cours' ? ' FCFA' : '') : '—' }}</p>
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
        @if ($canConsultIndemnites)
            <div class="dashboard-grid">
                <section class="table-card">
                    <div class="panel-header"><h2>Répartition des indemnités par type</h2></div>
                    <div class="table-responsive"><table class="table">
                        <thead><tr><th>Type</th><th>Dossiers</th><th>Montant (FCFA)</th></tr></thead>
                        <tbody>
                            @forelse (($metrics['indemnites_par_type'] ?? []) as $type)
                                <tr><td>{{ data_get($type, 'libelle', '—') }}</td><td>{{ data_get($type, 'total', '—') }}</td><td>{{ is_numeric(data_get($type, 'montant')) ? number_format($type['montant'], 0, ',', ' ') : '—' }}</td></tr>
                            @empty
                                <tr><td colspan="3">Aucune répartition disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table></div>
                </section>
                <section class="table-card">
                    <div class="panel-header"><h2>Derniers dossiers reçus ou traités</h2></div>
                    <div class="table-responsive"><table class="table">
                        <thead><tr><th>Référence</th><th>Type</th><th>Statut</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse (($metrics['derniers_dossiers'] ?? []) as $dossier)
                                <tr><td>{{ data_get($dossier, 'reference', '—') }}</td><td>{{ data_get($dossier, 'type', '—') }}</td><td>{{ data_get($dossier, 'statut', '—') }}</td><td>{{ data_get($dossier, 'updated_at', '—') }}</td></tr>
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
