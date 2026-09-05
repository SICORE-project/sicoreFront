(function () {
  "use strict";

  var currentStep = 1;
  var totalSteps = 3;
  var wizardForm = null;

  function getWizard() {
    if (!wizardForm) {
      wizardForm = document.querySelector("[data-teacher-wizard]");
    }
    return wizardForm;
  }

  function getFieldsForStep(stepNumber) {
    var form = getWizard();
    var panel = form
      ? form.querySelector('[data-wizard-panel="' + stepNumber + '"]')
      : null;
    return panel
      ? Array.prototype.slice.call(
          panel.querySelectorAll("input, select, textarea"),
        )
      : [];
  }

  function getFieldLabel(field) {
    var label = field.closest(".form-group")
      ? field.closest(".form-group").querySelector("label")
      : null;
    return label
      ? label.textContent.replace("*", "").trim().toLowerCase()
      : "ce champ";
  }

  function showFieldError(field, message) {
    var group = field.closest(".form-group");
    var error = group ? group.querySelector(".field-error") : null;

    if (!error && group) {
      error = document.createElement("p");
      error.className = "field-error";
      group.appendChild(error);
    }

    field.classList.add("is-invalid");
    field.setAttribute("aria-invalid", "true");
    if (error) {
      error.textContent = message;
    }
  }

  function clearFieldError(field) {
    var group = field.closest(".form-group");
    var error = group ? group.querySelector(".field-error") : null;
    field.classList.remove("is-invalid");
    field.setAttribute("aria-invalid", "false");
    if (error) {
      error.textContent = "";
    }
  }

  function validateField(field) {
    if (field.disabled) {
      clearFieldError(field);
      return true;
    }

    var value = field.value ? field.value.trim() : "";
    var label = getFieldLabel(field);

    clearFieldError(field);

    if (field.hasAttribute("required") && value === "") {
      showFieldError(field, "Le champ " + label + " est obligatoire.");
      return false;
    }

    if (
      field.type === "email" &&
      value !== "" &&
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
    ) {
      showFieldError(field, "Adresse email invalide.");
      return false;
    }

    if (
      field.type === "tel" &&
      value !== "" &&
      !/^[+0-9\s().-]{7,20}$/.test(value)
    ) {
      showFieldError(field, "Telephone invalide.");
      return false;
    }

    if (field.type === "number" && value !== "" && Number(value) <= 0) {
      showFieldError(field, "La valeur doit etre superieure a zero.");
      return false;
    }

    return true;
  }

  function validateCurrentStep() {
    var fields = getFieldsForStep(currentStep);
    var valid = true;
    var firstInvalid = null;

    fields.forEach(function (field) {
      var fieldValid = validateField(field);
      if (!fieldValid && !firstInvalid) {
        firstInvalid = field;
      }
      valid = valid && fieldValid;
    });

    if (!valid && firstInvalid) {
      firstInvalid.focus();
      if (typeof window.showToast === "function") {
        window.showToast("error", "Veuillez corriger les champs signales.");
      }
    }

    return valid;
  }

  function validateAllSteps() {
    var form = getWizard();
    var valid = true;
    var firstInvalid = null;

    if (!form) {
      return false;
    }

    form.querySelectorAll("input, select, textarea").forEach(function (field) {
      var fieldValid = validateField(field);
      if (!fieldValid && !firstInvalid) {
        firstInvalid = field;
      }
      valid = valid && fieldValid;
    });

    if (!valid && firstInvalid) {
      var panel = firstInvalid.closest("[data-wizard-panel]");
      if (panel) {
        goToStep(Number(panel.getAttribute("data-wizard-panel")));
      }
      firstInvalid.focus();
    }

    return valid;
  }

  /* ==========================================================
     NOUVEAU : synchronisation des champs conditionnels
     (ex: "grade" et "corps" visibles seulement si statut = fonctionnaire)

     Adaptez :
       - le sélecteur du champ "statut" ci-dessous
       - la valeur testée ("fonctionnaire")
       - ou utilisez data-show-if="fonctionnaire" sur les .form-group
         concernés dans votre HTML
     ========================================================== */
  function syncConditionalFields() {
    var form = getWizard();
    if (!form) {
      return;
    }

    var typeField = form.querySelector('[name="categorie_personnel"]');
    if (typeField) {
      form.querySelectorAll("[data-enable-if]").forEach(function (group) {
        var shouldEnable =
          group.getAttribute("data-enable-if") === typeField.value;
        var fields = group.matches("input, select, textarea")
          ? [group]
          : group.querySelectorAll("input, select, textarea");
        Array.prototype.forEach.call(fields, function (field) {
          field.disabled = !shouldEnable;
          field.required = shouldEnable;
          if (!shouldEnable) {
            clearFieldError(field);
          }
        });
      });
    }

    // Adaptez ce sélecteur au vrai name/id de votre champ statut
    var statutField = form.querySelector(
      '[name="statut"], [data-role="statut"]',
    );
    if (!statutField) {
      return;
    }

    var statutValue = statutField.value;

    form.querySelectorAll("[data-show-if]").forEach(function (group) {
      var expected = group.getAttribute("data-show-if");
      var shouldShow = expected === statutValue;
      group.hidden = !shouldShow;

      var fieldsInGroup = group.querySelectorAll("input, select, textarea");
      fieldsInGroup.forEach(function (field) {
        field.disabled = !shouldShow;
        if (!shouldShow) {
          clearFieldError(field);
        }
      });
    });
  }

  function updateProgressBar() {
    var form = getWizard();
    if (!form) {
      return;
    }

    form
      .querySelectorAll("[data-step-indicator]")
      .forEach(function (indicator) {
        var step = Number(indicator.getAttribute("data-step-indicator"));
        var marker = indicator.querySelector(".wizard-step-number");
        indicator.classList.toggle("active", step === currentStep);
        indicator.classList.toggle("done", step < currentStep);
        if (marker) {
          marker.textContent = step < currentStep ? "\u2713" : String(step);
        }
      });

    form.querySelectorAll("[data-wizard-panel]").forEach(function (panel) {
      var step = Number(panel.getAttribute("data-wizard-panel"));
      panel.hidden = step !== currentStep;
    });

    // Re-synchroniser les champs conditionnels a CHAQUE changement d'etape
    syncConditionalFields();

    var previousButton = form.querySelector("[data-wizard-prev]");
    var nextButton = form.querySelector("[data-wizard-next]");
    var submitButton = form.querySelector("[data-wizard-submit]");
    var cancelButton = form.querySelector("[data-wizard-cancel]");

    if (previousButton) {
      previousButton.hidden = currentStep === 1;
    }
    if (cancelButton) {
      cancelButton.hidden = currentStep !== 1;
    }
    if (nextButton) {
      nextButton.hidden = currentStep === totalSteps;
    }
    if (submitButton) {
      submitButton.hidden = currentStep !== totalSteps;
    }
  }

  function goToStep(stepNumber) {
    var next = Math.min(Math.max(stepNumber, 1), totalSteps);
    if (next > currentStep) {
      if (!validateCurrentStep()) {
        return;
      }
      next = Math.min(next, currentStep + 1);
    }
    currentStep = next;
    updateProgressBar();
  }

  function nextStep() {
    if (validateCurrentStep()) {
      currentStep = Math.min(currentStep + 1, totalSteps);
      updateProgressBar();
    }
  }

  function previousStep() {
    currentStep = Math.max(currentStep - 1, 1);
    updateProgressBar();
  }

  function showSuccessToast(message) {
    if (typeof window.showToast === "function") {
      window.showToast("success", message);
    }
  }

  function initTeacherWizard() {
    var form = getWizard();
    if (!form || form.dataset.wizardBound === "true") {
      return;
    }

    form.dataset.wizardBound = "true";
    totalSteps = form.querySelectorAll("[data-wizard-panel]").length || 3;
    currentStep = 1;

    form
      .querySelectorAll("[data-step-indicator]")
      .forEach(function (indicator) {
        indicator.addEventListener("click", function () {
          goToStep(Number(indicator.getAttribute("data-step-indicator")));
        });
      });

    form
      .querySelector("[data-wizard-next]")
      .addEventListener("click", nextStep);
    form
      .querySelector("[data-wizard-prev]")
      .addEventListener("click", previousStep);

    // Ecouter le changement du statut pour re-synchroniser en direct
    var statutField = form.querySelector(
      '[name="statut"], [data-role="statut"]',
    );
    if (statutField) {
      statutField.addEventListener("change", syncConditionalFields);
    }

    form.querySelectorAll("input, select, textarea").forEach(function (field) {
      field.addEventListener("input", function () {
        if (field.classList.contains("is-invalid")) {
          validateField(field);
        }
      });
      field.addEventListener("change", function () {
        if (field.classList.contains("is-invalid")) {
          validateField(field);
        }
      });
    });

    form.addEventListener("submit", async function (event) {
      event.preventDefault();
      if (!validateAllSteps()) {
        if (typeof window.showToast === "function") {
          window.showToast(
            "error",
            "Veuillez finaliser les informations obligatoires.",
          );
        }
        return;
      }

      event.target.submit();
    });

    syncConditionalFields();
    updateProgressBar();
  }

  document.addEventListener("DOMContentLoaded", initTeacherWizard);

  window.initTeacherWizard = initTeacherWizard;
  window.goToStep = goToStep;
  window.nextStep = nextStep;
  window.previousStep = previousStep;
  window.validateCurrentStep = validateCurrentStep;
  window.validateAllSteps = validateAllSteps;
  window.updateProgressBar = updateProgressBar;
  window.syncConditionalFields = syncConditionalFields;
  window.showFieldError = showFieldError;
  window.clearFieldError = clearFieldError;
  window.showSuccessToast = showSuccessToast;
})();
