<?php
    $navigation = config('navigation', []);
    $user = session('sicore_user', []);
    $userName = data_get($user, 'name', data_get($user, 'email', 'Utilisateur SICORE'));
    $userRole = data_get($user, 'role', data_get($user, 'roles.0.name', 'Utilisateur'));
?>

<aside class="sidebar" id="sidebar" aria-label="Menu principal SICORE">
  <div class="sidebar-header">
    <a class="sidebar-logo" href="<?php echo e(route('dashboard')); ?>" data-tooltip="SICORE" title="SICORE" aria-label="Accueil SICORE">
      <span class="sidebar-logo-mark">
        <img src="<?php echo e(asset('assets/images/image-fcfa.png')); ?>" alt="Logo SICORE">
      </span>
      <span class="sidebar-logo-text">SICORE</span>
    </a>
  </div>

  <nav class="sidebar-nav" aria-label="Navigation principale">
    <div class="sidebar-section">
      <?php $__currentLoopData = $navigation; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $patterns = (array) ($item['active'] ?? $item['route'] ?? []);
            $groupIsActive = collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
        ?>

        <?php if(($item['type'] ?? 'link') === 'link'): ?>
          <a
            class="sidebar-link <?php echo e($groupIsActive ? 'active' : ''); ?>"
            href="<?php echo e(route($item['route'])); ?>"
            data-page-link
            data-tooltip="<?php echo e($item['label']); ?>"
            title="<?php echo e($item['label']); ?>"
            aria-label="<?php echo e($item['label']); ?>"
            <?php if($groupIsActive): ?> aria-current="page" <?php endif; ?>
          >
            <span class="nav-icon"><i class="<?php echo e($item['icon']); ?>" aria-hidden="true"></i></span>
            <span class="nav-label"><?php echo e($item['label']); ?></span>
          </a>
        <?php else: ?>
          <button
            class="sidebar-link <?php echo e($groupIsActive ? 'active' : ''); ?>"
            type="button"
            data-submenu-toggle
            data-tooltip="<?php echo e($item['label']); ?>"
            title="<?php echo e($item['label']); ?>"
            aria-label="<?php echo e($item['label']); ?>"
            aria-expanded="<?php echo e($groupIsActive ? 'true' : 'false'); ?>"
          >
            <span class="nav-icon"><i class="<?php echo e($item['icon']); ?>" aria-hidden="true"></i></span>
            <span class="nav-label"><?php echo e($item['label']); ?></span>
            <span class="chevron"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
          </button>

          <div class="sidebar-submenu <?php echo e($groupIsActive ? 'open' : ''); ?>">
            <?php $__currentLoopData = $item['links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                  $linkIsActive = request()->routeIs($link['route'])
                      && (! isset($link['fragment']) || request()->fullUrlIs('*#'.$link['fragment']));
                  $href = route($link['route']).(isset($link['fragment']) ? '#'.$link['fragment'] : '');
              ?>
              <a
                class="<?php echo e($linkIsActive ? 'active' : ''); ?>"
                href="<?php echo e($href); ?>"
                data-page-link
                title="<?php echo e($link['label']); ?>"
                aria-label="<?php echo e($link['label']); ?>"
                <?php if($linkIsActive): ?> aria-current="page" <?php endif; ?>
              >
                <span class="nav-icon submenu-icon"><i class="<?php echo e($link['icon']); ?>" aria-hidden="true"></i></span>
                <span class="submenu-label"><?php echo e($link['label']); ?></span>
              </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card" title="<?php echo e($userName); ?> — <?php echo e($userRole); ?>">
      <span class="avatar" aria-hidden="true"><i class="fa-solid fa-user-shield"></i></span>
      <div>
        <p class="user-name"><?php echo e($userName); ?></p>
        <p class="user-role"><?php echo e($userRole); ?></p>
      </div>
    </div>

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="sidebar-logout-form">
      <?php echo csrf_field(); ?>
      <button class="logout-btn" type="submit" data-tooltip="Déconnexion" title="Déconnexion" aria-label="Déconnexion">
        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        <span class="logout-label">Déconnexion</span>
      </button>
    </form>
  </div>
</aside>
<?php /**PATH C:\Users\Baye Saliou DIAW\Downloads\SICORE-Frontend-Laravel12-Mode-Test-Complet\sicore-front-laravel-mode-test\resources\views/components/sidebar.blade.php ENDPATH**/ ?>