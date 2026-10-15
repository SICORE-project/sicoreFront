const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync('resources/views/pages/enseignants/index.blade.php', 'utf8');
const functions = ['isFonctionnaireCorpsValue', 'isFonctionnaireCorps', 'updateTeacherIdentity', 'teacherMatriculeLetter', 'formatTeacherMatricule', 'validateTeacherIdentity'].map(name => {
  const start = source.indexOf(`  function ${name}(`);
  assert.notEqual(start, -1);
  return source.slice(start, source.indexOf('\n  }', start) + 4);
}).join('\n');

function setup(prefix) {
  const fields = {};
  for (const name of ['statut-enseignant', 'matricule', 'indice']) {
    const wrapper = { hidden: false, style: {} };
    const classes = new Set();
    fields[prefix + name] = {
      value: '', disabled: false, required: false, validity: { badInput: false },
      closest: () => wrapper,
      setCustomValidity(message) { this.message = message; },
      setAttribute(name, value) { this[name] = value; },
      classList: { toggle: (name, value) => value ? classes.add(name) : classes.delete(name) },
    };
    fields[prefix + name + '-error'] = { hidden: true, textContent: '' };
  }
  const corps = fields[prefix + 'statut-enseignant'];
  Object.defineProperty(corps, 'options', { get: () => [{ value: corps.value, textContent: corps.value === 'fonctionnaire' ? 'Fonctionnaire' : 'Vacataire', dataset: { corpsCode: corps.value === 'fonctionnaire' ? 'FONC' : 'VAC' } }] });
  corps.selectedIndex = 0;
  const context = vm.createContext({ document: { getElementById: id => fields[id] } });
  vm.runInContext(functions, context);
  return { fields, context, status: fields[prefix + 'statut-enseignant'], matricule: fields[prefix + 'matricule'], indice: fields[prefix + 'indice'] };
}

for (const prefix of ['teacher-', 'edit-teacher-']) {
  test(`${prefix}: checksum uses absolute difference, zero as Z, and no modulo`, () => {
    const { context, status, matricule } = setup(prefix);
    for (const [digits, letter] of [['000000', 'Z'], ['000001', 'A'], ['100000', 'A'], ['090907', 'Y'], ['909090', null], ['543678', 'C']]) {
      assert.equal(context.teacherMatriculeLetter(digits), letter);
    }
    status.value = 'fonctionnaire';
    matricule.value = '909090/F';
    context.formatTeacherMatricule(prefix, {});
    assert.equal(matricule.value, '909090');
    context.validateTeacherIdentity(prefix, true);
    assert.match(matricule.message, /dépasse 25/);
    matricule.value = '000001';
    context.formatTeacherMatricule(prefix, { isComposing: true });
    assert.equal(matricule.value, '000001');
    context.formatTeacherMatricule(prefix, {});
    assert.equal(matricule.value, '000001/A');
  });

  test(`${prefix}: civil servant requires six digits and displays required index`, () => {
    const { context, status, matricule, indice } = setup(prefix);
    status.value = 'fonctionnaire';
    context.updateTeacherIdentity(prefix, true);
    assert.equal(indice.closest().hidden, false);
    assert.equal(indice.required, true);
    assert.equal(indice.disabled, false);
    assert.equal(matricule.pattern, '[0-9]{6}/[A-Z]');
    matricule.value = '001234/F';
    context.validateTeacherIdentity(prefix, true);
    assert.equal(matricule.message, '');
    assert.match(indice.message, /obligatoire/);
    for (const value of ['0012', '12345', '123456']) {
      indice.value = value;
      context.validateTeacherIdentity(prefix, true);
      assert.equal(indice.message, '');
    }
  });

  test(`${prefix}: changing to non-civil servant hides and clears index and requires nine digits`, () => {
    const { context, status, matricule, indice } = setup(prefix);
    status.value = 'fonctionnaire';
    indice.value = '1500';
    matricule.value = '001234/F';
    context.updateTeacherIdentity(prefix, true);
    status.value = 'non_fonctionnaire';
    context.updateTeacherIdentity(prefix, true);
    context.validateTeacherIdentity(prefix, true);
    assert.equal(indice.closest().hidden, true);
    assert.equal(indice.disabled, true);
    assert.equal(indice.required, false);
    assert.equal(indice.value, '');
    assert.equal(indice.message, '');
    assert.match(matricule.message, /9 chiffres/);
    matricule.value = '000001234/H';
    context.validateTeacherIdentity(prefix, true);
    assert.equal(matricule.message, '');
  });

  test(`${prefix}: letters, spaces, symbols, Unicode digits and wrong lengths are rejected`, () => {
    const { context, status, matricule } = setup(prefix);
    status.value = 'fonctionnaire';
    for (const value of ['A12345/F', ' 001234/F', '001234/F ', '12 345/F', '123-45/F', '١٢٣٤٥٦/F', '12345/F', '1234567/F', '001234/f', '001234/FF', '001234']) {
      matricule.value = value;
      context.validateTeacherIdentity(prefix, true);
      assert.match(matricule.message, /6 chiffres/, value);
      assert.equal(matricule.value, value, 'invalid input must not be silently rewritten');
    }
  });

  test(`${prefix}: invalid indices are refused`, () => {
    const { context, status, indice } = setup(prefix);
    status.value = 'fonctionnaire';
    for (const value of ['123', '-1234', '12.34', '1e3', 'abc', '1234567']) {
      indice.value = value;
      context.validateTeacherIdentity(prefix, true);
      assert.match(indice.message, /4 et 6 chiffres/, value);
    }
  });

  test(`${prefix}: index remains included on submission after leaving the identity step`, () => {
    const { context, status, indice } = setup(prefix);
    status.value = 'fonctionnaire';
    indice.value = '1500';
    context.updateTeacherIdentity(prefix, false);
    assert.equal(indice.closest().hidden, true);
    assert.equal(indice.disabled, false);
    assert.equal(indice.required, true);
    assert.equal(indice.value, '1500');
  });
}

for (const prefix of ['teacher-', 'edit-teacher-']) {
  test(`${prefix}: letter is generated from the digits for each status`, () => {
    const { context, status, matricule } = setup(prefix);
    for (const [corps, digits, letter] of [['fonctionnaire', '001234', 'B'], ['vacataire', '202409675', 'E']]) {
      status.value = corps;
      matricule.value = digits;
      context.formatTeacherMatricule(prefix, { inputType: 'insertText' });
      assert.equal(matricule.value, digits + '/' + letter);
      matricule.value = digits + 'F';
      context.formatTeacherMatricule(prefix, { inputType: 'insertFromPaste' });
      assert.equal(matricule.value, digits + '/' + letter);
      context.formatTeacherMatricule(prefix, {});
      assert.equal(matricule.value, digits + '/' + letter);
      matricule.value = digits;
      context.formatTeacherMatricule(prefix, { inputType: 'deleteContentBackward' });
      assert.equal(matricule.value, digits);
      matricule.value = digits + '/';
      context.formatTeacherMatricule(prefix, { inputType: 'deleteContentBackward' });
      assert.equal(matricule.value, digits);
      context.formatTeacherMatricule(prefix);
      assert.equal(matricule.value, digits + '/' + letter);
    }
  });
}
