<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <meta name="theme-color" content="#087f5b">
  <title><?php echo $__env->yieldContent('title', 'SICORE'); ?></title>

  <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/responsive.css')); ?>">
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body <?php echo $__env->yieldContent('body_attributes'); ?>>
  <?php echo $__env->yieldContent('body'); ?>

  <script src="<?php echo e(asset('assets/js/app.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/app.js'))); ?>" defer></script>
  <script src="<?php echo e(asset('assets/js/notifications.js')); ?>" defer></script>
  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\projets\sicoreFront\resources\views\layouts\base.blade.php ENDPATH**/ ?>