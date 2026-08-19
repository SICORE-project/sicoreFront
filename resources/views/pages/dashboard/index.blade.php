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
        <p>Les indicateurs et listes sont limites a : <strong>{{ $scopeLabel }}</strong>.</p>
      </section>
      <div class="stats-grid four">
        @foreach ([
          ['label' => 'Utilisateurs', 'key' => 'utilisateurs', 'icon' => 'fa-solid fa-users', 'color' => 'green'],
          ['label' => 'Enseignants', 'key' => 'enseignants', 'icon' => 'fa-solid fa-chalkboard-user', 'color' => 'blue'],
          ['label' => 'Dossiers en cours', 'key' => 'dossiers_en_cours', 'icon' => 'fa-solid fa-folder-open', 'color' => 'yellow'],
          ['label' => 'Alertes', 'key' => 'alertes', 'icon' => 'fa-solid fa-triangle-exclamation', 'color' => 'red'],
        ] as $stat)
          <article class="stat-card"><div><p class="stat-label">{{ $stat['label'] }}</p><p class="stat-value">{{ data_get($metrics, $stat['key'], 0) }}</p><p class="stat-note">{{ $scopeLabel }}</p></div><span class="stat-icon {{ $stat['color'] }}"><i class="{{ $stat['icon'] }}" aria-hidden="true"></i></span></article>
        @endforeach
      </div>
      <div class="dashboard-grid">
        <section class="panel">
          <div class="panel-header">
            <div>
              <h2>Indicateurs principaux</h2>
              <p>Suivi visuel de l&#39;execution</p>
            </div>
          </div>
          <div class="chart-panel">
            <div class="metric-circle">
              <strong>24</strong>
              <small>Parametres</small>
            </div>
            <div class="donut-metric"><span>75%</span></div>
            <div class="canvas-wrap">
              <canvas data-chart="main-donut" aria-label="Repartition des donnees"></canvas>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-header">
            <div>
              <h2>Activite mensuelle</h2>
              <p>Operations traitees</p>
            </div>
          </div>
          <div class="canvas-card">
            <div class="canvas-wrap">
              <canvas data-chart="main-bars" aria-label="Graphique en barres"></canvas>
            </div>
          </div>
        </section>
      </div>
    </section>
  </main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/charts.js') }}" defer></script>
@endpush

