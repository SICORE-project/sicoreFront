const { test } = require('node:test');
const assert = require('node:assert/strict');
const { message } = require('../../public/assets/js/teacher-validation.js');
function field(overrides = {}) {
  return { name: 'nom', value: '', required: false, type: 'text', tagName: 'INPUT', validity: { valid: true }, ...overrides };
}
test('required inputs and selects have explicit messages, optional and disabled fields do not', () => {
  assert.match(message(field({ required: true }), 'Nom'), /renseigner.*Nom/);
  assert.match(message(field({ required: true, tagName: 'SELECT' }), 'Banque'), /sélectionner.*Banque/);
  assert.equal(message(field(), 'Nom'), '');
  assert.equal(message(field({ required: true, disabled: true }), 'Nom'), '');
});
test('bank lengths and automatic key have actionable messages', () => {
  assert.match(message(field({ name: 'compte_bancaire[code_guichet]', value: '123' }), 'Code guichet'), /exactement 5 chiffres/);
  assert.match(message(field({ name: 'compte_bancaire[numero_compte]', value: '123' }), 'Compte'), /exactement 12 chiffres/);
  assert.match(message(field({ name: 'compte_bancaire[code_banque]', value: 'XX012' }), 'Banque'), /SN suivi de 3/);
  assert.match(message(field({ name: 'compte_bancaire[cle_rib]', required: true }), 'Cl?'), /calculer la clé RIB/);
});
test('identity, email, age and numeric constraints explain the correction', () => {
  assert.match(message(field({ value: '123' }), 'Nom'), /commencer par une lettre/);
  assert.match(message(field({ name: 'email', type: 'email', value: 'abc' }), 'Email'), /adresse e-mail valide/);
  assert.match(message(field({ name: 'date_naissance', value: '2020-01-01', validity: { rangeOverflow: true } }), 'Naissance'), /18 ans/);
  assert.match(message(field({ name: 'nombre_enfants', value: '-1', min: '0', validity: { rangeUnderflow: true } }), 'Enfants'), /supérieur ou égal à 0/);
  assert.equal(message(field({ value: 'Diop' }), 'Nom'), '');
});
test('existing specific validation messages are preserved', () => {
  assert.equal(message(field({ value: '123', validity: { customError: true }, validationMessage: 'Matricule déjà utilisé.' }), 'Matricule'), 'Matricule déjà utilisé.');
});

test('remote duplicate and pending messages block validation immediately', () => {
  assert.equal(message(field({ name: 'cni', value: '2432567865432', dataset: { remoteError: 'Duplicate CNI' } }), 'CNI'), 'Duplicate CNI');
  assert.equal(message(field({ name: 'cni', value: '2432567865432', dataset: { remoteError: '' } }), 'CNI'), '');
});
