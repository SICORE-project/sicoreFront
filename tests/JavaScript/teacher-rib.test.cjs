const { test } = require('node:test');
const assert = require('node:assert/strict');
const { ribKey } = require('../../public/assets/js/teacher-rib.js');
const samples = [
  ['SN012', '01301', '035159896701', '97'],
  ['SN010', '01520', '015618200086', '22'],
  ['SN012', '08276', '027635000077', '47'],
  ['SN010', '11415', '020073700011', '90'],
  ['SN010', '01520', '020646600041', '82'],
  ['SN012', '05290', '035196158401', '55'],
];
test('matches the six Excel examples, preserving leading zeroes', () => {
  for (const [bank, branch, account, key] of samples) assert.equal(ribKey(bank, branch, account), key);
});
test('formats single digit keys with a leading zero', () => {
  assert.equal(ribKey('SN012', '01301', '000000000017'), '07');
});
test('rejects incomplete, oversized and non-numeric coordinates', () => {
  for (const input of [
    ['SN0123', '01301', '035159896701'], ['SN012', '013010', '035159896701'],
    ['SN012', '01301', '0351598967010'], ['SN012', '1301', '035159896701'],
    ['SN012', '01301', '03515989670A'], ['FR012', '01301', '035159896701'],
  ]) assert.equal(ribKey(...input), '');
});
