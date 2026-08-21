@extends('layouts.app')

@section('title', 'SICORE - Fiche IA')

@section('content')
<main class="main-content">
  <x-topbar title="Fiche de l’inspection d’académie" subtitle="Consulter les informations et les structures rattachées" icon="fa-solid fa-building-columns" />

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb"><a href="{{ route('parametres.ia.index') }}">Inspections d’académie</a> &gt; {{ data_get($academy, 'code', 'IA') }}</p>
      <a class="btn-secondary" href="{{ route('parametres.ia.index') }}">Retour à la liste</a>
    </div>

    <section class="form-card" aria-labelledby="academyTitle">
      <div class="form-card-header">
        <div>
          <h2 id="academyTitle">{{ data_get($academy, 'libelle', data_get($academy, 'nom', 'Inspection d’académie')) }}</h2>
          <p class="table-card-subtitle">Code : {{ data_get($academy, 'code', '—') }}</p>
        </div>
        <span class="badge badge-primary">IA</span>
      </div>
    </section>

    <section class="table-card" aria-labelledby="attachedIefsTitle">
      <div class="table-card-header">
        <div>
          <h2 id="attachedIefsTitle">IEF rattachées</h2>
          <p class="table-card-subtitle">{{ count($iefs) }} IEF rattachée{{ count($iefs) > 1 ? 's' : '' }} à cette inspection d’académie</p>
        </div>
      </div>

      @if ($iefsError)
        <div class="alert alert-error" role="alert">{{ $iefsError }}</div>
      @endif

      @if (!empty($iefs))
        <div class="table-responsive">
          <table class="table" id="attachedIefsTable">
            <thead><tr><th>Code</th><th>Libellé</th></tr></thead>
            <tbody>
              @foreach ($iefs as $ief)
                <tr>
                  <td>{{ data_get($ief, 'code', '—') }}</td>
                  <td>{{ data_get($ief, 'libelle', data_get($ief, 'nom', '—')) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @elseif (!$iefsError)
        <p class="empty-message show" role="status">Aucune IEF n’est rattachée à cette inspection d’académie.</p>
      @endif
    </section>
  </section>
</main>
@endsection
