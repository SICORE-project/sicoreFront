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
      <div class="stats-grid">
        <article class="stat-card">
          <div>
            <p class="stat-label">Parametres</p>
            <p class="stat-value">24</p>
            <p class="stat-note">+3 cette semaine</p>
          </div>
          <span class="stat-icon green">&#9881;</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Alertes actives</p>
            <p class="stat-value">5</p>
            <p class="stat-note">+2 nouvelles</p>
          </div>
          <span class="stat-icon red">!</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Cessions</p>
            <p class="stat-value">12</p>
            <p class="stat-note neutral">4 en cours</p>
          </div>
          <span class="stat-icon blue">C</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Groupes IPM</p>
            <p class="stat-value">8</p>
            <p class="stat-note">1 nouveau</p>
          </div>
          <span class="stat-icon purple">IP</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Categories</p>
            <p class="stat-value">15</p>
            <p class="stat-note neutral">2 modifiees</p>
          </div>
          <span class="stat-icon yellow">CA</span>
        </article>
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
  {{-- Script spécifique au dashboard, ajouté après les scripts communs. --}}
  <script src="{{ asset('assets/js/charts.js') }}" defer></script>
@endpush
