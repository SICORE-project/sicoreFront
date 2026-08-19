

<?php $__env->startSection('title', 'SICORE - Tableau de bord'); ?>
<?php $__env->startSection('content'); ?>
<main class="main-content">
    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Tableau de bord','subtitle' => 'Vue d’ensemble des paramètres et de la paie','icon' => 'fa-solid fa-gauge-high','searchId' => 'dashboardSearch','searchPlaceholder' => 'Rechercher…']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tableau de bord','subtitle' => 'Vue d’ensemble des paramètres et de la paie','icon' => 'fa-solid fa-gauge-high','search-id' => 'dashboardSearch','search-placeholder' => 'Rechercher…']); ?>
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
      <section class="objective-card <?php echo e($isScoped ? 'sensitive-panel' : ''); ?>">
        <h2><?php echo e($isScoped ? 'Tableau de bord de votre structure' : 'Tableau de bord global'); ?></h2>
        <p>Les indicateurs et listes sont limites a : <strong><?php echo e($scopeLabel); ?></strong>.</p>
      </section>
      <div class="stats-grid four">
        <?php $__currentLoopData = [
          ['label' => 'Utilisateurs', 'key' => 'utilisateurs', 'icon' => 'fa-solid fa-users', 'color' => 'green'],
          ['label' => 'Enseignants', 'key' => 'enseignants', 'icon' => 'fa-solid fa-chalkboard-user', 'color' => 'blue'],
          ['label' => 'Dossiers en cours', 'key' => 'dossiers_en_cours', 'icon' => 'fa-solid fa-folder-open', 'color' => 'yellow'],
          ['label' => 'Alertes', 'key' => 'alertes', 'icon' => 'fa-solid fa-triangle-exclamation', 'color' => 'red'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <article class="stat-card"><div><p class="stat-label"><?php echo e($stat['label']); ?></p><p class="stat-value"><?php echo e(data_get($metrics, $stat['key'], 0)); ?></p><p class="stat-note"><?php echo e($scopeLabel); ?></p></div><span class="stat-icon <?php echo e($stat['color']); ?>"><i class="<?php echo e($stat['icon']); ?>" aria-hidden="true"></i></span></article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <div class="dashboard-grid">
        <section class="panel">
          <div class="panel-header">
            <div>
              <h2>Indicateurs principaux</h2>
              <p>Suivi visuel de l&#39;execution</p>
            </div>
          </div>
          <div class="chart-panel">
            <div class="metric-circle">
              <strong>24</strong>
              <small>Parametres</small>
            </div>
            <div class="donut-metric"><span>75%</span></div>
            <div class="canvas-wrap">
              <canvas data-chart="main-donut" aria-label="Repartition des donnees"></canvas>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-header">
            <div>
              <h2>Activite mensuelle</h2>
              <p>Operations traitees</p>
            </div>
          </div>
          <div class="canvas-card">
            <div class="canvas-wrap">
              <canvas data-chart="main-bars" aria-label="Graphique en barres"></canvas>
            </div>
          </div>
        </section>
      </div>
    </section>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/charts.js')); ?>" defer></script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\dashboard\index.blade.php ENDPATH**/ ?>