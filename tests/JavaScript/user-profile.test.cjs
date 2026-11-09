const { test } = require('node:test');
const assert = require('node:assert/strict');
const { values } = require('../../public/assets/js/user-profile.js');
test('only creation fields are exposed', () => {
  const result = values({ nom: 'Diop', prenom: 'Awa', fonction: 'Extra', password: 'secret', id: 42, created_at: 'today', role: { nom: 'Admin' }, statut: 'actif', lieu_service: { libelle: 'Direction', type: 'DRH' } });
  assert.equal(result.nom, 'Diop');
  assert.equal(result.role, 'Admin');
  assert.equal(result.direction, 'Direction');
  for (const key of ['password', 'id', 'created_at', 'fonction', 'etablissement', 'derniere_connexion']) assert.equal(Object.hasOwn(result, key), false);
});
test('regional assignment and teacher reference use available values', () => {
  const result = values({ ia_id: 1, ia: { libelle: 'IA Dakar' }, affectation: { ief: 'IEF Plateau' }, matricule: '123456/A' });
  assert.equal(result.ia, 'IA Dakar');
  assert.equal(result.ief, 'IEF Plateau');
  assert.equal(result.direction, '');
  assert.equal(result.matricule_enseignant, '123456/A');
});
