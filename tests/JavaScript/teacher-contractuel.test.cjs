const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync('resources/views/pages/enseignants/index.blade.php', 'utf8');
const functions = [
  'isContractuelCorps', 'isVacataireCorps', 'diplomaLabel', 'hideDuplicateDiplomas',
  'applyDiplomeSalary', 'filterCategories', 'updateCreateCategorieVisibility',
  'showCreateStep', 'validateCreateStep', 'updateEditCategorieVisibility',
].map(name => {
  const start = source.indexOf(`  function ${name}(`);
  assert.notEqual(start, -1, `${name} is present in the teacher form`);
  const end = source.indexOf('\n  }', start) + '\n  }'.length;
  // Render the unrelated birth-date cutoff so the extracted Blade JavaScript parses.
  return source.slice(start, end).replace(/\{\{\s*now\(\)->subYears\(18\)->format\('Y-m-d'\)\s*\}\}/g, '2000-01-01');
}).join('\n');
const submitStart = source.indexOf("    createForm.addEventListener('submit', function (event) {");
assert.notEqual(submitStart, -1, 'the creation submit handler is present');
const submitHandler = source.slice(submitStart, source.indexOf('\n    });', submitStart) + '\n    });'.length);

class Option {
  constructor(text, value, dataset = {}) {
    this.textContent = text;
    this.value = String(value);
    this.dataset = { ...dataset };
    this.hidden = false;
    this.disabled = false;
  }
  cloneNode() { return new Option(this.textContent, this.value, this.dataset); }
}

function select(options, value = '') {
  return {
    options, _value: String(value), disabled: false, required: false,
    validationMessage: '', classList: { remove() {} },
    get value() { return this._value; },
    set value(value) {
      this._value = this.options.some(option => option.value === String(value)) ? String(value) : '';
    },
    get selectedIndex() { return this.options.findIndex(option => option.value === this.value); },
    get selectedOptions() { return this.options.filter(option => option.value === this.value); },
    replaceChildren(...options) {
      this.options = options;
      this._value = options[0]?.value || '';
    },
    appendChild(option) {
      this.options.push(option);
      if (this.options.length === 1) this._value = option.value;
    },
    setCustomValidity(message) { this.validationMessage = message; },
  };
}

function setup() {
  const fields = {};
  const wrappers = {};
  for (const prefix of ['teacher-', 'edit-teacher-']) {
    fields[prefix + 'corps'] = select([
      new Option('Choisir', ''),
      new Option('Contractuel', '1', { corpsCode: 'CT' }),
      new Option('Fonctionnaire', '2', { corpsCode: 'FONC' }),
      new Option('Vacataire', '3', { corpsCode: 'VAC' }),
    ], '1');
    fields[prefix + 'categorie'] = select([
      new Option('Choisir', ''),
      new Option('Catégorie 2', '2', { corpsId: '1' }),
      new Option('Catégorie 3', '3', { corpsId: '1' }),
      new Option('Catégorie 4', '4', { corpsId: '1' }),
      new Option('Autre corps', '5', { corpsId: '2' }),
    ], '3');
    fields[prefix + 'diplome'] = select([
      new Option('Choisir', ''),
      new Option('Licence', '10', { categorieId: '2', salaireBrut: '210000.00' }),
      new Option(' LICENCE ', '11', { categorieId: '3', salaireBrut: '260000.50' }),
      new Option('Maîtrise', '12', { categorieId: '2', salaireBrut: '310000.00' }),
    ], '10');
    fields[prefix + 'date-fin-contrat'] = { value: '2027-06-30', required: false, disabled: false };
    fields[prefix + 'salaire'] = { value: '999' };
    for (const id of Object.keys(fields).filter(id => id.startsWith(prefix))) {
      const field = fields[id];
      const classes = new Set();
      wrappers[id] = { hidden: false, style: { display: '' } };
      field.closest = () => wrappers[id];
      field.classList = {
        remove: name => classes.delete(name),
        contains: name => classes.has(name),
        toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name),
      };
      field.setAttribute = (name, value) => { field[name] = value; };
      field.focus = () => { field.focused = true; };
      Object.defineProperty(field, 'validity', {
        get: () => ({ valid: (!field.required || Boolean(field.value)) && !field.validationMessage }),
      });
    }
  }
  const selectors = {
    '[data-create-contract-field]': wrappers['teacher-date-fin-contrat'],
    '[data-edit-contract-field]': wrappers['edit-teacher-date-fin-contrat'],
  };
  const navigation = {};
  const handlers = {};
  const context = vm.createContext({
    document: {
      getElementById: id => fields[id],
      querySelector: selector => selectors[selector],
      querySelectorAll: () => [],
    },
    createForm: {
      querySelector: selector => navigation[selector] ||= {},
      addEventListener: (event, handler) => { handlers[event] = handler; },
    },
    createCorps: fields['teacher-corps'],
    createCategorie: fields['teacher-categorie'],
    createCategorieField: wrappers['teacher-categorie'],
    createStep: 2,
    createGroups: [[], ['teacher-corps', 'teacher-diplome', 'teacher-categorie', 'teacher-salaire', 'teacher-date-fin-contrat'], [], []],
    editCorps: fields['edit-teacher-corps'],
    editCategorie: fields['edit-teacher-categorie'],
    editCategorieField: wrappers['edit-teacher-categorie'],
  });
  vm.runInContext(functions + '\n' + submitHandler, context);
  context.hideDuplicateDiplomas(fields['teacher-diplome']);
  context.hideDuplicateDiplomas(fields['edit-teacher-diplome']);
  return {
    context, fields, wrappers,
    submit() {
      const event = { defaultPrevented: false, preventDefault() { this.defaultPrevented = true; } };
      handlers.submit(event);
      return event;
    },
  };
}

