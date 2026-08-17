/*
 * SCRIPT COMMUN DU FRONTEND SICORE
 * Chargé par resources/views/layouts/base.blade.php sur toutes les pages.
 * Il gère la sidebar, le lien actif, les recherches de tableaux, les formulaires,
 * les confirmations et les notifications. La logique Paie reste dans payroll.js.
 */
(function () {
  "use strict";

  // Clés utilisées pour mémoriser les préférences du menu dans le navigateur.
  var SIDEBAR_STORAGE_KEY = "sicore_sidebar_collapsed";
  var SIDEBAR_SCROLL_STORAGE_KEY = "sicore_sidebar_scroll";
  var FONT_AWESOME_ID = "font-awesome-css";
  var FONT_AWESOME_HREF = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css";
  var sidebarTooltip = null;

  /** Charge Font Awesome si la feuille n'est pas déjà présente dans le layout. */
  function ensureFontAwesome() {
    if (!document.head || document.getElementById(FONT_AWESOME_ID) || document.querySelector('link[href*="font-awesome"]')) {
      return;
    }

    var link = document.createElement("link");
    link.id = FONT_AWESOME_ID;
    link.rel = "stylesheet";
    link.href = FONT_AWESOME_HREF;
    document.head.appendChild(link);
  }

  /** Normalise une URL pour comparer correctement les liens et la page actuelle. */
  function normalizeRoutePath(path) {
    var value = (path || "").split("?")[0].replace(/\\/g, "/");
    value = value.replace(/^https?:\/\/[^/]+/i, "");
    value = value.replace(/^\/+/, "").replace(/\/+$/, "");
    return value === "index.html" ? "" : value;
  }

  function getCurrentPage() {
    return normalizeRoutePath(window.location.pathname);
  }

  function getSidebar() {
    return document.getElementById("sidebar");
  }

  function isDesktop() {
    return window.innerWidth > 992;
  }

  function getHrefParts(href) {
    var parts = (href || "").split("#");
    return {
      page: normalizeRoutePath(parts[0]),
      hash: parts[1] ? "#" + parts[1] : ""
    };
  }

  function isSamePage(href, currentPage) {
    var parts = getHrefParts(href);
    if (!parts.page || parts.page !== currentPage) {
      return false;
    }

    if (parts.hash) {
      return window.location.hash === parts.hash;
    }

    return window.location.hash === "";
  }

  function hasSamePath(href, currentPage) {
    var parts = getHrefParts(href);
    return Boolean(parts.page && parts.page === currentPage);
  }

  /** Vérifie et complète la structure minimale sidebar + contenu + calque mobile. */
  function renderAppShell() {
    var sidebar = getSidebar();
    var mainContent = document.querySelector(".main-content");

    if (!sidebar || !mainContent) {
      return;
    }

    document.body.classList.add("app-body");

    if (!document.querySelector(".mobile-overlay")) {
      document.body.insertAdjacentHTML(
        "beforeend",
        '<div class="mobile-overlay" data-sidebar-close aria-hidden="true"></div>'
      );
    }
  }

  function setButtonIcon(button, iconClass) {
    var icon = button.querySelector("i");
    if (!icon) {
      button.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';
      return;
    }
    icon.className = iconClass;
  }

  /** Harmonise les boutons et icônes des anciens en-têtes avec le design actuel. */
  function enhanceTopbars() {
    document.querySelectorAll(".mobile-menu-btn").forEach(function (button) {
      setButtonIcon(button, "fa-solid fa-bars");
      button.setAttribute("aria-label", "Ouvrir le menu");
      button.title = "Ouvrir le menu";
    });

    document.querySelectorAll(".page-title-wrap").forEach(function (wrap) {
      if (wrap.querySelector("[data-sidebar-collapse]")) {
        return;
      }

      var button = document.createElement("button");
      button.className = "desktop-sidebar-btn";
      button.type = "button";
      button.setAttribute("data-sidebar-collapse", "");
      button.setAttribute("aria-label", "R\u00e9duire le menu");
      button.title = "R\u00e9duire le menu";
      button.innerHTML = '<i class="fa-solid fa-angles-left" aria-hidden="true"></i>';

      var mobileButton = wrap.querySelector(".mobile-menu-btn");
      if (mobileButton && mobileButton.nextSibling) {
        wrap.insertBefore(button, mobileButton.nextSibling);
      } else {
        wrap.insertBefore(button, wrap.firstChild);
      }
    });

    updateCollapseButtons();
  }

  function getTitleIconClass(titleIcon) {
    var text = titleIcon.textContent.trim().toUpperCase();
    var wrap = titleIcon.closest(".page-title-wrap");
    var heading = wrap && wrap.querySelector("h1") ? wrap.querySelector("h1").textContent.toLowerCase() : "";

    if (text === "TB" || heading.indexOf("tableau") !== -1) {
      return "fa-solid fa-gauge-high";
    }
    if (text === "IEF" || heading.indexOf("ief") !== -1) {
      return "fa-solid fa-sitemap";
    }
    if (heading.indexOf("enseignant") !== -1) {
      return "fa-solid fa-chalkboard-user";
    }
    if (heading.indexOf("param") !== -1) {
      return "fa-solid fa-gears";
    }
    return "";
  }

  function enhanceTitleIcons() {
    document.querySelectorAll(".title-icon").forEach(function (titleIcon) {
      if (titleIcon.querySelector("i")) {
        return;
      }

      var iconClass = getTitleIconClass(titleIcon);
      if (!iconClass) {
        return;
      }

      titleIcon.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';
      titleIcon.setAttribute("aria-hidden", "true");
    });
  }

  function enhanceIconActions() {
    var iconMap = {
      Voir: "fa-solid fa-eye",
      Modifier: "fa-solid fa-pen-to-square",
      Supprimer: "fa-solid fa-trash-can"
    };

    document.querySelectorAll(".icon-action").forEach(function (button) {
      var title = button.getAttribute("title") || button.getAttribute("aria-label") || "";
      var iconClass = iconMap[title];
      if (!iconClass) {
        return;
      }

      button.setAttribute("aria-label", title);
      if (!button.querySelector("i")) {
        button.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';
      }
    });
  }

  // ---------------------------------------------------------------------------
  // Gestion du menu latéral : état réduit, mobile, sous-menus et lien actif.
  // Vue concernée : resources/views/components/sidebar.blade.php.
  // ---------------------------------------------------------------------------
  function saveSidebarPreference(collapsed) {
    try {
      localStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? "true" : "false");
    } catch (error) {
      return false;
    }
    return true;
  }

  function restoreSidebarPreference() {
    var collapsed = false;
    try {
      collapsed = localStorage.getItem(SIDEBAR_STORAGE_KEY) === "true";
    } catch (error) {
      collapsed = false;
    }

    document.body.classList.toggle("sidebar-collapsed", collapsed && isDesktop());
    updateCollapseButtons();
  }

  function updateCollapseButtons() {
    var collapsed = document.body.classList.contains("sidebar-collapsed");
    document.querySelectorAll("[data-sidebar-collapse]").forEach(function (button) {
      button.classList.toggle("is-collapsed", collapsed);
      button.setAttribute("aria-label", collapsed ? "Afficher le menu" : "R\u00e9duire le menu");
      button.title = collapsed ? "Afficher le menu" : "R\u00e9duire le menu";
      setButtonIcon(button, collapsed ? "fa-solid fa-angles-right" : "fa-solid fa-angles-left");
    });
  }

  function collapseSidebar(forceCollapsed) {
    var shouldCollapse = typeof forceCollapsed === "boolean"
      ? forceCollapsed
      : !document.body.classList.contains("sidebar-collapsed");

    document.body.classList.toggle("sidebar-collapsed", shouldCollapse && isDesktop());
    saveSidebarPreference(shouldCollapse && isDesktop());
    updateCollapseButtons();
    hideSidebarTooltip();
  }

  function openMobileSidebar() {
    var sidebar = getSidebar();
    if (!sidebar) {
      return;
    }
    sidebar.classList.add("open");
    document.body.classList.add("sidebar-open");
  }

  function closeMobileSidebar() {
    var sidebar = getSidebar();
    if (!sidebar) {
      return;
    }
    sidebar.classList.remove("open");
    document.body.classList.remove("sidebar-open");
  }

  function toggleSidebar(forceOpen) {
    var sidebar = getSidebar();
    if (!sidebar) {
      return;
    }

    if (isDesktop()) {
      collapseSidebar();
      return;
    }

    var shouldOpen = typeof forceOpen === "boolean" ? forceOpen : !sidebar.classList.contains("open");
    if (shouldOpen) {
      openMobileSidebar();
    } else {
      closeMobileSidebar();
    }
  }

  function setSubmenuState(button, expanded) {
    var submenu = button ? button.nextElementSibling : null;
    if (!button || !submenu || !submenu.classList.contains("sidebar-submenu")) {
      return;
    }

    button.classList.toggle("expanded", expanded);
    button.setAttribute("aria-expanded", expanded ? "true" : "false");
    submenu.classList.toggle("open", expanded);
  }

  function toggleSubmenu(button) {
    if (!button || document.body.classList.contains("sidebar-collapsed")) {
      return;
    }

    setSubmenuState(button, !button.classList.contains("expanded"));
  }

  function setActiveMenu() {
    var currentPage = getCurrentPage();
    var links = document.querySelectorAll("[data-page-link]");

    document.querySelectorAll("[data-submenu-toggle]").forEach(function (button) {
      button.classList.remove("active");
      setSubmenuState(button, false);
    });

    links.forEach(function (link) {
      var target = link.getAttribute("href");
      var isActive = isSamePage(target, currentPage);
      var isRelated = hasSamePath(target, currentPage);
      link.classList.toggle("active", isActive);

      if (isActive || isRelated) {
        var submenu = link.closest(".sidebar-submenu");
        if (submenu) {
          var trigger = submenu.previousElementSibling;
          if (trigger) {
            setSubmenuState(trigger, true);
            trigger.classList.add("active");
          }
        }
      }
    });

    keepActiveSidebarLinkVisible();
  }

  function saveSidebarScroll() {
    var nav = document.querySelector(".sidebar-nav");
    if (!nav) return;

    try {
      sessionStorage.setItem(SIDEBAR_SCROLL_STORAGE_KEY, String(nav.scrollTop));
    } catch (error) {
      return;
    }
  }

  function keepActiveSidebarLinkVisible() {
    var nav = document.querySelector(".sidebar-nav");
    if (!nav) return;

    try {
      var savedScroll = sessionStorage.getItem(SIDEBAR_SCROLL_STORAGE_KEY);
      if (savedScroll !== null && Number.isFinite(Number(savedScroll))) {
        nav.scrollTop = Number(savedScroll);
      }
    } catch (error) {
      // Le stockage peut être désactivé ; le recentrage de secours reste actif.
    }

    window.requestAnimationFrame(function () {
      var activeLink = nav.querySelector(".sidebar-submenu a.active, .sidebar-link.active[href]");
      if (!activeLink) return;

      var navRect = nav.getBoundingClientRect();
      var linkRect = activeLink.getBoundingClientRect();
      var safeTop = navRect.top + 12;
      var safeBottom = navRect.bottom - 12;

      if (linkRect.top < safeTop || linkRect.bottom > safeBottom) {
        nav.scrollTop += linkRect.top - navRect.top - ((navRect.height - linkRect.height) / 2);
      }
      saveSidebarScroll();
    });
  }

  // ---------------------------------------------------------------------------
  // Recherche générale dans les tableaux.
  // L'input indique sa cible avec l'attribut data-table-filter="#idDuTableau".
  // ---------------------------------------------------------------------------
  function filterTable(input) {
    var selector = input.getAttribute("data-table-filter");
    var table = selector ? document.querySelector(selector) : null;
    if (!table) {
      return;
    }

    var query = input.value.trim().toLowerCase();
    var rows = table.querySelectorAll("tbody [data-table-row]");
    var visible = 0;

    rows.forEach(function (row) {
      var matches = row.textContent.toLowerCase().indexOf(query) !== -1;
      row.classList.toggle("is-hidden", !matches);
      if (matches && !row.classList.contains("is-hierarchy-hidden")) {
        visible += 1;
      }
    });

    var card = table.closest(".table-card");
    var empty = card ? card.querySelector(".empty-message") : null;
    if (empty) {
      empty.classList.toggle("show", visible === 0);
    }

    // La pagination repart à la première page et tient compte de la recherche.
    table.dispatchEvent(new CustomEvent("sicore:table-filtered", {
      detail: { resetPage: true }
    }));
  }

  function syncPasswordToggle(button) {
    var selector = button.getAttribute("data-password-toggle");
    var field = selector ? document.querySelector(selector) : null;
    if (!field) {
      return;
    }

    var isVisible = field.type === "text";
    button.setAttribute("aria-label", isVisible ? "Masquer le mot de passe" : "Afficher le mot de passe");
    button.setAttribute("aria-pressed", isVisible ? "true" : "false");
    setButtonIcon(button, isVisible ? "fa-solid fa-eye-slash" : "fa-solid fa-eye");
  }

  /** Affiche ou masque un mot de passe sans modifier sa valeur. */
  function togglePassword(button) {
    var selector = button.getAttribute("data-password-toggle");
    var field = selector ? document.querySelector(selector) : null;
    if (!field) {
      return;
    }
    field.type = field.type === "password" ? "text" : "password";
    syncPasswordToggle(button);
    field.focus();
  }

  /** Délègue l'affichage à notifications.js, avec alert comme solution de secours. */
  function notify(type, message) {
    if (typeof window.showToast === "function") {
      window.showToast(type, message);
      return;
    }
    window.alert(message);
  }

  /** Contrôle les champs HTML required avant une soumission locale. */
  function validateRequiredFields(form) {
    var fields = form.querySelectorAll("[required]");
    var valid = true;
    var firstInvalid = null;

    fields.forEach(function (field) {
      var isEmpty = !field.value || field.value.trim() === "";
      field.classList.toggle("is-invalid", isEmpty);
      field.setAttribute("aria-invalid", isEmpty ? "true" : "false");
      if (isEmpty && !firstInvalid) {
        firstInvalid = field;
      }
      valid = valid && !isEmpty;
    });

    var status = form.querySelector("[data-form-status]");
    if (status) {
      var successMessage = form.getAttribute("data-success-message") || "Formulaire valide. Donn\u00e9es pr\u00eates \u00e0 \u00eatre enregistr\u00e9es.";
      var errorMessage = form.getAttribute("data-error-message") || "Veuillez renseigner tous les champs obligatoires.";
      status.classList.toggle("success", valid);
      status.textContent = valid ? successMessage : errorMessage;
    }

    if (!valid && firstInvalid) {
      firstInvalid.focus();
    }

    return valid;
  }

  /**
   * Affiche une tranche de lignes et synchronise les boutons de navigation.
   * Les lignes masquées par la recherche générale ou IA/IEF/matricule ne sont
   * pas comptées dans le nombre de pages.
   */
  function refreshTablePagination(pagination, resetPage) {
    var selector = pagination.getAttribute("data-table-target");
    var table = selector ? document.querySelector(selector) : null;
    if (!table) return;

    var rows = Array.prototype.slice.call(table.querySelectorAll("tbody [data-table-row]"));
    var filteredRows = rows.filter(function (row) {
      return !row.classList.contains("is-hidden") &&
        !row.classList.contains("is-hierarchy-hidden");
    });
    var sizeField = pagination.querySelector("[data-page-size]");
    var pageSize = Math.max(1, Number(sizeField ? sizeField.value : 10) || 10);
    var totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
    var requestedPage = resetPage ? 1 : Number(pagination.dataset.currentPage || 1);
    var currentPage = Math.min(totalPages, Math.max(1, requestedPage));
    var firstIndex = (currentPage - 1) * pageSize;
    var lastIndex = Math.min(firstIndex + pageSize, filteredRows.length);

    rows.forEach(function (row) {
      row.classList.add("is-pagination-hidden");
    });
    filteredRows.slice(firstIndex, lastIndex).forEach(function (row) {
      row.classList.remove("is-pagination-hidden");
    });

    pagination.dataset.currentPage = String(currentPage);
    pagination.hidden = rows.length === 0;

    var currentPageNumber = pagination.querySelector("[data-current-page-number]");
    if (currentPageNumber) currentPageNumber.textContent = String(currentPage);

    var summary = pagination.querySelector("[data-pagination-summary]");
    if (summary) {
      summary.textContent = filteredRows.length === 0
        ? "0 résultat"
        : (firstIndex + 1) + "–" + lastIndex + " sur " + filteredRows.length;
    }

    ["first", "previous"].forEach(function (action) {
      var button = pagination.querySelector('[data-page-action="' + action + '"]');
      if (button) button.disabled = currentPage === 1;
    });
    ["next", "last"].forEach(function (action) {
      var button = pagination.querySelector('[data-page-action="' + action + '"]');
      if (button) button.disabled = currentPage === totalPages || filteredRows.length === 0;
    });

    var card = table.closest(".table-card");
    var empty = card ? card.querySelector(".empty-message") : null;
    if (empty && rows.length > 0) {
      empty.classList.toggle("show", filteredRows.length === 0);
    }
  }

  /** Active la pagination fonctionnelle de tous les tableaux SICORE. */
  function setupPagination() {
    document.querySelectorAll("[data-table-pagination]").forEach(function (pagination) {
      if (pagination.dataset.bound === "true") {
        refreshTablePagination(pagination, false);
        return;
      }
      pagination.dataset.bound = "true";

      var selector = pagination.getAttribute("data-table-target");
      var table = selector ? document.querySelector(selector) : null;
      var sizeField = pagination.querySelector("[data-page-size]");

      pagination.addEventListener("click", function (event) {
        var button = event.target.closest("[data-page-action]");
        if (!button || button.disabled) {
          return;
        }
        event.preventDefault();
        var action = button.getAttribute("data-page-action");
        var currentPage = Number(pagination.dataset.currentPage || 1);
        var visibleRows = table
          ? table.querySelectorAll("tbody [data-table-row]:not(.is-hidden):not(.is-hierarchy-hidden)").length
          : 0;
        var pageSize = Math.max(1, Number(sizeField ? sizeField.value : 10) || 10);
        var totalPages = Math.max(1, Math.ceil(visibleRows / pageSize));

        if (action === "first") currentPage = 1;
        if (action === "previous") currentPage -= 1;
        if (action === "next") currentPage += 1;
        if (action === "last") currentPage = totalPages;
        pagination.dataset.currentPage = String(currentPage);
        refreshTablePagination(pagination, false);
      });

      if (sizeField) {
        sizeField.addEventListener("change", function () {
          refreshTablePagination(pagination, true);
        });
      }
      if (table) {
        table.addEventListener("sicore:table-filtered", function (event) {
          refreshTablePagination(pagination, !event.detail || event.detail.resetPage !== false);
        });
      }

      refreshTablePagination(pagination, true);
    });
  }

  /** Branche les formulaires génériques qui ne disposent pas d'un contrôleur dédié. */
  function setupForms() {
    document.querySelectorAll("[data-validate-form]").forEach(function (form) {
      if (form.dataset.bound === "true" || form.hasAttribute("data-teacher-wizard")) {
        return;
      }
      form.dataset.bound = "true";
      form.addEventListener("submit", function (event) {
        event.preventDefault();
        if (validateRequiredFields(form)) {
          notify("success", form.getAttribute("data-success-message") || "Op\u00e9ration effectu\u00e9e avec succ\u00e8s.");
        } else {
          notify("error", form.getAttribute("data-error-message") || "Veuillez remplir les champs obligatoires.");
        }
      });

      form.querySelectorAll("[required]").forEach(function (field) {
        field.addEventListener("input", function () {
          if (field.classList.contains("is-invalid") && field.value.trim() !== "") {
            field.classList.remove("is-invalid");
            field.setAttribute("aria-invalid", "false");
          }
        });
      });
    });
  }

  // ---------------------------------------------------------------------------
  // Fenêtre de confirmation commune aux actions portant data-confirm.
  // ---------------------------------------------------------------------------
  function ensureConfirmModal() {
    var existing = document.querySelector("[data-confirm-modal]");
    if (existing) {
      return existing;
    }

    var modal = document.createElement("div");
    modal.className = "confirm-backdrop";
    modal.setAttribute("data-confirm-modal", "");
    modal.hidden = true;
    modal.innerHTML = [
      '<div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">',
      '<h2 id="confirmTitle">Confirmation</h2>',
      '<p data-confirm-message>Confirmer cette action ?</p>',
      '<div class="confirm-actions">',
      '<button class="btn-secondary" type="button" data-confirm-cancel>Annuler</button>',
      '<button class="btn-primary" type="button" data-confirm-accept>Confirmer</button>',
      "</div>",
      "</div>"
    ].join("");
    document.body.appendChild(modal);
    return modal;
  }

  function confirmAction(message, onConfirm) {
    var modal = ensureConfirmModal();
    var messageNode = modal.querySelector("[data-confirm-message]");
    var cancelButton = modal.querySelector("[data-confirm-cancel]");
    var acceptButton = modal.querySelector("[data-confirm-accept]");

    function close() {
      modal.hidden = true;
      cancelButton.removeEventListener("click", close);
      acceptButton.removeEventListener("click", accept);
    }

    function accept() {
      close();
      if (typeof onConfirm === "function") {
        onConfirm();
      }
    }

    messageNode.textContent = message || "Confirmer cette action ?";
    modal.hidden = false;
    cancelButton.addEventListener("click", close);
    acceptButton.addEventListener("click", accept);
    cancelButton.focus();
  }

  function setupConfirmations() {
    ensureConfirmModal();

    document.querySelectorAll(".icon-action[title='Supprimer']").forEach(function (button) {
      if (!button.hasAttribute("data-confirm")) {
        button.setAttribute("data-confirm", "Voulez-vous vraiment supprimer cet \u00e9l\u00e9ment ?");
        button.setAttribute("data-success-message", "\u00c9l\u00e9ment supprim\u00e9 avec succ\u00e8s.");
      }
    });

    document.querySelectorAll("[data-confirm]").forEach(function (trigger) {
      if (trigger.dataset.confirmBound === "true") {
        return;
      }
      trigger.dataset.confirmBound = "true";
      trigger.addEventListener("click", function (event) {
        event.preventDefault();
        confirmAction(trigger.getAttribute("data-confirm"), function () {
          notify("success", trigger.getAttribute("data-success-message") || "Action confirm\u00e9e.");
        });
      });
    });
  }

  /** Convertit certains messages de page en notifications temporaires. */
  function setupToasts() {
    document.querySelectorAll("[data-toast]").forEach(function (trigger) {
      if (trigger.dataset.toastBound === "true") {
        return;
      }
      trigger.dataset.toastBound = "true";
      trigger.addEventListener("click", function () {
        notify(trigger.getAttribute("data-toast"), trigger.getAttribute("data-toast-message"));
      });
    });
  }

  /** Initialise les petits calculs de présentation hors du moteur de paie. */
  function setupDynamicCalculations() {
    document.querySelectorAll("[data-calculate-indemnity]").forEach(function (button) {
      if (button.dataset.bound === "true") {
        return;
      }
      button.dataset.bound = "true";
      button.addEventListener("click", function () {
        var result = document.querySelector("[data-indemnity-result]");
        if (result) {
          result.hidden = false;
          result.innerHTML = "<strong>Montant simul\u00e9 :</strong> 185 000 FCFA pour la p\u00e9riode s\u00e9lectionn\u00e9e.";
        }
        notify("success", "Calcul des indemnit\u00e9s effectu\u00e9.");
      });
    });
  }

  function getSidebarTooltipLabel(element) {
    return element.getAttribute("data-tooltip") || element.getAttribute("aria-label") || element.title || "";
  }

  function hideSidebarTooltip() {
    if (sidebarTooltip) {
      sidebarTooltip.remove();
      sidebarTooltip = null;
    }
  }

  function showSidebarTooltip(element) {
    var label = getSidebarTooltipLabel(element);
    if (!label || !isDesktop() || !document.body.classList.contains("sidebar-collapsed")) {
      hideSidebarTooltip();
      return;
    }

    if (!sidebarTooltip) {
      sidebarTooltip = document.createElement("div");
      sidebarTooltip.className = "sidebar-tooltip";
      sidebarTooltip.setAttribute("role", "tooltip");
      document.body.appendChild(sidebarTooltip);
    }

    var rect = element.getBoundingClientRect();
    sidebarTooltip.textContent = label;
    sidebarTooltip.style.left = Math.round(rect.right + 12) + "px";
    sidebarTooltip.style.top = Math.round(rect.top + rect.height / 2) + "px";
  }

  /** Affiche le libellé des icônes lorsque la sidebar est réduite. */
  function setupSidebarTooltips() {
    document.querySelectorAll(".sidebar-link, .sidebar-logo, .logout-btn").forEach(function (element) {
      if (element.dataset.tooltipBound === "true") {
        return;
      }
      element.dataset.tooltipBound = "true";
      element.addEventListener("mouseenter", function () {
        showSidebarTooltip(element);
      });
      element.addEventListener("focus", function () {
        showSidebarTooltip(element);
      });
      element.addEventListener("mouseleave", hideSidebarTooltip);
      element.addEventListener("blur", hideSidebarTooltip);
    });
  }

  /** Regroupe tous les écouteurs de clic, saisie, redimensionnement et clavier. */
  function bindEvents() {
    var sidebarNav = document.querySelector(".sidebar-nav");
    if (sidebarNav && sidebarNav.dataset.scrollBound !== "true") {
      sidebarNav.dataset.scrollBound = "true";
      sidebarNav.addEventListener("scroll", saveSidebarScroll, { passive: true });
    }

    document.querySelectorAll("[data-sidebar-toggle]").forEach(function (button) {
      if (button.dataset.bound === "true") {
        return;
      }
      button.dataset.bound = "true";
      button.addEventListener("click", function () {
        toggleSidebar();
      });
    });

    document.querySelectorAll("[data-sidebar-collapse]").forEach(function (button) {
      if (button.dataset.bound === "true") {
        return;
      }
      button.dataset.bound = "true";
      button.addEventListener("click", function () {
        collapseSidebar();
      });
    });

    document.querySelectorAll("[data-sidebar-close]").forEach(function (button) {
      if (button.dataset.bound === "true") {
        return;
      }
      button.dataset.bound = "true";
      button.addEventListener("click", closeMobileSidebar);
    });

    document.querySelectorAll("[data-submenu-toggle]").forEach(function (button) {
      if (button.dataset.bound === "true") {
        return;
      }
      button.dataset.bound = "true";
      button.addEventListener("click", function () {
        toggleSubmenu(button);
      });
    });

    document.querySelectorAll("[data-page-link]").forEach(function (link) {
      if (link.dataset.pageBound === "true") {
        return;
      }
      link.dataset.pageBound = "true";
      link.addEventListener("click", function () {
        saveSidebarScroll();
        if (!isDesktop()) {
          closeMobileSidebar();
        }
      });
    });

    document.querySelectorAll("[data-table-filter]").forEach(function (input) {
      if (input.dataset.bound === "true") {
        return;
      }
      input.dataset.bound = "true";
      input.addEventListener("input", function () {
        filterTable(input);
      });
    });

    document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
      syncPasswordToggle(button);
      if (button.dataset.bound === "true") {
        return;
      }
      button.dataset.bound = "true";
      button.addEventListener("click", function () {
        togglePassword(button);
      });
    });
  }

  /** Réactive les comportements après l'ajout éventuel de contenu dans le DOM. */
  function refreshDynamicContent() {
    ensureFontAwesome();
    enhanceTopbars();
    enhanceTitleIcons();
    enhanceIconActions();
    setActiveMenu();
    bindEvents();
    setupSidebarTooltips();
    setupForms();
    setupPagination();
    setupConfirmations();
    setupToasts();
    setupDynamicCalculations();
  }

  window.addEventListener("resize", function () {
    hideSidebarTooltip();
    if (isDesktop()) {
      closeMobileSidebar();
      restoreSidebarPreference();
    } else {
      document.body.classList.remove("sidebar-collapsed");
      updateCollapseButtons();
    }
  });

  window.addEventListener("scroll", hideSidebarTooltip, true);
  window.addEventListener("hashchange", setActiveMenu);

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeMobileSidebar();
      hideSidebarTooltip();
      var modal = document.querySelector("[data-confirm-modal]");
      if (modal && !modal.hidden) {
        modal.hidden = true;
      }
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    ensureFontAwesome();
    renderAppShell();
    restoreSidebarPreference();
    refreshDynamicContent();
  });

  window.toggleSidebar = toggleSidebar;
  window.collapseSidebar = collapseSidebar;
  window.openMobileSidebar = openMobileSidebar;
  window.closeMobileSidebar = closeMobileSidebar;
  window.saveSidebarPreference = saveSidebarPreference;
  window.restoreSidebarPreference = restoreSidebarPreference;
  window.toggleSubmenu = toggleSubmenu;
  window.setActiveMenu = setActiveMenu;
  window.keepActiveSidebarLinkVisible = keepActiveSidebarLinkVisible;
  window.filterTable = filterTable;
  window.togglePassword = togglePassword;
  window.validateRequiredFields = validateRequiredFields;
  window.confirmAction = confirmAction;
  window.SICOREApp = {
    refresh: refreshDynamicContent,
    renderAppShell: renderAppShell,
    notify: notify
  };
})();
