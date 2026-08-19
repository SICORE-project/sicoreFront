

<?php $__env->startSection('title', 'SICORE - Nouvel enseignant'); ?>
<?php $__env->startSection('content'); ?>
<main class="main-content">
    <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => ['title' => 'Nouvel enseignant','subtitle' => 'Informations de l’enseignant, contact et progression','icon' => 'fa-solid fa-user-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Nouvel enseignant','subtitle' => 'Informations de l’enseignant, contact et progression','icon' => 'fa-solid fa-user-plus']); ?>
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
      <section class="form-card wizard-card">
        <div class="form-card-header">
          <div>
            <h2>Workflow enseignant</h2>
            <p class="breadcrumb">Saisie progressive du dossier administratif</p>
          </div>
          <span class="badge badge-primary">3 etapes</span>
        </div>

        <form class="teacher-form" data-teacher-wizard novalidate>
          <div class="wizard-progress" aria-label="Progression du formulaire">
            <button class="wizard-step" type="button" data-step-indicator="1">
              <span class="wizard-step-number">1</span>
              <span>Informations de l'enseignant</span>
            </button>
            <button class="wizard-step" type="button" data-step-indicator="2">
              <span class="wizard-step-number">2</span>
              <span>Contact</span>
            </button>
            <button class="wizard-step" type="button" data-step-indicator="3">
              <span class="wizard-step-number">3</span>
              <span>Profession &amp; affectation</span>
            </button>
          </div>

          <section class="wizard-panel" data-wizard-panel="1">
            <div class="form-section">
              <h3>Informations de l'enseignant</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="ia">Inspection academique (IA) <span class="required">*</span></label>
                  <select class="form-control" id="ia" name="ia" data-ia-select data-ief-target="#ief" required>
                    <option value="">Selectionner une IA</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="ief">Inspection de l'Education et de la Formation (IEF) <span class="required">*</span></label>
                  <select class="form-control" id="ief" name="ief" data-ief-select required disabled>
                    <option value="">Selectionner d'abord une IA</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="matricule">Matricule <span class="required">*</span></label>
                  <input class="form-control" id="matricule" name="matricule" type="text" required>
                </div>
                <div class="form-group">
                  <label for="nom">Nom <span class="required">*</span></label>
                  <input class="form-control" id="nom" name="nom" type="text" required>
                </div>
                <div class="form-group">
                  <label for="prenom">Prenom <span class="required">*</span></label>
                  <input class="form-control" id="prenom" name="prenom" type="text" required>
                </div>
                <div class="form-group">
                  <label for="dateNaissance">Date de naissance <span class="required">*</span></label>
                  <input class="form-control" id="dateNaissance" name="dateNaissance" type="date" required>
                </div>
                <div class="form-group full">
                  <label for="adresse">Adresse <span class="required">*</span></label>
                  <textarea class="form-control" id="adresse" name="adresse" required></textarea>
                </div>
              </div>
            </div>
          </section>

          <section class="wizard-panel" data-wizard-panel="2" hidden>
            <div class="form-section">
              <h3>Contact</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="email">Email <span class="required">*</span></label>
                  <input class="form-control" id="email" name="email" type="email" placeholder="prenom.nom@@sicore.sn" required>
                </div>
                <div class="form-group">
                  <label for="telephone">Telephone <span class="required">*</span></label>
                  <input class="form-control" id="telephone" name="telephone" type="tel" placeholder="77 000 00 00" required>
                </div>
              </div>
            </div>
          </section>

          <section class="wizard-panel" data-wizard-panel="3" hidden>
            <div class="form-section">
              <h3>Profession &amp; affectation</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="cfp">CFP <span class="required">*</span></label>
                  <input class="form-control" id="cfp" name="cfp" type="text" required>
                </div>
                <div class="form-group">
                  <label for="grade">Grade <span class="required">*</span></label>
                  <input class="form-control" id="grade" name="grade" type="text" required>
                </div>
                <div class="form-group">
                  <label for="indice">Indice <span class="required">*</span></label>
                  <input class="form-control" id="indice" name="indice" type="number" min="1" required>
                </div>
                <div class="form-group">
                  <label for="dateAffectation">Date d'affectation <span class="required">*</span></label>
                  <input class="form-control" id="dateAffectation" name="dateAffectation" type="date" required>
                </div>
                <div class="form-group">
                  <label for="corps">Corps</label>
                  <input class="form-control" id="corps" name="corps" type="text">
                </div>
                <div class="form-group">
                  <label for="statut">Statut <span class="required">*</span></label>
                  <select class="form-control" id="statut" name="statut" required>
                    <option value="">Selectionner</option>
                    <option>Actif</option>
                    <option>En attente</option>
                    <option>Suspendu</option>
                  </select>
                </div>
              </div>
            </div>
          </section>

          <p class="form-status" data-form-status aria-live="polite"></p>
          <div class="form-actions">
            <a class="btn-secondary" href="<?php echo e(route('enseignants.index')); ?>" data-wizard-cancel>Annuler</a>
            <button class="btn-secondary" type="button" data-wizard-prev hidden>Precedent</button>
            <button class="btn-primary" type="button" data-wizard-next>Suivant</button>
            <button class="btn-primary" type="submit" data-wizard-submit hidden>Enregistrer l'enseignant</button>
          </div>
        </form>
      </section>
    </section>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/education-structures.js')); ?>" defer></script>
  <script src="<?php echo e(asset('assets/js/form-wizard.js')); ?>" defer></script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projets\sicoreFront\resources\views\pages\enseignants\create.blade.php ENDPATH**/ ?>