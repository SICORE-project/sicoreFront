(function () {
  'use strict';
  function message(field, label) {
    if (field.disabled || field.type === 'hidden') return '';
    var value = String(field.value || '').trim();
    var v = field.validity;
    if (!value && field.required && field.name === 'compte_bancaire[cle_rib]') return 'Complétez correctement le code banque, le guichet et le numéro de compte pour calculer la clé RIB.';
    if (!value && field.required) return field.tagName === 'SELECT'
      ? 'Veuillez sélectionner : ' + label + '.' : 'Veuillez renseigner : ' + label + '.';
    if (!value && !v.badInput) return '';
    if (v.customError) return field.validationMessage;
    if (field.dataset && field.dataset.remoteError) return field.dataset.remoteError;
    var lengths = { 'compte_bancaire[code_guichet]': 5, 'compte_bancaire[numero_compte]': 12 };
    if (lengths[field.name] && !new RegExp('^[0-9]{' + lengths[field.name] + '}$').test(value)) {
      return label + ' doit contenir exactement ' + lengths[field.name] + ' chiffres (' + value.length + ' caractères saisis).';
    }
    if (field.name === 'compte_bancaire[code_banque]' && !/^SN[0-9]{3}$/.test(value)) return 'Le code banque doit être SN suivi de 3 chiffres, par exemple SN012.';
    if (field.name === 'compte_bancaire[cle_rib]' && !/^[0-9]{2}$/.test(value)) return 'Complétez correctement le code banque, le guichet et le numéro de compte pour calculer la clé RIB.';
    if (['nom', 'prenom'].includes(field.name) && !/^[\p{L}][\p{L}\p{M} ’'\-]*$/u.test(value)) return label + ' doit commencer par une lettre et contenir uniquement des lettres, espaces, apostrophes ou traits d’union.';
    if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Veuillez saisir une adresse e-mail valide, par exemple nom@exemple.com.';
    if (field.name === 'date_naissance' && v.rangeOverflow) return 'L’enseignant doit avoir au moins 18 ans.';
    if (v.badInput || v.typeMismatch) return 'Veuillez saisir une valeur valide pour : ' + label + '.';
    if (v.tooShort) return label + ' doit contenir au moins ' + field.minLength + ' caractères.';
    if (v.tooLong) return label + ' ne doit pas dépasser ' + field.maxLength + ' caractères.';
    if (v.rangeUnderflow) return label + ' doit être supérieur ou égal à ' + field.min + '.';
    if (v.rangeOverflow) return label + ' doit être inférieur ou égal à ' + field.max + '.';
    if (v.stepMismatch) return 'Veuillez saisir un nombre respectant le pas de ' + (field.step || '1') + ' pour : ' + label + '.';
    if (v.patternMismatch) {
      if (field.name === 'cni') return 'La carte d’identité doit contenir entre 13 et 15 chiffres.';
      if (field.name === 'indice') return 'L’indice doit contenir entre 4 et 6 chiffres.';
      if (field.name === 'matricule') return field.title || 'Veuillez vérifier le format du matricule.';
      return field.title || 'Le format de « ' + label + ' » est incorrect.';
    }
    return v.valid ? '' : 'Veuillez vérifier : ' + label + '.';
  }
  if (typeof module !== 'undefined') module.exports = { message: message };
  if (typeof window === 'undefined') return;
  function render(field, forced) {
    if (!field || !field.closest('.teacher-form') || !field.name || field.type === 'hidden') return true;
    var group = field.closest('.form-group');
    if (!group) return true;
    var label = group.querySelector('label');
    label = label ? label.textContent.replace(/\*/g, '').trim() : field.name;
    var text = forced === undefined ? message(field, label) : forced;
    var error = document.getElementById(field.id + '-error');
    if (!error) {
      error = document.createElement('span');
      error.id = field.id + '-error';
      error.setAttribute('aria-live', 'polite');
      group.appendChild(error);
    }
    error.classList.add('teacher-field-error');
    error.textContent = text;
    error.hidden = !text;
    var ids = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    ids.add(error.id);
    field.setAttribute('aria-describedby', Array.from(ids).join(' '));
    field.setAttribute('aria-invalid', text ? 'true' : 'false');
    field.classList.toggle('is-invalid', !!text);
    return !text;
  }
  window.validateTeacherField = render;
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.teacher-form').forEach(function (form) {
      form.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.addEventListener('blur', function () { render(field); });
        ['input', 'change'].forEach(function (event) {
          field.addEventListener(event, function () {
            // Run after the identity, bank and conditional-field handlers.
            queueMicrotask(function () {
              form.querySelectorAll('[aria-invalid="true"]').forEach(function (other) {
                if (other.disabled || other.name === 'compte_bancaire[cle_rib]') render(other);
              });
              render(field);
            });
          });
        });
      });
      form.addEventListener('invalid', function (event) { event.preventDefault(); render(event.target); }, true);
      form.addEventListener('reset', function () {
        form.querySelectorAll('.teacher-field-error').forEach(function (error) { error.hidden = true; error.textContent = ''; });
      });
    });
    var data = document.getElementById('teacher-validation-errors');
    if (data) queueMicrotask(function () {
      var state = JSON.parse(data.textContent);
      var modal = document.getElementById(state.editing ? 'teacher-edit-modal' : 'teacher-create-modal');
      Object.entries(state.errors).forEach(function (entry) {
        var name = entry[0].split('.').map(function (part, i) { return i ? '[' + part + ']' : part; }).join('');
        var field = Array.from(modal.querySelectorAll('[name]')).find(function (item) { return item.name === name; });
        if (field) render(field, entry[1].join(' '));
      });
    });
  });
})();
