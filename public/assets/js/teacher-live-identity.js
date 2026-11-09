(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var config = document.getElementById('teacher-page-config');
    if (!config) return;
    document.querySelectorAll('#teacher-create-modal .teacher-form, #teacher-edit-form').forEach(function (form) {
      ['cni', 'matricule', 'indice', 'email'].forEach(function (name) {
        var field = form.querySelector('[name="' + name + '"]');
        if (!field) return;
        var timer, controller, version = 0, lastKey = '', lastMessage = '';
        function check(immediate) {
          clearTimeout(timer);
          if (controller) controller.abort();
          var current = ++version;
          var value = field.value.trim();
          var id = form.querySelector('[name="_editing_id"]')?.value || '';
          var lookupKey = id + ':' + value;
          if (lastKey === lookupKey && !field.disabled) {
            field.dataset.remoteError = lastMessage;
            window.validateTeacherField(field);
            return;
          }
          delete field.dataset.remoteError;
          if (field.disabled || !value || field.validity.patternMismatch || field.validity.typeMismatch
              || (name === 'cni' && !/^[0-9]{13,15}$/.test(value))
              || (name === 'matricule' && !/^[0-9]{6,9}\/[A-Z]$/.test(value))) {
            window.validateTeacherField(field);
            return;
          }
          field.dataset.remoteError = 'Vérification en cours, veuillez patienter.';
          window.validateTeacherField(field);
          timer = setTimeout(async function () {
            controller = new AbortController();
            var timeout = setTimeout(function () { controller.abort(); }, 12000);
            try {
              var response = await fetch(config.dataset.checkIdentityUrl, {
                method: 'POST', signal: controller.signal,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name="_token"]').value },
                body: JSON.stringify({ field: name, value: value, id: id || null })
              });
              if (!response.ok) throw new Error('unavailable');
              var result = await response.json();
              if (typeof result.available !== 'boolean') throw new Error('invalid response');
              if (current !== version || value !== field.value.trim() || id !== (form.querySelector('[name="_editing_id"]')?.value || '')) return;
              lastKey = lookupKey;
              lastMessage = result.available ? '' : result.message;
              field.dataset.remoteError = lastMessage;
            } catch (error) {
              if (current !== version) return;
              field.dataset.remoteError = 'Vérification impossible. Quittez puis revenez dans ce champ pour réessayer.';
            } finally {
              clearTimeout(timeout);
              if (current === version) window.validateTeacherField(field);
            }
          }, immediate ? 0 : 450);
        }
        field.addEventListener('input', function () { queueMicrotask(function () { check(false); }); });
        field.addEventListener('blur', function () { check(true); });
        form.addEventListener('reset', function () {
          ++version; clearTimeout(timer); if (controller) controller.abort();
          delete field.dataset.remoteError;
        });
      });
    });
    document.querySelectorAll('[data-edit-teacher]').forEach(function (button) {
      button.addEventListener('click', function () {
        document.querySelectorAll('#teacher-edit-form [data-remote-error]').forEach(function (field) {
          delete field.dataset.remoteError;
          queueMicrotask(function () { window.validateTeacherField(field); });
        });
      });
    });
  });
})();
