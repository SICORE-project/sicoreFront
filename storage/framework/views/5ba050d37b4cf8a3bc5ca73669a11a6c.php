<main class="main-content">
  <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => $page['title'],'subtitle' => $page['breadcrumb'],'icon' => $pageIcon,'searchId' => 'moduleSearch','searchPlaceholder' => 'Rechercher…','filterTarget' => '#moduleTable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page['title']),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page['breadcrumb']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pageIcon),'search-id' => 'moduleSearch','search-placeholder' => 'Rechercher…','filter-target' => '#moduleTable']); ?>
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
    <?php if(! empty($page['objectives'])): ?>
      <section class="objective-card <?php echo e(! empty($page['sensitive']) ? 'sensitive-panel' : ''); ?>">
        <h2>Objectifs métier</h2>
        <ul class="objective-list">
          <?php $__currentLoopData = $page['objectives']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $objective): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($objective); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </section>
    <?php endif; ?>

    <div class="stats-grid four">
      <?php $__currentLoopData = $page['stats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="stat-card">
          <div>
            <p class="stat-label"><?php echo e($stat['label']); ?></p>
            <p class="stat-value"><?php echo e($stat['value']); ?></p>
            <p class="stat-note"><?php echo e($stat['note']); ?></p>
          </div>
          <span class="stat-icon <?php echo e($stat['color']); ?>">
            <i class="<?php echo e(str_contains((string) $stat['icon'], 'fa-') ? $stat['icon'] : ($statIconMap[$stat['icon']] ?? 'fa-solid fa-circle')); ?>" aria-hidden="true"></i>
          </span>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="actions-row">
      <p class="breadcrumb"><?php echo e($page['breadcrumb']); ?></p>
      <div class="actions-group">
        <?php $__currentLoopData = $page['actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <button
            class="<?php echo e($index === 0 ? 'btn-primary' : 'btn-secondary'); ?>"
            type="button"
            <?php if(! empty($page['calculator']) && $label === 'Calculer'): ?> data-calculate-indemnity <?php endif; ?>
          >
            <?php echo e($label); ?>

          </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(! empty($page['closePeriod'])): ?>
          <button
            class="btn-danger-soft"
            type="button"
            data-confirm="Êtes-vous sûr de vouloir fermer cette période de paie ? Cette action est sensible."
            data-success-message="Période de paie fermée."
          >
            Fermer la période
          </button>
        <?php endif; ?>
      </div>
    </div>

    <section class="filter-panel" aria-label="Filtres de la page">
      <?php $__currentLoopData = $page['filters']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($filterId = $slug.'-filter-'.$index); ?>
        <div class="form-group">
          <label for="<?php echo e($filterId); ?>"><?php echo e($filter); ?></label>
          <select class="form-control" id="<?php echo e($filterId); ?>">
            <option value="">Tous</option>
            <option>Juin 2026</option>
            <option>IA Dakar</option>
            <option>Validé</option>
          </select>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <div class="actions-group">
        <button class="btn-secondary" type="button">Filtrer</button>
      </div>
    </section>

    <?php if(! empty($page['helpText'])): ?>
      <section class="help-card">
        <h2><?php echo e($page['helpTitle']); ?></h2>
        <p><?php echo e($page['helpText']); ?></p>
      </section>
    <?php endif; ?>

    <?php if(! empty($page['chart'])): ?>
      <?php ($heights = [58, 74, 48, 86, 66, 96]); ?>
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Graphique mensuel</h2>
            <p>Vue synthétique de la période</p>
          </div>
        </div>
        <div class="mini-chart">
          <?php $__currentLoopData = $page['chart']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mini-bar">
              <span style="height: <?php echo e($heights[$index % count($heights)]); ?>px"></span>
              <?php echo e($label); ?>

            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if(! empty($page['calculator'])): ?>
      <section class="result-card" data-indemnity-result hidden></section>
    <?php endif; ?>

    <section class="table-card">
      <div class="table-responsive">
        <table class="table" id="moduleTable">
          <thead>
            <tr>
              <?php $__currentLoopData = $page['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th class="<?php echo \Illuminate\Support\Arr::toCssClasses(['actions-cell' => $loop->last]); ?>"><?php echo e($column); ?></th>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $page['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <td class="<?php echo \Illuminate\Support\Arr::toCssClasses(['actions-cell' => $loop->last]); ?>"><?php echo $cell; ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
      <p class="empty-message">Aucune donnée trouvée.</p>
      <div class="pagination" aria-label="Pagination">
        <button class="page-btn" type="button" aria-label="Page précédente">←</button>
        <button class="page-btn active" type="button" data-page-number>1</button>
        <button class="page-btn" type="button" data-page-number>2</button>
        <button class="page-btn" type="button" aria-label="Page suivante">→</button>
      </div>
    </section>
  </section>
</main>
<?php /**PATH /Applications/MAMP/htdocs/sicoreFront/resources/views/components/module-page.blade.php ENDPATH**/ ?>