for (const [code, label, expected] of [
  ['CTR', 'Contractuel', true],
  ['PC', 'Contractuel', true],
  ['CT', '  CONTRACTUEL  ', true],
  [' contractuel ', 'Autre libellé', true],
  ['', 'Contractuel', true],
  ['VAC', 'Vacataire', false],
  ['FONC', 'Fonctionnaire', false],
  ['', 'Choisir un corps', false],
]) {
  test(`recognizes contractual corps using code ${JSON.stringify(code)} and label ${JSON.stringify(label)}`, () => {
    const { context } = setup();
    const corps = select([new Option(label, '1', { corpsCode: code })], '1');
    assert.equal(context.isContractuelCorps(corps), expected);
  });
}

test('an empty or missing corps is not contractual', () => {
  const { context } = setup();
  assert.equal(context.isContractuelCorps(null), false);
  assert.equal(context.isContractuelCorps(select([new Option('Contractuel', '', { corpsCode: 'contractuel' })])), false);
});

test('creation shows and requires category and contract end date at step 2', () => {
  const { context, fields, wrappers } = setup();
  context.showCreateStep(2);
  for (const id of ['teacher-categorie', 'teacher-date-fin-contrat']) {
    assert.equal(wrappers[id].hidden, false);
    assert.equal(wrappers[id].style.display, '');
    assert.equal(fields[id].required, true);
    assert.equal(fields[id].disabled, false);
  }
});

test('contractual category and end date remain required and submitted after leaving step 2', () => {
  const { context, fields, wrappers } = setup();
  context.showCreateStep(2);
  for (const step of [3, 4, 1]) {
    context.showCreateStep(step);
    for (const id of ['teacher-categorie', 'teacher-date-fin-contrat']) {
      assert.equal(wrappers[id].hidden, true);
      assert.equal(wrappers[id].style.display, 'none');
      assert.equal(fields[id].required, true);
      assert.equal(fields[id].disabled, false);
    }
    assert.equal(fields['teacher-categorie'].value, '3');
    assert.equal(fields['teacher-date-fin-contrat'].value, '2027-06-30');
  }
});

for (const id of ['teacher-categorie', 'teacher-date-fin-contrat']) {
  test(`submission from step 4 returns to step 2 when ${id} is missing`, () => {
    const { context, fields, submit } = setup();
    fields[id].value = '';
    context.showCreateStep(4);
    const event = submit();
    assert.equal(event.defaultPrevented, true);
    assert.equal(context.createStep, 2);
    assert.equal(fields[id].classList.contains('is-invalid'), true);
    assert.equal(fields[id].focused, true);
  });
}

test('valid submission includes active contractual fields and the matching salary/diploma pair', () => {
  const { context, fields, submit } = setup();
  context.showCreateStep(4);
  const event = submit();
  assert.equal(event.defaultPrevented, false);
  assert.equal(context.createStep, 4);
  for (const [id, value] of [
    ['teacher-categorie', '3'],
    ['teacher-date-fin-contrat', '2027-06-30'],
    ['teacher-diplome', '11'],
    ['teacher-salaire', '260000.50'],
  ]) {
    assert.equal(fields[id].disabled, false);
    assert.equal(fields[id].value, value);
  }
});

