<?php $__env->startSection('title', 'SICORE - Suivi des envois'); ?>

<?php $__env->startSection('content'); ?>

<main class="main-content">

<?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Suivi des envois','subtitle' => 'Indemnites > Convocations > Suivi','icon' => 'fa-solid fa-paper-plane']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Suivi des envois','subtitle' => 'Indemnites > Convocations > Suivi','icon' => 'fa-solid fa-paper-plane']); ?>
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

    <div class="stats-grid three">

        <article class="stat-card">
            <div>
                <p class="stat-label">Envois</p>
                <p class="stat-value"><?php echo e($stats['total'] ?? 0); ?></p>
            </div>
            <span class="stat-icon green">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </span>
        </article>

        <article class="stat-card">
            <div>
                <p class="stat-label">Envoyés</p>
                <p class="stat-value"><?php echo e($stats['envoye'] ?? 0); ?></p>
            </div>
            <span class="stat-icon blue">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </span>
        </article>

        <article class="stat-card">
            <div>
                <p class="stat-label">Échecs</p>
                <p class="stat-value"><?php echo e($stats['echec'] ?? 0); ?></p>
            </div>
            <span class="stat-icon red">
                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
            </span>
        </article>

    </div>

    <div class="actions-row">
        <p class="breadcrumb">Gestion des indemnités &gt; Convocations &gt; Suivi</p>
        <div class="actions-group">
            <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations.show', $id)); ?>">
                Retour à la convocation
            </a>
            <form method="POST" action="<?php echo e(route('indemnites.convocations.relancer', $id)); ?>">
                <?php echo csrf_field(); ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $envois; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $envoi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php echo e(trim(($envoi['enseignant']['prenom'] ?? '') . ' ' . ($envoi['enseignant']['nom'] ?? '')) ?: '—'); ?>

                            </td>
                            <td><?php echo e(ucfirst($envoi['canal'] ?? '—')); ?></td>
                            <td><?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'statut-envoi','statut' => $envoi['statut'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'statut-envoi','statut' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($envoi['statut'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $attributes = $__attributesOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__attributesOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $component = $__componentOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__componentOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?></td>
                            <td><?php echo e($envoi['date_envoi'] ?? '—'); ?></td>
                            <td><?php echo e($envoi['message'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(empty($envois)): ?>
            <p class="empty-message">Aucun envoi enregistré pour cette convocation.</p>
        <?php endif; ?>

    </section>

</section>

</main>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\indemnites\convocations\suivi.blade.php ENDPATH**/ ?>