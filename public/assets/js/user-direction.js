(function () {
  'use strict';
  function allowedTypes(slug, configured) {
    var specific = { super_admin: ['CI', 'DAGE'], admin: ['CI', 'DAGE'], agent_decpc: ['DECPC'], agent_drh: ['DRH'], gestionnaire_ia: [], enseignant: [] };
    if (Object.hasOwn(specific, slug)) return specific[slug];
    var central = ['CI', 'DAGE', 'DRH', 'DECPC'];
    var types = (configured || []).map(function (type) { return String(type).toUpperCase(); }).filter(function (type) { return central.includes(type); });
    return types.length ? types : ['DRH', 'DAGE', 'DECPC'];
  }
  if (typeof module !== 'undefined') module.exports = { allowedTypes: allowedTypes };
  if (typeof window !== 'undefined') window.UserDirections = { allowedTypes: allowedTypes };
})();
if (typeof document !== 'undefined') document.addEventListener('DOMContentLoaded', function () {
  'use strict';
  var form = document.getElementById('direction-create-form');
  if (!form) return;
  var error = document.getElementById('direction-create-error');
  var type = form.querySelector('[name="type"]');
  function configureTypes() {
    var role = document.getElementById('role_id');
    var option = role?.selectedOptions[0];
    var allowed = window.UserDirections.allowedTypes(option?.dataset.roleSlug, JSON.parse(option?.dataset.structureTypes || '[]'));
    type.replaceChildren(new Option('Sélectionner un type', ''));
    allowed.forEach(function (value) { type.add(new Option(value, value)); });
    if (allowed.length === 1) type.value = allowed[0];
    error.hidden = true;
  }
  document.querySelectorAll('[data-modal-open="direction-create-modal"]').forEach(function (button) {
    button.addEventListener('click', configureTypes);
  });

  form.querySelector('[name="code"]').addEventListener('input', function () { this.value = this.value.toUpperCase(); });
  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!form.reportValidity()) return;
    var button = form.querySelector('[type="submit"]');
    button.disabled = true;
    error.hidden = true;
    try {
      var response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      var payload = await response.json();
      if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat().join(' ') || payload.message || 'Enregistrement impossible.');
      if (!payload.data?.id) throw new Error('La direction enregistrée est introuvable.');
      var direction = Object.assign({}, payload.data, { perimetre: 'national' });
      if (!window.UserDirections.allowedTypes(document.getElementById('role_id')?.selectedOptions[0]?.dataset.roleSlug, JSON.parse(document.getElementById('role_id')?.selectedOptions[0]?.dataset.structureTypes || '[]')).includes(direction.type)) throw new Error('Cette direction est incompatible avec le r\u00f4le choisi.');
      document.dispatchEvent(new CustomEvent('direction-created', { detail: direction }));
      document.getElementById('direction-create-modal').hidden = true;
      form.reset();
    } catch (failure) {
      error.textContent = failure.message || 'Le service est indisponible. Veuillez réessayer.';
      error.hidden = false;
    } finally { button.disabled = false; }
  });
});
