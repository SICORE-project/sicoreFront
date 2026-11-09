const { test } = require('node:test');
const assert = require('node:assert/strict');
const { allowedTypes } = require('../../public/assets/js/user-direction.js');
test('creation and dropdown use matching central types for each role', () => {
  assert.deepEqual(allowedTypes('agent_decpc', []), ['DECPC']);
  assert.deepEqual(allowedTypes('agent_drh', []), ['DRH']);
  assert.deepEqual(allowedTypes('super_admin', []), ['CI', 'DAGE']);
  assert.deepEqual(allowedTypes('consultant', []), ['DRH', 'DAGE', 'DECPC']);
});
test('configured types are normalized and regional types are excluded', () => {
  assert.deepEqual(allowedTypes('custom', ['decpc', 'ia', 'dage']), ['DECPC', 'DAGE']);
  assert.deepEqual(allowedTypes('gestionnaire_ia', ['ia']), []);
});
