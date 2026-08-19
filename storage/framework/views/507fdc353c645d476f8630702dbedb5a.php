<?php $__env->startSection('title', 'SICORE - Pièces justificatives'); ?>

<?php $__env->startSection('content'); ?>

<main class="main-content">

    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Pièces justificatives','subtitle' => 'Indemnites > Pièces justificatives','icon' => 'fa-solid fa-folder-open','searchId' => 'pieceJustificativeSearch','searchPlaceholder' => 'Rechercher…','filterTarget' => '#piecesJustificativesTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pièces justificatives','subtitle' => 'Indemnites > Pièces justificatives','icon' => 'fa-solid fa-folder-open','search-id' => 'pieceJustificativeSearch','search-placeholder' => 'Rechercher…','filter-target' => '#piecesJustificativesTable']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455)): ?>
<?php $attributes = $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455; ?>
<?php unset($__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57b7ac81b71e7fe2d81fa75baf439455)): ?>
<?php $component = $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455; ?>
<?php unset($__componentOriginal57b7ac81b71e7fe2d81fa75baf439455); ?>
<?php endif; ?>

    <section class="content-area">

        

        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Dossiers</p>
                    <p class="stat-value"><?php echo e($stats['total_dossiers'] ?? 0); ?></p>
                    <p class="stat-note">Convocation × centre d'examen</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Avec pièces déposées</p>
                    <p class="stat-value"><?php echo e($stats['sessions_avec_pieces'] ?? 0); ?></p>
                    <p class="stat-note">Dossiers avec au moins une pièce</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Sans aucune pièce</p>
                    <p class="stat-value"><?php echo e($stats['sessions_sans_pieces'] ?? 0); ?></p>
                    <p class="stat-note">Dossiers à compléter</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Pièces rejetées</p>
                    <p class="stat-value"><?php echo e($stats['pieces_rejetees'] ?? 0); ?></p>
                    <p class="stat-note">À redéposer</p>
                </div>
                <span class="stat-icon red">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        

        <form
            id="pieceJustificativeFilterForm"
            class="filter-panel"
            method="GET"
            action="<?php echo e(route('indemnites.pieces-justificatives')); ?>"
            aria-label="Filtres de la page"
        >

            <div class="form-group">
                <label for="piece-filter-session">Session</label>
                <select class="form-control" id="piece-filter-session" name="session" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    <?php $__currentLoopData = $optionsFiltres['sessions'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valeur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($valeur); ?>" <?php if(request('session') === $valeur): echo 'selected'; endif; ?>><?php echo e($valeur); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="piece-filter-objet">Objet</label>
                <select class="form-control" id="piece-filter-objet" name="objet" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    <?php $__currentLoopData = $optionsFiltres['objets'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valeur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($valeur); ?>" <?php if(request('objet') === $valeur): echo 'selected'; endif; ?>><?php echo e($valeur); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="piece-filter-centre">Centre</label>
                <select class="form-control" id="piece-filter-centre" name="centre" data-filter-auto-submit>
                    <option value="">Sélectionner</option>
                    <?php $__currentLoopData = $optionsFiltres['centres'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valeur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($valeur); ?>" <?php if(request('centre') === $valeur): echo 'selected'; endif; ?>><?php echo e($valeur); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="actions-group">
                <?php if($filtreActif): ?>
                    <a class="btn-secondary" href="<?php echo e(route('indemnites.pieces-justificatives')); ?>">
                        Réinitialiser
                    </a>
                <?php endif; ?>
            </div>

        </form>

        

        <?php if(! $filtreActif): ?>

            <section class="table-card">
                
                <p class="empty-message show">
                    Choisissez une session, un objet ou un centre d'examen ci-dessus pour afficher les membres concernés.
                </p>
            </section>

        <?php else: ?>

            <section class="table-card">

                <div class="table-responsive">
                    <table class="table" id="piecesJustificativesTable">

                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénoms</th>
                                <th>Fonction</th>
                                <th>Centre d'examen</th>
                                <th>Jury</th>
                                <th>Objet</th>
                                <th>Type de convocation</th>
                                <th>Session</th>
                                <th>Provenance</th>
                                <th>Statut du dossier</th>
                                <th class="actions-cell">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $membres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($membre['nom'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['prenom'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['fonction'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['centre'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['jury'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['objet'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['type_convocation'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['session'] ?? '—'); ?></td>
                                    <td><?php echo e($membre['provenance'] ?? '—'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e(($membre['dossier_complet'] ?? false) ? 'badge-active' : 'badge-pending'); ?>">
                                            <?php echo e($membre['dossier_resume'] ?? '—'); ?>

                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="table-actions-inline">
                                            <button
                                                class="table-action"
                                                type="button"
                                                data-modal-open="modal-voir-dossier"
                                                data-dossier-label="<?php echo e(trim(($membre['prenom'] ?? '').' '.($membre['nom'] ?? ''))); ?> — <?php echo e($membre['centre'] ?? '—'); ?>"
                                                data-dossier-json="<?php echo e(json_encode($membre['dossier'] ?? [])); ?>"
                                            >
                                                Voir le dossier
                                            </button>
                                            <button
                                                class="table-action"
                                                type="button"
                                                data-modal-open="modal-piece-justificative"
                                                data-piece-convocation-id="<?php echo e($membre['convocation_id']); ?>"
                                                data-piece-enseignant-id="<?php echo e($membre['enseignant_id']); ?>"
                                                data-piece-centre-id="<?php echo e($membre['centre_id']); ?>"
                                                data-piece-label="<?php echo e(trim(($membre['prenom'] ?? '').' '.($membre['nom'] ?? ''))); ?> — <?php echo e($membre['centre'] ?? '—'); ?>"
                                            >
                                                Ajouter une pièce
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <?php if(empty($membres)): ?>
                    
                    <p class="empty-message show">Aucune donnée trouvée.</p>
                <?php endif; ?>

                <div class="convocation-pagination" aria-label="Pagination">

                    <?php if($convocations->onFirstPage()): ?>
                        <span class="page-btn" aria-disabled="true">←</span>
                    <?php else: ?>
                        <a class="page-btn" href="<?php echo e($convocations->previousPageUrl()); ?>" aria-label="Page précédente">←</a>
                    <?php endif; ?>

                    <?php for($page = 1; $page <= $convocations->lastPage(); $page++): ?>
                        <a
                            class="page-btn <?php echo e($page === $convocations->currentPage() ? 'active' : ''); ?>"
                            href="<?php echo e($convocations->url($page)); ?>"
                            data-page-number
                        >
                            <?php echo e($page); ?>

                        </a>
                    <?php endfor; ?>

                    <?php if($convocations->hasMorePages()): ?>
                        <a class="page-btn" href="<?php echo e($convocations->nextPageUrl()); ?>" aria-label="Page suivante">→</a>
                    <?php else: ?>
                        <span class="page-btn" aria-disabled="true">→</span>
                    <?php endif; ?>

                </div>

            </section>

        <?php endif; ?>

        

        <div class="modal-backdrop" id="modal-piece-justificative" data-modal hidden>
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-piece-justificative-title">

                <div class="modal-header">
                    <h2 id="modal-piece-justificative-title">Ajouter une pièce justificative</h2>
                    <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
                </div>

                <p class="modal-piece-membre" data-piece-membre-label></p>

                <form
                    method="POST"
                    action="<?php echo e(route('indemnites.pieces-justificatives.deposer')); ?>"
                    enctype="multipart/form-data"
                    class="import-panel-form"
                    data-piece-form
                >
                    <?php echo csrf_field(); ?>

                    <input type="hidden" name="convocation_id" data-piece-field="convocation_id">
                    <input type="hidden" name="enseignant_id" data-piece-field="enseignant_id">
                    <input type="hidden" name="centre_id" data-piece-field="centre_id">

                    <div class="import-panel-form-grid">

                        <div class="form-group">
                            <label for="piece-service-fait">Service fait <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-service-fait" type="file" name="service_fait" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="service_fait">
                        </div>

                        <div class="form-group">
                            <label for="piece-ordre-mission">Ordre de mission <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-ordre-mission" type="file" name="ordre_mission" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="ordre_mission">
                        </div>

                        <div class="form-group">
                            <label for="piece-rapport-mission">Rapport de mission <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-rapport-mission" type="file" name="rapport_mission" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="rapport_mission">
                        </div>

                        <div class="form-group">
                            <label for="piece-bulletin-salaire">Bulletin de salaire <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-bulletin-salaire" type="file" name="bulletin_salaire" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="bulletin_salaire">
                        </div>

                        <div class="form-group">
                            <label for="piece-accuse-reception">Accusé de réception <span class="form-group-hint">(PDF, JPG, PNG — 100 Ko max)</span></label>
                            <input class="form-control" id="piece-accuse-reception" type="file" name="accuse_reception" accept=".pdf,.jpg,.jpeg,.png" required data-piece-fichier="accuse_reception">
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Dossier de convocation <span class="form-group-hint">(joint automatiquement)</span></label>
                        <p class="form-hint">PDF déjà généré pour cette convocation — rien à téléverser ici.</p>
                    </div>

                    

                    <div class="piece-recap" data-piece-recap>
                        <p class="piece-recap-title">
                            Récapitulatif — <span data-piece-recap-count>1</span>/6 fichiers prêts
                        </p>
                        <ul class="piece-recap-list">
                            <li data-piece-recap-item="service_fait">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Service fait
                            </li>
                            <li data-piece-recap-item="ordre_mission">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Ordre de mission
                            </li>
                            <li data-piece-recap-item="rapport_mission">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Rapport de mission
                            </li>
                            <li data-piece-recap-item="bulletin_salaire">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Bulletin de salaire
                            </li>
                            <li data-piece-recap-item="accuse_reception">
                                <i class="fa-regular fa-circle" aria-hidden="true"></i> Accusé de réception
                            </li>
                            <li class="is-ready">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Dossier de convocation (automatique)
                            </li>
                        </ul>
                    </div>

                    <div class="actions-group">
                        <button class="btn-primary" type="submit" data-piece-submit>
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            Déposer
                        </button>
                    </div>
                </form>

            </div>
        </div>

        

        <div
            class="modal-backdrop"
            id="modal-voir-dossier"
            data-modal
            hidden
            data-dossier-download-url-template="<?php echo e(route('indemnites.pieces-justificatives.telecharger', ':id')); ?>"
        >
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-voir-dossier-title">

                <div class="modal-header">
                    <h2 id="modal-voir-dossier-title">Dossier de pièces justificatives</h2>
                    <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
                </div>

                <p class="modal-piece-membre" data-dossier-membre-label></p>

                <ul class="piece-dossier-list" data-dossier-liste></ul>

            </div>
        </div>

    </section>

</main>

<?php $__env->stopSection(); ?>



<?php $__env->startPush('styles'); ?>
<style>

    .convocation-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 14px 18px;
        border-top: 1px solid var(--border-soft);
    }

    /* Modale "Ajouter une pièce justificative" — memes classes que la
       modale d'import de convocations/index.blade.php (pas de style
       partage globalement dans app.css pour les modales, il faut les
       redefinir sur chaque page qui en a besoin). */

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
        width: 100%;
        /* Plus large que la modale d'import de convocations (520px) : les
           4 champs fichiers sont affiches sur 2 colonnes ici, avec un
           libelle assez long (nom + format/taille entre parentheses). */
        max-width: 640px;
        max-height: calc(100vh - 40px);
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

    .modal-piece-membre {
        margin: 0 0 16px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .import-panel-form {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .import-panel-form .form-group {
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .import-panel-form .form-control {
        width: 100%;
    }

    /* Les 4 champs fichiers (service fait / ordre de mission / rapport de
       mission / bulletin de salaire) sur 2 colonnes plutot qu'empiles. */
    .import-panel-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    @media (max-width: 560px) {
        .import-panel-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .form-group-hint {
        font-weight: 400;
        font-size: 12px;
        color: var(--text-muted);
    }

    .import-panel-form .form-hint {
        margin: 0;
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .import-panel-form .actions-group {
        margin: 0;
    }

    .import-panel-form .actions-group .btn-primary {
        width: 100%;
    }

    .piece-recap {
        padding: 12px 14px;
        border: 1px solid var(--border-soft);
        border-radius: 8px;
        background: #f8fafc;
    }

    .piece-recap-title {
        margin: 0 0 8px;
        font-size: 13px;
        font-weight: 800;
    }

    .piece-recap-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 13px;
    }

    .piece-recap-list li {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
    }

    .piece-recap-list li i {
        color: #cbd5e1;
    }

    .piece-recap-list li.is-ready {
        color: inherit;
        font-weight: 600;
    }

    .piece-recap-list li.is-ready i {
        color: var(--green, #16a34a);
    }

    .piece-dossier-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .piece-dossier-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid var(--border-soft);
        border-radius: 8px;
    }

    .piece-dossier-item-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .piece-dossier-item-label {
        font-weight: 700;
        font-size: 13px;
    }

    .piece-dossier-item-date {
        font-size: 12px;
        color: var(--text-muted);
    }

    .piece-dossier-item-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

</style>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        "use strict";

        document.querySelectorAll("[data-filter-auto-submit]").forEach(function (champ) {
            champ.addEventListener("change", function () {
                champ.form.submit();
            });
        });
    })();
</script>



<script>
    (function () {
        "use strict";

        function ouvrirModal(modal) {
            modal.hidden = false;
        }

        function fermerModal(modal) {
            modal.hidden = true;
        }

        var formulairePiece = document.querySelector("[data-piece-form]");
        var recapPiece = document.querySelector("[data-piece-recap]");
        var compteurRecap = recapPiece ? recapPiece.querySelector("[data-piece-recap-count]") : null;

        // 100 Ko max par fichier (voir demande utilisateur) — verifie des
        // la selection, cote client, plutot que d'attendre le retour du
        // serveur apres l'envoi : memes classes (.field-error/.is-invalid)
        // que le wizard de convocation (convocation-wizard.js), pour rester
        // coherent avec le reste de l'appli.
        var TAILLE_MAX_OCTETS = 100 * 1024;

        function formaterKo(octets) {
            return Math.round(octets / 1024) + " Ko";
        }

        function afficherErreurChamp(champ, message) {
            var groupe = champ.closest(".form-group");

            if (!groupe) {
                return;
            }

            var erreur = groupe.querySelector(".field-error");

            if (!erreur) {
                erreur = document.createElement("p");
                erreur.className = "field-error";
                groupe.appendChild(erreur);
            }

            champ.classList.add("is-invalid");
            champ.setAttribute("aria-invalid", "true");
            erreur.textContent = message;
        }

        function effacerErreurChamp(champ) {
            var groupe = champ.closest(".form-group");
            var erreur = groupe ? groupe.querySelector(".field-error") : null;

            champ.classList.remove("is-invalid");
            champ.setAttribute("aria-invalid", "false");

            if (erreur) {
                erreur.textContent = "";
            }
        }

        // Renvoie false et vide le champ si le fichier depasse 100 Ko —
        // ainsi mettreAJourRecap() ne le compte jamais comme "pret" et le
        // formulaire ne peut pas etre soumis avec ce fichier.
        function verifierTailleFichier(champ) {
            var fichier = champ.files && champ.files[0];

            if (!fichier) {
                effacerErreurChamp(champ);

                return true;
            }

            if (fichier.size > TAILLE_MAX_OCTETS) {
                afficherErreurChamp(
                    champ,
                    "Fichier trop volumineux (" + formaterKo(fichier.size) + ") — diminuez sa taille à 100 Ko maximum."
                );
                champ.value = "";

                return false;
            }

            effacerErreurChamp(champ);

            return true;
        }

        function mettreAJourRecap() {
            if (!formulairePiece || !recapPiece) {
                return;
            }

            var prets = 1; // le dossier de convocation compte toujours

            formulairePiece.querySelectorAll("[data-piece-fichier]").forEach(function (champ) {
                var estPret = champ.files && champ.files.length > 0;
                var item = recapPiece.querySelector(
                    '[data-piece-recap-item="' + champ.getAttribute("data-piece-fichier") + '"]'
                );

                if (estPret) {
                    prets += 1;
                }

                if (!item) {
                    return;
                }

                item.classList.toggle("is-ready", estPret);

                var icone = item.querySelector("i");
                if (icone) {
                    icone.className = estPret ? "fa-solid fa-circle-check" : "fa-regular fa-circle";
                }
            });

            if (compteurRecap) {
                compteurRecap.textContent = String(prets);
            }
        }

        if (formulairePiece) {
            formulairePiece.querySelectorAll("[data-piece-fichier]").forEach(function (champ) {
                champ.addEventListener("change", function () {
                    verifierTailleFichier(champ);
                    mettreAJourRecap();
                });
            });
        }

        // Statuts possibles d'une piece (voir piece_justificatives.statut
        // cote back) : "null" = pas encore deposee du tout.
        var LIBELLES_STATUT_DOSSIER = {
            depose: { label: "Déposé", classe: "badge-primary" },
            valide: { label: "Validé", classe: "badge-active" },
            rejete: { label: "Rejeté", classe: "badge-inactive" },
        };

        function formaterDateDepot(date) {
            if (!date) {
                return "";
            }

            var parties = date.substring(0, 10).split("-");

            return parties.length === 3 ? parties.reverse().join("/") : date;
        }

        function remplirDossier(modal, bouton) {
            var libelle = modal.querySelector("[data-dossier-membre-label]");
            if (libelle) {
                libelle.textContent = bouton.getAttribute("data-dossier-label") || "";
            }

            var liste = modal.querySelector("[data-dossier-liste]");
            if (!liste) {
                return;
            }

            liste.innerHTML = "";

            var dossier = [];
            try {
                dossier = JSON.parse(bouton.getAttribute("data-dossier-json") || "[]");
            } catch (erreur) {
                dossier = [];
            }

            var urlTemplate = modal.getAttribute("data-dossier-download-url-template") || "";

            dossier.forEach(function (piece) {
                var item = document.createElement("li");
                item.className = "piece-dossier-item";

                var infos = document.createElement("div");
                infos.className = "piece-dossier-item-info";

                var label = document.createElement("span");
                label.className = "piece-dossier-item-label";
                label.textContent = piece.label;
                infos.appendChild(label);

                if (piece.date_depot) {
                    var date = document.createElement("span");
                    date.className = "piece-dossier-item-date";
                    date.textContent = "Déposé le " + formaterDateDepot(piece.date_depot);
                    infos.appendChild(date);
                }

                item.appendChild(infos);

                var actions = document.createElement("div");
                actions.className = "piece-dossier-item-actions";

                var infoStatut = LIBELLES_STATUT_DOSSIER[piece.statut] || { label: "Non déposé", classe: "badge-pending" };
                var badge = document.createElement("span");
                badge.className = "badge " + infoStatut.classe;
                badge.textContent = infoStatut.label;
                actions.appendChild(badge);

                if (piece.id && urlTemplate) {
                    // Ouvre dans un nouvel onglet : le fichier s'affiche
                    // (visionneuse du navigateur) plutot que de forcer un
                    // telechargement immediat — l'utilisateur peut ensuite
                    // le telecharger lui-meme depuis cette visionneuse s'il
                    // le souhaite, sans quitter la page/la modale.
                    var lien = document.createElement("a");
                    lien.className = "table-action";
                    lien.href = urlTemplate.replace(":id", piece.id);
                    lien.target = "_blank";
                    lien.rel = "noopener";
                    lien.textContent = "Voir / Télécharger";
                    actions.appendChild(lien);
                }

                item.appendChild(actions);
                liste.appendChild(item);
            });
        }

        document.querySelectorAll("[data-modal-open]").forEach(function (bouton) {
            var modal = document.getElementById(bouton.getAttribute("data-modal-open"));

            if (!modal) {
                return;
            }

            bouton.addEventListener("click", function () {
                if (bouton.hasAttribute("data-dossier-json")) {
                    remplirDossier(modal, bouton);
                    ouvrirModal(modal);

                    return;
                }

                // Pre-remplit les champs caches (convocation/enseignant/
                // centre) et le libelle du membre a partir des attributs
                // data-piece-* du bouton clique — un seul formulaire/modale
                // partage par toutes les lignes du tableau.
                var champsParAttribut = {
                    "convocation_id": "pieceConvocationId",
                    "enseignant_id": "pieceEnseignantId",
                    "centre_id": "pieceCentreId",
                };

                modal.querySelectorAll("[data-piece-field]").forEach(function (champ) {
                    var cle = champsParAttribut[champ.getAttribute("data-piece-field")];
                    champ.value = (cle && bouton.dataset[cle]) || "";
                });

                var libelle = modal.querySelector("[data-piece-membre-label]");
                if (libelle) {
                    libelle.textContent = bouton.getAttribute("data-piece-label") || "";
                }

                // Repart de zero a chaque ouverture : sinon un fichier
                // choisi (ou une erreur de taille affichee) pour un membre
                // resterait present si on ouvre la modale pour un AUTRE
                // membre juste apres.
                if (formulairePiece) {
                    formulairePiece.reset();
                    formulairePiece.querySelectorAll("[data-piece-fichier]").forEach(effacerErreurChamp);
                    mettreAJourRecap();
                }

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

        var boutonPiece = document.querySelector("[data-piece-submit]");
        var envoiPieceEnCours = false;

        if (formulairePiece && boutonPiece) {
            formulairePiece.addEventListener("submit", function (event) {
                if (envoiPieceEnCours) {
                    event.preventDefault();

                    return;
                }

                envoiPieceEnCours = true;

                boutonPiece.disabled = true;
                boutonPiece.setAttribute("aria-busy", "true");
                boutonPiece.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Dépôt en cours…';
            });
        }
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\indemnites\pieces-justificatives.blade.php ENDPATH**/ ?>