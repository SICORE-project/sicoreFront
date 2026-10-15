const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const script = fs.readFileSync('public/assets/js/teacher-status-fields.js', 'utf8');
for (const modal of ['teacher-create-modal', 'teacher-edit-modal']) {
  test(`${modal}: corps updates through delegated events without the wizard script`, () => {
    const handlers = {};
    const button = { disabled: false };
    const group = { hidden: false, style: { setProperty(name, value) { this[name] = value; } }, querySelectorAll: () => [button] };
    const corps = { value: '5', classList: { remove() {} }, setCustomValidity() {}, setAttribute() {}, closest: () => group };
    const form = { querySelector: selector => selector === '[name="corps_id"]' ? corps : null };
    const status = { value: '1', selectedIndex: 0, options: [{ textContent: 'Fonctionnaire', dataset: { corpsCode: 'FONC' } }], matches: () => true, closest: selector => selector === 'form' ? form : { hidden: false } };
    vm.runInNewContext(script, { document: { addEventListener: (name, fn) => { handlers[name] = fn; }, querySelectorAll: () => [status] } });
    assert.equal(group.hidden, false);
    assert.equal(corps.required, true);
    for (const label of ['Vacataire', 'Contractuel', 'Fonctionnaire']) {
      status.options[0].textContent = label;
      handlers.change({ target: status });
      const official = label === 'Fonctionnaire';
      assert.equal(group.hidden, !official);
      assert.equal(group.style.display, official ? '' : 'none');
      assert.equal(corps.disabled, !official);
      assert.equal(corps.required, official);
      assert.equal(button.disabled, !official);
      if (!official) assert.equal(corps.value, '');
    }
  });
}

test('switching status removes the old suffix and updates length before entering more digits', () => {
  const handlers = {};
  const group = { hidden: false, style: { setProperty() {} }, querySelectorAll: () => [] };
  const corps = { closest: () => group, classList: { remove() {} }, setCustomValidity() {}, setAttribute() {} };
  const matricule = { id: 'teacher-matricule', value: '543678/C', classList: { toggle() {} }, setAttribute() {}, setCustomValidity(message) { this.message = message; }, matches: selector => selector.includes('[name="matricule"]'), closest: () => form };
  const status = { value: '1', selectedIndex: 0, options: [{ textContent: 'Fonctionnaire', dataset: { corpsCode: 'FONC' } }], matches: selector => selector.includes('[name="statut_enseignant_id"]'), closest: selector => selector === 'form' ? form : group };
  const form = { querySelector: selector => ({ '[name="corps_id"]': corps, '[name="matricule"]': matricule, '[name="statut_enseignant_id"]': status }[selector] || null) };
  vm.runInNewContext(script, { document: { addEventListener: (name, fn) => { handlers[name] = fn; }, querySelectorAll: () => [status] } });
  status.options[0].textContent = 'Vacataire';
  handlers.change({ target: status });
  assert.equal(matricule.maxLength, 11);
  assert.equal(matricule.pattern, '[0-9]{9}/[A-Z]');
  assert.equal(matricule.value, '543678');
  matricule.value = '20240967';
  handlers.input({ target: matricule });
  assert.equal(matricule.value, '20240967');
  assert.match(matricule.message, /Il manque 1 chiffre/);
  matricule.value = '202409675';
  handlers.input({ target: matricule });
  assert.equal(matricule.value, '202409675/E');
  assert.equal(matricule.message, '');
  status.options[0].textContent = 'Fonctionnaire';
  handlers.change({ target: status });
  assert.equal(matricule.maxLength, 11);
  assert.equal(matricule.value, '202409675');
  assert.match(matricule.message, /6 chiffres/);
  matricule.value = '543678';
  handlers.input({ target: matricule });
  assert.equal(matricule.value, '543678/C');
  assert.equal(matricule.message, '');
});

test('insertion after the generated letter is blocked while deletion and replacement remain possible', () => {
  const handlers = {};
  vm.runInNewContext(script, { document: { addEventListener: (name, fn) => { handlers[name] = fn; }, querySelectorAll: () => [] } });
  const field = { value: '543678/C', selectionStart: 8, selectionEnd: 8, matches: () => true };
  for (const inputType of ['insertText', 'insertFromPaste']) {
    const event = { target: field, inputType, preventDefault() { this.prevented = true; } };
    handlers.beforeinput(event);
    assert.equal(event.prevented, true);
  }
  for (const [inputType, start, end] of [['deleteContentBackward', 8, 8], ['insertText', 2, 3], ['insertFromPaste', 0, 8]]) {
    field.selectionStart = start;
    field.selectionEnd = end;
    const event = { target: field, inputType, preventDefault() { this.prevented = true; } };
    handlers.beforeinput(event);
    assert.equal(event.prevented, undefined);
  }
});
