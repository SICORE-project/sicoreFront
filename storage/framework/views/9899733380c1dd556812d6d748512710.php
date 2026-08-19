<?php $__env->startSection('title', 'SICORE - Créer un utilisateur'); ?>

<?php $__env->startSection('content'); ?>
    <main class="main-content">

        <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Créer un utilisateur','subtitle' => 'Ajouter un nouvel utilisateur au système','icon' => 'fa-solid fa-user-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Créer un utilisateur','subtitle' => 'Ajouter un nouvel utilisateur au système','icon' => 'fa-solid fa-user-plus']); ?>
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
            <section class="form-card">

                <div class="form-card-header">
                    <div>
                        <h2>Informations du compte</h2>
                        <p class="breadcrumb">
                            Saisie des informations personnelles et des accès
                        </p>
                    </div>

                    <span class="badge badge-primary">Compte</span>
                </div>

                <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>

                
                <?php if(session('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <strong>Veuillez corriger les erreurs suivantes :</strong>

                        <ul>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form
                    class="teacher-form"
                    id="createUserForm"
                    method="POST"
                    action="<?php echo e(route('utilisateurs.store')); ?>"
                    novalidate
                >
                    <?php echo csrf_field(); ?>

                    
                    <div class="form-section">
                        <h3>Informations personnelles</h3>

                        <div class="form-grid">

                            
                            <div class="form-group">
                                <label for="nom">
                                    Nom <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="nom"
                                    name="nom"
                                    type="text"
                                    value="<?php echo e(old('nom')); ?>"
                                    maxlength="100"
                                    required
                                    autocomplete="family-name"
                                    placeholder="Ex. Diop"
                                    aria-describedby="nom-error"
                                >

                                <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" id="nom-error">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div class="form-group">
                                <label for="prenom">
                                    Prénom <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control <?php $__errorArgs = ['prenom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="prenom"
                                    name="prenom"
                                    type="text"
                                    value="<?php echo e(old('prenom')); ?>"
                                    maxlength="100"
                                    required
                                    autocomplete="given-name"
                                    placeholder="Ex. Aminata"
                                    aria-describedby="prenom-error"
                                >

                                <?php $__errorArgs = ['prenom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" id="prenom-error">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div class="form-group full">
                                <label for="email">
                                    Adresse e-mail <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="<?php echo e(old('email')); ?>"
                                    maxlength="255"
                                    required
                                    autocomplete="email"
                                    inputmode="email"
                                    placeholder="nom@exemple.sn"
                                    aria-describedby="email-help email-error"
                                >

                                <small class="form-text" id="email-help">Cette adresse servira d'identifiant de connexion.</small>

                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" id="email-error">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                        </div>
                    </div>

                    
                    <div class="form-section">
                        <h3>Accès au système</h3>

                        <div class="form-grid form-grid--balanced">

                            
                            <div class="form-group">
                                <label for="role_id">
                                    Rôle <span class="required">*</span>
                                </label>

                                <select
                                    class="form-control <?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="role_id"
                                    name="role_id"
                                    required
                                    aria-describedby="role_id-error"
                                >
                                    <option value="">
                                        Sélectionner un rôle
                                    </option>

                                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <option
                                            value="<?php echo e($role['id']); ?>"
                                            <?php if(old('role_id') == $role['id']): echo 'selected'; endif; ?>
                                        >
                                            <?php echo e($role['nom']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <option value="" disabled>
                                            Aucun rôle disponible
                                        </option>
                                    <?php endif; ?>
                                </select>

                                <?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" id="role_id-error">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div class="form-group">
                                <label for="statut">
                                    Statut <span class="required">*</span>
                                </label>

                                <select
                                    class="form-control <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="statut"
                                    name="statut"
                                    required
                                    aria-describedby="statut-help statut-error"
                                >
                                    <option
                                        value="actif"
                                        <?php if(old('statut', 'actif') === 'actif'): echo 'selected'; endif; ?>
                                    >
                                        Actif
                                    </option>

                                    <option
                                        value="inactif"
                                        <?php if(old('statut') === 'inactif'): echo 'selected'; endif; ?>
                                    >
                                        Inactif
                                    </option>
                                </select>

                                <small class="form-text" id="statut-help">Un compte inactif ne peut pas se connecter.</small>

                                <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" id="statut-error">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                        </div>
                    </div>

                    
                    <div class="form-section">
                        <h3>Sécurité</h3>

                        <div class="form-grid form-grid--balanced">

                            
                            <div class="form-group">
                                <label for="password">
                                    Mot de passe <span class="required">*</span>
                                </label>

                                <div class="password-field">
                                    <input
                                        class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="password"
                                        name="password"
                                        type="password"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        aria-describedby="password-help password-error"
                                    >
                                    <button class="password-toggle" type="button" data-password-toggle="#password">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>

                                <small class="form-text" id="password-help">
                                    Le mot de passe doit contenir au moins 8 caractères.
                                </small>

                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback" id="password-error">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div class="form-group">
                                <label for="password_confirmation">
                                    Confirmation du mot de passe
                                    <span class="required">*</span>
                                </label>

                                <div class="password-field">
                                    <input
                                        class="form-control"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        aria-describedby="password-confirmation-feedback"
                                    >
                                    <button class="password-toggle" type="button" data-password-toggle="#password_confirmation">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <small class="form-text" id="password-confirmation-feedback" aria-live="polite"></small>
                            </div>

                        </div>
                    </div>

                    
                    <div class="form-actions">
                        <a
                            class="btn-secondary"
                            href="<?php echo e(route('utilisateurs.index')); ?>"
                        >
                            Annuler
                        </a>

                        <button
                            class="btn-primary"
                            type="submit"
                            data-submit-button
                        >
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            Enregistrer
                        </button>
                    </div>

                </form>

            </section>
        </section>

    </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('createUserForm');
            const password = document.getElementById('password');
            const confirmation = document.getElementById('password_confirmation');
            const feedback = document.getElementById('password-confirmation-feedback');

            function checkPasswordConfirmation() {
                if (!confirmation.value) {
                    feedback.textContent = '';
                    feedback.className = 'form-text';
                    return;
                }

                const matches = password.value === confirmation.value;
                feedback.textContent = matches ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
                feedback.className = matches ? 'form-text text-success' : 'form-text text-danger';
            }

            password.addEventListener('input', checkPasswordConfirmation);
            confirmation.addEventListener('input', checkPasswordConfirmation);

            form.addEventListener('submit', function () {
                if (!form.checkValidity()) {
                    return;
                }

                const button = form.querySelector('[data-submit-button]');
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enregistrement…';
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\administration\utilisateurs\create.blade.php ENDPATH**/ ?>