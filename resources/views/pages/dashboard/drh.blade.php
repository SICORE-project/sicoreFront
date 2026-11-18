@extends('layouts.app')

@section('title', 'SICORE - Espace DRH')
@section('content')
@php
    $nombre = fn ($valeur) => is_numeric($valeur) ? number_format($valeur, 0, ',', ' ') : '—';
@endphp
<main class="main-content drh-dashboard">
    <x-topbar title="Tableau de bord DRH" subtitle="Gestion du personnel de votre périmètre" icon="fa-solid fa-gauge-high" />
    <section class="content-area">
        @if ($error)
            <div class="drh-alert" role="alert"><p>{{ $error }}</p><a href="{{ route('dashboard') }}">Réessayer</a></div>
        @endif

        <h2 class="drh-section-title">Prises de service</h2>
        <div class="stats-grid four">
            @foreach ([
                ['total_agents', 'Nombre total d’enseignants', 'Dans votre périmètre', 'fa-users', 'green', true],
                ['prise_service_enregistree', 'Enseignants ayant pris service', 'Date enregistrée, hors abandons', 'fa-user-check', 'blue', true],
                ['attente_prise_service', 'En attente de prise de service', 'Date non encore enregistrée', 'fa-hourglass-half', 'yellow', false],
                ['enseignants_abandon', 'Enseignants en abandon', 'Dossiers déclarés en abandon', 'fa-user-xmark', 'red', true],
            ] as [$key, $label, $note, $icon, $color, $lien])
                @php
                    $disponible = isset($metrics[$key]) && is_numeric($metrics[$key]);
                    $balise = $lien ? 'a' : 'article';
                @endphp
                <{{ $balise }} class="stat-card {{ $lien ? 'drh-stat-link' : '' }}" @if ($lien) href="{{ route('recruitment.index', ['situation' => $key]) }}#teacher-results" aria-label="{{ 'Consulter : '.$label }}" @endif>
                    <div><p class="stat-label">{{ $label }}</p>
                        <p class="stat-value">{{ $nombre($metrics[$key] ?? null) }}</p>
                        <p class="stat-note">{{ $disponible ? $note : 'Indicateur indisponible' }}</p>
                    </div>
                    <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                </{{ $balise }}>
            @endforeach
        </div>

        <h2 class="drh-section-title">Dossiers du personnel</h2>
        <div class="stats-grid four">
            @foreach ([
                ['enseignants_fonctionnaires', 'Fonctionnaires', 'Corps de fonctionnaires', 'fa-user-tie', 'green'],
                ['enseignants_non_fonctionnaires', 'Contractuels et vacataires', 'Hors fonctionnaires', 'fa-chalkboard-user', 'purple'],
                ['dossiers_actifs', 'Dossiers actifs', 'Enseignants en activité', 'fa-folder-open', 'blue'],
                ['dossiers_incomplets', 'Dossiers incomplets', 'Informations obligatoires manquantes', 'fa-triangle-exclamation', 'yellow'],
            ] as [$key, $label, $note, $icon, $color])
                <article class="stat-card">
                    <div><p class="stat-label">{{ $label }}</p>
                        <p class="stat-value">{{ $nombre($metrics[$key] ?? null) }}</p>
                        <p class="stat-note">{{ isset($metrics[$key]) && is_numeric($metrics[$key]) ? $note : 'Indicateur indisponible' }}</p>
                    </div>
                    <span class="stat-icon {{ $color }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                </article>
            @endforeach
        </div>

        <div class="dashboard-grid">
            <section class="panel">
                <div class="panel-header"><h2>Agents par lieu de service</h2></div>
                @php
                    $repartition = collect($metrics['agents_par_lieu_service'] ?? [])->sortByDesc('total')->values();
                    $maximum = max(1, (int) $repartition->max('total'));
                @endphp
                <div class="drh-distribution">
                    @if ($repartition->isNotEmpty())
                        <p class="drh-chart-caption">Effectif des enseignants</p>
                        <ul class="drh-bar-chart" aria-label="Agents par lieu de service">
                            @foreach ($repartition as $lieu)
                                @php
                                    $effectif = max(0, (int) data_get($lieu, 'total'));
                                @endphp
                                <li>
                                    <div class="drh-bar-label"><span>{{ data_get($lieu, 'libelle') ?: 'Non renseigné' }}</span><strong>{{ $nombre($effectif) }}</strong></div>
                                    <div class="drh-bar-track" aria-hidden="true"><span class="drh-bar-fill" style="width: {{ round($effectif / $maximum * 100, 2) }}%"></span></div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="drh-empty">
                            <i class="fa-solid fa-chart-bar" aria-hidden="true"></i>
                            <p>Aucune répartition disponible.</p>
                            <small>Le diagramme apparaîtra dès que des enseignants seront rattachés.</small>
                        </div>
                    @endif
                </div>
            </section>
            <section class="panel">
                <div class="panel-header"><h2>Derniers dossiers ajoutés ou modifiés</h2></div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th scope="col">Agent</th><th scope="col">Matricule</th><th scope="col">Dernière modification</th></tr></thead>
                    <tbody>
                        @forelse (($metrics['derniers_dossiers'] ?? []) as $dossier)
                            <tr>
                                <td>{{ trim(data_get($dossier, 'prenom').' '.data_get($dossier, 'nom')) ?: '—' }}</td>
                                <td>{{ data_get($dossier, 'matricule') ?: '—' }}</td>
                                <td>{{ data_get($dossier, 'updated_at') ? \Illuminate\Support\Carbon::parse($dossier['updated_at'])->format('d/m/Y à H:i') : '—' }}</td>
                            </tr>
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

