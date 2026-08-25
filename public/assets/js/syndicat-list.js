(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector("[data-syndicats-filters]");
    if (!form) return;

    var search = form.querySelector('[name="search"]');
    var status = form.querySelector('[name="est_actif"]');
    var timer;

    search.addEventListener("input", function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        form.requestSubmit();
      }, 450);
    });

    status.addEventListener("change", function () {
      clearTimeout(timer);
      form.requestSubmit();
    });

    document.querySelectorAll("[data-edit-syndicat-form]").forEach(function (editForm) {
      var submitted = false;
      var codeInput = editForm.querySelector('[name="code"]');
      if (codeInput) {
        codeInput.addEventListener("input", function () {
          codeInput.value = codeInput.value.toUpperCase();
        });
      }
      editForm.addEventListener("submit", function (event) {
        if (submitted) {
          event.preventDefault();
          return;
        }
        if (!editForm.checkValidity()) return;
        submitted = true;
        var button = editForm.querySelector('[type="submit"]');
        button.disabled = true;
        button.setAttribute("aria-busy", "true");
        button.textContent = "Enregistrement…";
      });
    });
  });
})();
