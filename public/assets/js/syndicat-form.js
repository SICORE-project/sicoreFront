(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector("[data-syndicat-form]");
    if (!form) return;

    var submitButton = form.querySelector("[data-submit-button]");
    var originalButtonHtml = submitButton.innerHTML;
    var timers = new WeakMap();
    var submitted = false;

    async function checkUniqueness(input) {
      var value = input.value.trim();
      var feedback = document.getElementById(input.name + "-feedback");
      input.setCustomValidity("");
      if (!value || !input.checkValidity()) return;

      try {
        var url = new URL(form.dataset.uniquenessUrl, window.location.origin);
        url.searchParams.set("field", input.name);
        url.searchParams.set("value", value);
        var response = await fetch(url, { headers: { Accept: "application/json" } });
        if (!response.ok) return;

        var result = await response.json();
        if (result.available === false) {
          input.setCustomValidity(result.message);
          input.classList.add("is-invalid");
          feedback.textContent = result.message;
        } else {
          input.classList.remove("is-invalid");
          feedback.textContent = "";
        }
      } catch (error) {
        // La validation finale reste assurée par le backend.
      }
    }

    form.querySelectorAll("[data-unique-field]").forEach(function (input) {
      input.addEventListener("input", function () {
        clearTimeout(timers.get(input));
        input.setCustomValidity("");
        input.classList.remove("is-invalid");
        document.getElementById(input.name + "-feedback").textContent = "";
        timers.set(input, setTimeout(function () { checkUniqueness(input); }, 450));
      });
      input.addEventListener("blur", function () { checkUniqueness(input); });
    });

    form.addEventListener("submit", function (event) {
      if (submitted || !form.checkValidity()) {
        if (submitted) event.preventDefault();
        return;
      }
      submitted = true;
      submitButton.disabled = true;
      submitButton.setAttribute("aria-busy", "true");
      submitButton.textContent = "Enregistrement…";
    });

    window.addEventListener("pageshow", function () {
      submitted = false;
      submitButton.disabled = false;
      submitButton.removeAttribute("aria-busy");
      submitButton.innerHTML = originalButtonHtml;
    });
  });
})();
