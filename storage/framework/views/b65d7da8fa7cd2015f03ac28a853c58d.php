<?php $__env->startSection('title', 'SICORE - Détails du rôle'); ?>

<?php $__env->startSection('content'); ?>
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Détails du rôle','subtitle' => 'Gestion Utilisateur > Profils / Rôles > Détails','icon' => 'fa-solid fa-users-cog']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Détails du rôle','subtitle' => 'Gestion Utilisateur > Profils / Rôles > Détails','icon' => 'fa-solid fa-users-cog']); ?>
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
        <section class="table-card" style="padding: 24px;">

            <?php if(session('success')): ?>
                <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Informations du rôle -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Nom du profil</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px; font-weight: 500;">
                        <?php echo e($role['nom'] ?? '-'); ?>

                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Slug</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <code><?php echo e($role['slug'] ?? '-'); ?></code>
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Niveau d'accès</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <span class="badge 
                            <?php if($role['niveau'] == 'systeme'): ?> badge-danger
                            <?php elseif($role['niveau'] == 'admin_metier'): ?> badge-purple
                            <?php elseif($role['niveau'] == 'gestionnaire'): ?> badge-info
                            <?php else: ?> badge-secondary <?php endif; ?>">
                            <?php echo e(ucfirst($role['niveau'] ?? '')); ?>

                        </span>
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Statut</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <span class="badge <?php echo e(($role['est_actif'] ?? false) ? 'badge-success' : 'badge-danger'); ?>">
                            <?php echo e(($role['est_actif'] ?? false) ? 'Actif' : 'Inactif'); ?>

                        </span>
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-weight: 600; color: #374151; font-size: 14px;">Description</label>
                <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px; min-height: 40px;">
                    <?php echo e($role['description'] ?? 'Aucune description'); ?>

                </p>
            </div>

            <!-- Permissions associées -->
            <div style="margin-bottom: 24px;">
                <label style="font-weight: 600; color: #374151; font-size: 14px;">Permissions associées</label>
                <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
                    <?php if(isset($role['permissions']) && count($role['permissions']) > 0): ?>
                        <?php $__currentLoopData = $role['permissions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 12px; font-size: 13px; display: inline-block;">
                                <?php echo e($permission['nom'] ?? $permission['name'] ?? 'Permission'); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p style="color: #6b7280; font-size: 14px;">Aucune permission associée</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Créé le</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <?php echo e(isset($role['created_at']) ? date('d/m/Y à H:i', strtotime($role['created_at'])) : '-'); ?>

                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Dernière modification</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <?php echo e(isset($role['updated_at']) ? date('d/m/Y à H:i', strtotime($role['updated_at'])) : '-'); ?>

                    </p>
                </div>
            </div>

            <!-- Boutons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn-secondary" style="padding: 10px 20px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; background: #f9fafb; text-decoration: none; transition: all 0.2s;">
                    Retour
                </a>
            </div>

        </section>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\administration\roles-show.blade.php ENDPATH**/ ?>