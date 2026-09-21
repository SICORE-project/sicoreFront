@extends('layouts.app')

@section('title', 'SICORE - Espace DRH')
@section('content')
<main class="main-content">
    <x-topbar title="Tableau de bord DRH" subtitle="Gestion du personnel de votre périmètre" icon="fa-solid fa-users" />
    <section class="content-area">
        @if ($error)
            <p class="alert alert-warning" role="alert">{{ $error }}</p>
        @endif
        <div class="stats-grid">
            @foreach ([
                'agents_total' => 'Nombre total d’enseignants',
                'enseignants_fonctionnaires' => 'Enseignants fonctionnaires',
                'enseignants_non_fonctionnaires' => 'Enseignants non-fonctionnaires',
                'dossiers_actifs' => 'Dossiers actifs',
                'dossiers_incomplets' => 'Dossiers incomplets',
            ] as $key => $label)
                <article class="stat-card">
                    <div><p class="stat-label">{{ $label }}</p>
                        <p class="stat-value">{{ isset($metrics[$key]) && is_numeric($metrics[$key]) ? number_format($metrics[$key], 0, ',', ' ') : '—' }}</p>
                        <p class="stat-note">{{ isset($metrics[$key]) ? $scopeLabel : 'Indicateur indisponible' }}</p>
                    </div>
                    <span class="stat-icon green"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
                </article>
            @endforeach
        </div>
        <div class="dashboard-grid">
            <section class="table-card">
                <div class="panel-header"><h2>Agents par lieu de service</h2></div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th>Lieu de service</th><th>Nombre d’agents</th></tr></thead>
                    <tbody>
                        @forelse (($metrics['agents_par_lieu_service'] ?? []) as $lieu)
                            <tr><td>{{ data_get($lieu, 'libelle', '—') }}</td><td>{{ data_get($lieu, 'total', '—') }}</td></tr>
                        @empty
                            <tr><td colspan="2">Aucune répartition disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </section>
            <section class="table-card">
                <div class="panel-header"><h2>Derniers dossiers ajoutés ou modifiés</h2></div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th>Agent</th><th>Matricule</th><th>Dernière modification</th></tr></thead>
                    <tbody>
                        @forelse (($metrics['derniers_dossiers'] ?? []) as $dossier)
                            <tr><td>{{ data_get($dossier, 'prenom') }} {{ data_get($dossier, 'nom') }}</td><td>{{ data_get($dossier, 'matricule', '—') }}</td><td>{{ data_get($dossier, 'updated_at', '—') }}</td></tr>
                        @empty
                            <tr><td colspan="3">Aucun dossier récent disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </section>
        </div>
    </section>
</main>
@endsection
