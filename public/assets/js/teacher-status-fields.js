(function () {
  'use strict';

  function updateMatricule(form, status, official, changed, event) {
    var field = form.querySelector('[name="matricule"]');
    if (!field) return;
    var length = official ? 6 : 9;
    field.pattern = status.value ? '[0-9]{' + length + '}/[A-Z]' : '[0-9]{6}/[A-Z]|[0-9]{9}/[A-Z]';
    field.minLength = status.value ? length + 2 : 8;
    // Keep room for nine digits and the checksum after any status transition.
    // The pattern, rather than a stale typing limit, enforces the exact length.
    field.maxLength = 11;
    field.title = 'Saisissez ' + length + ' chiffres : la lettre après / est calculée automatiquement.';
    if (event && event.isComposing) return;
    if (changed) field.value = field.value.replace(/\/[A-Za-z]?$/, '');
    if (event && String(event.inputType).startsWith('delete')) {
      field.value = field.value.replace(/\/$/, '');
      return;
    }
    var match = field.value.match(new RegExp('^([0-9]{' + length + '})(?:/?[A-Za-z]?)$'));
    var message = '';
    if (status.value && match) {
      var difference = 0;
      for (var index = 0; index < match[1].length; index++) {
        difference += (index % 2 === 1 ? 1 : -1) * Number(match[1][index]);
      }
      var value = Math.abs(difference);
      if (value > 25) message = 'La différence entre les sommes des positions paires et impaires dépasse 25. Vérifiez les chiffres du matricule.';
      field.value = match[1] + (value > 25 ? '' : '/' + (value === 0 ? 'Z' : String.fromCharCode(64 + value)));
    } else if (status.value && field.value) {
      message = 'Le matricule doit contenir ' + length + ' chiffres suivis de la lettre calculée automatiquement.';
      if (/^[0-9]+$/.test(field.value) && field.value.length < length) {
        var missing = length - field.value.length;
        message = field.value.length + ' chiffres saisis sur ' + length + ' chiffres attendus. Il manque ' + missing
          + ' chiffre' + (missing > 1 ? 's' : '') + ' ; la lettre sera calculée automatiquement.';
      }
    }
    field.setCustomValidity(message);
    field.classList.toggle('is-invalid', !!message);
    field.setAttribute('aria-invalid', message ? 'true' : 'false');
    var error = form.querySelector('#' + field.id + '-error');
    if (error) { error.textContent = message; error.hidden = !message; }
  }

  function updateCorps(status, changed, event) {
    var form = status.closest('form');
    var corps = form && form.querySelector('[name="corps_id"]');
    if (!corps) return;
    var option = status.options[status.selectedIndex];
    var label = String(option && option.textContent || '').trim().toLowerCase();
    var code = String(option && option.dataset.corpsCode || '').trim().toLowerCase();
    var official = !!status.value && !/vacataire|contractuel/.test(label)
      && (['fonctionnaire', 'fonctionnaires'].includes(label) || ['fonc', 'fonctionnaire'].includes(code));
    var group = corps.closest('.form-group');
    var visible = official && !status.closest('.form-group').hidden;
    group.hidden = !visible;
    group.style.setProperty('display', visible ? '' : 'none', visible ? '' : 'important');
    corps.disabled = !official;
    corps.required = official;
    if (!official) {
      corps.value = '';
      corps.setCustomValidity('');
      corps.classList.remove('is-invalid');
      corps.setAttribute('aria-invalid', 'false');
    }
    group.querySelectorAll('[data-referentiel-open]').forEach(function (button) {
      button.disabled = !official;
    });
    updateMatricule(form, status, official, changed, event);
  }

  ['input', 'change'].forEach(function (eventName) {
    document.addEventListener(eventName, function (event) {
      if (event.target.matches('#teacher-create-modal [name="statut_enseignant_id"], #teacher-edit-modal [name="statut_enseignant_id"]')) {
        updateCorps(event.target, true);
      } else if (event.target.matches('#teacher-create-modal [name="matricule"], #teacher-edit-modal [name="matricule"]')) {
        var status = event.target.closest('form').querySelector('[name="statut_enseignant_id"]');
        if (status) updateCorps(status, false, event);
      }
    });
  });
  document.addEventListener('beforeinput', function (event) {
    var field = event.target;
    if (!field.matches('#teacher-create-modal [name="matricule"], #teacher-edit-modal [name="matricule"]')) return;
    if (!String(event.inputType).startsWith('insert') || event.isComposing) return;
    var suffix = field.value.match(/\/[A-Z]$/);
    if (!suffix) return;
    var slash = field.value.length - 2;
    if (field.selectionStart > slash && field.selectionStart === field.selectionEnd) {
      event.preventDefault();
    }
  });
  document.querySelectorAll('#teacher-create-modal [name="statut_enseignant_id"], #teacher-edit-modal [name="statut_enseignant_id"]').forEach(function (status) { updateCorps(status, false); });
})();
