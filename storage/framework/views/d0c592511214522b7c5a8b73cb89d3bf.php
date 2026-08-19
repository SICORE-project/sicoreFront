<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'icon' => 'fa-solid fa-circle',
    'searchId' => null,
    'searchPlaceholder' => 'Rechercher…',
    'filterTarget' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title',
    'subtitle' => null,
    'icon' => 'fa-solid fa-circle',
    'searchId' => null,
    'searchPlaceholder' => 'Rechercher…',
    'filterTarget' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header class="topbar">
  <div class="page-title-wrap">
    <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Ouvrir le menu">
      <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>
    <span class="title-icon" aria-hidden="true"><i class="<?php echo e($icon); ?>"></i></span>
    <div>
      <h1><?php echo e($title); ?></h1>
      <?php if($subtitle): ?>
        <p><?php echo e($subtitle); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if($searchId): ?>
    <div class="search-wrap">
      <label class="sr-only" for="<?php echo e($searchId); ?>">Rechercher</label>
      <input
        class="search-input"
        id="<?php echo e($searchId); ?>"
        type="search"
        placeholder="<?php echo e($searchPlaceholder); ?>"
        <?php if($filterTarget): ?> data-table-filter="<?php echo e($filterTarget); ?>" <?php endif; ?>
      >
    </div>
  <?php endif; ?>

  <?php echo e($slot); ?>

</header>
<?php /**PATH C:\projets\sicoreFront\resources\views/components/topbar.blade.php ENDPATH**/ ?>