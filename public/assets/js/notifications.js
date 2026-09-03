/*
 * NOTIFICATIONS TEMPORAIRES (TOASTS)
 * Chargé sur toutes les pages par layouts/base.blade.php.
 * app.js et payroll.js appellent window.showToast(type, message).
 */
(function () {
  "use strict";

  // Libellé lisible associé à chaque niveau de notification.
  var labels = {
    success: "Succes",
    error: "Erreur",
    warning: "Avertissement",
    info: "Information"
  };

  /** Crée le conteneur de notifications s'il n'existe pas encore. */
  function ensureToastHost() {
    var host = document.getElementById("toastHost");
    if (host) {
      return host;
    }

    host = document.createElement("div");
    host.id = "toastHost";
    host.className = "toast-host";
    host.setAttribute("aria-live", "polite");
    host.setAttribute("aria-atomic", "true");
    document.body.appendChild(host);
    return host;
  }

  /** Affiche une notification, permet sa fermeture puis la retire automatiquement. */
  function showToast(type, message) {
    var variant = labels[type] ? type : "info";
    var host = ensureToastHost();
    var toast = document.createElement("div");
    toast.className = "toast toast-" + variant;
    toast.innerHTML = [
      '<strong>' + labels[variant] + "</strong>",
      '<span>' + (message || "Operation effectuee.") + "</span>",
      '<button type="button" aria-label="Fermer">&times;</button>'
    ].join("");

    toast.querySelector("button").addEventListener("click", function () {
      toast.remove();
    });

    host.appendChild(toast);
    window.setTimeout(function () {
      toast.classList.add("is-leaving");
      window.setTimeout(function () {
        toast.remove();
      }, 180);
    }, 4820);
  }

  /** Raccourci conservé pour les anciens formulaires de présentation. */
  function showSuccessToast(message) {
    showToast("success", message);
  }

  window.showToast = showToast;
  window.showSuccessToast = showSuccessToast;

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".server-flash-success").forEach(function (flash) {
      window.setTimeout(function () {
        flash.classList.add("is-leaving");
        window.setTimeout(function () {
          flash.remove();
        }, 180);
      }, 4820);
    });
  });
})();
