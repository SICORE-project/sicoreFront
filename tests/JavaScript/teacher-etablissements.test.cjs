const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('resources/views/pages/enseignants/index.blade.php', 'utf8');
const loaders = source.slice(source.indexOf('  function resetTeacherEtablissements('), source.indexOf('  if (ia && ief) {'));
class Option {
  constructor(text, value) { this.textContent = text; this.value = String(value); }
}
function select(value = '') {
  return {
    value, options: [], dataset: { etablissementsUrl: '/etablissements', iefsUrl: '/iefs' }, disabled: false,
    add(option) { this.options.push(option); },
    replaceChildren(...options) { this.options = options; this.value = ''; },
    set innerHTML(value) { this.replaceChildren(new Option(value, '')); },
  };
}
function setup() {
  const requests = [];
  const context = vm.createContext({ Option, Map, fetch: url => new Promise(resolve => requests.push({ url, resolve })) });
  vm.runInContext(loaders, context);
  const ief = select('2');
  const lieu = select('99');
  ief.closest = () => ({ querySelector: () => lieu });
  return { context, requests, ief, lieu };
}
const response = items => ({ ok: true, json: async () => ({ items }) });
const flush = () => new Promise(resolve => setImmediate(resolve));
test('no IEF clears and disables establishments without a request', async () => {
  const { context, requests, ief, lieu } = setup();
  ief.value = '';
  await context.loadTeacherEtablissements(ief, lieu, '');
  assert.equal(lieu.value, '');
  assert.equal(lieu.disabled, true);
  assert.equal(requests.length, 0);
});
test('loads only the selected IEF and restores the saved establishment on edit', async () => {
  const { context, requests, ief, lieu } = setup();
  const pending = context.loadTeacherEtablissements(ief, lieu, '7');
  assert.equal(lieu.value, '');
  assert.equal(lieu.disabled, true);
  assert.equal(requests[0].url, '/etablissements?ief_id=2');
  requests[0].resolve(response([{ id: 7, libelle: 'École', ief_id: 2 }, { id: 8, libelle: 'Autre IEF', ief_id: 3 }]));
  await pending;
  assert.deepEqual(lieu.options.map(option => option.value), ['', '7']);
  assert.equal(lieu.value, '7');
  assert.equal(lieu.disabled, false);
});
test('a late response cannot replace establishments after changing IEF', async () => {
  const { context, requests, ief, lieu } = setup();
  const first = context.loadTeacherEtablissements(ief, lieu, '');
  ief.value = '3';
  const second = context.loadTeacherEtablissements(ief, lieu, '');
  requests[1].resolve(response([{ id: 8, libelle: 'École 3', ief_id: 3 }]));
  await second;
  requests[0].resolve(response([{ id: 7, libelle: 'École 2', ief_id: 2 }]));
  await first;
  assert.deepEqual(lieu.options.map(option => option.value), ['', '8']);
});
test('changing IA immediately clears establishments and waits for a new IEF', async () => {
  const { context, requests, ief, lieu } = setup();
  const ia = select('4');
  context.loadTeacherHierarchy(ia, ief, '', '');
  assert.equal(lieu.value, '');
  assert.equal(lieu.disabled, true);
  requests[0].resolve(response([{ id: 3, libelle: 'IEF 4' }]));
  await flush();
  assert.equal(requests.length, 1);
  assert.equal(lieu.disabled, true);
});
test('edit loads saved IEF before requesting its establishments', async () => {
  const { context, requests, ief, lieu } = setup();
  context.loadTeacherHierarchy(select('4'), ief, '2', '7');
  requests[0].resolve(response([{ id: 2, libelle: 'IEF 2' }]));
  await flush();
  assert.equal(requests[1].url, '/etablissements?ief_id=2');
  requests[1].resolve(response([{ id: 7, libelle: 'École', ief_id: 2 }]));
  await flush();
  assert.equal(lieu.value, '7');
});
test('an establishment created while loading remains selected', async () => {
  const { context, requests, ief, lieu } = setup();
  const pending = context.loadTeacherEtablissements(ief, lieu, '');
  lieu.createdEtablissements = new Map([['42', { id: 42, libelle: 'Nouvelle école', ief_id: 2 }]]);
  lieu.value = '42';
  requests[0].resolve(response([]));
  await pending;
  assert.equal(lieu.value, '42');
  assert.ok(lieu.options.some(option => option.value === '42'));
});
test('loading errors leave a clear disabled error state', async () => {
  const { context, requests, ief, lieu } = setup();
  const pending = context.loadTeacherEtablissements(ief, lieu, '');
  requests[0].resolve({ ok: false });
  await pending;
  assert.equal(lieu.disabled, true);
  assert.match(lieu.options[0].textContent, /Impossible/);
});
