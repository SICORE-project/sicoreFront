(function () {
  "use strict";

  function initToggleTargets() {
    document.querySelectorAll("[data-toggle-target]").forEach(function (trigger) {
      if (trigger.dataset.toggleBound === "true") {
        return;
      }
      trigger.dataset.toggleBound = "true";

      trigger.addEventListener("click", function () {
        var target = document.querySelector(trigger.getAttribute("data-toggle-target"));
        if (!target) {
          return;
        }
        target.hidden = !target.hidden;
        if (!target.hidden) {
          var firstField = target.querySelector("input, select, textarea");
          if (firstField) {
            firstField.focus();
          }
        }
      });
    });
  }

  document.addEventListener("DOMContentLoaded", initToggleTargets);
})();
