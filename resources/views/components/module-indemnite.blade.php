{{--
    Composant unique pour tout le module Indemnités — badge de statut de
    convocation, badge de statut d'envoi, fenêtre modale — regroupés dans
    UN SEUL fichier (à la demande de l'utilisatrice) plutôt que 3 fichiers
    séparés, pour que n'importe quelle page du module Indemnités
    (convocations, et les autres pages du module plus tard) y trouve tout
    au même endroit sans avoir à chercher plusieurs composants.

    Usage :
        <x-module-indemnite type="statut-convocation" :statut="$convocation->statut ?? null" />
        <x-module-indemnite type="statut-envoi" :statut="$envoi['statut'] ?? null" />
        <x-module-indemnite type="modal" id="mon-id" title="Mon titre" :open="$condition ?? false">
            ... contenu (formulaire, texte...) ...
        </x-module-indemnite>

    Le déclencheur d'une modale reste un bouton/lien classique ailleurs sur
    la page : <button type="button" data-modal-open="mon-id">Ouvrir</button>
--}}
@props([
    'type',
    'statut' => null,
    'id' => null,
    'title' => null,
    'open' => false,
])

@if ($type === 'statut-convocation')

    {{-- Statut de la convocation elle-même : brouillon/émise/envoyée/clôturée. --}}
    @php
        $badges = [
            'brouillon' => ['badge-pending', 'Brouillon'],
            'emise' => ['badge-primary', 'Émise'],
            'envoyee' => ['badge-active', 'Envoyée'],
            'cloturee' => ['badge-inactive', 'Clôturée'],
        ];

        [$classe, $libelle] = $badges[$statut] ?? ['badge-pending', $statut ? ucfirst($statut) : '—'];
    @endphp

    <span {{ $attributes->merge(['class' => "badge {$classe}"]) }}>{{ $libelle }}</span>

@elseif ($type === 'statut-envoi')

    {{-- Statut D'ENVOI d'une convocation (envoyé/échec — suivi.blade.php),
         domaine différent du statut de la convocation elle-même ci-dessus. --}}
    @php
        $badges = [
            'envoye' => ['badge-active', 'Envoyé'],
            'echec' => ['badge-inactive', 'Échec'],
        ];

        [$classe, $libelle] = $badges[$statut] ?? ['badge-pending', $statut ? ucfirst($statut) : '—'];
    @endphp

    <span {{ $attributes->merge(['class' => "badge {$classe}"]) }}>{{ $libelle }}</span>

@elseif ($type === 'modal')

    {{-- Fenêtre modale générique : CSS + JS d'ouverture/fermeture
         auto-injectés une seule fois via @once, même si plusieurs modales
         sont utilisées sur la même page. --}}
    @once
        @push('styles')
        <style>

            .modal-backdrop {
                position: fixed;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: rgba(15, 23, 42, 0.5);
                z-index: 1000;
            }

            .modal-backdrop[hidden] {
                display: none;
            }

            .modal-dialog {
                width: min(900px, calc(100vw - 40px)) !important;
                max-width: 900px !important;
                height: min(820px, calc(100vh - 40px)) !important;
                max-height: calc(100vh - 40px) !important;
                overflow-y: auto;
                padding: 20px 22px;
                border-radius: 10px;
                background: #ffffff;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
            }

            .modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
            }

            .modal-header h2 {
                margin: 0;
                font-size: 1.1rem;
            }

            .modal-close {
                border: 0;
                background: transparent;
                font-size: 1.4rem;
                line-height: 1;
                cursor: pointer;
                color: inherit;
            }

        </style>
        @endpush

        @push('scripts')
        <script>
            (function () {
                "use strict";

                function ouvrirModal(modal) {
                    modal.hidden = false;
                }

                function fermerModal(modal) {
                    modal.hidden = true;
                }

                document.querySelectorAll("[data-modal-open]").forEach(function (bouton) {
                    var modal = document.getElementById(bouton.getAttribute("data-modal-open"));

                    if (!modal) {
                        return;
                    }

                    bouton.addEventListener("click", function () {
                        ouvrirModal(modal);
                    });
                });

                document.querySelectorAll("[data-modal]").forEach(function (modal) {
                    modal.addEventListener("click", function (event) {
                        if (event.target === modal) {
                            fermerModal(modal);
                        }
                    });

                    modal.querySelectorAll("[data-modal-close]").forEach(function (bouton) {
                        bouton.addEventListener("click", function () {
                            fermerModal(modal);
                        });
                    });
                });

                document.addEventListener("keydown", function (event) {
                    if (event.key !== "Escape") {
                        return;
                    }

                    document.querySelectorAll("[data-modal]:not([hidden])").forEach(fermerModal);
                });
            })();
        </script>
        @endpush
    @endonce

    <div class="modal-backdrop" id="{{ $id }}" data-modal @if (! $open) hidden @endif>
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">

            <div class="modal-header">
                <h2 id="{{ $id }}-title">{{ $title }}</h2>
                <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
            </div>

            {{ $slot }}

        </div>
    </div>

@endif
