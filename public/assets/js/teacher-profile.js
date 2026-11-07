(function () {
  'use strict';
  function teacherValue(teacher, path, fallback) {
    var value = path.split('.').reduce(function (current, key) { return current && current[key] !== undefined ? current[key] : null; }, teacher);
    return value === null || value === undefined || value === '' ? (fallback === undefined ? '—' : fallback) : value;
  }

  function teacherDate(value) {
    if (!value) { return '—'; }
    var parts = String(value).slice(0, 10).split('-');
    return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : value;
  }

  function isFonctionnaireCorpsValue(status) {
    var label = String(status?.libelle || '').trim().toLowerCase();
    var code = String(status?.code || '').trim().toLowerCase();
    return !/vacataire|contractuel/.test(label) && (['fonctionnaire', 'fonctionnaires'].includes(label) || ['fonc', 'fonctionnaire'].includes(code));
  }
  function renderProfile(modal, teacher) {
      var firstName = teacherValue(teacher, 'prenom', '');
      var lastName = teacherValue(teacher, 'nom', '');
      var fullName = (firstName + ' ' + lastName).trim() || 'Enseignant';
      var initials = ((firstName.charAt(0) || '') + (lastName.charAt(0) || '')).toUpperCase() || 'EN';
      var statuses = { en_activite: 'En activité', retraite: 'Retraité', suspension_provisoire: 'Suspension provisoire', abandon: 'Abandon', decede: 'Décédé', integre: 'Intégré', radie: 'Radié', cessation_paiement: 'Cessation de paiement' };
      modal.querySelector('[data-view-initials]').textContent = initials;
      modal.querySelector('[data-view-full-name]').textContent = fullName;
      var matricule = teacherValue(teacher, 'matricule', '');
      var matriculeField = modal.querySelector('[data-view-matricule]');
      matriculeField.hidden = matricule === '';
      matriculeField.textContent = matricule === '' ? '' : 'Matricule : ' + matricule;
      var status = statuses[teacher.statut] || teacherValue(teacher, 'statut', '');
      var statusField = modal.querySelector('[data-view-status]');
      statusField.hidden = status === '';
      statusField.textContent = status;
      modal.querySelector('.teacher-profile-meta').hidden = matriculeField.hidden && statusField.hidden;
      var showIndice = isFonctionnaireCorpsValue(teacher.statut_enseignant);
      modal.querySelector('[data-view-indice]').hidden = !showIndice;
      modal.querySelectorAll('[data-teacher-detail]').forEach(function (field) {
        var value = teacherValue(teacher, field.dataset.teacherDetail, 'Non renseigné');
        var assignedValue = teacherValue(teacher, field.dataset.teacherDetail, '');
        var detailRow = field.closest('.teacher-detail-list > div');
        if (detailRow) {
          detailRow.hidden = assignedValue === '' || assignedValue === null || assignedValue === undefined
            || (typeof assignedValue === 'string' && assignedValue.trim() === '')
            || (field.dataset.teacherDetail === 'indice' && !showIndice);
        }
        if (value !== 'Non renseigné') {
          if (field.dataset.format === 'date') { value = teacherDate(value); }
          if (field.dataset.format === 'matrimonial') { value = ({ celibataire: 'Célibataire', marie: 'Marié(e)', veuf: 'Veuf/Veuve', divorce: 'Divorcé(e)' })[value] || value; }
          if (field.dataset.format === 'bool') { value = value === true || value === 1 || value === '1' ? 'Oui' : 'Non'; }
          if (field.dataset.format === 'money') { value = Number(value).toLocaleString('fr-FR') + ' FCFA'; }
          if (field.dataset.format === 'money-number') { value = Number(value).toLocaleString('fr-FR', { maximumFractionDigits: 0 }); }
        }
        field.textContent = value;
      });
      modal.querySelectorAll('.teacher-detail-card').forEach(function (card) {
        var rows = Array.from(card.querySelectorAll('.teacher-detail-list > div'));
        card.hidden = rows.length > 0 && rows.every(function (row) { return row.hidden; });
      });
  }
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-view-teacher]').forEach(function (button) {
      button.addEventListener('click', function () {
        renderProfile(document.getElementById('teacher-view-modal'), JSON.parse(button.dataset.viewTeacher));
      });
    });
    var record = document.getElementById('teacher-profile-record');
    if (record) renderProfile(document.getElementById('teacher-view-modal'), JSON.parse(record.textContent));
  });
})();
