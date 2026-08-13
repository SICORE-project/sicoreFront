@extends('layouts.app')

@section('title', 'SICORE - Convocation')

@section('content')

<main class="main-content">

<x-topbar
    title="Détail de la convocation"
    subtitle="Indemnites > Convocations > Détail"
    icon="fa-solid fa-envelope-open-text"
/>

<section class="content-area">

    @php
        $statutBadges = [
            'brouillon'   => ['badge-pending', 'Brouillon'],
            'emise'       => ['badge-primary', 'Émise'],
            'envoyee'     => ['badge-active', 'Envoyée'],
            'cloturee'    => ['badge-inactive', 'Clôturée'],
        ];
        [$badgeClass, $badgeLabel] = $statutBadges[$convocation->statut ?? null]
            ?? ['badge-pending', ucfirst($convocation->statut ?? '—')];
    @endphp

    <section class="form-card convocation-card">

        <div class="form-card-header">

            <div>
                <h2>{{ $convocation->objet ?? '—' }}</h2>
                <p class="breadcrumb">
                    Émise le {{ optional($convocation->date_emission)->format('d/m/Y') ?? '—' }}
                    @if (! empty($convocation->typeConvocation['libelle'] ?? null))
                        &middot; {{ $convocation->typeConvocation['libelle'] }}
                    @endif
                </p>
            </div>

            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>

        </div>

        <div class="convocation-form">

            {{-- ============================================================
                 INFORMATIONS GENERALES
            ============================================================ --}}

            <div class="form-section">

                <h3>Informations générales</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Type de convocation</label>
                        <p>{{ $convocation->typeConvocation['libelle'] ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Session</label>
                        <p>{{ $convocation->session ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Période</label>
                        <p>
                            @if (! empty($convocation->date_debut) && ! empty($convocation->date_fin))
                                Du {{ optional($convocation->date_debut)->format('d/m/Y') }}
                                au {{ optional($convocation->date_fin)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Heure</label>
                        <p>{{ $convocation->heure_debut ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Lieu d'examen</label>
                        <p>{{ $convocation->lieu_examen ?? '—' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Lieu d'affectation</label>
                        <p>{{ $convocation->lieu_affectation ?? '—' }}</p>
                    </div>

                </div>

            </div>

            {{-- ============================================================
                 CENTRES D'EXAMEN
            ============================================================ --}}

            <div class="form-section">

                <div class="panel-header">
                    <h3>Centres d'examen</h3>
                </div>

                @if (($convocation->centres ?? collect())->isEmpty())

                    <p class="empty-message">Aucun centre renseigné pour cette convocation.</p>

                @else

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Centre</th>
                                    <th>Jury</th>
                                    <th>Métier</th>
                                    <th>Chef de centre</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($convocation->centres as $centre)
                                    <tr>
                                        <td>{{ $centre['centre'] ?? '—' }}</td>
                                        <td>{{ $centre['jury'] ?? '—' }}</td>
                                        <td>{{ $centre['metier'] ?? '—' }}</td>
                                        <td>{{ $centre['chefCentre']['nom'] ?? '—' }}</td>
                                        <td>{{ $centre['chef_centre_telephone'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @endif

            </div>

            {{-- ============================================================
                 BENEFICIAIRES / MEMBRES DU JURY
            ============================================================ --}}

            <div class="form-section">

                <div class="panel-header">
                    <h3>Membres du jury</h3>
                </div>

                @if (empty($beneficiaires))

                    <p class="empty-message">Aucun membre ajouté pour le moment.</p>

                @else

                    @php
                        $statutsPersonnel = [
                            'fonctionnaire' => 'Fonctionnaire',
                            'contractuel' => 'Contractuelle',
                            'vacataire' => 'Vacataire',
                        ];
                    @endphp

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Fonction</th>
                                    <th>Statut</th>
                                    <th>Provenance</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($beneficiaires as $beneficiaire)
                                    <tr>
                                        <td>{{ trim(($beneficiaire['prenom'] ?? '') . ' ' . ($beneficiaire['nom'] ?? '')) ?: '—' }}</td>
                                        <td>{{ $beneficiaire['pivot']['fonction'] ?? '—' }}</td>
                                        <td>{{ $statutsPersonnel[$beneficiaire['categorie_personnel'] ?? null] ?? '—' }}</td>
                                        <td>{{ $beneficiaire['pivot']['provenance'] ?? '—' }}</td>
                                        <td>{{ $beneficiaire['telephone'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @endif

            </div>

            {{-- ============================================================
                 ACTIONS
            ============================================================ --}}

            <div class="form-actions">

                <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">
                    Retour à la liste
                </a>

                <a class="btn-primary" href="{{ route('indemnites.convocations.edit', $id) }}">
                    Modifier
                </a>

            </div>

        </div>

    </section>

</section>

</main>

@endsection

{{-- ================================================================
     STYLES
     Le .form-card global (app.css) est plafonné à 720px et sans
     padding — pensé pour un formulaire simple, pas pour cette page qui
     empile plusieurs sections et des tableaux (min-width 760px, cf.
     .table en CSS globale). Sans cet élargissement, les tableaux
     débordaient/étaient tronqués et le contenu touchait les bords de
     la carte (même souci que sur edit.blade.php, cf. .convocation-card
     dans create.blade.php qui s'en sort déjà correctement).
================================================================ --}}

@push('styles')
<style>

    .convocation-card {
        width: calc(100% - 40px);
        max-width: 980px;
        margin: 24px auto 40px;
    }

    .convocation-card .convocation-form {
        display: grid;
        gap: 22px;
        padding: 26px 30px 30px;
        box-sizing: border-box;
    }

    .convocation-card .form-section {
        padding: 22px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .convocation-card .form-section h3 {
        margin: 0 0 16px;
    }

    .convocation-card .panel-header {
        margin-bottom: 14px;
    }

    @media (max-width: 768px) {

        .convocation-card {
            width: calc(100% - 20px);
            margin-top: 15px;
        }

        .convocation-card .convocation-form {
            padding: 18px;
        }

        .convocation-card .form-section {
            padding: 16px;
        }

    }

</style>
@endpush
