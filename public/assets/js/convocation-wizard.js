(function () {
  "use strict";

  var currentStep = 1;
  var totalSteps = 2;
  var wizardForm = null;

  function getWizard() {
    if (!wizardForm) {
      wizardForm = document.querySelector("[data-convocation-wizard]");
    }
    return wizardForm;
  }

  function getFieldsForStep(stepNumber) {
    var form = getWizard();
    var panel = form ? form.querySelector('[data-wizard-panel="' + stepNumber + '"]') : null;
    return panel ? Array.prototype.slice.call(panel.querySelectorAll("input, select, textarea")) : [];
  }

  function getFieldLabel(field) {
    var label = field.closest(".form-group") ? field.closest(".form-group").querySelector("label") : null;
    return label ? label.textContent.replace("*", "").trim().toLowerCase() : "ce champ";
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
    if (field.disabled || field.type === "checkbox") {
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

  function updateProgressBar() {
    var form = getWizard();
    if (!form) {
      return;
    }

    form.querySelectorAll("[data-step-indicator]").forEach(function (indicator) {
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

  function updateBeneficiaireCount() {
    var form = getWizard();
    if (!form) {
      return;
    }
    var checked = form.querySelectorAll("[data-beneficiaire-check]:checked").length;
    var counter = form.querySelector("[data-beneficiaire-count]");
    if (counter) {
      counter.textContent = checked + " beneficiaire(s) selectionne(s)";
    }
  }

  function initBeneficiaireTable() {
    var form = getWizard();
    if (!form) {
      return;
    }

    form.querySelectorAll("[data-beneficiaire-check]").forEach(function (checkbox) {
      checkbox.addEventListener("change", function () {
        var row = checkbox.closest("tr");
        var fonctionInput = row ? row.querySelector('input[name^="fonction"]') : null;
        if (fonctionInput) {
          fonctionInput.disabled = !checkbox.checked;
          if (!checkbox.checked) {
            fonctionInput.value = "";
          }
        }
        updateBeneficiaireCount();
      });
    });

    updateBeneficiaireCount();
  }

  function initConvocationWizard() {
    var form = getWizard();
    if (!form || form.dataset.wizardBound === "true") {
      return;
    }

    form.dataset.wizardBound = "true";
    totalSteps = form.querySelectorAll("[data-wizard-panel]").length || 2;
    currentStep = 1;

    form.querySelectorAll("[data-step-indicator]").forEach(function (indicator) {
      indicator.addEventListener("click", function () {
        goToStep(Number(indicator.getAttribute("data-step-indicator")));
      });
    });

    form.querySelector("[data-wizard-next]").addEventListener("click", nextStep);
    form.querySelector("[data-wizard-prev]").addEventListener("click", previousStep);

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

    initBeneficiaireTable();

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!validateAllSteps()) {
        if (typeof window.showToast === "function") {
          window.showToast("error", "Veuillez finaliser les informations obligatoires.");
        }
        return;
      }

      var status = form.querySelector("[data-form-status]");
      var submitButton = form.querySelector("[data-wizard-submit]");

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.setAttribute("aria-busy", "true");
      }
      if (status) {
        status.classList.remove("success");
        status.textContent = "Validation locale en cours...";
      }

      window.setTimeout(function () {
        if (status) {
          status.classList.add("success");
          status.textContent = "Convocation validee en mode test. Elle sera transmise au backend lorsque l'API sera connectee.";
        }
        showSuccessToast("Convocation validee en mode test.");

        if (submitButton) {
          submitButton.disabled = false;
          submitButton.removeAttribute("aria-busy");
        }
      }, 350);
    });

    updateProgressBar();
  }

  document.addEventListener("DOMContentLoaded", initConvocationWizard);

  window.initConvocationWizard = initConvocationWizard;
})();
