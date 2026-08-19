<?php $__env->startSection('title', 'SICORE - Convocation'); ?>

<?php $__env->startSection('content'); ?>

<main class="main-content">

<?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Détail de la convocation','subtitle' => 'Indemnites > Convocations > Détail','icon' => 'fa-solid fa-envelope-open-text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Détail de la convocation','subtitle' => 'Indemnites > Convocations > Détail','icon' => 'fa-solid fa-envelope-open-text']); ?>
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



    <section class="convocation-page">

        <div class="convocation-page-header">

            <div>
                <h2><?php echo e($convocation->objet ?? '—'); ?></h2>
                <p class="breadcrumb">
                    Émise le <?php echo e(optional($convocation->date_emission)->format('d/m/Y') ?? '—'); ?>

                    <?php if(! empty($convocation->type_convocation['libelle'] ?? null)): ?>
                        &middot; <?php echo e($convocation->type_convocation['libelle']); ?>

                    <?php endif; ?>
                </p>
                <?php if(! empty($centreId)): ?>
                    <p class="breadcrumb">
                        Centre : <?php echo e($convocation->centres->first()['centre'] ?? '—'); ?>

                    </p>
                <?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'statut-convocation','statut' => $convocation->statut ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'statut-convocation','statut' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($convocation->statut ?? null)]); ?>
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

        </div>

        <div class="convocation-page-body">

            

            <div class="form-section">

                <h3>Informations générales</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Type de convocation</label>
                        <p><?php echo e($convocation->type_convocation['libelle'] ?? '—'); ?></p>
                    </div>

                    <div class="form-group">
                        <label>Session</label>
                        <p><?php echo e($convocation->session ?? '—'); ?></p>
                    </div>

                    <div class="form-group">
                        <label>Période</label>
                        <p>
                            <?php if(! empty($convocation->date_debut) && ! empty($convocation->date_fin)): ?>
                                Du <?php echo e(optional($convocation->date_debut)->format('d/m/Y')); ?>

                                au <?php echo e(optional($convocation->date_fin)->format('d/m/Y')); ?>

                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Heure</label>
                        <p><?php echo e($convocation->heure_debut ?? '—'); ?></p>
                    </div>

                    <div class="form-group">
                        <label>Lieu d'examen</label>
                        <p><?php echo e($convocation->lieu_examen ?? '—'); ?></p>
                    </div>

                   

                </div>

            </div>

          

            <div class="form-section">

                <div class="panel-header">
                    <h3>Centres d'examen</h3>
                </div>

                <?php
                    $statutsPersonnel = [
                        'fonctionnaire' => 'Fonctionnaire',
                        'contractuel' => 'Contractuelle',
                        'vacataire' => 'Vacataire',
                    ];
                ?>

                <?php $__empty_1 = true; $__currentLoopData = $centresAvecMetiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $centre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                    <div class="centre-block">

                        <div class="form-grid centre-info-grid">

                            <div class="form-group">
                                <label>Centre</label>
                                <p><?php echo e($centre['centre'] ?? '—'); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Jury</label>
                                <p><?php echo e($centre['jury'] ?? '—'); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Président du jury</label>
                                <p><?php echo e(trim(($centre['president_jury']['prenom'] ?? '') . ' ' . ($centre['president_jury']['nom'] ?? '')) ?: '—'); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Téléphone</label>
                                <p><?php echo e($centre['president_jury_telephone'] ?? '—'); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Chef de centre</label>
                                <p><?php echo e(trim(($centre['chef_centre']['prenom'] ?? '') . ' ' . ($centre['chef_centre']['nom'] ?? '')) ?: '—'); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Téléphone</label>
                                <p><?php echo e($centre['chef_centre_telephone'] ?? '—'); ?></p>
                            </div>

                        </div>

                        <?php $__empty_2 = true; $__currentLoopData = $centre['metiers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>

                            <h4 class="sub-form-subheading"><?php echo e($metier['metier'] ?? '—'); ?></h4>

                            <?php if(empty($metier['beneficiaires'])): ?>

                                <p class="empty-message">Aucun membre ajouté pour ce métier.</p>

                            <?php else: ?>

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
                                            <?php $__currentLoopData = $metier['beneficiaires']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $beneficiaire): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($beneficiaire['nom'] ?? '—'); ?></td>
                                                    <td><?php echo e($beneficiaire['prenom'] ?? '—'); ?></td>
                                                    <td><?php echo e($beneficiaire['pivot']['fonction'] ?? '—'); ?></td>
                                                    <td><?php echo e($statutsPersonnel[$beneficiaire['pivot']['categorie_personnel'] ?? null] ?? '—'); ?></td>
                                                    <td><?php echo e($beneficiaire['pivot']['provenance'] ?? '—'); ?></td>
                                                    <td><?php echo e($beneficiaire['telephone'] ?? '—'); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php endif; ?>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>

                            <p class="empty-message">Aucun métier renseigné pour ce centre.</p>

                        <?php endif; ?>

                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                    <p class="empty-message">Aucun centre renseigné pour cette convocation.</p>

                <?php endif; ?>

            </div>

            

            <div class="form-actions">

                <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations')); ?>">
                    Retour à la liste
                </a>

                <?php if(! empty($centreId)): ?>
                    <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations.show', $id)); ?>">
                        Voir tous les centres
                    </a>
                <?php endif; ?>

                <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations.pdf', $id)); ?>">
                    Télécharger PDF
                </a>

                <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations.suivi', $id)); ?>">
                    Suivi des envois
                </a>

                <button class="btn-secondary" type="button" data-modal-open="envoyer-convocation">
                    Envoyer aux bénéficiaires
                </button>

                <a class="btn-primary" href="<?php echo e(! empty($centreId) ? route('indemnites.convocations.centres.edit', [$id, $centreId]) : route('indemnites.convocations.edit', $id)); ?>">
                    Modifier
                </a>

            </div>

        </div>

    </section>

</section>


<?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'modal','id' => 'envoyer-convocation','title' => 'Envoyer aux bénéficiaires']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'modal','id' => 'envoyer-convocation','title' => 'Envoyer aux bénéficiaires']); ?>

    <form method="POST" action="<?php echo e(route('indemnites.convocations.envoyer', $id)); ?>">
        <?php echo csrf_field(); ?>

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

</main>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\indemnites\convocations\show.blade.php ENDPATH**/ ?>