(function () {
  'use strict';
  function label(value) { return typeof value === 'string' ? value : value?.libelle || value?.nom || ''; }
  function values(user) {
    var access = user.acces_organisationnel || {};
    var structure = access.lieu_service || access.structure || user.lieu_service;
    var regional = access.perimetre === 'regional' || ['IA', 'IEF'].includes(structure?.type) || !!(access.ia || access.ief || user.ia_id);
    var status = user.statut || user.status || '';
    return {
      nom: user.nom, prenom: user.prenom, date_naiss: user.date_naiss || user.date_naiss_iso,
      lieu_naissance: user.lieu_naissance, genre: ({ masculin: 'Masculin', feminin: 'Féminin', M: 'Masculin', F: 'Féminin' })[user.genre] || user.genre,
      matricule_enseignant: user.matricule_enseignant || user.enseignant?.matricule || user.matricule,
      email: user.email, telephone: user.telephone, adresse: user.adresse,
      role: label(user.role), statut: ({ actif: 'Actif', inactif: 'Inactif', suspendu: 'Suspendu' })[status] || status,
      perimetre: regional ? 'Régional' : 'Central', direction: regional ? '' : label(structure),
      ia: label(access.ia || user.ia) || user.affectation?.ia,
      ief: label(access.ief || user.ief) || user.affectation?.ief
    };
  }
  function render(root, user) {
    var data = values(user);
    root.querySelector('[data-user-name]').textContent = [user.prenom, user.nom].filter(Boolean).join(' ') || user.nom_complet || 'Utilisateur';
    root.querySelector('[data-user-initials]').textContent = ((user.prenom || '').charAt(0) + (user.nom || '').charAt(0)).toUpperCase() || 'UT';
    root.querySelector('[data-user-role]').textContent = data.role || '';
    root.querySelector('[data-user-status]').textContent = data.statut || '';
    root.querySelectorAll('[data-user-field]').forEach(function (field) {
      var value = data[field.dataset.userField];
      field.textContent = value || '';
      field.parentElement.hidden = value === undefined || value === null || String(value).trim() === '';
    });
    root.querySelectorAll('.teacher-detail-card').forEach(function (card) {
      card.hidden = Array.from(card.querySelectorAll('[data-user-field]')).every(function (field) { return field.parentElement.hidden; });
    });
  }
  if (typeof module !== 'undefined') module.exports = { values: values };
  if (typeof window === 'undefined') return;
  window.renderUserProfile = render;
  document.addEventListener('DOMContentLoaded', function () {
    var record = document.getElementById('user-profile-record');
    if (record) render(document.querySelector('[data-user-profile]'), JSON.parse(record.textContent));
  });
})();
