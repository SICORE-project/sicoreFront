@extends('layouts.app')

{{--
  PAGE : Liste des enseignants — URL /enseignants dans routes/web.php.
  En-tête et recherche : components/topbar.blade.php + public/assets/js/app.js.
  Filtre IA/IEF : public/assets/js/education-structures.js.
  Les lignes sont actuellement écrites dans cette vue ; l'équipe Enseignants
  pourra les remplacer par des données de son contrôleur ou de son API.
--}}
@section('title', 'SICORE - Dashboard Enseignant')
@section('content')
<main class="main-content">
    <x-topbar
      title="Dashboard Enseignant"
      subtitle="Administration > Enseignants > Dashboard"
      icon="fa-solid fa-chalkboard-user"
      search-id="teacherSearch"
      search-placeholder="Rechercher un enseignant…"
      filter-target="#teacherTable"
    />

    <section class="content-area">
      <div class="actions-row">
        <p class="breadcrumb">Administration &gt; Enseignants</p>
        <div class="actions-group">
          <a class="btn-primary" href="{{ route('enseignants.create') }}">
            <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
            Nouvel enseignant
          </a>
        </div>
      </div>

      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Total Enseignants</p><p class="stat-value">1 247</p><p class="stat-note">+12 ce mois</p></div>
          <span class="stat-icon blue">EN</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Actifs</p><p class="stat-value">1 198</p><p class="stat-note neutral">96% du total</p></div>
          <span class="stat-icon green">OK</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">En attente</p><p class="stat-value">49</p><p class="stat-note neutral">4% du total</p></div>
          <span class="stat-icon yellow">AT</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">IA</p><p class="stat-value">24</p><p class="stat-note neutral">52 par IA</p></div>
          <span class="stat-icon purple">IA</span>
        </article>
      </div>

      {{-- Filtres administratifs de la liste. --}}
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Evolution des enseignants</h2>
            <p>Janvier a decembre</p>
          </div>
          <div class="legend-row">
            <span class="legend-item"><span class="legend-dot blue"></span>Enseignants</span>
            <span class="legend-item"><span class="legend-dot green"></span>Nouveaux</span>
          </div>
        </div>
        <div class="canvas-card">
          <div class="canvas-wrap tall">
            <canvas data-chart="teacher-bars" aria-label="Evolution des enseignants"></canvas>
          </div>
        </div>
      </section>

      {{-- Tableau des enseignants ciblé par la recherche et les filtres JS. --}}
      <section class="table-card">
        <div class="panel-header">
          <div>
            <h2>Derniers enseignants ajoutes</h2>
            <p>Suivi des creations recentes</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table" id="teacherTable">
            <thead>
              <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>IA</th>
                <th>Grade</th>
                <th>Statut</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>ENS001</td>
                <td>DIOP</td>
                <td>Mamadou</td>
                <td>Dakar</td>
                <td>Professeur</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><a class="icon-action" href="{{ route('enseignants.show', 1) }}" title="Voir">&#128065;</a><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>ENS002</td>
                <td>FALL</td>
                <td>Aissatou</td>
                <td>Thies</td>
                <td>Maitre</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>ENS003</td>
                <td>SOW</td>
                <td>Ibrahima</td>
                <td>Saint-Louis</td>
                <td>Professeur</td>
                <td><span class="badge badge-suspended">Suspendu</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>ENS004</td>
                <td>NDIAYE</td>
                <td>Cheikh</td>
                <td>Kaolack</td>
                <td>Maitre</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
              <tr>
                <td>ENS005</td>
                <td>GUEYE</td>
                <td>Fatou</td>
                <td>Ziguinchor</td>
                <td>Conseiller</td>
                <td><span class="badge badge-active">Actif</span></td>
                <td class="actions-cell"><button class="icon-action" title="Voir">&#128065;</button><button class="icon-action" title="Modifier">&#9998;</button><button class="icon-action" title="Supprimer">&#128465;</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="empty-message">Aucun enseignant trouve.</p>
      </section>
    </section>
  </main>
@endsection

@push('scripts')
  {{-- Gestion de la dépendance IA → IEF sur cette page. --}}
<script src="{{ asset('assets/js/charts.js') }}" defer></script>
@endpush
