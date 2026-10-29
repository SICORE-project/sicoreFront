@extends('layouts.app')

@section('title', 'SICORE - Mes pièces')

@section('content')

<main class="main-content">

    <x-topbar
        title="Mes pièces"
        subtitle="Indemnités > Mes pièces justificatives"
        icon="fa-solid fa-folder-open"
    />

    <section class="content-area">

        <p class="info-depot" role="note">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            Vos pièces sont fournies <strong>après les services faits (après l'examen)</strong>. Chaque fichier (PDF, JPG ou PNG) pèse
            <strong>1 Mo maximum</strong>. L'ordre de mission se dépose en <strong>un seul fichier</strong> contenant le recto et le verso.
        </p>

        @if ($erreur)
            <div class="alert alert-danger">{{ $erreur }}</div>
        @endif

        @forelse ($convocations as $convocation)
            @php
                $statuts = [
                    'depose' => ['Déposé', 'badge-primary'],
                    'valide' => ['Validé', 'badge-active'],
                    'rejete' => ['Rejeté', 'badge-inactive'],
                ];
            @endphp

            <article class="panel mes-pieces-convocation">
                <header class="mes-pieces-entete">
                    <div>
                        <h2>{{ $convocation['objet'] }} — {{ $convocation['session'] }}</h2>
                        <p>
                            Du {{ \Illuminate\Support\Carbon::parse($convocation['date_debut'])->format('d/m/Y') }}
                            au {{ \Illuminate\Support\Carbon::parse($convocation['date_fin'])->format('d/m/Y') }}
                            @if (! empty($convocation['centres']))
                                · {{ implode(', ', $convocation['centres']) }}
                            @endif
                        </p>
                    </div>
                    @unless ($convocation['depot_ouvert'])
                        <span class="badge badge-pending">Dépôt possible après le {{ \Illuminate\Support\Carbon::parse($convocation['date_fin'])->format('d/m/Y') }}</span>
                    @endunless
                </header>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Pièce</th>
                                <th>Statut</th>
                                <th>Fichier</th>
                                <th>Date de dépôt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($convocation['pieces'] as $piece)
                                @php
                                    [$libelleStatut, $classe] = $statuts[$piece['statut']] ?? ['Non déposé', 'badge-pending'];
                                    $peutDeposer = $piece['deposable'] && $convocation['depot_ouvert'] && $piece['statut'] !== 'valide';
                                @endphp
                                <tr>
                                    <td>{{ $piece['libelle'] }}</td>
                                    <td>
                                        <span class="badge {{ $classe }}">{{ $libelleStatut }}</span>
                                        @if ($piece['statut'] === 'rejete' && $piece['commentaire_rejet'])
                                            <div class="mes-pieces-motif">Motif : {{ $piece['commentaire_rejet'] }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $piece['nom_original'] ?? '—' }}</td>
                                    <td>{{ $piece['date_depot'] ? \Illuminate\Support\Carbon::parse($piece['date_depot'])->format('d/m/Y') : '—' }}</td>
                                    <td class="mes-pieces-actions">
                                        @if ($piece['id'])
                                            <a class="table-action" href="{{ route('indemnites.mes-pieces.telecharger', $piece['id']) }}">Télécharger</a>
                                        @endif
                                        @if ($peutDeposer)
                                            <form method="POST" action="{{ route('indemnites.mes-pieces.deposer') }}" enctype="multipart/form-data" class="mes-pieces-form">
                                                @csrf
                                                <input type="hidden" name="convocation_id" value="{{ $convocation['id'] }}">
                                                <input type="hidden" name="type" value="{{ $piece['type'] }}">
                                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required data-taille-max="1048576">
                                                <button class="table-action" type="submit">{{ $piece['id'] ? 'Remplacer' : 'Déposer' }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            @unless ($erreur)
                <p class="empty-state">Vous n'avez aucune convocation pour le moment.</p>
            @endunless
        @endforelse

    </section>
</main>

@endsection

@push('styles')
<style>
    .info-depot { margin: 0 0 16px; padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; color: #1e3a8a; font-size: 14px; }
    .mes-pieces-convocation { margin-bottom: 20px; }
    .mes-pieces-entete { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .mes-pieces-entete h2 { margin: 0 0 4px; font-size: 17px; }
    .mes-pieces-entete p { margin: 0; color: #6b7280; font-size: 14px; }
    .mes-pieces-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .mes-pieces-form { display: flex; gap: 8px; align-items: center; }
    .mes-pieces-motif { margin-top: 4px; font-size: 12px; color: #b91c1c; }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('input[type="file"][data-taille-max]').forEach(function (champ) {
        champ.addEventListener('change', function () {
            var fichier = champ.files && champ.files[0];
            if (fichier && fichier.size > Number(champ.dataset.tailleMax)) {
                alert('Fichier trop volumineux (' + Math.round(fichier.size / 1024) + ' Ko) — 1 Mo maximum.');
                champ.value = '';
            }
        });
    });
</script>
@endpush
