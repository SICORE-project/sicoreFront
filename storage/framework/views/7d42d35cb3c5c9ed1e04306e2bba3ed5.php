

<?php $__env->startSection('title', 'SICORE - Connexion'); ?>
<?php $__env->startSection('content'); ?>
<main class="login-wrapper">
    <section class="login-card" aria-label="Connexion SICORE">
      <div class="login-left">
        <div class="login-left-content">
          <div class="republic-block">
            <img class="flag-image" src="<?php echo e(asset('assets/images/flag-senegal.svg')); ?>" alt="Drapeau du S&eacute;n&eacute;gal">
            <div>
              <p class="republic-title">R&Eacute;PUBLIQUE DU S&Eacute;N&Eacute;GAL</p>
              <p class="republic-motto">Un Peuple &ndash; Un But &ndash; Une Foi</p>
            </div>
          </div>
          <p class="ministry">Minist&egrave;re de l&rsquo;Emploi et de la Formation<br>Professionnelle et Technique</p>
        </div>

        <div class="login-brand">
          <span class="brand-emblem">
            <img src="<?php echo e(asset('assets/images/image-fcfa.png')); ?>" alt="Logo SICORE - Syst&egrave;me Int&eacute;gr&eacute; des COrps &Eacute;mergents">
          </span>
          <h1>SICORE</h1>
          <p>Syst&egrave;me Int&eacute;gr&eacute; des COrps &Eacute;mergents</p>
        </div>
      </div>

      <div class="login-right">
        <div class="auth-panel">
          <span class="auth-kicker">Bienvenue sur SICORE</span>
          <p class="auth-subtitle">Connectez-vous avec vos identifiants pour acc&eacute;der &agrave; votre espace s&eacute;curis&eacute;.</p>

          <form class="form-stack" method="POST" action="<?php echo e(route('login.submit')); ?>" novalidate>
            <?php echo csrf_field(); ?>
            <div class="field-group">
              <label for="email">Adresse e-mail</label>
              <div class="input-shell">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                <input class="login-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="adresse@sicore.sn" autocomplete="username" required autofocus>
              </div>
            </div>

            <div class="field-group">
              <label for="password">Mot de passe</label>
              <div class="password-field">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                <input class="login-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" type="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="Afficher le mot de passe" aria-pressed="false">
                  <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="form-options">
              <label class="check-label">
                <input type="checkbox" name="remember" value="1" <?php echo e(old('remember', true) ? 'checked' : ''); ?>>
                <span>Se souvenir de moi</span>
              </label>
              <a class="forgot-link" href="#">Mot de passe oubli&eacute; ?</a>
            </div>

            <?php if($errors->any()): ?>
              <div class="login-errors" role="alert">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <span><?php echo e($errors->first()); ?></span>
              </div>
            <?php endif; ?>
            <button class="login-button" type="submit">
              <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
              <span>Se connecter</span>
            </button>
          </form>

          <p class="auth-footer">Besoin d&rsquo;aide pour acc&eacute;der &agrave; votre compte ? <a href="#">Contactez l&rsquo;administrateur du syst&egrave;me.</a></p>
          <p class="demo-credentials">
              Utilisez vos identifiants professionnels SICORE.
          </p>
        </div>
      </div>
    </section>
  </main>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\SICORE\sicoreFront\resources\views/pages/auth/login.blade.php ENDPATH**/ ?>