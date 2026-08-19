<?php $__env->startSection('title', 'SICORE - Modifier convocation'); ?>

<?php $__env->startSection('content'); ?>

<main class="main-content">

<?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Modifier la convocation','subtitle' => 'Indemnites > Convocations > Modifier','icon' => 'fa-solid fa-envelope-open-text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Modifier la convocation','subtitle' => 'Indemnites > Convocations > Modifier','icon' => 'fa-solid fa-envelope-open-text']); ?>
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

    <section class="form-card wizard-card convocation-card">

        <div class="form-card-header">
            <div>
                <h2>Modifier la convocation</h2>
                <p class="breadcrumb">Mise à jour de la convocation et de ses centres d'examen</p>
            </div>
           
            <?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'statut-convocation','statut' => $convocation->statut ?? 'brouillon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'statut-convocation','statut' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($convocation->statut ?? 'brouillon')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $attributes = $__attributesOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__attributesOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale94ef177a4a26601709776c0cc882ade)): ?>
<?php $component = $__componentOriginale94ef177a4a26601709776c0cc882ade; ?>
<?php unset($__componentOriginale94ef177a4a26601709776c0cc882ade); ?>
<?php endif; ?>
        </div>

        <form
            id="convocationForm"
            class="convocation-form"
            role="form"
            method="POST"
            action="<?php echo e(route('indemnites.convocations.update', $id)); ?>"
            enctype="multipart/form-data"
            data-convocation-wizard
            data-wizard-mode="edit"
            data-search-url="<?php echo e(route('indemnites.convocations.enseignants.rechercher')); ?>"
            aria-describedby="<?php echo e($errors->any() ? 'form-errors' : ''); ?>"
            novalidate
        >

            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            

            <div class="wizard-progress" aria-label="Progression du formulaire">
                <button class="wizard-step active" type="button" data-step-indicator="1">
                    <span class="wizard-step-number">1</span>
                    <span>Informations générales</span>
                </button>
                <button class="wizard-step" type="button" data-step-indicator="2">
                    <span class="wizard-step-number">2</span>
                    <span>Centres, jurys et membres</span>
                </button>
            </div>

            

            <?php if($errors->any()): ?>
                <div id="form-errors" class="form-errors" role="alert">
                    <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            

            <section class="wizard-panel" data-wizard-panel="1">

                <div class="form-section">

                    <h3>Informations de la convocation</h3>
                    <p class="section-description">Renseignez les informations générales de la convocation.</p>

                    <div class="form-grid">

                        <div class="form-group full">
                            <label for="objet">Objet <span class="required">*</span></label>
                            <input
                                class="form-control <?php $__errorArgs = ['objet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="objet"
                                name="objet"
                                type="text"
                                placeholder="Ex : Examen de certification en Brevet de Technicien (BT)"
                                value="<?php echo e(old('objet', $convocation->objet ?? '')); ?>"
                                required
                            >
                            <?php $__errorArgs = ['objet'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group full">
                            <label for="session">Session (ex : BFEM 2026)</label>
                            <input
                                class="form-control <?php $__errorArgs = ['session'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="session"
                                name="session"
                                type="text"
                                placeholder="Ex : BT 2026"
                                value="<?php echo e(old('session', $convocation->session ?? '')); ?>"
                            >
                            <?php $__errorArgs = ['session'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group full">
                            <label for="type_convocation_id">Type de convocation</label>
                            <select
                                class="form-control <?php $__errorArgs = ['type_convocation_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="type_convocation_id"
                                name="type_convocation_id"
                            >
                                <option value="">Sélectionner</option>
                                <?php $__currentLoopData = $typesConvocation ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($type['id']); ?>"
                                        <?php if((string) old('type_convocation_id', $convocation->type_convocation_id ?? '') === (string) $type['id']): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($type['libelle']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="section-description" style="margin: 6px 0 0;">
                                Détermine le modèle utilisé pour le PDF (ex : tableau centre/jury/métier pour un jury d'examen).
                            </p>
                            <?php $__errorArgs = ['type_convocation_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="date_emission">Date d'émission <span class="required">*</span></label>
                            <input
                                class="form-control <?php $__errorArgs = ['date_emission'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="date_emission"
                                name="date_emission"
                                type="date"
                                value="<?php echo e(old('date_emission', optional($convocation->date_emission ?? null)->format('Y-m-d'))); ?>"
                                required
                            >
                            <?php $__errorArgs = ['date_emission'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select class="form-control <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="statut" name="statut">
                                <?php $__currentLoopData = ['brouillon' => 'Brouillon', 'emise' => 'Émise', 'envoyee' => 'Envoyée', 'cloturee' => 'Clôturée']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php if(old('statut', $convocation->statut ?? 'brouillon') === $value): echo 'selected'; endif; ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                    </div>

                </div>

                <div class="form-section">

                    <h3>Période de l'examen</h3>
                    <p class="section-description">Indiquez la période et l'heure prévues pour l'examen.</p>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="date_debut">Du <span class="required">*</span></label>
                            <input
                                class="form-control <?php $__errorArgs = ['date_debut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="date_debut"
                                name="date_debut"
                                type="date"
                                value="<?php echo e(old('date_debut', optional($convocation->date_debut ?? null)->format('Y-m-d'))); ?>"
                                required
                            >
                            <?php $__errorArgs = ['date_debut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="date_fin">Au <span class="required">*</span></label>
                            <input
                                class="form-control <?php $__errorArgs = ['date_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="date_fin"
                                name="date_fin"
                                type="date"
                                value="<?php echo e(old('date_fin', optional($convocation->date_fin ?? null)->format('Y-m-d'))); ?>"
                                required
                            >
                            <?php $__errorArgs = ['date_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="heure_debut">À partir de <span class="required">*</span></label>
                            <input
                                class="form-control <?php $__errorArgs = ['heure_debut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="heure_debut"
                                name="heure_debut"
                                type="time"
                                value="<?php echo e(old('heure_debut', $convocation->heure_debut ?? '08:00')); ?>"
                                required
                            >
                            <?php $__errorArgs = ['heure_debut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="lieu_examen">Lieu d'examen</label>
                            <input
                                class="form-control <?php $__errorArgs = ['lieu_examen'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="lieu_examen"
                                name="lieu_examen"
                                type="text"
                                value="<?php echo e(old('lieu_examen', $convocation->lieu_examen ?? '')); ?>"
                            >
                            <?php $__errorArgs = ['lieu_examen'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        

                    </div>

                </div>

            </section>

            

            <section class="wizard-panel" data-wizard-panel="2" hidden>

                <div class="form-section">

                    <div class="panel-header">
                        <div>
                            <h3>Centres d'examen</h3>
                            <p>
                                Ajoutez les différents centres concernés par la convocation. Pour
                                chaque centre, précisez le jury et le chef de centre, puis ajoutez
                                un groupe par métier (ex : MVM, puis FC) avec ses propres membres.
                            </p>
                        </div>
                        <button class="btn-secondary" type="button" data-add-centre>
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            Ajouter un centre
                        </button>
                    </div>

                    <div class="centres-container" data-centres-container></div>

                    <p class="empty-message" data-centres-empty>Aucun centre ajouté pour le moment.</p>

                    <?php $__errorArgs = ['centres'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

            </section>

            

            <template data-centre-template>

                <div class="centre-card" data-centre-card>

                    <input type="hidden" data-field="id">

                    <div class="centre-card-header">
                        <div>
                            <h4>Centre d'examen <span data-centre-number></span></h4>
                            <p>Centre, métier, jury et chef de centre</p>
                        </div>
                        <button type="button" class="icon-action" title="Supprimer le centre" aria-label="Supprimer le centre" data-remove-centre>
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label>Centre d'examen <span class="required">*</span></label>
                            <input
                                class="form-control"
                                type="text"
                                placeholder="Ex : Centre LTP FXN/THIES"
                                data-centre-input
                                data-field="centre"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>Jury</label>
                            <input class="form-control" type="text" placeholder="Ex : Jury 1" data-jury-input data-field="jury">
                        </div>

                        <div class="form-group">
                            <label>Président du jury</label>
                            <div class="enseignant-search" data-president-search>
                                <input class="form-control" type="text" placeholder="Rechercher le président du jury..." autocomplete="off" data-president-search-input>
                                <input type="hidden" data-president-id-input data-field="president_jury_id">
                                <ul class="enseignant-suggestions" data-president-suggestions hidden></ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Téléphone du président du jury</label>
                            <input class="form-control" type="text" placeholder="33 901 10 71" data-president-telephone-input data-field="president_jury_telephone">
                        </div>

                        <div class="form-group">
                            <label>Chef de centre</label>
                            <div class="enseignant-search" data-chef-search>
                                <input class="form-control" type="text" placeholder="Rechercher le chef de centre..." autocomplete="off" data-chef-search-input>
                                <input type="hidden" data-chef-id-input data-field="chef_centre_id">
                                <ul class="enseignant-suggestions" data-chef-suggestions hidden></ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Téléphone du chef de centre</label>
                            <input class="form-control" type="text" placeholder="33 901 10 71" data-chef-telephone-input data-field="chef_centre_telephone">
                        </div>

                    </div>

                    <div class="metier-groups-section">

                        <div class="panel-header">
                            <div>
                                <h4>Métiers &amp; membres du jury</h4>
                                <p>
                                    Un centre peut regrouper plusieurs métiers (ex : MVM puis FC).
                                    Ajoutez un groupe par métier, avec ses propres membres.
                                </p>
                            </div>
                            <button type="button" class="btn-secondary" data-add-metier-group>
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                Ajouter un groupe métier
                            </button>
                        </div>

                        <div class="metier-groups-container" data-metiers-container></div>

                        <p class="empty-message" data-metiers-empty>Aucun groupe métier ajouté pour ce centre.</p>

                        <?php $__errorArgs = ['centres.*.metier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

            </template>

            

            <template data-metier-group-template>

                <div class="metier-group" data-metier-group>

                    <input type="hidden" data-field="id">

                    <div class="metier-group-header">
                        <div>
                            <h5>Groupe métier <span data-metier-number></span></h5>
                            <p>
                                Laissez le métier vide pour un groupe "général"
                                (ex : président de jury, sans métier associé).
                            </p>
                        </div>
                        <button type="button" class="icon-action" title="Supprimer ce groupe" aria-label="Supprimer ce groupe métier" data-remove-metier-group>
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Métier / spécialité</label>
                            <input
                                class="form-control"
                                type="text"
                                placeholder="Ex : Technicien en Maintenance Véhicules Moteurs (MVM)"
                                data-metier-input
                                data-field="metier"
                            >
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table members-table">
                            <thead>
                                <tr>
                                    <th>Prénom</th>
                                    <th>Nom</th>
                                    <th>Fonction</th>
                                    <th>Statut</th>
                                    <th>Provenance</th>
                                    <th>Téléphone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody data-members-body></tbody>
                        </table>
                    </div>

                    <div class="member-import-actions">

                        <button type="button" class="btn-secondary" data-add-member>
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                            Ajouter un membre
                        </button>

                        <label class="btn-secondary" data-import-members-label title="Fichier CSV avec les colonnes : matricule, fonction, statut, provenance">
                            <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                            Importer une liste (CSV)
                            <input type="file" accept=".csv,text/csv,text/plain" data-import-members-input hidden>
                        </label>

                    </div>

                    <p class="import-members-status" data-import-members-status hidden></p>

                    <p class="empty-message" data-members-empty>Aucun membre ajouté pour ce groupe.</p>

                </div>

            </template>

            

            <template data-member-template>

                <tr class="member-row">
                    <td data-label="Prénom">
                        <div class="enseignant-search" data-member-search>
                            <input class="form-control" type="text" placeholder="Rechercher..." autocomplete="off" data-member-search-input>
                            <input type="hidden" data-member-id-input>
                            <ul class="enseignant-suggestions" data-member-suggestions hidden></ul>
                        </div>
                    </td>
                    <td data-label="Nom">
                        <input class="form-control" type="text" placeholder="Nom" data-member-nom>
                    </td>
                    <td data-label="Fonction">
                        <select class="form-control" data-member-fonction>
                            <option value="">Sélectionner</option>
                            <option value="Président de jury">Président de jury</option>
                            <option value="Membre du jury">Membre du jury</option>
                            <option value="Surveillant/correcteur">Surveillant/correcteur</option>
                            <option value="Chef de centre">Chef de centre</option>
                        </select>
                    </td>
                    <td data-label="Statut">
                        <select class="form-control" data-member-categorie>
                            <option value="">Sélectionner</option>
                            <option value="fonctionnaire">Fonctionnaire</option>
                            <option value="contractuel">Contractuelle</option>
                            <option value="vacataire">Vacataire</option>
                        </select>
                    </td>
                    <td data-label="Provenance">
                        <input class="form-control" type="text" placeholder="Ex : LTP-FXN/THIES" data-member-provenance>
                    </td>
                    <td data-label="Téléphone">
                        <input class="form-control" type="text" placeholder="77 000 00 00" data-member-telephone>
                    </td>
                    <td class="actions-cell" data-label="Action">
                        <button type="button" class="icon-action" title="Retirer" aria-label="Retirer le membre" data-remove-member>
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>

            </template>

            

            <p class="form-status" data-form-status aria-live="polite"></p>

            

            <div class="form-actions">
                <a class="btn-secondary" href="<?php echo e(route('indemnites.convocations.show', $id)); ?>" data-wizard-cancel>
                    Annuler
                </a>
                <button class="btn-secondary" type="button" data-wizard-prev hidden>
                    <i class="fa-solid fa-arrow-left"></i>
                    Précédent
                </button>
                <button class="btn-primary" type="button" data-wizard-next>
                    Suivant
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button class="btn-primary" type="submit" data-wizard-submit hidden>
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Enregistrer la convocation
                </button>
            </div>

        </form>

    </section>

</section>

</main>

<?php $__env->stopSection(); ?>



<?php $__env->startPush('scripts'); ?>
<script>
    window.__convocationWizardPrefill = <?php echo json_encode($wizardData ?? [], 15, 512) ?>;
</script>
<script src="<?php echo e(asset('assets/js/indemnites/convocation-wizard.js')); ?>"></script>
<?php $__env->stopPush(); ?>



<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/indemnites/convocation-wizard.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\indemnites\convocations\edit.blade.php ENDPATH**/ ?>