@push('styles')
<style>
    .drh-dashboard .drh-section-title { margin: 8px 0 -4px; font-size: 18px; color: #1e293b; }
    .drh-dashboard .drh-stat-link { color: inherit; text-decoration: none; transition: box-shadow .15s, border-color .15s; }
    .drh-dashboard .drh-stat-link:hover { border-color: #176637; box-shadow: 0 6px 20px #17663720; }
    .drh-dashboard .drh-stat-link:focus-visible { outline: 3px solid #176637; outline-offset: 3px; }
    .drh-dashboard .drh-alert { display: flex; align-items: center; justify-content: space-between; gap: 16px; border: 1px solid #ead397; border-radius: 12px; background: #fff8e7; padding: 16px 22px; color: #705114; }
    .drh-dashboard .drh-alert p { margin: 0; }
    .drh-dashboard .drh-alert a { color: inherit; font-weight: 700; }
    .drh-dashboard .drh-distribution { max-height: 420px; overflow-y: auto; padding: 24px; }
    .drh-dashboard .drh-chart-caption { margin: 0 0 20px; color: #64748b; font-size: 13px; }
    .drh-dashboard .drh-bar-chart { list-style: none; margin: 0; padding: 0; display: grid; gap: 22px; }
    .drh-dashboard .drh-bar-label { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 8px; font-size: 14px; }
    .drh-dashboard .drh-bar-label span { min-width: 0; overflow-wrap: anywhere; }
    .drh-dashboard .drh-bar-label strong { color: #176637; flex-shrink: 0; font-variant-numeric: tabular-nums; }
    .drh-dashboard .drh-bar-track { height: 16px; border-radius: 6px; background: #eef4f1; overflow: hidden; }
    .drh-dashboard .drh-bar-fill { display: block; height: 100%; background: #176637; border-radius: inherit; }
    .drh-dashboard .drh-empty { min-height: 170px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #64748b; gap: 12px; }
    .drh-dashboard .drh-empty i { font-size: 32px; color: #176637; }
    .drh-dashboard .drh-empty p { margin: 0; color: #334155; }
    .drh-dashboard .dashboard-grid > *, .drh-dashboard .stat-card > div { min-width: 0; }
    .drh-dashboard .stat-value { overflow-wrap: anywhere; }
    @media (max-width: 900px) { .drh-dashboard .stats-grid, .drh-dashboard .dashboard-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .drh-dashboard .drh-alert { align-items: flex-start; flex-direction: column; } }
</style>
@endpush
