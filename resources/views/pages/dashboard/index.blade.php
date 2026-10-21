@extends('layouts.app')
@section('title', 'SICORE - Tableau de bord')
@section('content')
<main class="main-content">
  <x-topbar title="Tableau de bord" subtitle="Votre espace de travail" icon="fa-solid fa-gauge-high" />
  <section class="content-area role-dashboard">
    @if ($error)
      <section class="dashboard-empty" role="alert">
        <i class="fa-solid fa-cloud-arrow-down" aria-hidden="true"></i>
        <h1>Tableau de bord indisponible</h1><p>{{ $error }}</p>
        <a class="btn-secondary" href="{{ route('dashboard') }}">Réessayer</a>
      </section>
    @elseif ($dashboard)
      @if (($dashboard['layout'] ?? '') === 'teacher')
        @include('pages.dashboard.teacher')
      @elseif (($dashboard['layout'] ?? '') === 'super-admin')
        @include('pages.dashboard.super-admin')
      @else
      <header class="dashboard-welcome">
        <div>
          <span class="dashboard-kicker">{{ $dashboard['role'] }}</span>
          <h1>{{ $dashboard['title'] }}</h1>
          <p>Bonjour {{ $dashboard['name'] }}. Retrouvez les informations utiles à votre activité.</p>
        </div>
        <div class="dashboard-context"><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $dashboard['scope'] }}</span><small>Actualisé le {{ \Carbon\Carbon::parse($dashboard['updated_at'])->format('d/m/Y à H:i') }}</small></div>
      </header>

      @if ($dashboard['cards'])
        @foreach (collect($dashboard['cards'])->groupBy(fn ($card) => $card['group'] ?? 'Vos indicateurs') as $group => $cards)
        <section class="dashboard-metric-group" aria-label="{{ $group }}">
          <h2>{{ $group }}</h2>
          <div class="dashboard-metrics">
          @foreach ($cards as $card)
            <article class="dashboard-metric">
              <span class="dashboard-metric-icon"><i class="fa-solid {{ $card['icon'] }}" aria-hidden="true"></i></span>
              <p>{{ $card['label'] }}</p>
              <strong>{{ number_format((float) $card['value'], 0, ',', ' ') }} @if ($card['unit'])<small>{{ $card['unit'] }}</small>@endif</strong>
            </article>
          @endforeach
          </div>
        </section>
        @endforeach
      @endif

      <div class="dashboard-workspace">
        <section class="dashboard-panel">
          <div class="dashboard-panel-heading"><span class="dashboard-section-icon"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span><div><h2>Points de suivi</h2><p>Les informations à prendre en compte dans votre périmètre.</p></div></div>
          @forelse ($dashboard['notices'] as $notice)
            <p class="dashboard-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span>{{ $notice }}</span></p>
          @empty
            <p class="dashboard-muted">Consultez vos indicateurs et vos dernières informations ci-dessous.</p>
          @endforelse
        </section>
        @if ($dashboard['actions'])
          <section class="dashboard-panel">
            <div class="dashboard-panel-heading"><span class="dashboard-section-icon"><i class="fa-solid fa-arrow-pointer" aria-hidden="true"></i></span><div><h2>Accès rapides</h2><p>Vos outils de travail.</p></div></div>
            <div class="dashboard-shortcuts">
              @foreach ($dashboard['actions'] as $action)
                <a href="{{ route($action['route']) }}"><i class="fa-solid {{ $action['icon'] }}" aria-hidden="true"></i><span>{{ $action['label'] }}</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
              @endforeach
            </div>
          </section>
        @endif
      </div>
      @foreach ($dashboard['sections'] as $section)
        <section class="dashboard-panel">
          <div class="dashboard-panel-heading"><h2>{{ $section['title'] }}</h2></div>
          @if ($section['rows'])
            <div class="dashboard-table-scroll"><table class="dashboard-table"><thead><tr>@foreach ($section['columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
              <tbody>@foreach ($section['rows'] as $row)<tr>@foreach ($row as $cell)<td>{{ $cell ?? 'Non renseigné' }}</td>@endforeach</tr>@endforeach</tbody>
            </table></div>
          @else
            <p class="dashboard-muted">Aucune donnée disponible dans votre périmètre pour le moment.</p>
          @endif
        </section>
      @endforeach
      @endif
    @endif
  </section>
</main>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/role-dashboard.css') }}">
@endpush

@if (($dashboard['layout'] ?? '') === 'teacher')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-dashboard.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/js/teacher-dashboard.js') }}" defer></script>
@endpush
@endif

@if (($dashboard['layout'] ?? '') === 'super-admin')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/super-admin-dashboard.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/js/super-admin-dashboard.js') }}" defer></script>
@endpush
@endif
