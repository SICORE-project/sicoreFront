@extends('layouts.app')
@section('title', 'SICORE - Paie de mon IA')
@section('content')
<main class="main-content">
    <x-topbar :title="$title" subtitle="Paie des enseignants de votre Inspection d’Académie" icon="fa-solid fa-money-check-dollar" />
    <section class="content-area">
        <section class="objective-card"><h2>{{ $scopeLabel }}</h2><p>Périmètre : {{ $scopeLabel }}</p></section>
        @if ($error)<p class="alert alert-warning" role="alert">{{ $error }}</p>@endif
        <div class="stats-grid three">
            @foreach (['bulletins_generes' => 'Bulletins générés', 'bulletins_payes' => 'Bulletins payés', 'sommes_percues' => 'Sommes perçues'] as $key => $label)
                @if (array_key_exists($key, $data['indicateurs'] ?? []))
                    <article class="stat-card"><div><p class="stat-label">{{ $label }}</p><p class="stat-value">{{ number_format($data['indicateurs'][$key], 0, ',', ' ') }}{{ $key === 'sommes_percues' ? ' FCFA' : '' }}</p></div></article>
                @endif
            @endforeach
        </div>
        <section class="panel">
            <div class="panel-header"><h2>{{ $title }}</h2></div>
            <form method="get" class="ia-filters">
                <label>Période<select name="period_id">
                    @forelse (($data['periodes'] ?? []) as $period)<option value="{{ $period['id'] }}" @selected(($data['periode']['id'] ?? '') == $period['id'])>{{ $period['code'] }}</option>
                    @empty<option value="">Aucune période disponible</option>@endforelse
                </select></label><button class="btn btn-primary" type="submit">Afficher</button>
                @if (app(\App\Services\Organisation\InterfaceAccess::class)->allows('paie.bulletins.export') && ! $error && isset($data['periode']['id']))
                    <a href="{{ route('ia.payroll.export', ['period_id' => $data['periode']['id']]) }}">Exporter les bulletins de la période (CSV)</a>
                @endif
            </form>
            @if (isset($data['rapport']))
                <div class="table-responsive"><table class="table">
                    <thead><tr>@foreach ($data['rapport']['columns'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
                    <tbody>@forelse ($data['rapport']['rows'] as $row)
                        <tr>@foreach ($row as $index => $cell)<td>{{ str_contains($data['rapport']['columns'][$index], '(FCFA)') && is_numeric($cell) ? number_format($cell, 0, ',', ' ') : $cell }}</td>@endforeach</tr>
                    @empty<tr><td colspan="{{ count($data['rapport']['columns']) }}">Aucune donnée disponible pour votre IA.</td></tr>@endforelse</tbody>
                </table></div>
            @else
            <div class="table-responsive"><table class="table"><thead><tr><th>Matricule</th><th>Enseignant</th><th>Brut (FCFA)</th><th>Net (FCFA)</th><th>Paiement</th><th>Bulletin</th></tr></thead><tbody>
                @forelse (($data['bulletins']['data'] ?? []) as $slip)
                    <tr><td>{{ $slip['matricule'] }}</td><td>{{ $slip['prenom'] }} {{ $slip['nom'] }}</td><td>{{ number_format($slip['gross_amount'], 0, ',', ' ') }}</td><td>{{ number_format($slip['net_amount'], 0, ',', ' ') }}</td><td>{{ ['paid' => 'Payé', 'pending' => 'En attente', 'rejected' => 'Rejeté'][$slip['payment_status']] ?? $slip['payment_status'] }}</td><td><a href="{{ route('ia.payroll.show', $slip['id']) }}">Consulter</a></td></tr>
                @empty<tr><td colspan="6">{{ $error ? 'Bulletins indisponibles.' : 'Aucun bulletin pour cette période dans votre IA.' }}</td></tr>@endforelse
            </tbody></table></div>
            @include('pages.ia.pagination', ['pagination' => $data['bulletins'] ?? []])
            @endif
        </section>
    </section>
</main>
@endsection
@include('pages.ia.styles')
