(function () {
  "use strict";

  /*
    ============================================================
    RECHERCHE D'ENSEIGNANT (fiche "Modifier" — ajout d'un centre
    ou d'un membre du jury à une convocation existante)

    Version autonome, plus simple que convocation-wizard.js : ici
    il n'y a qu'une seule instance de chaque widget par page (pas
    de lignes générées dynamiquement), donc pas besoin de gérer un
    conteneur "wizard" global — chaque [data-enseignant-search]
    porte directement son URL de recherche.
    ============================================================
    */

  function debounce(fn, delay) {
    var timer = null;

    return function () {
      var context = this;
      var args = arguments;

      clearTimeout(timer);

      timer = setTimeout(function () {
        fn.apply(context, args);
      }, delay);
    };
  }

  function initSearch(container) {
    var searchUrl = container.getAttribute("data-search-url");
    var input = container.querySelector("[data-enseignant-search-input]");
    var hiddenInput = container.querySelector("[data-enseignant-id-input]");
    var suggestions = container.querySelector("[data-enseignant-suggestions]");

    if (!searchUrl || !input || !hiddenInput || !suggestions) {
      return;
    }

    var form = container.closest("form");

    // Un meme formulaire peut contenir PLUSIEURS widgets de recherche
    // independants (ex: "Président du jury" et "Chef de centre" sur le
    // formulaire "Ajouter le centre") : sans cle de groupe, le premier
    // champ telephone/statut trouve dans tout le formulaire serait
    // toujours cible, meme s'il appartient a l'AUTRE widget. Quand
    // data-enseignant-group est present, on ne cible que les champs
    // portant la MEME valeur ; sinon (formulaires a un seul widget,
    // comportement historique) on garde la recherche sur tout le
    // formulaire.
    var groupe = container.getAttribute("data-enseignant-group");

    var runSearch = debounce(function () {
      var search = input.value.trim();

      hiddenInput.value = "";

      if (search.length < 2) {
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

          var results = payload.data || [];

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

              if (form) {
                var selecteurTelephone = groupe
                  ? '[data-enseignant-telephone-target][data-enseignant-group="' + groupe + '"]'
                  : "[data-enseignant-telephone-target]";

                var telephoneTarget = form.querySelector(selecteurTelephone);

                if (telephoneTarget && enseignant.telephone) {
                  telephoneTarget.value = enseignant.telephone;
                }

                // Pre-remplit le statut (fonctionnaire/contractuel/vacataire)
                // s'il est deja renseigne sur la fiche — sinon laisse
                // "Selectionner" pour que l'utilisateur le complete.
                var selecteurCategorie = groupe
                  ? '[data-enseignant-categorie-target][data-enseignant-group="' + groupe + '"]'
                  : "[data-enseignant-categorie-target]";

                var categorieTarget = form.querySelector(selecteurCategorie);

                if (categorieTarget && enseignant.categorie_personnel) {
                  categorieTarget.value = enseignant.categorie_personnel;
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
    }, 250);

    input.addEventListener("input", runSearch);

    input.addEventListener("focus", function () {
      if (suggestions.innerHTML) {
        suggestions.hidden = false;
      }
    });

    document.addEventListener("click", function (event) {
      if (!container.contains(event.target)) {
        suggestions.hidden = true;
      }
    });
  }

  function init() {
    document
      .querySelectorAll("[data-enseignant-search]")
      .forEach(initSearch);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
