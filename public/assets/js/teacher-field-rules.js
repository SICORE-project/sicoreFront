(function () {
  'use strict';
  var rules = {
    nom: /^[\p{L}\p{M} '’\-]*$/u,
    prenom: /^[\p{L}\p{M} '’\-]*$/u,
    telephone: /^\+?[0-9 ()\-]*$/,
    indice: /^[0-9]*$/,
    cni: /^[0-9]*$/,
    annee_recrutement: /^[0-9]*$/,
    nombre_enfants: /^[0-9]*$/,
    nombre_femmes: /^[0-9]*$/,
    'compte_bancaire[code_guichet]': /^[0-9]*$/,
    'compte_bancaire[cle_rib]': /^[0-9]*$/
  };
  function ruleFor(field) {
    return field.matches('.teacher-form input') && rules[field.name];
  }
  document.addEventListener('beforeinput', function (event) {
    var field = event.target;
    var rule = ruleFor(field);
    if (!rule || event.isComposing || !String(event.inputType).startsWith('insert')) return;
    var data = event.data || (event.dataTransfer && event.dataTransfer.getData('text/plain'));
    if (!data) return;
    var start = field.selectionStart;
    var end = field.selectionEnd;
    var candidate = start === null ? data : field.value.slice(0, start) + data + field.value.slice(end);
    if (!rule.test(candidate)) event.preventDefault();
  });
  document.addEventListener('input', function (event) {
    var field = event.target;
    var rule = ruleFor(field);
    if (!rule || event.isComposing) return;
    var invalid = !rule.test(field.value) || field.validity.badInput;
    var message = ['nom', 'prenom'].includes(field.name)
      ? 'Utilisez des lettres, espaces, apostrophes ou traits d’union.'
      : 'Ce champ n’accepte pas de lettres.';
    field.setCustomValidity(invalid ? message : '');
    field.classList.toggle('is-invalid', invalid);
    field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
  });
})();
