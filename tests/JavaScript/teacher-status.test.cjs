const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('resources/views/pages/enseignants/index.blade.php', 'utf8');
const names = ['isFonctionnaireCorpsValue', 'isFonctionnaireCorps', 'applyTeacherStatus', 'calculateCreateParts', 'calculateEditParts'];
const script = names.map(name => {
  const start = source.indexOf(`  function ${name}(`);
  assert.notEqual(start, -1);
  return source.slice(start, source.indexOf('\n  }', start) + 4);
}).join('\n');

for (const prefix of ['teacher-', 'edit-teacher-']) {
  function setup(official) {
    const fields = {};
    const step = { hidden: false, style: {} };
    for (const name of ['statut-enseignant', 'corps', 'salaire', 'date-recrutement', 'generation', 'diplome', 'statut', 'observations', 'couple', 'enfants', 'femmes', 'conjoint-travaille', 'parts']) {
      const button = { disabled: false };
      const group = { hidden: false, style: {}, querySelectorAll: () => [button] };
      fields[prefix + name] = { value: '', disabled: false, required: false, classList: { remove() {} }, closest: () => group };
    }
    const status = fields[prefix + 'statut-enseignant'];
    status.selectedIndex = 0;
    status.options = [{ value: '1', textContent: official ? 'Fonctionnaire' : 'Contractuel', dataset: { corpsCode: official ? 'FONC' : 'CONT' } }];
    const context = vm.createContext({ createStep: 3, document: { getElementById: id => fields[id], querySelector: () => step } });
    vm.runInContext(script, context);
    return { fields, step, context };
  }
  test(`${prefix}: only officials have corps; career and family are hidden for officials`, () => {
    for (const official of [true, false]) {
      const { fields, step, context } = setup(official);
      context.createStep = 1;
      context.applyTeacherStatus(prefix);
      assert.equal(fields[prefix + 'corps'].closest().hidden, !official);
      assert.equal(fields[prefix + 'corps'].required, official);
      assert.equal(fields[prefix + 'corps'].disabled, !official);
      for (const name of ['salaire', 'date-recrutement', 'generation', 'diplome', 'statut', 'observations']) {
        assert.equal(fields[prefix + name].disabled, official, name);
        const visible = !official && prefix !== 'teacher-';
        assert.equal(fields[prefix + name].closest().hidden, !visible, name);
        assert.equal(fields[prefix + name].closest().style.display, visible ? '' : 'none', name);
        assert.equal(fields[prefix + name].closest().querySelectorAll()[0].disabled, official, name);
      }
      assert.equal(step.hidden, official);
      assert.equal(step.style.display, official ? 'none' : '');
      assert.equal(fields[prefix + 'enfants'].required, !official);
      for (const name of ['couple', 'enfants', 'femmes', 'conjoint-travaille', 'parts']) {
        if (official) {
          assert.equal(fields[prefix + name].closest().hidden, true, name);
          assert.equal(fields[prefix + name].disabled, true, name);
        }
      }
    }
  });
  test(`${prefix}: single, widowed and divorced teachers retain required children and calculated parts`, () => {
    const { fields, context } = setup(false);
    for (const state of ['celibataire', 'marie', 'veuf', 'divorce']) {
      fields[prefix + 'couple'].value = state;
      fields[prefix + 'enfants'].value = '3';
      fields[prefix + 'femmes'].value = '1';
      fields[prefix + 'conjoint-travaille'].value = '0';
      context[prefix === 'teacher-' ? 'calculateCreateParts' : 'calculateEditParts']();
      assert.equal(fields[prefix + 'enfants'].value, '3');
      assert.equal(fields[prefix + 'enfants'].disabled, false);
      assert.equal(fields[prefix + 'enfants'].required, true);
      assert.equal(fields[prefix + 'parts'].value, 3.5);
      assert.equal(fields[prefix + 'femmes'].disabled, state !== 'marie');
    }
  });
  test(`${prefix}: switching to a non-official status clears the previously selected corps`, () => {
    const { fields, context } = setup(true);
    fields[prefix + 'corps'].value = '5';
    context.applyTeacherStatus(prefix);
    assert.equal(fields[prefix + 'corps'].value, '5');
    fields[prefix + 'statut-enseignant'].options[0].textContent = 'Vacataire';
    // A stale code must not override the newly selected status label.
    fields[prefix + 'statut-enseignant'].options[0].dataset.corpsCode = 'FONC';
    context.applyTeacherStatus(prefix);
    assert.equal(fields[prefix + 'corps'].value, '');
    assert.equal(fields[prefix + 'corps'].required, false);
    assert.equal(fields[prefix + 'corps'].disabled, true);
  });
  test(`${prefix}: changing status refreshes career and family visibility during entry`, () => {
    const { fields, step, context } = setup(true);
    const refresh = prefix === 'teacher-' ? 'refreshCreateTeacherStatus' : 'refreshEditTeacherStatus';
    const start = source.indexOf(`  function ${refresh}(`);
    assert.notEqual(start, -1);
    const calls = [];
    context.createStep = 2;
    context.editStep = 2;
    context.showCreateStep = () => context.applyTeacherStatus(prefix);
    context.showEditStep = () => context.applyTeacherStatus(prefix);
    context.updateEditCategorieVisibility = () => {};
    context.updateTeacherIdentity = () => {};
    context.formatTeacherMatricule = value => calls.push(value);
    context.validateTeacherIdentity = () => {};
    vm.runInContext(source.slice(start, source.indexOf('\n  }', start) + 4), context);
    context[refresh]();
    assert.equal(fields[prefix + 'diplome'].closest().hidden, true);
    assert.equal(step.hidden, true);
    const status = fields[prefix + 'statut-enseignant'].options[0];
    status.textContent = 'Contractuel';
    status.dataset.corpsCode = 'CONT';
    context[refresh]();
    assert.equal(fields[prefix + 'diplome'].closest().hidden, false);
    assert.equal(fields[prefix + 'diplome'].disabled, false);
    assert.equal(step.hidden, false);
    assert.deepEqual(calls, [prefix, prefix]);
  });
}
