<?php
    $messages = [
        'success' => session('success'),
        'warning' => session('warning'),
        'error' => session('error'),
        'info' => session('info'),
    ];
?>

<div class="server-flashes" aria-live="polite" aria-atomic="true">
  <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($message): ?>
      <div class="server-flash server-flash-<?php echo e($type); ?>" role="status">
        <i class="fa-solid <?php echo e($type === 'success' ? 'fa-circle-check' : ($type === 'warning' ? 'fa-triangle-exclamation' : ($type === 'error' ? 'fa-circle-xmark' : 'fa-circle-info'))); ?>" aria-hidden="true"></i>
        <span><?php echo e($message); ?></span>
      </div>
    <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\Users\Baye Saliou DIAW\Downloads\SICORE-Frontend-Laravel12-Mode-Test-Complet\sicore-front-laravel-mode-test\resources\views/components/flash-messages.blade.php ENDPATH**/ ?>