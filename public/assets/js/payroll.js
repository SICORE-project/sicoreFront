/*
 * INTERACTIONS DU MODULE GESTION DE LA PAIE
 * Chargé par resources/views/components/module-page.blade.php uniquement sur
 * une page Paie connectée. Le script construit les formulaires, gère la chaîne
 * IA → IEF → matricule, filtre les tableaux et envoie les actions au frontend.
 * Les validations définitives et les calculs restent toujours dans sicoreBack.
 */
(function () {
  "use strict";

  // Configuration injectée par Blade : données API, formulaires, slug et URL.
  var page = window.SICOREPayrollPage;
  if (!page || !document.querySelector("[data-payroll-module]")) {
    return;
  }

  // Éléments de la modale partagée, définie dans module-page.blade.php.
  var modal = document.querySelector("[data-payroll-modal]");
  var form = modal ? modal.querySelector("[data-payroll-form]") : null;
  var fieldsHost = modal ? modal.querySelector("[data-payroll-fields]") : null;
  var title = modal ? modal.querySelector("[data-payroll-modal-title]") : null;
  var confirmation = modal ? modal.querySelector("[data-payroll-confirmation]") : null;
  var status = modal ? modal.querySelector("[data-payroll-status]") : null;
  var submitButton = modal ? modal.querySelector("[data-payroll-submit]") : null;
  var currentAction = null;
  var modalDefaults = {};

  /** Affiche une notification commune ou une alerte de secours. */
  function notify(type, message) {
    if (window.SICOREApp && typeof window.SICOREApp.notify === "function") {
      window.SICOREApp.notify(type, message);
      return;
    }
    window.alert(message);
  }

  /** Échappe toute valeur avant insertion HTML pour empêcher une injection XSS. */
  function escapeHtml(value) {
    var node = document.createElement("div");
    node.textContent = value == null ? "" : String(value);
    return node.innerHTML;
  }

  function optionMarkup(options, selectedValue) {
    return (options || []).map(function (option) {
      var selected = String(option.value) === String(selectedValue || "") ? " selected" : "";
      return '<option value="' + escapeHtml(option.value) + '"' + selected + ">" +
        escapeHtml(option.label) + "</option>";
    }).join("");
  }

  /** Retourne les options dynamiques adaptées au type du champ. */
  function fieldOptions(field) {
    if (field.type === "period") {
      var periods = page.data.periods || [];
      if (field.open_only) {
        periods = periods.filter(function (period) {
          return period.status === "open";
        });
      }
      return periods.map(function (period) {
        return {
          value: period.id,
          label: period.label + " — " + period.status_label
        };
      });
    }
    if (field.type === "academic_inspection") {
      return page.data.academic_inspections || [];
    }
    if (field.type === "education_inspection") {
      return page.data.education_inspections || [];
    }
    if (field.type === "academic_year") {
      return page.data.academic_years || [];
    }
    if (field.type === "teaching_corps") {
      return page.data.teaching_corps || [];
    }
    if (field.type === "payroll_month") {
      return page.data.payroll_months || [];
    }
    if (field.type === "checkbox_group") {
      return page.data[field.source] || field.options || [];
    }
    if (field.type === "teacher") {
      return page.data.teachers || [];
    }
    return field.options || [];
  }

  function isCollectiveTabaskiAction() {
    return currentAction === "apply-tabaski-advance" ||
      currentAction === "apply-tabaski-deduction";
  }

  /** Déduit l'année académique d'un mois : octobre 2025 à septembre 2026. */
  function academicYearForPeriod(period) {
    if (!period || !/^\d{4}-\d{2}$/.test(String(period.code || ""))) return "";
    var parts = period.code.split("-");
    var year = Number(parts[0]);
    var month = Number(parts[1]);
    var startYear = month >= 10 ? year : year - 1;
    return startYear + "-" + (startYear + 1);
  }

  function fieldPlaceholder(field) {
    if (field.type === "academic_inspection") return "Sélectionner une IA…";
    if (field.type === "education_inspection") return "Sélectionner une IEF…";
    if (field.type === "academic_year") return "Sélectionner une année académique…";
    if (field.type === "teaching_corps") return "Sélectionner VAC ou PC…";
    if (field.type === "payroll_month") return "Sélectionner un mois…";
    return "Sélectionner…";
  }

  function checkboxGroupMarkup(field, value) {
    var selected = Array.isArray(value) ? value.map(String) : [];
    var controls = field.select_all
      ? '<div class="payroll-checkbox-actions">' +
          '<button type="button" data-checkbox-action="all">Tout sélectionner</button>' +
          '<button type="button" data-checkbox-action="none">Tout désélectionner</button>' +
        '</div>'
      : "";
    var options = fieldOptions(field).map(function (option) {
      var checked = selected.indexOf(String(option.value)) !== -1 ? " checked" : "";
      return '<label class="payroll-checkbox-option">' +
        '<input type="checkbox" name="' + escapeHtml(field.name) + '" value="' +
          escapeHtml(option.value) + '"' + checked + '>' +
        '<span>' + escapeHtml(option.label) + '</span>' +
      '</label>';
    }).join("");
    var count = field.exact
      ? '<small class="field-help" data-checkbox-count>0 / ' + escapeHtml(field.exact) + ' mois sélectionnés</small>'
      : "";

    return controls + '<div class="payroll-checkbox-grid" data-checkbox-options>' + options + '</div>' + count;
  }

  /** Construit le HTML d'un champ depuis config/payroll-forms.php. */
  function fieldMarkup(field, defaults) {
    var value = defaults[field.name] == null ? (field.default == null ? "" : field.default) : defaults[field.name];
    var required = field.required ? " required" : "";
    var attributes = [
      field.min != null ? ' min="' + escapeHtml(field.min) + '"' : "",
      field.max != null ? ' max="' + escapeHtml(field.max) + '"' : "",
      field.step != null ? ' step="' + escapeHtml(field.step) + '"' : "",
      field.minlength != null ? ' minlength="' + escapeHtml(field.minlength) + '"' : "",
      field.placeholder ? ' placeholder="' + escapeHtml(field.placeholder) + '"' : ""
    ].join("");

    if (field.type === "hidden") {
      return '<input type="hidden" name="' + escapeHtml(field.name) +
        '" value="' + escapeHtml(value) + '">';
    }

    var input;
    if (field.type === "checkbox_group") {
      input = checkboxGroupMarkup(field, value);
    } else if (field.type === "matricule") {
      input = '<input class="form-control" type="text" name="' + escapeHtml(field.name) +
        '" value="' + escapeHtml(value) + '"' + required + attributes +
        ' list="payrollMatriculeSuggestions" autocomplete="off" autocapitalize="characters" spellcheck="false">' +
        '<datalist id="payrollMatriculeSuggestions" data-payroll-matricule-suggestions></datalist>';
    } else if (
      field.type === "period" ||
      field.type === "teacher" ||
      field.type === "academic_inspection" ||
      field.type === "education_inspection" ||
      field.type === "academic_year" ||
      field.type === "teaching_corps" ||
      field.type === "payroll_month" ||
      field.type === "select"
    ) {
      input = '<select class="form-control" name="' + escapeHtml(field.name) + '"' + required + ">" +
        '<option value="">' + fieldPlaceholder(field) + "</option>" +
        optionMarkup(fieldOptions(field), value) +
        "</select>";
    } else if (field.type === "textarea") {
      input = '<textarea class="form-control" name="' + escapeHtml(field.name) + '"' +
        required + attributes + ">" + escapeHtml(value) + "</textarea>";
    } else {
      input = '<input class="form-control" type="' + escapeHtml(field.type || "text") +
        '" name="' + escapeHtml(field.name) + '" value="' + escapeHtml(value) + '"' +
        required + attributes + ">";
    }

    var classes = "form-group payroll-field" + (field.full_width ? " payroll-field-full" : "");
    var dependent = field.after_teacher ? " data-teacher-dependent hidden" : "";
    var showFor = field.show_for && field.show_for.length
      ? ' data-show-for="' + escapeHtml(field.show_for.join(",")) + '"'
      : "";
    var requiredMarker = field.required ? ' data-field-required="true"' : "";
    var teacherSummary = field.type === "matricule"
      ? '<div class="payroll-teacher-summary" data-payroll-teacher-summary hidden></div>'
      : "";

    return '<div class="' + classes + '"' + dependent + showFor + requiredMarker + ">" +
      '<label>' + escapeHtml(field.label || field.name) +
      (field.required ? ' <span aria-hidden="true">*</span>' : "") + "</label>" +
      input +
      teacherSummary +
      (field.help ? '<small class="field-help">' + escapeHtml(field.help) + "</small>" : "") +
      "</div>";
  }

  /** Fusionne les valeurs du bouton avec la période actuellement sélectionnée. */
  function defaultsFor(triggerDefaults) {
    var selectedPeriod = page.data.period || null;
    var defaults = Object.assign({}, triggerDefaults || {});

    if (selectedPeriod && defaults.payroll_period_id == null) {
      defaults.payroll_period_id = selectedPeriod.id;
    }
    if (isCollectiveTabaskiAction() && defaults.annee_academique_id == null) {
      var academicYears = page.data.academic_years || [];
      if (academicYears.length) defaults.annee_academique_id = academicYears[0].id;
    }
    if (selectedPeriod && defaults.expected_version == null && currentAction === "close-period") {
      defaults.expected_version = selectedPeriod.version;
    }
    if (defaults.enseignant_id != null) {
      var teacher = (page.data.teachers || []).find(function (item) {
        return String(item.id) === String(defaults.enseignant_id);
      });
      if (teacher) {
        if (defaults.ia_id == null) defaults.ia_id = teacher.ia_id;
        if (defaults.ief_id == null) defaults.ief_id = teacher.ief_id;
        if (defaults.matricule == null) defaults.matricule = teacher.matricule;
        [
          "type_engagement",
          "payroll_diploma_level",
          "payroll_category_level",
          "impr_monthly_amount",
          "trimf_monthly_amount",
          "ipm_monthly_amount",
          "union_checkoff_monthly_amount"
        ].forEach(function (key) {
          if (defaults[key] == null && teacher[key] != null) defaults[key] = teacher[key];
        });
      }
    }
    if (currentAction === "save-attendance") {
      if (defaults.absence_days == null) defaults.absence_days = 0;
      if (defaults.delay_minutes == null) defaults.delay_minutes = 0;
    }

    return defaults;
  }

  function parseDefaults(trigger) {
    try {
      return JSON.parse(trigger.getAttribute("data-action-defaults") || "{}");
    } catch (error) {
      return {};
    }
  }

  /** Ouvre la modale et prépare les champs de l'action choisie. */
  function openModal(trigger) {
    currentAction = trigger.getAttribute("data-payroll-action");
    var definition = page.forms[currentAction];
    if (!definition || !modal || !form) {
      notify("error", "Cette opération n’est pas disponible.");
      return;
    }

    var defaults = defaultsFor(parseDefaults(trigger));
    modalDefaults = Object.assign({}, defaults);
    title.textContent = definition.title || "Opération de paie";
    confirmation.textContent = definition.confirmation || "";
    confirmation.hidden = !definition.confirmation;
    status.textContent = "";
    status.classList.remove("success");
    submitButton.disabled = false;
    fieldsHost.innerHTML = (definition.fields || []).map(function (field) {
      return fieldMarkup(field, defaults);
    }).join("");
    modal.hidden = false;
    document.body.classList.add("payroll-modal-open");

    var periodField = form.elements.payroll_period_id;
    if (periodField) {
      periodField.addEventListener("change", function () {
        if (currentAction === "close-period") {
          syncPeriodVersion();
          return;
        }
        syncEditableRecord();
      });
      if (isCollectiveTabaskiAction()) {
        periodField.addEventListener("change", syncApplicationAcademicYear);
      }
    }
    initializeTeacherHierarchy(defaults);
    initializeCollectiveHierarchy(defaults);
    initializeCheckboxGroups();
    initializePayrollProfileFields();
    var codeField = form.elements.code;
    if (currentAction === "create-period" && codeField) {
      codeField.addEventListener("change", syncPeriodDates);
    }
    if (currentAction === "add-element" && codeField) {
      codeField.addEventListener("change", syncEditableRecord);
    }
    syncEditableRecord();

    var firstField = fieldsHost.querySelector("input:not([type='hidden']), select, textarea");
    if (firstField) {
      window.setTimeout(function () { firstField.focus(); }, 20);
    }
  }

  function replaceSelectOptions(select, options, placeholder, selectedValue) {
    select.innerHTML = '<option value="">' + escapeHtml(placeholder) + "</option>" +
      optionMarkup(options, selectedValue);
    select.value = selectedValue == null ? "" : String(selectedValue);
    if (select.value !== String(selectedValue == null ? "" : selectedValue)) {
      select.value = "";
    }
  }

  // ---------------------------------------------------------------------------
  // Sélection obligatoire du formateur : IA, puis IEF, puis matricule saisi.
  // Les listes et suggestions viennent de PayrollPageService.php.
  // ---------------------------------------------------------------------------
  function initializeTeacherHierarchy(defaults) {
    var iaField = form.elements.ia_id;
    var iefField = form.elements.ief_id;
    var matriculeField = form.elements.matricule;
    var teacherIdField = form.elements.enseignant_id;
    var suggestions = fieldsHost.querySelector("[data-payroll-matricule-suggestions]");
    var availableTeachers = [];

    if (!iaField || !iefField || !matriculeField || !teacherIdField || !suggestions) {
      return;
    }

    function normalizedMatricule(value) {
      return String(value || "").trim().toLocaleUpperCase("fr-FR");
    }

    function selectedTeacher() {
      return (page.data.teachers || []).find(function (teacher) {
        return String(teacher.id) === String(teacherIdField.value);
      }) || null;
    }

  /** Vérifie le matricule saisi et affiche le résumé du formateur résolu. */
  function syncTeacherDetails() {
      var teacher = selectedTeacher();
      var summary = fieldsHost.querySelector("[data-payroll-teacher-summary]");

      fieldsHost.querySelectorAll("[data-teacher-dependent]").forEach(function (field) {
        field.hidden = !teacher;
        field.querySelectorAll("input, select, textarea").forEach(function (control) {
          control.disabled = !teacher;
        });
      });

      if (summary) {
        summary.hidden = !teacher;
        summary.innerHTML = teacher
          ? '<strong><i class="fa-solid fa-user-check" aria-hidden="true"></i> Formateur identifié</strong>' +
            "<span>" + escapeHtml(teacher.name) + " · " + escapeHtml(teacher.matricule) + "</span>" +
            "<small>" + escapeHtml(teacher.establishment || "Établissement non renseigné") + "</small>"
          : "";
      }

      if (teacher && currentAction === "configure-teacher-payroll") {
        [
          "type_engagement",
          "payroll_diploma_level",
          "payroll_category_level",
          "impr_monthly_amount",
          "trimf_monthly_amount",
          "ipm_monthly_amount",
          "union_checkoff_monthly_amount"
        ].forEach(function (key) {
          var control = form.elements[key];
          if (control) control.value = teacher[key] == null ? "" : teacher[key];
        });
      }

      syncEditableRecord();
      syncPayrollProfileFields();

      submitButton.disabled = !teacher;
    }

    function resolveMatricule() {
      var value = normalizedMatricule(matriculeField.value);
      var teacher = availableTeachers.find(function (item) {
        return normalizedMatricule(item.matricule) === value;
      }) || null;

      matriculeField.value = value;
      teacherIdField.value = teacher ? teacher.id : "";
      matriculeField.setCustomValidity(
        value && !teacher ? "Ce matricule ne correspond pas à l’IA et à l’IEF sélectionnées." : ""
      );
      syncTeacherDetails();
    }

  /** Limite les matricules aux enseignants appartenant à l'IEF sélectionnée. */
  function populateTeachers(selectedTeacherId) {
      availableTeachers = (page.data.teachers || []).filter(function (teacher) {
        return String(teacher.ia_id) === String(iaField.value) &&
          String(teacher.ief_id) === String(iefField.value) &&
          teacher.matricule;
      });
      suggestions.innerHTML = availableTeachers.map(function (teacher) {
        return '<option value="' + escapeHtml(teacher.matricule) + '">' +
          escapeHtml(teacher.name) + "</option>";
      }).join("");

      var selected = availableTeachers.find(function (teacher) {
        return String(teacher.id) === String(selectedTeacherId);
      }) || null;
      matriculeField.disabled = !iefField.value || availableTeachers.length === 0;
      matriculeField.placeholder = availableTeachers.length
        ? "Saisir le matricule"
        : "Aucun matricule disponible";
      matriculeField.value = selected ? selected.matricule : "";
      teacherIdField.value = "";
      matriculeField.setCustomValidity("");
      resolveMatricule();
    }

  /** Limite les IEF à celles de l'IA sélectionnée. */
  function populateEducationInspections(selectedIef, selectedTeacherId) {
      var inspections = (page.data.education_inspections || []).filter(function (inspection) {
        return String(inspection.ia_id) === String(iaField.value);
      });

      replaceSelectOptions(
        iefField,
        inspections,
        inspections.length ? "Sélectionner une IEF…" : "Aucune IEF disponible",
        selectedIef
      );
      iefField.disabled = !iaField.value || inspections.length === 0;
      populateTeachers(selectedTeacherId);
    }

    replaceSelectOptions(
      iaField,
      page.data.academic_inspections || [],
      "Sélectionner une IA…",
      defaults.ia_id
    );
    populateEducationInspections(defaults.ief_id, defaults.enseignant_id);

    iaField.addEventListener("change", function () {
      populateEducationInspections("", "");
    });
    iefField.addEventListener("change", function () {
      populateTeachers("");
    });
    matriculeField.addEventListener("input", resolveMatricule);
    matriculeField.addEventListener("change", resolveMatricule);
  }

  // ---------------------------------------------------------------------------
  // Ciblage collectif Tabaski : corps, IA et IEF, sans formateur individuel.
  // ---------------------------------------------------------------------------
  function initializeCollectiveHierarchy(defaults) {
    if (!isCollectiveTabaskiAction()) return;

    var iaField = form.elements.ia_id;
    var iefField = form.elements.ief_id;
    if (!iaField || !iefField || form.elements.matricule) return;

    function populateEducationInspections(selectedIef) {
      var inspections = (page.data.education_inspections || []).filter(function (inspection) {
        return String(inspection.ia_id) === String(iaField.value);
      });
      replaceSelectOptions(
        iefField,
        inspections,
        inspections.length ? "Sélectionner une IEF…" : "Aucune IEF disponible",
        selectedIef
      );
      iefField.disabled = !iaField.value || inspections.length === 0;
    }

    replaceSelectOptions(
      iaField,
      page.data.academic_inspections || [],
      "Sélectionner une IA…",
      defaults.ia_id
    );
    populateEducationInspections(defaults.ief_id);

    iaField.addEventListener("change", function () {
      populateEducationInspections("");
    });
  }

  function initializeCheckboxGroups() {
    fieldsHost.querySelectorAll('.payroll-field').forEach(function (container) {
      var checkboxes = Array.prototype.slice.call(container.querySelectorAll('input[type="checkbox"]'));
      if (!checkboxes.length) return;

      var definition = (page.forms[currentAction].fields || []).find(function (field) {
        return field.name === checkboxes[0].name;
      }) || {};
      var count = container.querySelector('[data-checkbox-count]');

      function sync() {
        var selected = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
        if (count && definition.exact) {
          count.textContent = selected + ' / ' + definition.exact + ' mois sélectionnés';
        }
        checkboxes.forEach(function (checkbox) {
          checkbox.disabled = Boolean(definition.exact && selected >= definition.exact && !checkbox.checked);
        });
      }

      container.querySelectorAll('[data-checkbox-action]').forEach(function (button) {
        button.addEventListener('click', function () {
          var select = button.getAttribute('data-checkbox-action') === 'all';
          checkboxes.forEach(function (checkbox) { checkbox.checked = select; });
          sync();
        });
      });
      checkboxes.forEach(function (checkbox) { checkbox.addEventListener('change', sync); });
      sync();
    });
  }

  /** Affiche les champs pertinents selon le profil vacataire ou contractuel. */
  function syncPayrollProfileFields() {
    if (!form || currentAction !== "configure-teacher-payroll") return;

    var engagementField = form.elements.type_engagement;
    var engagement = engagementField ? engagementField.value : "";
    fieldsHost.querySelectorAll("[data-show-for]").forEach(function (container) {
      var allowed = (container.getAttribute("data-show-for") || "").split(",");
      var visible = allowed.indexOf(engagement) !== -1;
      var teacherResolved = form.elements.enseignant_id && form.elements.enseignant_id.value;
      container.hidden = !visible || !teacherResolved;
      container.querySelectorAll("input, select, textarea").forEach(function (control) {
        control.disabled = !visible || !teacherResolved;
        control.required = visible && container.getAttribute("data-field-required") === "true";
      });
    });
  }

  function initializePayrollProfileFields() {
    if (!form || currentAction !== "configure-teacher-payroll") return;
    var engagementField = form.elements.type_engagement;
    if (engagementField) {
      engagementField.addEventListener("change", syncPayrollProfileFields);
    }
    syncPayrollProfileFields();
  }

  // ---------------------------------------------------------------------------
  // Filtre instantané du tableau : aucune requête réseau ni rechargement.
  // ---------------------------------------------------------------------------
  function initializeLiveHierarchyFilter() {
    var container = document.querySelector("[data-payroll-live-filter]");
    var table = document.querySelector("#moduleTable");
    if (!container || !table) return;

    var iaField = container.querySelector("[data-payroll-live-ia]");
    var iefField = container.querySelector("[data-payroll-live-ief]");
    var matriculeField = container.querySelector("[data-payroll-live-matricule]");
    var suggestions = container.querySelector("[data-payroll-live-suggestions]");
    var resetButton = container.querySelector("[data-payroll-live-reset]");
    var resultText = container.querySelector("[data-payroll-live-results]");
    var rows = Array.prototype.slice.call(table.querySelectorAll("tbody [data-payroll-row]"));

    if (!iaField || !iefField || !matriculeField || !suggestions || !resetButton) return;

    function normalize(value) {
      return String(value || "").trim().toLocaleUpperCase("fr-FR");
    }

    function syncEmptyState(visible) {
      var card = table.closest(".table-card");
      var empty = card ? card.querySelector(".empty-message") : null;
      if (empty) empty.classList.toggle("show", visible === 0);
    }

    /** Compare chaque ligne aux trois critères et actualise le compteur. */
    function applyFilter() {
      var ia = String(iaField.value || "");
      var ief = String(iefField.value || "");
      var matricule = normalize(matriculeField.value);
      var hasFilter = Boolean(ia || ief || matricule);
      var visible = 0;

      rows.forEach(function (row) {
        var rowMatricule = normalize(row.getAttribute("data-payroll-matricule"));
        var matches = (!ia || row.getAttribute("data-payroll-ia") === ia) &&
          (!ief || row.getAttribute("data-payroll-ief") === ief) &&
          (!matricule || rowMatricule.indexOf(matricule) !== -1);

        row.classList.toggle("is-hierarchy-hidden", !matches);
        if (matches && !row.classList.contains("is-hidden")) visible += 1;
      });

      resetButton.disabled = !hasFilter;
      if (resultText) {
        resultText.textContent = hasFilter
          ? visible + " résultat(s) affiché(s), sans rechargement."
          : "Affichage de tous les résultats (" + visible + ").";
      }
      syncEmptyState(visible);
      table.dispatchEvent(new CustomEvent("sicore:table-filtered", {
        detail: { resetPage: true }
      }));
    }

    function populateMatricules() {
      var ia = String(iaField.value || "");
      var ief = String(iefField.value || "");
      var teachers = (page.data.teachers || []).filter(function (teacher) {
        return String(teacher.ia_id || "") === ia &&
          String(teacher.ief_id || "") === ief &&
          teacher.matricule;
      });

      suggestions.innerHTML = teachers.map(function (teacher) {
        return '<option value="' + escapeHtml(teacher.matricule) + '">' +
          escapeHtml(teacher.name || "") + "</option>";
      }).join("");
      matriculeField.disabled = !ief || teachers.length === 0;
      matriculeField.placeholder = teachers.length
        ? "Saisir un matricule"
        : "Aucun matricule pour cette IEF";
    }

    function populateIefs() {
      var ia = String(iaField.value || "");
      var inspections = (page.data.education_inspections || []).filter(function (inspection) {
        return String(inspection.ia_id || "") === ia;
      });

      replaceSelectOptions(
        iefField,
        inspections,
        ia ? "Toutes les IEF de cette IA" : "Choisissez d’abord une IA",
        ""
      );
      iefField.disabled = !ia || inspections.length === 0;
      matriculeField.value = "";
      matriculeField.disabled = true;
      suggestions.innerHTML = "";
      applyFilter();
    }

    iaField.addEventListener("change", populateIefs);
    iefField.addEventListener("change", function () {
      matriculeField.value = "";
      populateMatricules();
      applyFilter();
    });
    matriculeField.addEventListener("input", applyFilter);
    matriculeField.addEventListener("change", function () {
      matriculeField.value = normalize(matriculeField.value);
      applyFilter();
    });
    resetButton.addEventListener("click", function () {
      iaField.value = "";
      matriculeField.value = "";
      populateIefs();
      iaField.focus();
    });

    applyFilter();
  }

  /** Ferme la modale et réinitialise son état visuel. */
  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    document.body.classList.remove("payroll-modal-open");
    currentAction = null;
    modalDefaults = {};
    form.reset();
  }

  /**
   * Recharge la version et les valeurs d'une saisie existante. Une nouvelle
   * saisie conserve une version vide ; une modification envoie exactement la
   * version fournie par le backend, ce qui évite les faux conflits tout en
   * gardant la protection contre une vraie modification concurrente.
   */
  function syncEditableRecord() {
    if (!form || ["save-attendance", "add-element"].indexOf(currentAction) === -1) return;

    var periodField = form.elements.payroll_period_id;
    var teacherField = form.elements.enseignant_id;
    var versionField = form.elements.expected_version;
    if (!periodField || !teacherField || !versionField) return;

    var code = form.elements.code
      ? String(form.elements.code.value || "").trim().toLocaleUpperCase("fr-FR")
      : "";
    var record = (page.data.input_records || []).find(function (item) {
      if (item.action !== currentAction ||
          String(item.payroll_period_id) !== String(periodField.value) ||
          String(item.enseignant_id) !== String(teacherField.value)) {
        return false;
      }

      return currentAction !== "add-element" ||
        String(item.code || "").trim().toLocaleUpperCase("fr-FR") === code;
    }) || null;

    var hadLoadedRecord = versionField.value !== "";
    versionField.value = record ? record.expected_version : "";

    if (record) {
      var editableFields = currentAction === "save-attendance"
        ? ["absence_days", "delay_minutes", "deduction_amount", "notes"]
        : ["label", "category", "amount"];
      editableFields.forEach(function (name) {
        var control = form.elements[name];
        if (control) control.value = record[name] == null ? "" : record[name];
      });
      status.textContent = "Saisie existante chargée avec sa version courante.";
      return;
    }

    if (hadLoadedRecord) {
      var resetFields = currentAction === "save-attendance"
        ? ["absence_days", "delay_minutes", "deduction_amount", "notes"]
        : ["label", "category", "amount"];
      resetFields.forEach(function (name) {
        var control = form.elements[name];
        if (control) {
          control.value = modalDefaults[name] == null ? "" : modalDefaults[name];
        }
      });
      status.textContent = "Nouvelle saisie : aucune version antérieure n’est requise.";
    }
  }

  function syncPeriodVersion() {
    var periodField = form.elements.payroll_period_id;
    var versionField = form.elements.expected_version;
    if (!periodField || !versionField) return;

    var period = (page.data.periods || []).find(function (item) {
      return String(item.id) === String(periodField.value);
    });
    versionField.value = period ? period.version : "";
  }

  /** Garde l'année académique cohérente avec le mois d'application choisi. */
  function syncApplicationAcademicYear() {
    if (!isCollectiveTabaskiAction()) return;
    var periodField = form.elements.payroll_period_id;
    var academicYearField = form.elements.academic_year;
    if (!periodField || !academicYearField) return;

    var period = (page.data.periods || []).find(function (item) {
      return String(item.id) === String(periodField.value);
    });
    var academicYear = academicYearForPeriod(period);
    if (academicYear) academicYearField.value = academicYear;
  }

  function syncPeriodDates() {
    var code = form.elements.code ? form.elements.code.value : "";
    if (!/^\d{4}-\d{2}$/.test(code)) return;

    var parts = code.split("-");
    var year = Number(parts[0]);
    var month = Number(parts[1]);
    var lastDay = new Date(Date.UTC(year, month, 0)).getUTCDate();
    if (form.elements.start_date && !form.elements.start_date.value) {
      form.elements.start_date.value = code + "-01";
    }
    if (form.elements.end_date && !form.elements.end_date.value) {
      form.elements.end_date.value = code + "-" + String(lastDay).padStart(2, "0");
    }
  }

  /** Convertit le formulaire en objet JSON envoyé au contrôleur frontend. */
  function payloadFromForm() {
    var payload = {};
    new FormData(form).forEach(function (value, key) {
      if (value !== "") {
        if (Object.prototype.hasOwnProperty.call(payload, key)) {
          payload[key] = Array.isArray(payload[key]) ? payload[key] : [payload[key]];
          payload[key].push(value);
        } else {
          payload[key] = value;
        }
      }
    });
    ['ia_ids', 'months'].forEach(function (key) {
      if (payload[key] != null && !Array.isArray(payload[key])) payload[key] = [payload[key]];
    });
    return payload;
  }

  function validateTabaskiForm() {
    if (!isCollectiveTabaskiAction()) return true;

    var iaCount = form.querySelectorAll('input[name="ia_ids"]:checked').length;
    if (iaCount < 1) {
      status.textContent = 'Sélectionnez au moins une Inspection académique (IA).';
      return false;
    }
    if (currentAction === 'apply-tabaski-deduction') {
      var monthCount = form.querySelectorAll('input[name="months"]:checked').length;
      if (monthCount !== 10) {
        status.textContent = 'La retenue Tabaski doit porter sur exactement 10 mois distincts.';
        return false;
      }
    }
    var amount = form.elements.amount ? Number(form.elements.amount.value) : 0;
    if (!Number.isFinite(amount) || amount <= 0) {
      status.textContent = 'Le montant doit être strictement supérieur à zéro.';
      return false;
    }

    return true;
  }

  /** Produit une clé unique empêchant le double traitement d'une commande. */
  function idempotencyKey() {
    if (window.crypto && typeof window.crypto.randomUUID === "function") {
      return window.crypto.randomUUID();
    }
    return "web-" + Date.now() + "-" + Math.random().toString(16).slice(2);
  }

  function errorMessage(payload) {
    if (payload && payload.errors) {
      var messages = [];
      Object.keys(payload.errors).forEach(function (key) {
        var values = Array.isArray(payload.errors[key]) ? payload.errors[key] : [payload.errors[key]];
        messages = messages.concat(values);
      });
      if (messages.length) return messages.join(" ");
    }
    return payload && payload.message ? payload.message : "L’opération n’a pas pu être effectuée.";
  }

  /** Envoie l'action par fetch, affiche les erreurs ou recharge la page réussie. */
  function submit(event) {
    event.preventDefault();
    if (!currentAction || !form.reportValidity() || !validateTabaskiForm()) return;

    var csrf = document.querySelector('meta[name="csrf-token"]');
    var url = page.actionUrl.replace("__ACTION__", encodeURIComponent(currentAction));
    submitButton.disabled = true;
    submitButton.classList.add("is-loading");
    status.textContent = "Traitement sécurisé en cours…";

    fetch(url, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf ? csrf.content : "",
        "X-SICORE-NEXT": window.location.pathname + window.location.search,
        "Idempotency-Key": idempotencyKey()
      },
      body: JSON.stringify(payloadFromForm())
    }).then(function (response) {
      return response.json().catch(function () { return {}; }).then(function (payload) {
        if (response.status === 401 && payload.login_url) {
          var redirectError = new Error(payload.message || "Votre session a expiré. Reconnexion requise.");
          redirectError.redirecting = true;
          window.location.assign(payload.login_url);
          throw redirectError;
        }
        if (!response.ok) {
          throw new Error(errorMessage(payload));
        }
        return payload;
      });
    }).then(function (payload) {
      status.classList.add("success");
      status.textContent = payload.message || "Opération effectuée.";
      notify("success", status.textContent);
      window.setTimeout(function () {
        closeModal();
        window.location.reload();
      }, 650);
    }).catch(function (error) {
      if (error.redirecting) return;
      status.classList.remove("success");
      status.textContent = error.message;
      notify("error", error.message);
    }).finally(function () {
      submitButton.disabled = false;
      submitButton.classList.remove("is-loading");
    });
  }

  document.querySelectorAll("[data-payroll-action]").forEach(function (trigger) {
    trigger.addEventListener("click", function () {
      openModal(trigger);
    });
  });

  initializeLiveHierarchyFilter();

  if (form) {
    form.addEventListener("submit", submit);
  }

  document.querySelectorAll("[data-payroll-modal-close]").forEach(function (button) {
    button.addEventListener("click", closeModal);
  });

  if (modal) {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) closeModal();
    });
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal && !modal.hidden) {
      closeModal();
    }
  });
})();
