(function () {
  'use strict';
  var modal = document.getElementById('delete-confirmation');
  if (!modal) return;
  var pendingForm = null;
  var trigger = null;
  var submitting = false;
  var accept = modal.querySelector('[data-delete-accept]');

  // Intercepte aussi Entrée et requestSubmit(), avant les anciens gestionnaires.
  document.addEventListener('submit', function (event) {
    var form = event.target;
    var method = form.querySelector('input[name="_method"]');
    if (!method || method.value.toUpperCase() !== 'DELETE') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (submitting || modal.open) return;
    pendingForm = form;
    trigger = event.submitter || document.activeElement;
    var row = form.closest('tr');
    var label = form.dataset.deleteLabel || (row && row.cells[0] && row.cells[0].textContent.trim());
    var message = form.dataset.confirmMessage || (label ? 'Voulez-vous supprimer « ' + label + ' » ?' : 'Voulez-vous supprimer cet élément ?');
    modal.querySelector('#delete-confirmation-description').textContent = message;
    modal.showModal();
    modal.querySelector('[data-delete-cancel]').focus();
  }, true);

  modal.querySelectorAll('[data-delete-cancel]').forEach(function (button) {
    button.addEventListener('click', function () { if (!submitting) modal.close(); });
  });
  modal.addEventListener('cancel', function (event) {
    if (submitting) event.preventDefault();
    event.stopPropagation();
  });
  modal.addEventListener('keydown', function (event) { event.stopPropagation(); });
  modal.addEventListener('click', function (event) {
    var box = modal.getBoundingClientRect();
    if (!submitting && event.target === modal && (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom)) modal.close();
  });
  modal.addEventListener('close', function () {
    pendingForm = null;
    if (trigger && trigger.isConnected) trigger.focus();
  });
  accept.addEventListener('click', function () {
    if (!pendingForm || submitting) return;
    submitting = true;
    accept.disabled = true;
    accept.textContent = 'Suppression…';
    modal.setAttribute('aria-busy', 'true');
    // Conserve l’action, le jeton CSRF et la méthode DELETE du formulaire.
    HTMLFormElement.prototype.submit.call(pendingForm);
  });
  window.addEventListener('pageshow', function () {
    submitting = false;
    accept.disabled = false;
    accept.textContent = 'Supprimer';
    modal.removeAttribute('aria-busy');
    if (modal.open) modal.close();
  });
})();
