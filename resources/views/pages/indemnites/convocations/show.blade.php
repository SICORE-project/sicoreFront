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



    <section class="convocation-page">

        <div class="convocation-page-header">

            <div>
                <h2>{{ $convocation->objet ?? '—' }}</h2>
                <p class="breadcrumb">
                    Émise le {{ optional($convocation->date_emission)->format('d/m/Y') ?? '—' }}
                </p>
                @if (! empty($centreId))
                    <p class="breadcrumb">
                        Centre : {{ $convocation->centres->first()['centre'] ?? '—' }}
                    </p>
                @endif
            </div>

            <x-module-indemnite type="statut-convocation" :statut="$convocation->statut ?? null" />

        </div>

        <div class="convocation-page-body">

            {{-- ============================================================
                 INFORMATIONS GENERALES
            ============================================================ --}}

            <div class="form-section">

                <h3>Informations générales</h3>

                <div class="form-grid">

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
                        <label>Lieu d'affectation</label>
                        <p>{{ $convocation->lieu_affectation ?? '—' }}</p>
                    </div>

                   

                </div>

            </div>

          

            <div class="form-section">

                <div class="panel-header">
                    <h3>Centres d'examen</h3>
                </div>

                @php
                    $statutsPersonnel = [
                        'fonctionnaire' => 'Fonctionnaire',
                        'contractuel' => 'Contractuelle',
                        'vacataire' => 'Vacataire',
                    ];
                @endphp

                @forelse ($centresAvecMetiers as $centre)

                    <div class="centre-block">

                        <div class="form-grid centre-info-grid">

                            <div class="form-group">
                                <label>Centre</label>
                                <p>{{ $centre['centre'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Jury</label>
                                <p>{{ $centre['jury'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Président du jury</label>
                                <p>{{ trim(($centre['president_jury']['prenom'] ?? '') . ' ' . ($centre['president_jury']['nom'] ?? '')) ?: '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Téléphone</label>
                                <p>{{ $centre['president_jury_telephone'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Provenance du président du jury</label>
                                <p>{{ $centre['president_jury_provenance'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Chef de centre</label>
                                <p>{{ trim(($centre['chef_centre']['prenom'] ?? '') . ' ' . ($centre['chef_centre']['nom'] ?? '')) ?: '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Téléphone</label>
                                <p>{{ $centre['chef_centre_telephone'] ?? '—' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Provenance du chef de centre</label>
                                <p>{{ $centre['chef_centre_provenance'] ?? '—' }}</p>
                            </div>

                        </div>

                        @forelse ($centre['metiers'] as $metier)

                            <h4 class="sub-form-subheading">{{ $metier['metier'] ?? '—' }}</h4>

                            @if (empty($metier['beneficiaires']))

                                <p class="empty-message">Aucun membre ajouté pour ce métier.</p>

                            @else

                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Prénoms</th>
                                                <th>Fonction</th>
                                                <th>Statut</th>
                                                <th>Provenance</th>
                                                <th>Téléphone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($metier['beneficiaires'] as $beneficiaire)
                                                <tr>
                                                    <td>{{ $beneficiaire['nom'] ?? '—' }}</td>
                                                    <td>{{ $beneficiaire['prenom'] ?? '—' }}</td>
                                                    <td>{{ $beneficiaire['pivot']['fonction'] ?? '—' }}</td>
                                                    <td>{{ $statutsPersonnel[$beneficiaire['pivot']['categorie_personnel'] ?? null] ?? '—' }}</td>
                                                    <td>{{ $beneficiaire['pivot']['provenance'] ?? '—' }}</td>
                                                    <td>{{ $beneficiaire['telephone'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            @endif

                        @empty

                            <p class="empty-message">Aucun métier renseigné pour ce centre.</p>

                        @endforelse

                    </div>

                @empty

                    <p class="empty-message">Aucun centre renseigné pour cette convocation.</p>

                @endforelse

            </div>

            {{-- ============================================================
                 ACTIONS
            ============================================================ --}}

            <div class="form-actions">

                <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">
                    Retour à la liste
                </a>

                @if (! empty($centreId))
                    <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
                        Voir tous les centres
                    </a>
                @endif

                <a class="btn-secondary" href="{{ route('indemnites.convocations.pdf', $id) }}">
                    Télécharger PDF
                </a>

                <a class="btn-secondary" href="{{ route('indemnites.convocations.suivi', $id) }}">
                    Suivi des envois
                </a>

                <button class="btn-secondary" type="button" data-modal-open="envoyer-convocation">
                    Envoyer aux bénéficiaires
                </button>

                <a class="btn-primary" href="{{ ! empty($centreId) ? route('indemnites.convocations.centres.edit', [$id, $centreId]) : route('indemnites.convocations.edit', $id) }}">
                    Modifier
                </a>

            </div>

        </div>

    </section>

</section>

{{-- Envoi de la convocation par e-mail aux bénéficiaires — par défaut à
     tous les bénéficiaires de la convocation, avec un message personnalisé
     optionnel ajouté au modèle d'e-mail. Le suivi détaillé (par
     bénéficiaire, avec relance des échecs) se fait depuis la page "Suivi
     des envois" ci-dessus. --}}
<x-module-indemnite type="modal" id="envoyer-convocation" title="Envoyer aux bénéficiaires">

    <form method="POST" action="{{ route('indemnites.convocations.envoyer', $id) }}">
        @csrf

        <p>
            La convocation sera envoyée par e-mail à tous les bénéficiaires
            actuellement rattachés à cette convocation.
        </p>

        <div class="form-group">
            <label for="envoyer-message">Message personnalisé (optionnel)</label>
            <textarea class="form-control" id="envoyer-message" name="message" rows="4" maxlength="2000"
                placeholder="Ce message sera ajouté au modèle de convocation envoyé par e-mail."></textarea>
        </div>

        <div class="form-actions">
            <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
            <button class="btn-primary" type="submit">Envoyer</button>
        </div>

    </form>

</x-module-indemnite>

</main>

@endsection


@push('styles')
<style>

    .convocation-page {
        width: 100%;
        margin: 24px 0 40px;
        box-sizing: border-box;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .convocation-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .convocation-page-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .convocation-page-header .breadcrumb {
        margin: 4px 0 0;
    }

    .convocation-page-body {
        display: grid;
        gap: 22px;
        padding: 26px 30px 30px;
        box-sizing: border-box;
    }

    .convocation-page-body .form-section {
        padding: 22px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .convocation-page-body .form-section h3 {
        margin: 0 0 16px;
    }

    .convocation-page-body .panel-header {
        margin-bottom: 14px;
    }

    /* Sous-titre "nom du metier" au-dessus du tableau de ses membres. */
    .convocation-page-body .sub-form-subheading {
        margin: 0 0 10px;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
    }

    /* Un bloc par centre (Centre/Jury/Chef de centre + ses metiers) dans la
       section "Centres d'examen" — separateur discret entre plusieurs
       centres d'une meme convocation. */
    .convocation-page-body .centre-block {
        padding: 18px 0;
        border-top: 1px solid #e5e7eb;
    }

    .convocation-page-body .centre-block:first-child {
        padding-top: 0;
        border-top: none;
    }

    .convocation-page-body .centre-block:last-child {
        padding-bottom: 0;
    }

    .convocation-page-body .centre-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-bottom: 16px;
    }

    .convocation-page-body .centre-info-grid .form-group label {
        font-size: 12px;
    }

   
    .convocation-page-body .table-responsive {
        overflow-x: auto;
    }

    @media (max-width: 900px) {

        .convocation-page-body .centre-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    @media (max-width: 768px) {

        .convocation-page-header {
            padding: 16px 18px;
        }

        .convocation-page-body {
            padding: 18px;
        }

        .convocation-page-body .form-section {
            padding: 16px;
        }

    }

</style>
@endpush
