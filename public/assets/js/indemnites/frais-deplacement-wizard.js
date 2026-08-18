/*
 * Wizard générique (3 étapes) pour "Nouvelle fiche de déplacement" —
 * demande utilisatrice : "le formulaire doit être en 3 étapes (regarde
 * workflow de création de convocation)".
 *
 * Reprend le MÊME MÉCANISME générique que convocation-wizard.js (mêmes
 * attributs data-wizard-panel / data-step-indicator / data-wizard-next /
 * data-wizard-prev / data-wizard-submit / data-wizard-cancel, même logique
 * de validation "un champ required vide bloque le passage à l'étape
 * suivante") — mais SANS toute la partie spécifique aux convocations
 * (recherche de centres/jurys/membres, détection de doublons, etc.), qui
 * ne s'applique pas ici. Fichier séparé plutôt que réutiliser
 * convocation-wizard.js tel quel : ce dernier lie son écouteur "submit" à
 * des vérifications propres aux convocations (ex. "au moins un centre
 * d'examen"), qui auraient bloqué à tort la soumission de cette page.
 *
 * Scopé sur [data-frais-deplacement-wizard] (au lieu de
 * [data-convocation-wizard]) pour ne jamais entrer en collision avec
 * convocation-wizard.js si les deux scripts se retrouvaient un jour
 * chargés sur la même page.
 */
(function () {
  "use strict";

  var currentStep = 1;
  var totalSteps = 3;
  var wizardForm = null;

  function getWizard() {
    if (!wizardForm) {
      wizardForm = document.querySelector("[data-frais-deplacement-wizard]");
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
    var group = field.closest(".form-group");

    var label = group ? group.querySelector("label") : null;

    return label
      ? label.textContent.replace("*", "").trim().toLowerCase()
      : "ce champ";
  }

  function showFieldError(field, message) {
    var group = field.closest(".form-group");

    if (!group) {
      return;
    }

    var error = group.querySelector(".field-error");

    if (!error) {
      error = document.createElement("p");
      error.className = "field-error";
      group.appendChild(error);
    }

    field.classList.add("is-invalid");
    field.setAttribute("aria-invalid", "true");

    error.textContent = message;
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
    if (
      field.disabled ||
      field.type === "hidden" ||
      field.type === "checkbox" ||
      field.type === "file"
    ) {
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
        window.showToast("error", "Veuillez corriger les champs signalés.");
      }
    }

    return valid;
  }

  function validateAllSteps() {
    var form = getWizard();

    if (!form) {
      return false;
    }

    var valid = true;
    var firstInvalid = null;

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
        currentStep = Number(panel.getAttribute("data-wizard-panel"));

        updateProgressBar();
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

    form
      .querySelectorAll("[data-step-indicator]")
      .forEach(function (indicator) {
        var step = Number(indicator.getAttribute("data-step-indicator"));

        var marker = indicator.querySelector(".wizard-step-number");

        indicator.classList.toggle("active", step === currentStep);

        indicator.classList.toggle("done", step < currentStep);

        if (marker) {
          marker.textContent = step < currentStep ? "✓" : String(step);
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
    if (!validateCurrentStep()) {
      return;
    }

    currentStep = Math.min(currentStep + 1, totalSteps);

    updateProgressBar();
  }

  function previousStep() {
    currentStep = Math.max(currentStep - 1, 1);

    updateProgressBar();
  }

  function initFraisDeplacementWizard() {
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

    var nextButton = form.querySelector("[data-wizard-next]");

    var previousButton = form.querySelector("[data-wizard-prev]");

    if (nextButton) {
      nextButton.addEventListener("click", nextStep);
    }

    if (previousButton) {
      previousButton.addEventListener("click", previousStep);
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

    form.addEventListener("submit", function (event) {
      if (!validateAllSteps()) {
        event.preventDefault();

        if (typeof window.showToast === "function") {
          window.showToast(
            "error",
            "Veuillez finaliser les informations obligatoires.",
          );
        }

        return;
      }

      var submitButton = form.querySelector("[data-wizard-submit]");

      if (submitButton) {
        submitButton.disabled = true;

        submitButton.setAttribute("aria-busy", "true");
      }
    });

    updateProgressBar();
  }

  document.addEventListener("DOMContentLoaded", initFraisDeplacementWizard);

  window.initFraisDeplacementWizard = initFraisDeplacementWizard;
})();
