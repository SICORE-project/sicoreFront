<?php $__env->startSection('title', 'SICORE - Gestion des convocations'); ?>

<?php $__env->startSection('content'); ?>

<main class="main-content">

    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Gestion des convocations','subtitle' => 'Indemnites > Convocations','icon' => 'fa-solid fa-envelope-open-text','searchId' => 'convocationSearch','searchPlaceholder' => 'Rechercher…','filterTarget' => '#convocationsTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Gestion des convocations','subtitle' => 'Indemnites > Convocations','icon' => 'fa-solid fa-envelope-open-text','search-id' => 'convocationSearch','search-placeholder' => 'Rechercher…','filter-target' => '#convocationsTable']); ?>
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
                    <p class="stat-label">Convocations</p>
                    <p class="stat-value"><?php echo e($stats['total'] ?? $convocations->total()); ?></p>
                    <p class="stat-note">Période active</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Envoyées</p>
                    <p class="stat-value"><?php echo e($stats['envoyees'] ?? 0); ?></p>
                    <p class="stat-note">Statut final</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </span>
            </article>

           

            <article class="stat-card">
                <div>
                    <p class="stat-label">Brouillons</p>
                    <p class="stat-value"><?php echo e($stats['brouillons'] ?? 0); ?></p>
                    <p class="stat-note">À finaliser</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Clôturées</p>
                    <p class="stat-value"><?php echo e($stats['cloturees'] ?? 0); ?></p>
                    <p class="stat-note">Traitées</p>
                </div>
                <span class="stat-icon red">
                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                </span>
            </article>

        </div>

        

        <div class="actions-row">
            <p class="breadcrumb">Gestion des indemnités &gt; Convocations</p>
            <div class="actions-group">
                <a class="btn-primary" href="<?php echo e(route('indemnites.convocations.create')); ?>">
                    Nouvelle convocation
                </a>

                <button class="btn-secondary" type="button" data-modal-open="import-convocations">
                    Importer
                </button>

                <button class="btn-secondary btn-bulk-delete" type="button" data-bulk-delete-button disabled>
                    Supprimer la sélection
                </button>
            </div>
        </div>

    

        <?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'modal','id' => 'import-convocations','title' => 'Importer une convocation','open' => ! empty($importAvertissements)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'modal','id' => 'import-convocations','title' => 'Importer une convocation','open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! empty($importAvertissements))]); ?>

            <a class="btn-secondary modal-modele-link" href="<?php echo e(route('indemnites.convocations.modele-word')); ?>">
                Télécharger le modèle Word
            </a>

            <?php if(! empty($importAvertissements)): ?>
                <div class="form-errors" role="alert">
                    <p><strong>Points à vérifier sur le dernier import :</strong></p>
                    <ul>
                        <?php $__currentLoopData = $importAvertissements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $avertissement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($avertissement); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?php echo e(route('indemnites.convocations.import')); ?>"
                enctype="multipart/form-data"
                class="import-panel-form"
            >
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="import-type-convocation">Type de convocation</label>
                    <select
                        class="form-control"
                        id="import-type-convocation"
                        name="type_convocation_id"
                        required
                    >
                        <option value="">Sélectionner</option>
                        <?php $__currentLoopData = $typesConvocation ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type['id']); ?>"><?php echo e($type['libelle']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="import-fichier">Fichier (Word)</label>
                    <input
                        class="form-control"
                        id="import-fichier"
                        name="fichier"
                        type="file"
                        accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        required
                    >
                </div>

                <!-- <form
                    method="POST"
                    action="<?php echo e(route('indemnites.convocations.import')); ?>"
                    enctype="multipart/form-data"
                    class="import-panel-form"
                    data-import-form
                >
                    <?php echo csrf_field(); ?>

                    <div class="form-group">
                        <label for="import-type-convocation">Type de convocation</label>
                        <select
                            class="form-control"
                            id="import-type-convocation"
                            name="type_convocation_id"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <?php $__currentLoopData = $typesConvocation ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type['id']); ?>"><?php echo e($type['libelle']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="import-fichier">Fichier (Word)</label>
                        <input
                            class="form-control"
                            id="import-fichier"
                            name="fichier"
                            type="file"
                            accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required
                        >
                    </div> -->

                    <div class="actions-group">
                        <button class="btn-primary" type="submit" data-import-submit>
                            Importer
                        </button>
                    </div>
                </form>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $attributes = $__attributesOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__attributesOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $component = $__componentOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__componentOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>

        

      
        <form
            id="convocationFilterForm"
            class="filter-panel"
            method="GET"
            action="<?php echo e(route('indemnites.convocations')); ?>"
            aria-label="Filtres de la page"
            data-auto-submit
        >

            <div class="form-group">
                <label for="convocation-filter-date">Date</label>
                <select class="form-control" id="convocation-filter-date" name="date">
                    <option value="">Toutes les dates</option>
                    <?php $__currentLoopData = $filtreOptions['dates'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dateOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dateOption); ?>" <?php if(request('date') === $dateOption): echo 'selected'; endif; ?>>
                            <?php echo e(\Illuminate\Support\Carbon::parse($dateOption)->format('d/m/Y')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="convocation-filter-objet">Objet</label>
                <select class="form-control" id="convocation-filter-objet" name="objet">
                    <option value="">Tous les objets</option>
                    <?php $__currentLoopData = $filtreOptions['objets'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $objetOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($objetOption); ?>" <?php if(request('objet') === $objetOption): echo 'selected'; endif; ?>>
                            <?php echo e($objetOption); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="convocation-filter-metier">Métier</label>
                <select class="form-control" id="convocation-filter-metier" name="metier">
                    <option value="">Tous les métiers</option>
                    <?php $__currentLoopData = $filtreOptions['metiers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metierOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($metierOption); ?>" <?php if(request('metier') === $metierOption): echo 'selected'; endif; ?>>
                            <?php echo e($metierOption); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="convocation-filter-centre">Centre</label>
                <select class="form-control" id="convocation-filter-centre" name="centre">
                    <option value="">Tous les centres</option>
                    <?php $__currentLoopData = $filtreOptions['centres'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $centreOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($centreOption); ?>" <?php if(request('centre') === $centreOption): echo 'selected'; endif; ?>>
                            <?php echo e($centreOption); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="actions-group">
                <button class="btn-secondary" type="submit">
                    Filtrer
                </button>

                <?php if(request()->hasAny(['date', 'objet', 'metier', 'centre'])): ?>
                    <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations')); ?>">
                        Réinitialiser
                    </a>
                <?php endif; ?>
            </div>

        </form>

    

        <section class="table-card">

            <div class="table-responsive">
                <table class="table" id="convocationsTable">

                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" data-select-all aria-label="Tout sélectionner">
                            </th>
                            <th>Objet</th>
                            <th>Centre</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th class="actions-cell">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $centresLignes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ligne): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <?php if(! empty($ligne['convocation_id'])): ?>
                                        <input type="checkbox" data-row-checkbox value="<?php echo e($ligne['convocation_id']); ?>" aria-label="Sélectionner cette ligne">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($ligne['objet'] ?? '—'); ?></td>
                                <td><?php echo e($ligne['centre'] ?? '—'); ?></td>
                                <td><?php echo e($ligne['date_debut'] ? \Illuminate\Support\Carbon::parse($ligne['date_debut'])->format('d/m/Y') : '—'); ?></td>
                                <td><?php echo e($ligne['date_fin'] ? \Illuminate\Support\Carbon::parse($ligne['date_fin'])->format('d/m/Y') : '—'); ?></td>
                                <td>
                                    <?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'statut-convocation','statut' => $ligne['statut'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'statut-convocation','statut' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ligne['statut'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $attributes = $__attributesOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__attributesOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $component = $__componentOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__componentOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <div class="table-actions-inline">
                                        <?php if(! empty($ligne['centre_id'])): ?>
                                            <a class="table-action" href="<?php echo e(route('indemnites.convocations.centres.show', [$ligne['convocation_id'], $ligne['centre_id']])); ?>">
                                                Voir
                                            </a>
                                            <a class="table-action" href="<?php echo e(route('indemnites.convocations.centres.edit', [$ligne['convocation_id'], $ligne['centre_id']])); ?>">
                                                Modifier
                                            </a>
                                        <?php else: ?>
                                            <a class="table-action" href="<?php echo e(route('indemnites.convocations.show', $ligne['convocation_id'])); ?>">
                                                Voir
                                            </a>
                                            <a class="table-action" href="<?php echo e(route('indemnites.convocations.edit', $ligne['convocation_id'])); ?>">
                                                Modifier
                                            </a>
                                        <?php endif; ?>
                                        <?php if(! empty($ligne['centre_id'])): ?>
                                            
                                            <?php
                                                $messageConfirmation = ! empty($ligne['dernier_centre'])
                                                    ? 'Supprimer le centre « '.($ligne['centre'] ?? '—').' » ? C\'est le dernier centre de la convocation « '.($ligne['objet'] ?? '—').' » : la convocation entière sera donc supprimée aussi.'
                                                    : 'Supprimer le centre « '.($ligne['centre'] ?? '—').' » de la convocation « '.($ligne['objet'] ?? '—').' » ? Les autres centres de cette convocation ne seront pas supprimés.';
                                            ?>
                                            <form
                                                method="POST"
                                                action="<?php echo e(route('indemnites.convocations.centres.destroy', [$ligne['convocation_id'], $ligne['centre_id']])); ?>"
                                                onsubmit="return confirm(<?php echo e(\Illuminate\Support\Js::from($messageConfirmation)); ?>);"
                                                style="display:inline;"
                                            >
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="table-action danger" type="submit">
                                                    Supprimer
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <?php
                                                $messageConfirmation = 'Supprimer définitivement la convocation « '.($ligne['objet'] ?? '—').' » (aucun centre n\'y est rattaché) ?';
                                            ?>
                                            <form
                                                method="POST"
                                                action="<?php echo e(route('indemnites.convocations.destroy', $ligne['convocation_id'])); ?>"
                                                onsubmit="return confirm(<?php echo e(\Illuminate\Support\Js::from($messageConfirmation)); ?>);"
                                                style="display:inline;"
                                            >
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="table-action danger" type="submit">
                                                    Supprimer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <?php if(empty($centresLignes)): ?>
                
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

    </section>

</main>

<?php $__env->stopSection(); ?>



<?php $__env->startPush('styles'); ?>
<style>


    .modal-modele-link {
        display: block;
        width: 100%;
        text-align: center;
        margin-bottom: 18px;
        color: #ffffff;
        background: var(--blue, #2563eb);
        border-color: var(--blue, #2563eb);
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

    .import-panel-form .actions-group {
        margin: 0;
    }

    .import-panel-form .actions-group .btn-primary {
        width: 100%;
    }

    .checkbox-cell {
        width: 40px;
        text-align: center;
    }

  
    #convocationFilterForm .actions-group {
        grid-column: 1 / -1;
        justify-content: flex-end;
        margin-top: 2px;
    }

    #convocationFilterForm .actions-group .btn-secondary {
        min-height: 30px;
        padding: 5px 12px;
        font-size: 12.5px;
    }

    
    .convocation-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 14px 18px;
        border-top: 1px solid var(--border-soft);
    }

    .btn-bulk-delete {
        color: #b91c1c;
        border-color: #fca5a5;
    }

    .btn-bulk-delete:disabled {
        color: inherit;
        border-color: inherit;
        opacity: 0.5;
        cursor: not-allowed;
    }

</style>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('scripts'); ?>


<script>
    (function () {
        "use strict";

        var formulaireFiltres = document.querySelector("[data-auto-submit]");

        if (formulaireFiltres) {
            formulaireFiltres.querySelectorAll("select, input").forEach(function (champ) {
                champ.addEventListener("change", function () {
                    formulaireFiltres.submit();
                });
            });
        }
    })();
</script>


<script>
    (function () {
        "use strict";

        var formulaireImport = document.querySelector("[data-import-form]");
        var boutonImport = document.querySelector("[data-import-submit]");

        if (!formulaireImport || !boutonImport) {
            return;
        }

        // Verrou explicite (pas seulement "disabled" sur le bouton) :
        // bloque tout second envoi meme si le style "disabled" n'a pas eu
        // le temps de s'afficher (double-clic tres rapide, navigateur lent
        // a repeindre) - le premier "submit" pose le verrou, tout "submit"
        // suivant tant qu'il est pose est annule avant meme de partir.
        var envoiEnCours = false;

        formulaireImport.addEventListener("submit", function (event) {
            if (envoiEnCours) {
                event.preventDefault();

                return;
            }

            envoiEnCours = true;

            boutonImport.disabled = true;
            boutonImport.setAttribute("aria-busy", "true");
            boutonImport.dataset.labelOriginal = boutonImport.innerHTML;
            boutonImport.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Import en cours…';
        });
    })();
</script>

.


<script>
    (function () {
        "use strict";

        var selectAll = document.querySelector("[data-select-all]");
        var deleteButton = document.querySelector("[data-bulk-delete-button]");

        function getRowCheckboxes() {
            return Array.prototype.slice.call(
                document.querySelectorAll("[data-row-checkbox]"),
            );
        }

        function updateState() {
            var boxes = getRowCheckboxes();
            var checked = boxes.filter(function (box) {
                return box.checked;
            });

            if (deleteButton) {
                deleteButton.disabled = checked.length === 0;
                deleteButton.textContent =
                    checked.length > 0
                        ? "Supprimer la sélection (" + checked.length + ")"
                        : "Supprimer la sélection";
            }

            if (selectAll) {
                selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener("change", function () {
                getRowCheckboxes().forEach(function (box) {
                    box.checked = selectAll.checked;
                });

                updateState();
            });
        }

        document.addEventListener("change", function (event) {
            if (event.target.matches && event.target.matches("[data-row-checkbox]")) {
                updateState();
            }
        });

        if (deleteButton) {
            deleteButton.addEventListener("click", function () {
                var ids = getRowCheckboxes()
                    .filter(function (box) {
                        return box.checked;
                    })
                    .map(function (box) {
                        return box.value;
                    });

                if (ids.length === 0) {
                    return;
                }

                var confirmation =
                    ids.length === 1
                        ? "Supprimer définitivement cette convocation ?"
                        : "Supprimer définitivement ces " + ids.length + " convocations ?";

                if (!window.confirm(confirmation)) {
                    return;
                }

                var form = document.createElement("form");
                form.method = "POST";
                form.action = "<?php echo e(route('indemnites.convocations.destroy-multiple')); ?>";
                form.style.display = "none";

                var csrf = document.createElement("input");
                csrf.type = "hidden";
                csrf.name = "_token";
                csrf.value = "<?php echo e(csrf_token()); ?>";
                form.appendChild(csrf);

                var methode = document.createElement("input");
                methode.type = "hidden";
                methode.name = "_method";
                methode.value = "DELETE";
                form.appendChild(methode);

                ids.forEach(function (id) {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "ids[]";
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        }

        updateState();
    })();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\indemnites\convocations\index.blade.php ENDPATH**/ ?>