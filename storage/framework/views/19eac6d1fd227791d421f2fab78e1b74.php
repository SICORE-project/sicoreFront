
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type',
    'statut' => null,
    'id' => null,
    'title' => null,
    'open' => false,
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
    'type',
    'statut' => null,
    'id' => null,
    'title' => null,
    'open' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($type === 'statut-convocation'): ?>

    
    <?php
        $badges = [
            'brouillon' => ['badge-pending', 'Brouillon'],
            'emise' => ['badge-primary', 'Émise'],
            'envoyee' => ['badge-active', 'Envoyée'],
            'cloturee' => ['badge-inactive', 'Clôturée'],
        ];

        [$classe, $libelle] = $badges[$statut] ?? ['badge-pending', $statut ? ucfirst($statut) : '—'];
    ?>

    <span <?php echo e($attributes->merge(['class' => "badge {$classe}"])); ?>><?php echo e($libelle); ?></span>

<?php elseif($type === 'statut-envoi'): ?>

    
    <?php
        $badges = [
            'envoye' => ['badge-active', 'Envoyé'],
            'echec' => ['badge-inactive', 'Échec'],
        ];

        [$classe, $libelle] = $badges[$statut] ?? ['badge-pending', $statut ? ucfirst($statut) : '—'];
    ?>

    <span <?php echo e($attributes->merge(['class' => "badge {$classe}"])); ?>><?php echo e($libelle); ?></span>

<?php elseif($type === 'modal'): ?>

    
    <?php if (! $__env->hasRenderedOnce('2dbe128f-81df-415a-ba20-34b1d9f08d45')): $__env->markAsRenderedOnce('2dbe128f-81df-415a-ba20-34b1d9f08d45'); ?>
        <?php $__env->startPush('styles'); ?>
        <style>

            .modal-backdrop {
                position: fixed;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: rgba(15, 23, 42, 0.5);
                z-index: 1000;
            }

            .modal-backdrop[hidden] {
                display: none;
            }

            .modal-dialog {
                width: 100%;
                max-width: 520px;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
                padding: 20px 22px;
                border-radius: 10px;
                background: #ffffff;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
            }

            .modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
            }

            .modal-header h2 {
                margin: 0;
                font-size: 1.1rem;
            }

            .modal-close {
                border: 0;
                background: transparent;
                font-size: 1.4rem;
                line-height: 1;
                cursor: pointer;
                color: inherit;
            }

        </style>
        <?php $__env->stopPush(); ?>

        <?php $__env->startPush('scripts'); ?>
        <script>
            (function () {
                "use strict";

                function ouvrirModal(modal) {
                    modal.hidden = false;
                }

                function fermerModal(modal) {
                    modal.hidden = true;
                }

                document.querySelectorAll("[data-modal-open]").forEach(function (bouton) {
                    var modal = document.getElementById(bouton.getAttribute("data-modal-open"));

                    if (!modal) {
                        return;
                    }

                    bouton.addEventListener("click", function () {
                        ouvrirModal(modal);
                    });
                });

                document.querySelectorAll("[data-modal]").forEach(function (modal) {
                    modal.addEventListener("click", function (event) {
                        if (event.target === modal) {
                            fermerModal(modal);
                        }
                    });

                    modal.querySelectorAll("[data-modal-close]").forEach(function (bouton) {
                        bouton.addEventListener("click", function () {
                            fermerModal(modal);
                        });
                    });
                });

                document.addEventListener("keydown", function (event) {
                    if (event.key !== "Escape") {
                        return;
                    }

                    document.querySelectorAll("[data-modal]:not([hidden])").forEach(fermerModal);
                });
            })();
        </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>

    <div class="modal-backdrop" id="<?php echo e($id); ?>" data-modal <?php if(! $open): ?> hidden <?php endif; ?>>
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo e($id); ?>-title">

            <div class="modal-header">
                <h2 id="<?php echo e($id); ?>-title"><?php echo e($title); ?></h2>
                <button class="modal-close" type="button" data-modal-close aria-label="Fermer">&times;</button>
            </div>

            <?php echo e($slot); ?>


        </div>
    </div>

<?php endif; ?>
<?php /**PATH C:\projets\sicoreFront\resources\views/components/module-indemnite.blade.php ENDPATH**/ ?>