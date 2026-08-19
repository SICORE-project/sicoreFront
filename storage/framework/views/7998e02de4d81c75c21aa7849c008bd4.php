<?php $__env->startSection('title', 'SICORE - Utilisateurs'); ?>
<?php $__env->startSection('content'); ?>
  <?php if($usersError): ?>
    <div class="alert alert-danger" role="alert"><?php echo e($usersError); ?></div>
  <?php endif; ?>
  <?php if (isset($component)) { $__componentOriginalf02cf1377f3dda091891e2b87c45ebec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf02cf1377f3dda091891e2b87c45ebec = $attributes; } ?>
<?php $component = App\View\Components\ModulePage::resolve(['slug' => 'utilisateurs'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ModulePage::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf02cf1377f3dda091891e2b87c45ebec)): ?>
<?php $attributes = $__attributesOriginalf02cf1377f3dda091891e2b87c45ebec; ?>
<?php unset($__attributesOriginalf02cf1377f3dda091891e2b87c45ebec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf02cf1377f3dda091891e2b87c45ebec)): ?>
<?php $component = $__componentOriginalf02cf1377f3dda091891e2b87c45ebec; ?>
<?php unset($__componentOriginalf02cf1377f3dda091891e2b87c45ebec); ?>
<?php endif; ?>

  <?php if (isset($component)) { $__componentOriginale94ef177a4a26601709776c0cc882ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94ef177a4a26601709776c0cc882ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.module-indemnite','data' => ['type' => 'modal','id' => 'create-user-modal','title' => 'Créer un utilisateur','open' => $errors->any() || session()->has('error')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('module-indemnite'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'modal','id' => 'create-user-modal','title' => 'Créer un utilisateur','open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->any() || session()->has('error'))]); ?>
    <p class="breadcrumb">Ajouter un nouveau compte et lui attribuer un rôle.</p>

    <form class="teacher-form" id="createUserForm" method="POST" action="<?php echo e(route('utilisateurs.store')); ?>">
      <?php echo csrf_field(); ?>

      <div class="form-grid form-grid--balanced">
        <div class="form-group">
          <label for="nom">Nom <span class="required">*</span></label>
          <input class="form-control <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="nom" name="nom" type="text" value="<?php echo e(old('nom')); ?>" maxlength="100" required autocomplete="family-name">
          <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
          <label for="prenom">Prénom <span class="required">*</span></label>
          <input class="form-control <?php $__errorArgs = ['prenom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="prenom" name="prenom" type="text" value="<?php echo e(old('prenom')); ?>" maxlength="100" required autocomplete="given-name">
          <?php $__errorArgs = ['prenom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group full">
          <label for="email">Adresse e-mail <span class="required">*</span></label>
          <input class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" maxlength="255" required autocomplete="email" placeholder="nom@exemple.sn" data-check-url="<?php echo e(route('utilisateurs.check-email')); ?>" aria-describedby="email-error">
          <div class="invalid-feedback" id="email-error" aria-live="polite"><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><?php echo e($message); ?><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
        </div>

        <div class="form-group">
          <label for="role_id">Rôle <span class="required">*</span></label>
          <select class="form-control <?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="role_id" name="role_id" required>
            <option value="">Sélectionner un rôle</option>
            <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <option value="<?php echo e($role['id']); ?>" data-structure-types='<?php echo json_encode($role['structure_types'] ?? [], 15, 512) ?>' <?php if((string) old('role_id') === (string) $role['id']): echo 'selected'; endif; ?>><?php echo e($role['nom']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <option value="" disabled>Aucun rôle disponible</option>
            <?php endif; ?>
          </select>
          <?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
          <label for="statut">Statut <span class="required">*</span></label>
          <select class="form-control <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="statut" name="statut" required>
            <option value="actif" <?php if(old('statut', 'actif') === 'actif'): echo 'selected'; endif; ?>>Actif</option>
            <option value="inactif" <?php if(old('statut') === 'inactif'): echo 'selected'; endif; ?>>Inactif</option>
          </select>
          <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group full organisation-section">
          <strong>Accès organisationnel</strong>
          <small>Choisissez une structure nationale ou une IA et son IEF.</small>
        </div>

        <div class="form-group">
          <label for="perimetre">Périmètre <span class="required">*</span></label>
          <select class="form-control <?php $__errorArgs = ['perimetre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="perimetre" name="perimetre" required>
            <option value="national" <?php if(old('perimetre', 'national') === 'national'): echo 'selected'; endif; ?>>National</option>
            <option value="regional" <?php if(old('perimetre') === 'regional'): echo 'selected'; endif; ?>>Régional</option>
          </select>
          <?php $__errorArgs = ['perimetre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group" id="national-structure-group">
          <label for="structure_organisationnelle_id">Structure nationale <span class="required">*</span></label>
          <select class="form-control <?php $__errorArgs = ['structure_organisationnelle_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="structure_organisationnelle_id" name="structure_organisationnelle_id">
            <option value="">Sélectionner DRH, DAGE ou DECPC</option>
            <?php $__currentLoopData = ($organisation['national'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $structure): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php if(is_array($structure) && data_get($structure, 'id') && in_array(strtoupper((string) data_get($structure, 'type', data_get($structure, 'code'))), ['DRH', 'DAGE', 'DECPC'], true)): ?>
                <option value="<?php echo e(data_get($structure, 'id')); ?>" <?php if((string) old('structure_organisationnelle_id') === (string) data_get($structure, 'id')): echo 'selected'; endif; ?>><?php echo e(collect([data_get($structure, 'code'), data_get($structure, 'libelle', data_get($structure, 'nom'))])->filter()->join(' — ')); ?></option>
              <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php $__errorArgs = ['structure_organisationnelle_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group" id="ia-group" hidden>
          <label for="ia_id">IA <span class="required">*</span></label>
          <select class="form-control <?php $__errorArgs = ['ia_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="ia_id" name="ia_id">
            <option value="">Sélectionner une IA</option>
          </select>
          <?php $__errorArgs = ['ia_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group" id="ief-group" hidden>
          <label for="ief_id">IEF</label>
          <select class="form-control <?php $__errorArgs = ['ief_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="ief_id" name="ief_id" disabled>
            <option value="">Toutes les IEF de l'IA</option>
          </select>
          <?php $__errorArgs = ['ief_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe <span class="required">*</span></label>
          <input class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
          <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmation <span class="required">*</span></label>
          <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
          <small class="form-text" id="password-confirmation-feedback" aria-live="polite"></small>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
        <button class="btn-primary" type="submit" data-submit-button>
          <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer
        </button>
      </div>
    </form>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
  <style>
    #create-user-modal .modal-dialog { max-width: 760px; }
    #create-user-modal .teacher-form { margin-top: 18px; }
    #create-user-modal .organisation-section { display:flex; flex-direction:column; gap:4px; margin-top:8px; padding-top:16px; border-top:1px solid var(--border); }
    #create-user-modal .organisation-section small { color:var(--text-muted); }
    #create-user-modal .form-group[hidden] { display:none !important; }
    .users-filter-panel { grid-template-columns: repeat(3, minmax(0, 1fr)) auto; align-items:end; }
    .users-filter-panel .actions-group { min-width:120px; }
    .users-filter-panel .actions-group .btn-secondary { width:100%; }
    @media (max-width:1200px) {
      .users-filter-panel { grid-template-columns:repeat(2, minmax(0, 1fr)); }
      .users-filter-panel .actions-group { min-width:0; }
    }
    @media (max-width:700px) {
      .users-filter-panel { grid-template-columns:1fr; }
    }
  </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('createUserForm');
      const password = document.getElementById('password');
      const confirmation = document.getElementById('password_confirmation');
      const feedback = document.getElementById('password-confirmation-feedback');
      const email = document.getElementById('email');
      const emailError = document.getElementById('email-error');
      const hierarchy = <?php echo json_encode($organisation['regional'] ?? [], 15, 512) ?>;
      const role = document.getElementById('role_id');
      const perimeter = document.getElementById('perimetre');
      const nationalGroup = document.getElementById('national-structure-group');
      const national = document.getElementById('structure_organisationnelle_id');
      const iaGroup = document.getElementById('ia-group');
      const ia = document.getElementById('ia_id');
      const iefGroup = document.getElementById('ief-group');
      const ief = document.getElementById('ief_id');
      const oldIa = <?php echo json_encode((string) old('ia_id', ''), 512) ?>;
      const oldIef = <?php echo json_encode((string) old('ief_id', ''), 512) ?>;
      let emailTimer;
      let emailRequest;
      let emailIsChecking = false;

      function optionLabel(item) {
        return [item.code, item.libelle].filter(Boolean).join(' — ');
      }

      function fillIefs(selectedValue = '') {
        ief.replaceChildren(new Option("Toutes les IEF de l'IA", ''));
        const selectedIa = hierarchy.find(item => String(item.id) === ia.value);
        (selectedIa?.iefs ?? []).forEach(item => ief.add(new Option(optionLabel(item), item.id)));
        ief.disabled = !ia.value || perimeter.value !== 'regional';
        ief.value = selectedValue;
      }

      hierarchy
        .filter(item => item.type_perimetre === 'regional')
        .forEach(item => ia.add(new Option(optionLabel(item), item.id)));

      function toggleOrganisation() {
        const regional = perimeter.value === 'regional';
        nationalGroup.hidden = regional;
        national.disabled = regional;
        national.required = !regional;
        iaGroup.hidden = !regional;
        iefGroup.hidden = !regional;
        ia.disabled = !regional;
        ia.required = regional;

        if (regional) {
          national.value = '';
          fillIefs(ief.value);
        } else {
          ia.value = '';
          fillIefs();
        }
      }

      function applyRoleStructureRules() {
        const selectedRole = role.options[role.selectedIndex];
        const allowedTypes = JSON.parse(selectedRole?.dataset.structureTypes || '[]');
        const hasSelectedRole = Boolean(role.value);
        const allowsNational = allowedTypes.includes('national');
        const allowsIa = allowedTypes.includes('ia');
        const allowsIef = allowedTypes.includes('ief');
        const nationalOption = perimeter.querySelector('option[value="national"]');
        const regionalOption = perimeter.querySelector('option[value="regional"]');

        nationalOption.disabled = hasSelectedRole && !allowsNational;
        regionalOption.disabled = hasSelectedRole && !allowsIa && !allowsIef;

        if (!hasSelectedRole) {
          perimeter.disabled = false;
          if (!perimeter.value) perimeter.value = 'national';
        } else if (!allowedTypes.length) {
          perimeter.value = '';
          perimeter.disabled = true;
        } else {
          perimeter.disabled = false;
          if (perimeter.value === 'national' && !allowsNational) perimeter.value = 'regional';
          if (perimeter.value === 'regional' && !allowsIa && !allowsIef) perimeter.value = 'national';
        }

        toggleOrganisation();

        if (perimeter.value === 'regional') {
          iefGroup.hidden = !allowsIef;
          ief.disabled = !allowsIef || !ia.value;
          ief.required = allowsIef && !allowsIa;
          if (!allowsIef) ief.value = '';
        } else {
          ief.required = false;
        }
      }

      ia.value = oldIa;
      fillIefs(oldIef);
      perimeter.addEventListener('change', applyRoleStructureRules);
      role.addEventListener('change', applyRoleStructureRules);
      ia.addEventListener('change', () => fillIefs());
      applyRoleStructureRules();

      function setEmailError(message) {
        email.setCustomValidity(message || '');
        email.classList.toggle('is-invalid', Boolean(message));
        emailError.textContent = message || '';
      }

      async function checkEmailAvailability() {
        const value = email.value.trim();

        if (!value || !email.validity.valid) return;

        if (emailRequest) emailRequest.abort();
        emailRequest = new AbortController();
        emailIsChecking = true;

        try {
          const url = new URL(email.dataset.checkUrl, window.location.origin);
          url.searchParams.set('email', value);

          const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            signal: emailRequest.signal
          });
          const result = await response.json();

          if (email.value.trim() !== value) return;

          setEmailError(result.available ? '' : (result.message || 'Cette adresse e-mail est déjà utilisée.'));
        } catch (error) {
          if (error.name !== 'AbortError') {
            setEmailError('Impossible de vérifier cette adresse pour le moment.');
          }
        } finally {
          emailIsChecking = false;
        }
      }

      email.addEventListener('input', function () {
        clearTimeout(emailTimer);
        setEmailError('');

        if (!email.value || !email.validity.valid) return;

        emailTimer = setTimeout(checkEmailAvailability, 450);
      });

      email.addEventListener('blur', function () {
        clearTimeout(emailTimer);
        checkEmailAvailability();
      });

      function checkPasswords() {
        if (!confirmation.value) {
          feedback.textContent = '';
          return;
        }

        const matches = password.value === confirmation.value;
        feedback.textContent = matches ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
        feedback.className = matches ? 'form-text text-success' : 'form-text text-danger';
      }

      password.addEventListener('input', checkPasswords);
      confirmation.addEventListener('input', checkPasswords);

      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearTimeout(emailTimer);

        if (!email.validationMessage) {
          await checkEmailAvailability();
        }

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        const button = form.querySelector('[data-submit-button]');
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enregistrement…';
        form.submit();
      });
    });
  </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views/pages/administration/utilisateurs.blade.php ENDPATH**/ ?>