test('vacataire submission keeps category and contract end date disabled', () => {
  const { context, fields, submit } = setup();
  fields['teacher-corps'].value = '3';
  context.showCreateStep(4);
  const event = submit();
  assert.equal(event.defaultPrevented, false);
  for (const id of ['teacher-categorie', 'teacher-date-fin-contrat']) {
    assert.equal(fields[id].disabled, true);
    assert.equal(fields[id].required, false);
  }
  assert.equal(fields['teacher-salaire'].disabled, false);
  assert.equal(fields['teacher-salaire'].value, '150000');
});

for (const [prefix, update] of [
  ['teacher-', 'updateCreateCategorieVisibility'],
  ['edit-teacher-', 'updateEditCategorieVisibility'],
]) {
  test(`${prefix}: contractual category and end date are visible and mandatory`, () => {
    const { context, fields, wrappers } = setup();
    context[update]();
    for (const suffix of ['categorie', 'date-fin-contrat']) {
      assert.equal(wrappers[prefix + suffix].hidden, false);
      assert.equal(fields[prefix + suffix].disabled, false);
      assert.equal(fields[prefix + suffix].required, true);
    }
  });

  test(`${prefix}: switching corps hides and disables contractual fields`, () => {
    const { context, fields, wrappers } = setup();
    context[update]();
    fields[prefix + 'corps'].value = '2';
    context[update]();
    for (const suffix of ['categorie', 'date-fin-contrat']) {
      assert.equal(wrappers[prefix + suffix].hidden, true);
      assert.equal(wrappers[prefix + suffix].style.display, 'none');
      assert.equal(fields[prefix + suffix].disabled, true);
      assert.equal(fields[prefix + suffix].required, false);
    }
    assert.equal(fields[prefix + 'categorie'].value, '');
  });

  test(`${prefix}: salary and submitted diploma follow the selected diploma/category pair`, () => {
    const { context, fields } = setup();
    const diploma = fields[prefix + 'diplome'];
    const category = fields[prefix + 'categorie'];
    const salary = fields[prefix + 'salaire'];
    const applySalary = () => context.applyDiplomeSalary(diploma, fields[prefix + 'corps'], category, salary);
    assert.equal(diploma.options.some(option => option.value === '11'), false);
    applySalary();
    assert.equal(diploma.value, '11');
    assert.equal(salary.value, '260000.50');
    assert.equal(diploma.validationMessage, '');
    category.value = '2';
    applySalary();
    assert.equal(diploma.value, '10');
    assert.equal(salary.value, '210000.00');
  });

  test(`${prefix}: contractual salary is empty until a category is selected`, () => {
    const { context, fields } = setup();
    fields[prefix + 'categorie'].value = '';
    context.applyDiplomeSalary(fields[prefix + 'diplome'], fields[prefix + 'corps'], fields[prefix + 'categorie'], fields[prefix + 'salaire']);
    assert.equal(fields[prefix + 'salaire'].value, '');
    assert.equal(fields[prefix + 'diplome'].validationMessage, '');
  });

  test(`${prefix}: an unavailable salary pair blocks validation until a valid category is chosen`, () => {
    const { context, fields } = setup();
    const diploma = fields[prefix + 'diplome'];
    const category = fields[prefix + 'categorie'];
    const salary = fields[prefix + 'salaire'];
    category.value = '4';
    context.applyDiplomeSalary(diploma, fields[prefix + 'corps'], category, salary);
    assert.equal(salary.value, '');
    assert.match(diploma.validationMessage, /Aucun salaire brut paramétré pour ce diplôme et cette catégorie/);
    category.value = '3';
    context.applyDiplomeSalary(diploma, fields[prefix + 'corps'], category, salary);
    assert.equal(diploma.validationMessage, '');
    assert.equal(salary.value, '260000.50');
  });

  test(`${prefix}: vacataire salary stays at 150000 without a diploma or category`, () => {
    const { context, fields } = setup();
    fields[prefix + 'corps'].value = '3';
    fields[prefix + 'diplome'].value = '';
    fields[prefix + 'categorie'].value = '';
    context.applyDiplomeSalary(fields[prefix + 'diplome'], fields[prefix + 'corps'], fields[prefix + 'categorie'], fields[prefix + 'salaire']);
    assert.equal(fields[prefix + 'salaire'].value, '150000');
    assert.equal(fields[prefix + 'diplome'].validationMessage, '');
  });
}
