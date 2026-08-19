<?php $__env->startSection('title', 'SICORE - Fiche utilisateur'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $access = data_get($user, 'acces_organisationnel', []);
  $role = data_get($user, 'role.nom', data_get($user, 'role', '—'));
  $structure = data_get($access, 'lieu_service', data_get($access, 'ief', data_get($access, 'ia', data_get($access, 'structure'))));
  $structureLabel = is_array($structure) ? collect([data_get($structure, 'code'), data_get($structure, 'libelle', data_get($structure, 'nom')])->filter()->join(' — ') : ($structure ?: '—');
  $type = data_get($access, 'niveau', data_get($access, 'type_structure', data_get($access, 'ief') ? 'IEF' : (data_get($access, 'ia') ? 'IA' : 'National')));
?>
<main class="main-content"><?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Fiche utilisateur','subtitle' => 'Administration > Utilisateurs','icon' => 'fa-solid fa-user-shield']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Fiche utilisateur','subtitle' => 'Administration > Utilisateurs','icon' => 'fa-solid fa-user-shield']); ?>
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
<section class="content-area"><section class="panel"><div class="panel-header"><div><h2><?php echo e(trim(data_get($user, 'prenom').' '.data_get($user, 'nom'))); ?></h2><p><?php echo e(data_get($user, 'email')); ?></p></div><div class="actions-group"><a class="btn-secondary" href="<?php echo e(route('utilisateurs.index')); ?>">Retour</a><a class="btn-primary" href="<?php echo e(route('utilisateurs.edit', data_get($user, 'id'))); ?>">Modifier</a></div></div>
<div class="form-grid form-grid--balanced"><div class="form-group"><strong>Rôle</strong><p><?php echo e(is_array($role) ? data_get($role, 'nom', '—') : $role); ?></p></div><div class="form-group"><strong>Statut</strong><p><?php echo e(data_get($user, 'statut', data_get($user, 'status', '—'))); ?></p></div><div class="form-group"><strong>Type de structure</strong><p><?php echo e(strtoupper((string) $type)); ?></p></div><div class="form-group"><strong>Rattachement organisationnel</strong><p><?php echo e($structureLabel); ?></p></div></div>
</section></section></main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\administration\utilisateurs\show.blade.php ENDPATH**/ ?>