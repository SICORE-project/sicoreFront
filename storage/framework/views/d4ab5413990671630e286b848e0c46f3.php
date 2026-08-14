

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
      <div class="stats-grid">
        <article class="stat-card">
          <div>
            <p class="stat-label">Parametres</p>
            <p class="stat-value">24</p>
            <p class="stat-note">+3 cette semaine</p>
          </div>
          <span class="stat-icon green">&#9881;</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Alertes actives</p>
            <p class="stat-value">5</p>
            <p class="stat-note">+2 nouvelles</p>
          </div>
          <span class="stat-icon red">!</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Cessions</p>
            <p class="stat-value">12</p>
            <p class="stat-note neutral">4 en cours</p>
          </div>
          <span class="stat-icon blue">C</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Groupes IPM</p>
            <p class="stat-value">8</p>
            <p class="stat-note">1 nouveau</p>
          </div>
          <span class="stat-icon purple">IP</span>
        </article>
        <article class="stat-card">
          <div>
            <p class="stat-label">Categories</p>
            <p class="stat-value">15</p>
            <p class="stat-note neutral">2 modifiees</p>
          </div>
          <span class="stat-icon yellow">CA</span>
        </article>
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


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sicoreFront\resources\views/pages/dashboard/index.blade.php ENDPATH**/ ?>