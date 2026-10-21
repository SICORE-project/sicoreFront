const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/assets/js/delete-confirmation.js', 'utf8');
function setup() {
  const events = {};
  const node = () => ({ handlers: {}, addEventListener(name, cb) { this.handlers[name] = cb; }, focus() {} });
  const cancel = node(), accept = node(), message = node();
  const modal = Object.assign(node(), {
    open: false, querySelector: s => s.includes('accept') ? accept : s.includes('description') ? message : cancel,
    querySelectorAll: () => [cancel], showModal() { this.open = true; },
    close() { this.open = false; this.handlers.close(); }, setAttribute() {}, removeAttribute() {},
  });
  let calls = 0;
  const form = { dataset: { deleteLabel: 'Spécialité <test>' }, querySelector: () => ({ value: 'DELETE' }), closest: () => null };
  vm.runInNewContext(source, {
    document: { getElementById: () => modal, addEventListener: (name, cb) => events[name] = cb },
    window: { addEventListener() {} }, HTMLFormElement: { prototype: { submit() { assert.equal(this, form); calls++; } } },
  });
  return { modal, cancel, accept, message, form, calls: () => calls, submit() {
    let prevented = false;
    events.submit({ target: form, preventDefault() { prevented = true; }, stopImmediatePropagation() {} });
    return prevented;
  } };
}
test('opening and cancelling never submits a deletion', () => {
  const s = setup();
  assert.equal(s.submit(), true);
  assert.equal(s.modal.open, true);
  assert.equal(s.message.textContent, 'Voulez-vous supprimer « Spécialité <test> » ?');
  assert.equal(s.calls(), 0);
  s.cancel.handlers.click();
  assert.equal(s.modal.open, false);
  s.accept.handlers.click();
  assert.equal(s.calls(), 0);
});
test('confirmation submits the original form only once', () => {
  const s = setup(); s.submit();
  s.accept.handlers.click(); s.accept.handlers.click();
  assert.equal(s.calls(), 1);
  assert.equal(s.accept.disabled, true);
});
test('non-delete forms are not intercepted', () => {
  const s = setup(); s.form.querySelector = () => ({ value: 'PUT' });
  assert.equal(s.submit(), false);
  assert.equal(s.modal.open, false);
  assert.equal(s.calls(), 0);
});
