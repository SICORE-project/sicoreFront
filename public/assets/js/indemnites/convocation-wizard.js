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

  /*
    ============================================================
    VALIDATION
    ============================================================
    */

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
      field.type === "checkbox"
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

  /*
    ============================================================
    WIZARD
    ============================================================
    */

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

  /*
    ============================================================
    CENTRES D'EXAMEN
    ============================================================
    */

  function initCentres() {
    var form = getWizard();

    if (!form) {
      return;
    }

    var container = form.querySelector("[data-centres-container]");

    var template = form.querySelector("[data-centre-template]");

    var addButton = form.querySelector("[data-add-centre]");

    var emptyMessage = form.querySelector("[data-centres-empty]");

    if (!container || !template || !addButton) {
      return;
    }

    function updateCentres() {
      var centres = container.querySelectorAll("[data-centre-card]");

      centres.forEach(function (centre, index) {
        var number = centre.querySelector("[data-centre-number]");

        if (number) {
          number.textContent = index + 1;
        }
      });

      if (emptyMessage) {
        emptyMessage.hidden = centres.length > 0;
      }
    }

    function initCentre(centre) {
      var removeButton = centre.querySelector("[data-remove-centre]");

      if (removeButton) {
        removeButton.addEventListener("click", function () {
          centre.remove();

          updateCentres();
        });
      }

      var addMemberButton = centre.querySelector("[data-add-member]");

      if (addMemberButton) {
        addMemberButton.addEventListener("click", function () {
          addMember(centre);
        });
      }

      initChefSearch(centre);
    }

    function addCentre() {
      var fragment = template.content.cloneNode(true);

      var centre = fragment.querySelector("[data-centre-card]");

      if (!centre) {
        return;
      }

      container.appendChild(centre);

      initCentre(centre);

      updateCentres();
    }

    function addMember(centre) {
      var memberTemplate = form.querySelector("[data-member-template]");

      var membersBody = centre.querySelector("[data-members-body]");

      if (!memberTemplate || !membersBody) {
        return;
      }

      var fragment = memberTemplate.content.cloneNode(true);

      var row = fragment.querySelector(".member-row");

      if (!row) {
        return;
      }

      membersBody.appendChild(row);

      initMember(row);

      updateMembers(centre);
    }

    function initMember(row) {
      var removeButton = row.querySelector("[data-remove-member]");

      if (removeButton) {
        removeButton.addEventListener("click", function () {
          var centre = row.closest("[data-centre-card]");

          row.remove();

          if (centre) {
            updateMembers(centre);
          }
        });
      }

      initMemberSearch(row);
    }

    function updateMembers(centre) {
      var body = centre.querySelector("[data-members-body]");

      var empty = centre.querySelector("[data-members-empty]");

      if (!body || !empty) {
        return;
      }

      empty.hidden = body.children.length > 0;
    }

    addButton.addEventListener("click", addCentre);

    updateCentres();
  }

  /*
    ============================================================
    RECHERCHE DES ENSEIGNANTS
    ============================================================
    */

  function rechercherEnseignant(input, hiddenInput, suggestions) {
    var form = getWizard();

    if (!form) {
      return;
    }

    var searchUrl = form.getAttribute("data-search-url");

    if (!searchUrl) {
      return;
    }

    var search = input.value.trim();

    if (search.length < 2) {
      hiddenInput.value = "";

      suggestions.innerHTML = "";
      suggestions.hidden = true;

      return;
    }

    fetch(searchUrl + "?search=" + encodeURIComponent(search), {
      method: "GET",

      headers: {
        Accept: "application/json",

        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Erreur de recherche");
        }

        return response.json();
      })
      .then(function (payload) {
        suggestions.innerHTML = "";

        var results = payload.data || payload.enseignants || payload || [];

        if (!Array.isArray(results) || results.length === 0) {
          suggestions.innerHTML = "<li>Aucun enseignant trouvé.</li>";

          suggestions.hidden = false;

          return;
        }

        results.forEach(function (enseignant) {
          var li = document.createElement("li");

          var prenom = enseignant.prenom || "";

          var nom = enseignant.nom || "";

          li.textContent = (prenom + " " + nom).trim();

          li.addEventListener("click", function () {
            input.value = li.textContent;

            hiddenInput.value = enseignant.id || "";

            suggestions.innerHTML = "";

            suggestions.hidden = true;

            var memberRow = input.closest(".member-row");

            if (memberRow) {
              // Recherche d'un membre du jury : on renseigne le nom et
              // le telephone de CETTE ligne, pas ceux du chef de centre.
              var memberNomInput = memberRow.querySelector("[data-member-nom]");

              var memberTelephoneInput = memberRow.querySelector(
                "[data-member-telephone]",
              );

              if (memberNomInput) {
                memberNomInput.value = nom;
              }

              if (memberTelephoneInput && enseignant.telephone) {
                memberTelephoneInput.value = enseignant.telephone;
              }
            } else {
              // Recherche du chef de centre.
              var telephoneInput = input
                .closest("[data-centre-card]")
                ?.querySelector("[data-chef-telephone-input]");

              if (telephoneInput && enseignant.telephone) {
                telephoneInput.value = enseignant.telephone;
              }
            }
          });

          suggestions.appendChild(li);
        });

        suggestions.hidden = false;
      })
      .catch(function () {
        suggestions.innerHTML = "<li>Erreur lors de la recherche.</li>";

        suggestions.hidden = false;
      });
  }

  function initChefSearch(centre) {
    var container = centre.querySelector("[data-chef-search]");

    if (!container) {
      return;
    }

    var input = container.querySelector("[data-chef-search-input]");

    var hiddenInput = container.querySelector("[data-chef-id-input]");

    var suggestions = container.querySelector("[data-chef-suggestions]");

    if (!input || !hiddenInput || !suggestions) {
      return;
    }

    input.addEventListener("input", function () {
      rechercherEnseignant(input, hiddenInput, suggestions);
    });

    input.addEventListener("focus", function () {
      if (input.value.trim().length >= 2) {
        rechercherEnseignant(input, hiddenInput, suggestions);
      }
    });
  }

  function initMemberSearch(row) {
    var container = row.querySelector("[data-member-search]");

    if (!container) {
      return;
    }

    var input = container.querySelector("[data-member-search-input]");

    var hiddenInput = container.querySelector("[data-member-id-input]");

    var suggestions = container.querySelector("[data-member-suggestions]");

    if (!input || !hiddenInput || !suggestions) {
      return;
    }

    input.addEventListener("input", function () {
      rechercherEnseignant(input, hiddenInput, suggestions);
    });
  }

  /*
    ============================================================
    SERIALISATION DES CENTRES ET MEMBRES
    ============================================================
    */

  function prepareFormData() {
    var form = getWizard();

    if (!form) {
      return;
    }

    form
      .querySelectorAll("[data-generated-convocation]")
      .forEach(function (input) {
        input.remove();
      });

    var centresContainer = form.querySelector("[data-centres-container]");

    if (!centresContainer) {
      return;
    }

    var centres = centresContainer.querySelectorAll("[data-centre-card]");

    var beneficiaireIndex = 0;

    centres.forEach(function (centre, centreIndex) {
      function addHidden(name, value) {
        var input = document.createElement("input");

        input.type = "hidden";

        input.name = name;

        input.value = value || "";

        input.setAttribute("data-generated-convocation", "true");

        form.appendChild(input);
      }

      var centreInput = centre.querySelector('[data-field="centre"]');

      var juryInput = centre.querySelector('[data-field="jury"]');

      var metierInput = centre.querySelector('[data-field="metier"]');

      var chefInput = centre.querySelector('[data-field="chef_centre_id"]');

      var telephoneInput = centre.querySelector(
        '[data-field="chef_centre_telephone"]',
      );

      addHidden(
        "centres[" + centreIndex + "][centre]",
        centreInput ? centreInput.value : "",
      );

      addHidden(
        "centres[" + centreIndex + "][jury]",
        juryInput ? juryInput.value : "",
      );

      addHidden(
        "centres[" + centreIndex + "][metier]",
        metierInput ? metierInput.value : "",
      );

      addHidden(
        "centres[" + centreIndex + "][chef_centre_id]",
        chefInput ? chefInput.value : "",
      );

      addHidden(
        "centres[" + centreIndex + "][chef_centre_telephone]",
        telephoneInput ? telephoneInput.value : "",
      );

      var membersBody = centre.querySelector("[data-members-body]");

      if (!membersBody) {
        return;
      }

      var rows = membersBody.querySelectorAll(".member-row");

      rows.forEach(function (row) {
        var memberId = row.querySelector("[data-member-id-input]");

        var fonction = row.querySelector("[data-member-fonction]");

        var provenance = row.querySelector("[data-member-provenance]");

        var nom = row.querySelector("[data-member-nom]");

        // Le back (ConvocationBeneficiaireController / pivot
        // convocation_enseignant) ne stocke que enseignant_id, fonction
        // et centre_id - il n'existe pas de colonne pour un nom saisi a
        // la main. Un membre du jury doit donc obligatoirement etre
        // selectionne dans l'autocomplete (data-member-id-input rempli)
        // pour pouvoir etre enregistre.
        if (!memberId || !memberId.value) {
          if (nom && nom.value.trim() && typeof window.showToast === "function") {
            window.showToast(
              "error",
              '"' +
                nom.value.trim() +
                '" n\'a pas ete selectionne dans la liste de recherche et ne sera pas enregistre.',
            );
          }

          return;
        }

        addHidden(
          "beneficiaires[" + beneficiaireIndex + "][enseignant_id]",
          memberId.value,
        );

        addHidden(
          "beneficiaires[" + beneficiaireIndex + "][fonction]",
          fonction ? fonction.value : "",
        );

        addHidden(
          "beneficiaires[" + beneficiaireIndex + "][provenance]",
          provenance ? provenance.value : "",
        );

        // C'etait le bug principal : sans centre_index, le back ne
        // pouvait jamais rattacher ce membre a la carte "centre" dans
        // laquelle il avait ete saisi (centre_id restait toujours null).
        addHidden(
          "beneficiaires[" + beneficiaireIndex + "][centre_index]",
          String(centreIndex),
        );

        beneficiaireIndex++;
      });
    });
  }

  /*
    ============================================================
    INITIALISATION
    ============================================================
    */

  function initConvocationWizard() {
    var form = getWizard();

    if (!form || form.dataset.wizardBound === "true") {
      return;
    }

    form.dataset.wizardBound = "true";

    totalSteps = form.querySelectorAll("[data-wizard-panel]").length || 2;

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

    initCentres();

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

      prepareFormData();

      var submitButton = form.querySelector("[data-wizard-submit]");

      if (submitButton) {
        submitButton.disabled = true;

        submitButton.setAttribute("aria-busy", "true");
      }
    });

    updateProgressBar();
  }

  document.addEventListener("DOMContentLoaded", initConvocationWizard);

  window.initConvocationWizard = initConvocationWizard;

  window.nextStep = nextStep;

  window.previousStep = previousStep;

  window.goToStep = goToStep;

  window.validateCurrentStep = validateCurrentStep;

  window.validateAllSteps = validateAllSteps;
})();
