@extends('layouts.app')

@section('title', 'SICORE - Fiche de déplacement')

@section('content')

<main class="main-content">

<x-topbar
    title="Fiche de déplacement"
    subtitle="Indemnites > Frais de déplacement > Détail"
    icon="fa-solid fa-route"
/>

<section class="content-area">

    <section class="convocation-page">

        <div class="convocation-page-header">
            <div>
                <h2>{{ $fiche['reference'] ?? '—' }}</h2>
                <p class="breadcrumb">
                    {{ trim(($fiche['beneficiaire']['prenom'] ?? '') . ' ' . ($fiche['beneficiaire']['nom'] ?? '')) ?: '—' }}
                    @if (! empty($fiche['convocation']['objet']))
                        &middot; {{ $fiche['convocation']['objet'] }}
                    @endif
                </p>
            </div>

            <x-module-indemnite type="statut-frais-deplacement" :statut="$fiche['statut'] ?? null" />
        </div>

        <div class="convocation-page-body">

            <div class="form-section">

                <h3>Informations générales</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Type de bénéficiaire</label>
                        <p>{{ ucfirst($fiche['statut_agent'] ?? '—') }}</p>
                    </div>

                    <div class="form-group">
                        <label>Indice</label>
                        <p>{{ $fiche['indice_agent'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Montant calculé</label>
                        <p>{{ isset($fiche['montant_calcule']) ? number_format($fiche['montant_calcule'], 0, ',', ' ') . ' FCFA' : '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Montant approuvé</label>
                        <p>{{ isset($fiche['montant_approuve']) ? number_format($fiche['montant_approuve'], 0, ',', ' ') . ' FCFA' : '—' }}</p>
                    </div>

                </div>

            </div>

            <div class="form-section">

                <h3>Déplacement</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Lieu de départ</label>
                        <p>{{ $fiche['lieu_depart'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Lieu de destination</label>
                        <p>{{ $fiche['lieu_destination'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Date de départ</label>
                        <p>{{ ! empty($fiche['date_depart']) ? \Illuminate\Support\Carbon::parse($fiche['date_depart'])->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Date de retour</label>
                        <p>{{ ! empty($fiche['date_retour']) ? \Illuminate\Support\Carbon::parse($fiche['date_retour'])->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Nature du déplacement</label>
                        <p>{{ $fiche['motif'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Moyen de transport</label>
                        <p>{{ $fiche['moyen_transport'] ?? '—' }}</p>
                    </div>

                </div>

            </div>

            <div class="form-section">

                <h3>Pièces jointes</h3>

                @forelse ($fiche['justificatifs'] ?? [] as $justificatif)
                    <p>
                        <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                        {{ $justificatif['nom_original'] ?? 'Fichier' }}
                    </p>
                @empty
                    <p class="empty-message">Aucune feuille de déplacement scannée n'a encore été déposée.</p>
                @endforelse

            </div>

            <div class="form-actions">

                <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement', ['objet' => $fiche['convocation']['objet'] ?? null]) }}">
                    Retour
                </a>

            </div>

        </div>

    </section>

</section>

</main>

@endsection
