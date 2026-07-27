<?php $__env->startSection('title', 'SICORE - Retenues rappel'); ?>
<?php $__env->startSection('content'); ?>
  <?php if (isset($component)) { $__componentOriginalf02cf1377f3dda091891e2b87c45ebec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf02cf1377f3dda091891e2b87c45ebec = $attributes; } ?>
<?php $component = App\View\Components\ModulePage::resolve(['slug' => 'paie-retenues-rappel'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ModulePage::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf02cf1377f3dda091891e2b87c45ebec)): ?>
<?php $attributes = $__attributesOriginalf02cf1377f3dda091891e2b87c45ebec; ?>
<?php unset($__attributesOriginalf02cf1377f3dda091891e2b87c45ebec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf02cf1377f3dda091891e2b87c45ebec)): ?>
<?php $component = $__componentOriginalf02cf1377f3dda091891e2b87c45ebec; ?>
<?php unset($__componentOriginalf02cf1377f3dda091891e2b87c45ebec); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Baye Saliou DIAW\Downloads\SICORE-Frontend-Laravel12-Mode-Test-Complet\sicore-front-laravel-mode-test\resources\views/pages/paie/retenues-rappel.blade.php ENDPATH**/ ?>