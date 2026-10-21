const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('resources/views/pages/enseignants/index.blade.php', 'utf8');
const append = source.slice(source.indexOf('  function appendReferentielOption('), source.indexOf("  document.querySelectorAll('[data-referentiel-open]')"));
const diplomas = source.slice(source.indexOf('  function diplomaLabel('), source.indexOf("  ['teacher-diplome', 'edit-teacher-diplome']"));
class Option {
  constructor(text, value) { this.textContent = text; this.value = String(value); this.dataset = {}; }
  cloneNode() { const copy = new Option(this.textContent, this.value); copy.dataset = { ...this.dataset }; return copy; }
}
function setup(type) {
  const selects = {};
  const buttons = [];
  for (const prefix of ['teacher-', 'edit-teacher-']) {
    const ia = { value: '1' };
    const ief = {
      value: '1', options: [new Option('IEF 1', '1')],
      add(option) { this.options.push(option); },
      replaceChildren(...options) { this.options = options; this.value = ''; },
      dispatchEvent() {},
    };
    const select = {
      options: [new Option('Choisir', ''), new Option('Ancien', '9')], value: '9', disabled: false, events: [],
      closest: () => ({ querySelector: name => name === '[name="ief_id"]' ? ief : ia }),
      add(option) { this.options.push(option); },
      appendChild(option) { this.add(option); },
      replaceChildren(...options) { this.options = options; this.value = ''; },
      dispatchEvent(event) { this.events.push(event.type); },
    };
    if (type === 'diplome') select.diplomaOptions = select.options.map(option => option.cloneNode());
    selects[prefix + type] = select;
    buttons.push({ dataset: { referentielTarget: prefix + type } });
  }
  const context = vm.createContext({ Option, Event, Map, document: {
    getElementById: id => selects[id], querySelectorAll: () => buttons,
  } });
  vm.runInContext(diplomas + append, context);
  return { context, selects };
}
for (const type of ['ia', 'ief', 'corps', 'categorie', 'diplome', 'discipline', 'lieu_service', 'banque']) {
  for (const targetPrefix of ['teacher-', 'edit-teacher-']) {
    test(`${type}: immediately selects new record in ${targetPrefix} and updates other form`, () => {
      const { context, selects } = setup(type);
      const item = { id: 42, libelle: 'Nouveau', ia_id: 1, ief_id: 1, corps_id: 3, categorie_id: 4, salaire_brut: '123456.50', code: 'CTR' };
      const targetId = targetPrefix + type;
      context.appendReferentielOption(type, targetId, item);
      for (const [id, select] of Object.entries(selects)) {
        assert.ok(select.options.some(option => option.value === '42'));
        assert.equal(String(select.value), id === targetId ? '42' : '9');
        assert.equal(select.events.length, id === targetId ? 1 : 0);
        if (type === 'diplome') {
          context.hideDuplicateDiplomas(select);
          assert.ok(select.options.some(option => option.value === '42'));
          assert.equal(select.diplomaOptions.find(option => option.value === '42').dataset.salaireBrut, '123456.50');
        }
      }
      context.appendReferentielOption(type, targetId, item);
      assert.equal(selects[targetId].options.filter(option => option.value === '42').length, 1);
    });
  }
}
test('IEF created in another IA switches the target parent without polluting the other form', () => {
  const { context, selects } = setup('ief');
  context.appendReferentielOption('ief', 'teacher-ief', { id: 42, libelle: 'IEF 2', ia_id: 2 });
  assert.equal(selects['teacher-ief'].closest().querySelector().value, 2);
  assert.equal(String(selects['teacher-ief'].value), '42');
  assert.equal(selects['edit-teacher-ief'].options.some(option => option.value === '42'), false);
});
const loader = source.slice(source.indexOf('  function loadTeacherIefs('), source.indexOf('  if (ia && ief) {'));
test('an establishment created in another IEF updates its parent and only the matching form', () => {
  const { context, selects } = setup('lieu_service');
  context.appendReferentielOption('lieu_service', 'teacher-lieu_service', {
    id: 42, libelle: 'École', ia_id: 2, ief_id: 3, ief: { libelle: 'IEF 3' },
  });
  const form = selects['teacher-lieu_service'].closest();
  assert.equal(String(form.querySelector('[name="ia_id"]').value), '2');
  assert.equal(String(form.querySelector('[name="ief_id"]').value), '3');
  assert.equal(String(selects['teacher-lieu_service'].value), '42');
  assert.equal(selects['edit-teacher-lieu_service'].options.some(option => option.value === '42'), false);
});
test('an IEF created during loading survives the response and remains selected', async () => {
  const { context, selects } = setup('ief');
  let resolve;
  context.fetch = () => new Promise(done => { resolve = done; });
  vm.runInContext(loader, context);
  const select = selects['teacher-ief'];
  const ia = select.closest().querySelector();
  ia.dataset = { iefsUrl: '/iefs' };
  context.loadTeacherIefs(ia, select, '');
  context.appendReferentielOption('ief', 'teacher-ief', { id: 42, libelle: 'Nouvelle IEF', ia_id: 1 });
  resolve({ ok: true, json: async () => ({ items: [{ id: 9, libelle: 'Ancienne IEF' }] }) });
  await new Promise(done => setImmediate(done));
  assert.equal(String(select.value), '42');
  assert.ok(select.options.some(option => option.value === '42'));
  assert.equal(select.disabled, false);
});
test('a late response for the old IA cannot replace an IEF created in a different IA', async () => {
  const { context, selects } = setup('ief');
  let resolve;
  context.fetch = () => new Promise(done => { resolve = done; });
  vm.runInContext(loader, context);
  const select = selects['teacher-ief'];
  const ia = select.closest().querySelector();
  ia.dataset = { iefsUrl: '/iefs' };
  context.loadTeacherIefs(ia, select, '');
  context.appendReferentielOption('ief', 'teacher-ief', { id: 42, libelle: 'IEF nouvelle IA', ia_id: 2 });
  resolve({ ok: true, json: async () => ({ items: [{ id: 9, libelle: 'Ancienne IA' }] }) });
  await new Promise(done => setImmediate(done));
  assert.equal(String(select.value), '42');
  assert.equal(select.options.some(option => option.value === '9'), false);
});
