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
                'total_agents' => 'Nombre total d’enseignants',
                'prise_service_enregistree' => 'Enseignants ayant pris service',
                'enseignants_abandon' => 'Enseignants en abandon',
            ] as $key => $label)
                <a class="stat-card" style="text-decoration: none; color: inherit;" href="{{ route('recruitment.index', ['situation' => $key]) }}#teacher-results">
                    <div><p class="stat-label">{{ $label }}</p>
                        <p class="stat-value">{{ isset($metrics[$key]) && is_numeric($metrics[$key]) ? number_format($metrics[$key], 0, ',', ' ') : '—' }}</p>
                        @if (!isset($metrics[$key]))
                            <p class="stat-note">Indicateur indisponible</p>
                        @endif
                    </div>
                    <span class="stat-icon green"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
                </a>
            @endforeach
        </div>
        <p>Prise de service : date enregistrée, hors abandons. Abandon : uniquement les dossiers déclarés en abandon.</p>
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
