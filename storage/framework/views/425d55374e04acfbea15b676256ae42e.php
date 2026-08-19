<?php $__env->startSection('title', 'SICORE - Modifier un rôle'); ?>

<?php $__env->startSection('content'); ?>
<main class="main-content">
    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Modifier le rôle','subtitle' => 'Gestion Utilisateur > Profils / Rôles > Modifier','icon' => 'fa-solid fa-user-pen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Modifier le rôle','subtitle' => 'Gestion Utilisateur > Profils / Rôles > Modifier','icon' => 'fa-solid fa-user-pen']); ?>
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

            <form action="<?php echo e(route('admin.roles.update', $role['id'])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="filter-panel" aria-label="Informations du rôle" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" value="<?php echo e($role['nom']); ?>" required class="form-control">
                        <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p style="color:#dc2626; font-size:12px; margin-top:4px;"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control"><?php echo e($role['description']); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p style="color:#dc2626; font-size:12px; margin-top:4px;"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="filter-panel" aria-label="Niveau et statut" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="niveau">Niveau *</label>
                        <select id="niveau" name="niveau" class="form-control" required>
                            <option value="systeme" <?php if($role['niveau'] === 'systeme'): echo 'selected'; endif; ?>>Système</option>
                            <option value="admin_metier" <?php if($role['niveau'] === 'admin_metier'): echo 'selected'; endif; ?>>Admin Métier</option>
                            <option value="gestionnaire" <?php if($role['niveau'] === 'gestionnaire'): echo 'selected'; endif; ?>>Gestionnaire</option>
                            <option value="consultation" <?php if($role['niveau'] === 'consultation'): echo 'selected'; endif; ?>>Consultation</option>
                        </select>
                        <?php $__errorArgs = ['niveau'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p style="color:#dc2626; font-size:12px; margin-top:4px;"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label for="est_actif">Statut *</label>
                        <select id="est_actif" name="est_actif" class="form-control" required>
                            <option value="1" <?php if($role['est_actif']): echo 'selected'; endif; ?>>Actif</option>
                            <option value="0" <?php if(!$role['est_actif']): echo 'selected'; endif; ?>>Inactif</option>
                        </select>
                    </div>
                </div>

                <hr style="margin: 24px 0; border-color: #e5e7eb;">

                <h3 style="font-weight: 700; font-size: 1.125rem; margin-bottom: 16px;">Permissions</h3>

                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="objective-card" style="margin-bottom: 16px;">
                        <h4 style="font-weight: 700; color: #2563eb; margin-bottom: 8px;"><?php echo e(ucfirst($module)); ?></h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
                            <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="permissions[]" value="<?php echo e($permission['id']); ?>"
                                        <?php if(in_array($permission['id'], $rolePermissions ?? [])): echo 'checked'; endif; ?>>
                                    <span style="font-size: 14px;"><?php echo e($permission['nom']); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="actions-group" style="justify-content: flex-end; margin-top: 24px;">
                    <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn-secondary">Retour</a>
                    <button type="submit" class="btn-primary">Mettre à jour</button>
                </div>
            </form>

        </section>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\administration\roles-edit.blade.php ENDPATH**/ ?>