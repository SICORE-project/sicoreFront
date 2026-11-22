{{--
    Boutons de l'état de paiement des frais de déplacement d'une convocation :
    PDF (aperçu dans un nouvel onglet, téléchargeable depuis la visionneuse)
    et fichier SICA (CSV).
    Paramètres : $convocationId (obligatoire), $centre (facultatif),
    $libelle (facultatif, ajouté au bouton PDF quand il y a plusieurs
    convocations).
--}}
@php
    $parametresEtat = array_filter(['convocation_id' => $convocationId, 'centre' => $centre ?? null]);
@endphp

<div class="etat-paiement-boutons">
    {{-- Un seul bouton aperçu + téléchargement : le PDF s'ouvre dans un
         nouvel onglet (inline), d'où on le télécharge ou l'imprime avec
         les boutons de la visionneuse du navigateur. --}}
    <a
        class="btn-primary"
        href="{{ route('indemnites.frais-deplacement.etat-paiement', $parametresEtat + ['apercu' => 1]) }}"
        target="_blank"
        rel="noopener"
        title="Ouvre l'état de paiement dans un nouvel onglet : vous pouvez le vérifier puis le télécharger ou l'imprimer"
    >
        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
        État de paiement (PDF){{ ! empty($libelle) ? ' — '.$libelle : '' }}
    </a>

    <a class="btn-secondary" href="{{ route('indemnites.frais-deplacement.etat-paiement.sica', $parametresEtat) }}">
        <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
        Fichier SICA (CSV)
    </a>
</div>

@once
    @push('styles')
        <style>
            .etat-paiement-boutons {
                display: inline-flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 10px;
            }

            .etat-paiement-boutons a {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                text-decoration: none;
            }
        </style>
    @endpush
@endonce
