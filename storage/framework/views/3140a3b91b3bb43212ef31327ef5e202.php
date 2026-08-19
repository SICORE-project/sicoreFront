<?php $__env->startSection('title', 'SICORE - Modifier un utilisateur'); ?>
<?php $__env->startSection('content'); ?>
<?php $access = data_get($user, 'acces_organisationnel', []); ?>
<main class="main-content"><?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Modifier l’utilisateur','subtitle' => 'Administration > Utilisateurs','icon' => 'fa-solid fa-user-pen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Modifier l’utilisateur','subtitle' => 'Administration > Utilisateurs','icon' => 'fa-solid fa-user-pen']); ?>
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
<section class="content-area"><section class="panel"><form class="teacher-form" method="POST" action="<?php echo e(route('utilisateurs.update', data_get($user, 'id'))); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<?php if($errors->any()): ?><div class="alert alert-danger" role="alert"><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
<div class="form-grid form-grid--balanced">
<div class="form-group"><label for="nom">Nom</label><input class="form-control" id="nom" name="nom" value="<?php echo e(old('nom', data_get($user, 'nom'))); ?>" required></div>
<div class="form-group"><label for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" value="<?php echo e(old('prenom', data_get($user, 'prenom'))); ?>" required></div>
<div class="form-group"><label for="email">E-mail</label><input class="form-control" id="email" type="email" name="email" value="<?php echo e(old('email', data_get($user, 'email'))); ?>" required></div>
<div class="form-group"><label for="role_id">Rôle</label><select class="form-control" id="role_id" name="role_id" required><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($role['id']); ?>" <?php if((string) old('role_id', data_get($user, 'role.id', data_get($user, 'role_id'))) === (string) $role['id']): echo 'selected'; endif; ?>><?php echo e($role['nom']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="form-group"><label for="statut">Statut</label><select class="form-control" id="statut" name="statut"><option value="actif" <?php if(old('statut', data_get($user, 'statut')) === 'actif'): echo 'selected'; endif; ?>>Actif</option><option value="inactif" <?php if(old('statut', data_get($user, 'statut')) === 'inactif'): echo 'selected'; endif; ?>>Inactif</option></select></div>
<div class="form-group full"><strong>Rattachement organisationnel</strong><small>Renseignez une structure nationale, ou une IA et éventuellement une IEF.</small></div>
<div class="form-group"><label for="structure_organisationnelle_id">ID structure nationale</label><input class="form-control" id="structure_organisationnelle_id" type="number" name="structure_organisationnelle_id" value="<?php echo e(old('structure_organisationnelle_id', data_get($access, 'structure_organisationnelle_id', data_get($access, 'structure.id')))); ?>"></div>
<div class="form-group"><label for="ia_id">ID IA</label><input class="form-control" id="ia_id" type="number" name="ia_id" value="<?php echo e(old('ia_id', data_get($access, 'ia_id', data_get($access, 'ia.id')))); ?>"></div>
<div class="form-group"><label for="ief_id">ID IEF</label><input class="form-control" id="ief_id" type="number" name="ief_id" value="<?php echo e(old('ief_id', data_get($access, 'ief_id', data_get($access, 'ief.id')))); ?>"></div>
</div><div class="actions-group"><a class="btn-secondary" href="<?php echo e(route('utilisateurs.show', data_get($user, 'id'))); ?>">Annuler</a><button class="btn-primary" type="submit">Enregistrer</button></div></form></section></section></main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\administration\utilisateurs\edit.blade.php ENDPATH**/ ?>