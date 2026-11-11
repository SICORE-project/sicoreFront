(function () {
  'use strict';
  function ribKey(bank, branch, account) {
    if (!/^SN[0-9]{3}$/.test(bank) || !/^[0-9]{5}$/.test(branch) || !/^[0-9]{12}$/.test(account)) return '';
    var sum = 17 * Number('25' + bank.slice(2)) + 53 * Number(branch)
      + 81 * Number(account.slice(0, 6)) + 3 * Number(account.slice(6));
    return String(97 - sum % 97).padStart(2, '0');
  }
  if (typeof module !== 'undefined') module.exports = { ribKey: ribKey };
  if (typeof document === 'undefined') return;
  document.addEventListener('DOMContentLoaded', function () {
    ['teacher-', 'edit-teacher-'].forEach(function (prefix) {
      var bank = document.getElementById(prefix + 'code-banque');
      var branch = document.getElementById(prefix + 'code-guichet');
      var account = document.getElementById(prefix + 'numero-compte');
      var key = document.getElementById(prefix + 'cle-rib');
      if (!bank || !branch || !account || !key) return;
      function update() {
        key.value = ribKey(bank.value, branch.value, account.value);
        key.classList.remove('is-invalid');
        key.setCustomValidity('');
      }
      [bank, branch, account].forEach(function (field) {
        field.addEventListener('input', function () {
          if (field === bank) {
            field.value = field.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 5);
          } else {
            field.value = field.value.replace(/[^0-9]/g, '').slice(0, field === branch ? 5 : 12);
          }
          update();
        });
        field.addEventListener('change', update);
      });
      var form = bank.closest('form');
      if (form) form.addEventListener('reset', function () { setTimeout(update, 0); });
      update();
    });
  });
})();
