@extends('layouts.app')

@section('title', 'SICORE - Tableau de bord')
@section('content')
<main class="main-content">
    <x-topbar
      title="Tableau de bord"
      subtitle="Vue d’ensemble des paramètres et de la paie"
      icon="fa-solid fa-gauge-high"
      search-id="dashboardSearch"
      search-placeholder="Rechercher…"
    />

    <section class="content-area">
      <section class="objective-card {{ $isScoped ? 'sensitive-panel' : '' }}">
        <h2>{{ $isScoped ? 'Tableau de bord de votre structure' : 'Tableau de bord global' }}</h2>
        <p>Les indicateurs et les listes sont limités au <strong>{{ $scopeLabel }}</strong>.</p>
      </section>
      <div class="stats-grid four">
        @php
          $stats = $isGlobalAdmin ? [
            ['label' => 'Utilisateurs', 'key' => 'utilisateurs', 'icon' => 'fa-solid fa-users', 'color' => 'green'],
            ['label' => 'Rôles', 'key' => 'roles', 'icon' => 'fa-solid fa-user-shield', 'color' => 'blue'],
            ['label' => 'Permissions', 'key' => 'permissions', 'icon' => 'fa-solid fa-key', 'color' => 'yellow'],
            ['label' => 'Comptes actifs', 'key' => 'utilisateurs_actifs', 'icon' => 'fa-solid fa-user-check', 'color' => 'green'],
          ] : [
            ['label' => 'Utilisateurs', 'key' => 'utilisateurs', 'icon' => 'fa-solid fa-users', 'color' => 'green'],
            ['label' => 'Enseignants', 'key' => 'enseignants', 'icon' => 'fa-solid fa-chalkboard-user', 'color' => 'blue'],
            ['label' => 'Dossiers en cours', 'key' => 'dossiers_en_cours', 'icon' => 'fa-solid fa-folder-open', 'color' => 'yellow'],
            ['label' => 'Alertes', 'key' => 'alertes', 'icon' => 'fa-solid fa-triangle-exclamation', 'color' => 'red'],
          ];
          $activeRate = data_get($metrics, 'utilisateurs', 0) > 0
            ? (int) round(data_get($metrics, 'utilisateurs_actifs', 0) * 100 / data_get($metrics, 'utilisateurs'))
            : 0;
        @endphp
        @foreach ($stats as $stat)
          <article class="stat-card"><div><p class="stat-label">{{ $stat['label'] }}</p><p class="stat-value">{{ data_get($metrics, $stat['key'], 0) }}</p><p class="stat-note">{{ $scopeLabel }}</p></div><span class="stat-icon {{ $stat['color'] }}"><i class="{{ $stat['icon'] }}" aria-hidden="true"></i></span></article>
        @endforeach
      </div>
      <div class="dashboard-grid">
        <section class="panel">
          <div class="panel-header">
            <div>
              <h2>Indicateurs principaux</h2>
              <p>Vue synthétique des comptes</p>
            </div>
          </div>
          <div class="chart-panel">
            <div class="metric-circle">
              <strong>{{ data_get($metrics, 'utilisateurs_actifs', 0) }}</strong>
              <small>Comptes actifs</small>
            </div>
            <div class="donut-metric"><span>{{ $activeRate }}%</span></div>
            <div class="canvas-wrap">
              <canvas data-chart="main-donut" data-percentage="{{ $activeRate }}" aria-label="Taux de comptes actifs"></canvas>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-header">
            <div>
              <h2>Répartition administrative</h2>
              <p>Volumes actuels</p>
            </div>
          </div>
          <div class="canvas-card">
            <div class="canvas-wrap">
              <canvas data-chart="main-bars"
                data-labels='@json(collect($stats)->pluck('label')->values())'
                data-values='@json(collect($stats)->map(fn ($stat) => (int) data_get($metrics, $stat['key'], 0))->values())'
                aria-label="Répartition des données administratives"></canvas>
            </div>
          </div>
        </section>
      </div>
    </section>
  </main>
@endsection

@push('scripts')
  {{-- Script spécifique au dashboard, ajouté après les scripts communs. --}}
  <script src="{{ asset('assets/js/charts.js') }}" defer></script>
@endpush
