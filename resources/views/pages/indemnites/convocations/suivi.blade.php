@extends('layouts.app')

@section('title', 'SICORE - Suivi des envois')

@section('content')

<main class="main-content">

<x-topbar
    title="Suivi des envois"
    subtitle="Indemnites > Convocations > Suivi"
    icon="fa-solid fa-paper-plane"
/>

<section class="content-area">

    <div class="stats-grid three">

        <article class="stat-card">
            <div>
                <p class="stat-label">Envois</p>
                <p class="stat-value">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <span class="stat-icon green">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </span>
        </article>

        <article class="stat-card">
            <div>
                <p class="stat-label">Envoyés</p>
                <p class="stat-value">{{ $stats['envoye'] ?? 0 }}</p>
            </div>
            <span class="stat-icon blue">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </span>
        </article>

        <article class="stat-card">
            <div>
                <p class="stat-label">Échecs</p>
                <p class="stat-value">{{ $stats['echec'] ?? 0 }}</p>
            </div>
            <span class="stat-icon red">
                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
            </span>
        </article>

    </div>

    <div class="actions-row">
        <p class="breadcrumb">Gestion des indemnités &gt; Convocations &gt; Suivi</p>
        <div class="actions-group">
            <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
                Retour à la convocation
            </a>
            <form method="POST" action="{{ route('indemnites.convocations.relancer', $id) }}">
                @csrf
                <button class="btn-primary" type="submit">
                    Relancer les envois en échec
                </button>
            </form>
        </div>
    </div>

    <section class="table-card">

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Destinataire</th>
                        <th>Canal</th>
                        <th>Statut</th>
                        <th>Date d'envoi</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($envois as $envoi)
                        <tr>
                            <td>
                                {{ trim(($envoi['enseignant']['prenom'] ?? '') . ' ' . ($envoi['enseignant']['nom'] ?? '')) ?: '—' }}
                            </td>
                            <td>{{ ucfirst($envoi['canal'] ?? '—') }}</td>
                            <td><x-module-indemnite type="statut-envoi" :statut="$envoi['statut'] ?? null" /></td>
                            <td>{{ $envoi['date_envoi'] ?? '—' }}</td>
                            <td>{{ $envoi['message'] ?? '—' }}</td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (empty($envois))
            <p class="empty-message">Aucun envoi enregistré pour cette convocation.</p>
        @endif

    </section>

</section>

</main>

@endsection
