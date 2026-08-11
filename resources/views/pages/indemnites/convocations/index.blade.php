
@extends('layouts.app')

@section('title', 'SICORE - Gestion des convocations')
@section('content')
<main class="main-content">
  <x-topbar
    title="Convocations"
    subtitle="Indemnites > Convocations"
    icon="fa-solid fa-envelope-open-text"
    search-id="convocationSearch"
    search-placeholder="Rechercher un objet, un centre…"
    filter-target="#convocationsTable"
  />

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb">Indemnites &gt; Convocations</p>
      <div class="actions-group">
        <a class="btn-primary" href="{{ route('indemnites.convocations.create') }}">
          <i class="fa-solid fa-plus" aria-hidden="true"></i>
          Nouvelle convocation
        </a>
      </div>
    </div>

    <div class="stats-grid">
      <article class="stat-card">
        <div><p class="stat-label">Total</p><p class="stat-value">{{ $stats['total'] }}</p><p class="stat-note">Sur la page courante</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Brouillons</p><p class="stat-value">{{ $stats['brouillon'] }}</p><p class="stat-note neutral">A finaliser</p></div>
        <span class="stat-icon yellow"><i class="fa-solid fa-pen" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Emises</p><p class="stat-value">{{ $stats['emise'] }}</p><p class="stat-note neutral">Pretes a l'envoi</p></div>
        <span class="stat-icon purple"><i class="fa-solid fa-file-circle-check" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Envoyees</p><p class="stat-value">{{ $stats['envoyee'] }}</p><p class="stat-note neutral">Notifiees aux beneficiaires</p></div>
        <span class="stat-icon green"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></span>
      </article>
      <article class="stat-card">
        <div><p class="stat-label">Cloturees</p><p class="stat-value">{{ $stats['cloturee'] }}</p><p class="stat-note neutral">Dossier termine</p></div>
        <span class="stat-icon blue"><i class="fa-solid fa-box-archive" aria-hidden="true"></i></span>
      </article>
    </div>

    <section class="filter-panel" aria-label="Filtres">
      <form method="GET" action="{{ route('indemnites.convocations') }}" class="form-grid" style="flex: 1; display: flex; gap: 12px; align-items: flex-end;">
        <div class="form-group">
          <label for="statut">Statut</label>
          <select class="form-control" id="statut" name="statut">
            <option value="" @selected($statutFiltre === '')>Tous les statuts</option>
            <option value="brouillon" @selected($statutFiltre === 'brouillon')>Brouillon</option>
            <option value="emise" @selected($statutFiltre === 'emise')>Emise</option>
            <option value="envoyee" @selected($statutFiltre === 'envoyee')>Envoyee</option>
            <option value="cloturee" @selected($statutFiltre === 'cloturee')>Cloturee</option>
          </select>
        </div>
        <div class="actions-group">
          <button class="btn-secondary" type="submit">
            <i class="fa-solid fa-filter" aria-hidden="true"></i>
            Filtrer
          </button>
          @if ($statutFiltre !== '')
            <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">Reinitialiser</a>
          @endif
        </div>
      </form>
    </section>

    <section class="table-card">
      <div class="panel-header">
        <div>
          <h2>Liste des convocations</h2>
          <p>Suivi des convocations emises pour les jurys et centres d'examen</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table" id="convocationsTable">
          <thead>
            <tr>
              <th>Objet</th>
              <th>Date d'emission</th>
              <th>Centre d'examen</th>
              <th>Lieu d'affectation</th>
              <th>Statut</th>
              <th class="actions-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($convocations as $convocation)
              <tr>
                <td>{{ $convocation['objet'] ?? '—' }}</td>
                <td>{{ isset($convocation['date_emission']) ? \Illuminate\Support\Carbon::parse($convocation['date_emission'])->format('d/m/Y') : '—' }}</td>
                <td>{{ $convocation['lieu_examen'] ?? '—' }}</td>
                <td>{{ $convocation['lieu_affectation'] ?? '—' }}</td>
                <td><x-convocation-statut-badge :statut="$convocation['statut'] ?? null" /></td>
                <td class="actions-cell">
                  <a class="icon-action" title="Voir" href="{{ route('indemnites.convocations.show', $convocation['id']) }}">&#128065;</a>
                  <a class="icon-action" title="Modifier" href="{{ route('indemnites.convocations.edit', $convocation['id']) }}">&#9998;</a>
                  <form
                    method="POST"
                    action="{{ route('indemnites.convocations.destroy', $convocation['id']) }}"
                    style="display: inline;"
                    onsubmit="return confirm('Voulez-vous vraiment supprimer cette convocation ?');"
                  >
                    @csrf
                    @method('DELETE')
                    <button class="icon-action" type="submit" title="Supprimer">&#128465;</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if (empty($convocations))
        <p class="empty-message">Aucune convocation trouvee.</p>
      @endif

      <div class="pagination" aria-label="Pagination">
        @php($pageCourante = (int) request()->query('page', 1))
        <a class="page-btn" href="{{ route('indemnites.convocations', array_filter(['statut' => $statutFiltre ?: null, 'page' => max(1, $pageCourante - 1)])) }}" aria-label="Page precedente">&#8592;</a>
        <span class="page-btn active">{{ $pageCourante }}</span>
        <a class="page-btn" href="{{ route('indemnites.convocations', array_filter(['statut' => $statutFiltre ?: null, 'page' => $pageCourante + 1])) }}" aria-label="Page suivante">&#8594;</a>
      </div>
    </section>
  </section>
</main>
@endsection
