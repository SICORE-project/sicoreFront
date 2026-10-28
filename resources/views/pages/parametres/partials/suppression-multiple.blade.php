@push('styles')
<style>
  .checkbox-cell { width: 40px; text-align: center; }

  .btn-bulk-delete {
    color: #b91c1c;
    border-color: #fca5a5;
  }

  .btn-bulk-delete:disabled {
    color: inherit;
    border-color: inherit;
    opacity: 0.5;
    cursor: not-allowed;
  }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    'use strict';

    var cible = @json($cible);
    var action = @json($action);
    var token = @json(csrf_token());

    function rowBoxes() {
      return Array.prototype.slice.call(document.querySelectorAll('[data-row-checkbox]'));
    }

    function updateState() {
      var boxes = rowBoxes();
      var checked = boxes.filter(function (box) { return box.checked; });
      var selectAll = document.querySelector('[data-select-all]');
      var deleteButton = document.querySelector('[data-bulk-delete-button]');

      if (deleteButton) {
        deleteButton.disabled = checked.length === 0;
        deleteButton.textContent = checked.length > 0
          ? 'Supprimer la sélection (' + checked.length + ')'
          : 'Supprimer la sélection';
      }

      if (selectAll) {
        selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
      }
    }

    function addHidden(form, name, value) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    }

    document.addEventListener('change', function (event) {
      if (event.target.matches('[data-select-all]')) {
        rowBoxes().forEach(function (box) { box.checked = event.target.checked; });
        updateState();
      } else if (event.target.matches('[data-row-checkbox]')) {
        updateState();
      }
    });

    document.addEventListener('click', function (event) {
      var button = event.target.closest('[data-bulk-delete-button]');

      if (!button || button.disabled) {
        return;
      }

      var ids = rowBoxes()
        .filter(function (box) { return box.checked; })
        .map(function (box) { return box.value; });

      if (ids.length === 0) {
        return;
      }

      var question = ids.length === 1
        ? 'Supprimer définitivement cette ' + cible + ' ?'
        : 'Supprimer définitivement ces ' + ids.length + ' ' + cible + ' ?';

      if (!window.confirm(question)) {
        return;
      }

      var form = document.createElement('form');
      form.method = 'POST';
      form.action = action;
      form.style.display = 'none';
      addHidden(form, '_token', token);
      addHidden(form, '_method', 'DELETE');
      ids.forEach(function (id) { addHidden(form, 'ids[]', id); });
      document.body.appendChild(form);
      form.submit();
    });

    document.addEventListener('sicore:ajax-regions-mises-a-jour', updateState);
    updateState();
  })();
</script>
@endpush
