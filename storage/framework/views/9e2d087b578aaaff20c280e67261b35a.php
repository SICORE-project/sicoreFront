

<?php $__env->startSection('title', 'SICORE - Permissions'); ?>

<?php $__env->startSection('content'); ?>
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Permissions','subtitle' => 'Gestion Utilisateur > Permissions','icon' => 'fa-solid fa-lock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Permissions','subtitle' => 'Gestion Utilisateur > Permissions','icon' => 'fa-solid fa-lock']); ?>
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

    <?php
        $permissionsData = $permissions['data'] ?? [];

        // Comptage réel par groupe, sans supposer les noms à l'avance
        $countByGroupe = collect($permissionsData)
            ->countBy(fn ($p) => $p['groupe'] ?? 'Non classé')
            ->sortDesc();

        // On prend les 3 groupes les plus fréquents pour les 3 cartes secondaires
        $topGroupes = $countByGroupe->take(3);

        $iconsCycle = ['fa-solid fa-eye', 'fa-solid fa-check-double', 'fa-solid fa-shield-halved'];
        $colorsCycle = ['green', 'yellow', 'red'];
    ?>

    <section class="content-area">
        <!-- Objectifs métier -->
        <section class="objective-card">
            <h2>Objectifs métier</h2>
            <ul class="objective-list">
                <li>Visualiser les droits par module.</li>
                <li>Séparer les permissions de consultation, saisie, validation et administration.</li>
                <li>Préparer les paramètres sans inventer de backend.</li>
            </ul>
        </section>

        <!-- Statistiques -->
        <div class="stats-grid four">
            <article class="stat-card">
                <div>
                    <p class="stat-label">Permissions</p>
                    <p class="stat-value"><?php echo e(count($permissionsData)); ?></p>
                    <p class="stat-note">Droits définis</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-lock"></i>
                </span>
            </article>

            <?php $__currentLoopData = $topGroupes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupeNom => $groupeCount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="stat-card">
                    <div>
                        <p class="stat-label"><?php echo e(ucfirst($groupeNom)); ?></p>
                        <p class="stat-value"><?php echo e($groupeCount); ?></p>
                        <p class="stat-note">Permissions</p>
                    </div>
                    <span class="stat-icon <?php echo e($colorsCycle[$loop->index] ?? 'blue'); ?>">
                        <i class="<?php echo e($iconsCycle[$loop->index] ?? 'fa-solid fa-circle'); ?>"></i>
                    </span>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Actions -->
        <div class="actions-row">
            <p class="breadcrumb">Gestion Utilisateur > Permissions</p>
            <div class="actions-group">
                <a href="<?php echo e(route('admin.permissions.create')); ?>" class="btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle permission
                </a>
                <button class="btn-secondary" type="button">Exporter</button>
                <a href="<?php echo e(route('admin.permissions.sync')); ?>" class="btn-warning" style="background: #f59e0b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-sync"></i> Synchroniser
                </a>
            </div>
        </div>

        <!-- Filtres -->
        <section class="filter-panel" aria-label="Filtres">
            <div class="form-group">
                <label for="filter-module">Module</label>
                <select class="form-control" id="filter-module">
                    <option value="">Tous</option>
                    <?php $__currentLoopData = collect($permissionsData)->pluck('module')->filter()->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($module); ?>"><?php echo e($module); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter-permission">Permission</label>
                <select class="form-control" id="filter-permission">
                    <option value="">Tous</option>
                    <?php $__currentLoopData = collect($permissionsData)->pluck('nom')->filter()->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($nom); ?>"><?php echo e($nom); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="actions-group">
                <button class="btn-secondary" type="button" id="btn-filtrer">Filtrer</button>
                <button class="btn-secondary" type="button" id="btn-reset-filtres">Réinitialiser</button>
            </div>
        </section>

        <!-- Tableau -->
        <section class="table-card">
            <div class="table-responsive">
                <table class="table" id="moduleTable">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Permission</th>
                            <th>Groupe</th>
                            <th>Statut</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $permissionsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr data-module="<?php echo e($permission['module'] ?? ''); ?>" data-permission="<?php echo e($permission['nom'] ?? ''); ?>">
                            <td><?php echo e($permission['module'] ?? '-'); ?></td>
                            <td><strong><?php echo e($permission['nom'] ?? '-'); ?></strong></td>
                            <td><?php echo e($permission['groupe'] ?? '-'); ?></td>
                            <td>
                                <span class="badge <?php echo e(($permission['est_actif'] ?? false) ? 'badge-success' : 'badge-danger'); ?>">
                                    <?php echo e(($permission['est_actif'] ?? false) ? 'Actif' : 'Inactif'); ?>

                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <a href="<?php echo e(route('admin.permissions.edit', $permission['id'])); ?>" 
                                       class="action-btn" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.permissions.destroy', $permission['id'])); ?>" method="POST" style="display: inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="action-btn delete" 
                                                onclick="return confirm('Supprimer cette permission ?')" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <i class="fas fa-inbox" style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                Aucune permission trouvée
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="empty-message" id="empty-message-filtre" style="display: none;">Aucun résultat pour ce filtre.</p>
            <p class="empty-message">Aucune donnée trouvée.</p>
            <div class="pagination" aria-label="Pagination">
                <?php if(!empty($permissions['links'])): ?>
                    <?php $__currentLoopData = $permissions['links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($link['url']): ?>
                            <a href="<?php echo e($link['url']); ?>" class="page-btn <?php echo e($link['active'] ? 'active' : ''); ?>">
                                <?php echo $link['label']; ?>

                            </a>
                        <?php else: ?>
                            <span class="page-btn disabled"><?php echo $link['label']; ?></span>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <button class="page-btn" type="button">←</button>
                    <button class="page-btn active" type="button">1</button>
                    <button class="page-btn" type="button">2</button>
                    <button class="page-btn" type="button">→</button>
                <?php endif; ?>
            </div>
        </section>
    </section>

    <?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            const btnFiltrer = document.getElementById('btn-filtrer');
            const btnReset = document.getElementById('btn-reset-filtres');
            const selectModule = document.getElementById('filter-module');
            const selectPermission = document.getElementById('filter-permission');
            const rows = document.querySelectorAll('#moduleTable tbody tr[data-module]');
            const emptyMessage = document.getElementById('empty-message-filtre');

            function applyFilters() {
                const module = selectModule.value;
                const permission = selectPermission.value;
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchModule = !module || row.dataset.module === module;
                    const matchPermission = !permission || row.dataset.permission === permission;
                    const visible = matchModule && matchPermission;

                    row.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            if (btnFiltrer) {
                btnFiltrer.addEventListener('click', applyFilters);
            }

            if (btnReset) {
                btnReset.addEventListener('click', function () {
                    selectModule.value = '';
                    selectPermission.value = '';
                    applyFilters();
                });
            }
        })();
    </script>
    <?php $__env->stopPush(); ?>
</main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\SICORE\sicoreFront\resources\views/pages/administration/permissions.blade.php ENDPATH**/ ?>