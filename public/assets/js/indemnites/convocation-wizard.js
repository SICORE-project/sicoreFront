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

    var metierGroupTemplate = form.querySelector(
      "[data-metier-group-template]",
    );

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

      var addMetierGroupButton = centre.querySelector(
        "[data-add-metier-group]",
      );

      if (addMetierGroupButton) {
        addMetierGroupButton.addEventListener("click", function () {
          addMetierGroup(centre);
        });
      }

      initChefSearch(centre);
      initPresidentSearch(centre);
    }

    function addCentre() {
      var fragment = template.content.cloneNode(true);

      var centre = fragment.querySelector("[data-centre-card]");

      if (!centre) {
        return;
      }

      container.appendChild(centre);

      initCentre(centre);

      // Un centre commence toujours avec un premier groupe métier, pour
      // coller au papier (formconf.jpeg n'a jamais de centre sans aucun
      // groupe) tout en autorisant d'en ajouter/retirer d'autres ensuite.
      addMetierGroup(centre);

      updateCentres();
    }

    /*
      ============================================================
      GROUPES MÉTIER (un centre peut regrouper plusieurs métiers,
      ex: MVM puis FC sur formconf.jpeg, chacun avec ses propres
      membres mais le même centre/jury/chef de centre)
      ============================================================
      */

    function updateMetierGroups(centre) {
      var groupsContainer = centre.querySelector("[data-metiers-container]");

      var empty = centre.querySelector("[data-metiers-empty]");

      if (!groupsContainer) {
        return;
      }

      var groups = groupsContainer.querySelectorAll("[data-metier-group]");

      groups.forEach(function (group, index) {
        var number = group.querySelector("[data-metier-number]");

        if (number) {
          number.textContent = index + 1;
        }
      });

      if (empty) {
        empty.hidden = groups.length > 0;
      }
    }

    function addMetierGroup(centre) {
      var groupsContainer = centre.querySelector("[data-metiers-container]");

      if (!metierGroupTemplate || !groupsContainer) {
        return;
      }

      var fragment = metierGroupTemplate.content.cloneNode(true);

      var group = fragment.querySelector("[data-metier-group]");

      if (!group) {
        return;
      }

      groupsContainer.appendChild(group);

      initMetierGroup(group);

      updateMetierGroups(centre);
    }

    function initMetierGroup(group) {
      var removeButton = group.querySelector("[data-remove-metier-group]");

      if (removeButton) {
        removeButton.addEventListener("click", function () {
          var centre = group.closest("[data-centre-card]");

          group.remove();

          if (centre) {
            updateMetierGroups(centre);
          }
        });
      }

      var addMemberButton = group.querySelector("[data-add-member]");

      if (addMemberButton) {
        addMemberButton.addEventListener("click", function () {
          addMember(group);
        });
      }

      var importInput = group.querySelector("[data-import-members-input]");

      if (importInput) {
        importInput.addEventListener("change", function () {
          var file = importInput.files && importInput.files[0];

          if (file) {
            importerMembresDepuisCsv(file, group);
          }

          // Permet de reimporter le meme fichier deux fois de suite
          // (le navigateur ne redeclenche pas "change" sinon).
          importInput.value = "";
        });
      }
    }

    function addMember(group) {
      var memberTemplate = form.querySelector("[data-member-template]");

      var membersBody = group.querySelector("[data-members-body]");

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

      updateMembers(group);
    }

    function initMember(row) {
      var removeButton = row.querySelector("[data-remove-member]");

      if (removeButton) {
        removeButton.addEventListener("click", function () {
          var group = row.closest("[data-metier-group]");

          row.remove();

          if (group) {
            updateMembers(group);
          }
        });
      }

      initMemberSearch(row);
    }

    function updateMembers(group) {
      var body = group.querySelector("[data-members-body]");

      var empty = group.querySelector("[data-members-empty]");

      if (!body || !empty) {
        return;
      }

      empty.hidden = body.children.length > 0;
    }

    /*
      ============================================================
      IMPORT CSV D'UNE LISTE DE MEMBRES
      Permet de remplir le tableau "Métiers & membres du jury" d'un
      coup, plutot que de chercher/ajouter chaque enseignant a la
      main. Colonnes attendues (1re ligne = en-tetes) : matricule
      (ou agent), fonction, statut, provenance — seule "matricule"
      est obligatoire. Le matricule est recherche via le meme
      endpoint que la recherche manuelle (data-search-url) pour
      retrouver l'enseignant et pre-remplir nom/telephone/statut ;
      s'il n'est pas trouve, la ligne est quand meme ajoutee (avec le
      matricule saisi tel quel) pour ne rien perdre de l'import.
      ============================================================
      */

    function parseCsvSimple(texte) {
      var lignes = String(texte || "")
        .split(/\r\n|\r|\n/)
        .filter(function (ligne) {
          return ligne.trim().length > 0;
        });

      if (lignes.length === 0) {
        return { entetes: [], lignes: [] };
      }

      function decouper(ligne) {
        return ligne.split(",").map(function (cellule) {
          return cellule.trim().replace(/^"|"$/g, "");
        });
      }

      var entetes = decouper(lignes[0]).map(function (entete) {
        return entete.toLowerCase();
      });

      var donnees = lignes.slice(1).map(decouper);

      return { entetes: entetes, lignes: donnees };
    }

    function indexEntete(entetes, noms) {
      for (var i = 0; i < noms.length; i++) {
        var index = entetes.indexOf(noms[i]);

        if (index !== -1) {
          return index;
        }
      }

      return -1;
    }

    function rechercherEnseignantParMatricule(matricule) {
      var form = getWizard();

      var searchUrl = form ? form.getAttribute("data-search-url") : null;

      if (!searchUrl || !matricule) {
        return Promise.resolve(null);
      }

      return fetch(searchUrl + "?search=" + encodeURIComponent(matricule), {
        method: "GET",

        headers: {
          Accept: "application/json",

          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then(function (response) {
          return response.ok ? response.json() : null;
        })
        .then(function (payload) {
          var resultats = (payload && payload.data) || [];

          if (!Array.isArray(resultats) || resultats.length === 0) {
            return null;
          }

          var exact = resultats.filter(function (enseignant) {
            return (
              enseignant.matricule &&
              String(enseignant.matricule).toLowerCase() ===
                String(matricule).toLowerCase()
            );
          });

          return exact[0] || resultats[0];
        })
        .catch(function () {
          return null;
        });
    }

    function importerMembresDepuisCsv(file, group) {
      var statusEl = group.querySelector("[data-import-members-status]");

      function setStatus(texte) {
        if (statusEl) {
          statusEl.textContent = texte;
          statusEl.hidden = false;
        }
      }

      setStatus("Import en cours…");

      var reader = new FileReader();

      reader.onload = function () {
        var parsed = parseCsvSimple(reader.result);

        var idxMatricule = indexEntete(parsed.entetes, ["matricule", "agent"]);
        var idxFonction = indexEntete(parsed.entetes, ["fonction", "role"]);
        var idxStatut = indexEntete(parsed.entetes, [
          "statut",
          "categorie_personnel",
          "categorie",
        ]);
        var idxProvenance = indexEntete(parsed.entetes, ["provenance"]);

        if (idxMatricule === -1) {
          setStatus('Le fichier doit contenir une colonne "matricule".');

          return;
        }

        var total = parsed.lignes.length;
        var trouves = 0;
        var introuvables = [];

        function traiterLigne(index) {
          if (index >= parsed.lignes.length) {
            var resume = trouves + " membre(s) importé(s) sur " + total + ".";

            if (introuvables.length > 0) {
              resume +=
                " Introuvable(s) dans la liste des enseignants : " +
                introuvables.join(", ") +
                ".";
            }

            setStatus(resume);

            return;
          }

          var ligne = parsed.lignes[index];
          var matricule = ligne[idxMatricule] || "";

          if (!matricule) {
            traiterLigne(index + 1);

            return;
          }

          rechercherEnseignantParMatricule(matricule).then(function (
            enseignant,
          ) {
            addMember(group);

            var lignesMembres = group.querySelectorAll(".member-row");
            var nouvelleLigne = lignesMembres[lignesMembres.length - 1];

            if (!nouvelleLigne) {
              traiterLigne(index + 1);

              return;
            }

            var searchInput = nouvelleLigne.querySelector(
              "[data-member-search-input]",
            );
            var idInput = nouvelleLigne.querySelector("[data-member-id-input]");
            var nomInput = nouvelleLigne.querySelector("[data-member-nom]");
            var telephoneInput = nouvelleLigne.querySelector(
              "[data-member-telephone]",
            );
            var fonctionInput = nouvelleLigne.querySelector(
              "[data-member-fonction]",
            );
            var categorieInput = nouvelleLigne.querySelector(
              "[data-member-categorie]",
            );
            var provenanceInput = nouvelleLigne.querySelector(
              "[data-member-provenance]",
            );

            if (enseignant) {
              trouves++;

              var nomComplet = (
                (enseignant.prenom || "") +
                " " +
                (enseignant.nom || "")
              ).trim();

              if (searchInput) searchInput.value = nomComplet;
              if (idInput) idInput.value = enseignant.id || "";
              if (nomInput) nomInput.value = enseignant.nom || "";

              if (telephoneInput && enseignant.telephone) {
                telephoneInput.value = enseignant.telephone;
              }

              if (categorieInput && enseignant.categorie_personnel) {
                categorieInput.value = enseignant.categorie_personnel;
              }
            } else {
              introuvables.push(matricule);

              if (searchInput) searchInput.value = matricule;
              if (nomInput) nomInput.value = matricule;
            }

            if (idxFonction !== -1 && fonctionInput && ligne[idxFonction]) {
              fonctionInput.value = ligne[idxFonction];
            }

            if (idxStatut !== -1 && categorieInput && ligne[idxStatut]) {
              categorieInput.value = ligne[idxStatut].toLowerCase();
            }

            if (
              idxProvenance !== -1 &&
              provenanceInput &&
              ligne[idxProvenance]
            ) {
              provenanceInput.value = ligne[idxProvenance];
            }

            traiterLigne(index + 1);
          });
        }

        traiterLigne(0);
      };

      reader.onerror = function () {
        setStatus("Impossible de lire ce fichier.");
      };

      reader.readAsText(file, "UTF-8");
    }

    addButton.addEventListener("click", addCentre);

    updateCentres();
  }

  /*
    ============================================================
    RECHERCHE DES ENSEIGNANTS
    ============================================================
    */

  // "UN BENEFICIAIRE NE PEUT PAS ETRE CONVOQUE PLUS DE UNE FOIS" : vrai sur
  // TOUTE la convocation (tous centres/metiers confondus), pas seulement au
  // sein d'un meme groupe metier - on parcourt donc TOUS les
  // [data-member-id-input] du formulaire, pas seulement ceux du groupe en
  // cours. "hiddenInputActuel" est exclu de la comparaison pour ne pas se
  // bloquer soi-meme en reselectionnant la meme personne sur SA propre ligne.
  function estEnseignantDejaBeneficiaire(form, enseignantId, hiddenInputActuel) {
    if (!form || !enseignantId) {
      return false;
    }

    var dejaPresent = false;

    form
      .querySelectorAll("[data-member-id-input]")
      .forEach(function (autreInput) {
        if (
          autreInput !== hiddenInputActuel &&
          autreInput.value &&
          String(autreInput.value) === String(enseignantId)
        ) {
          dejaPresent = true;
        }
      });

    return dejaPresent;
  }

  // Doublons deja presents dans le DOM au moment de l'enregistrement (ex :
  // import CSV, ou pre-remplissage) - meme regle que
  // estEnseignantDejaBeneficiaire() ci-dessus, mais evaluee sur TOUT le
  // formulaire d'un coup pour bloquer la soumission (cf. handler "submit").
  function trouverBeneficiairesEnDouble(form) {
    var vus = {};
    var doublons = [];

    form
      .querySelectorAll("[data-member-id-input]")
      .forEach(function (input) {
        var id = input.value;

        if (!id) {
          return;
        }

        if (vus[id] && doublons.indexOf(id) === -1) {
          doublons.push(id);
        }

        vus[id] = true;
      });

    return doublons;
  }

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
            var memberRow = input.closest(".member-row");

            // "UN BENEFICIAIRE NE PEUT PAS ETRE CONVOQUE PLUS DE UNE FOIS" -
            // un meme enseignant ne peut pas etre ajoute deux fois comme
            // membre du jury de CETTE convocation (deja vrai en base, cf.
            // contrainte sur convocation_enseignant, mais jusqu'ici
            // silencieusement ecrase sans prevenir l'utilisatrice - cf.
            // ConvocationSyncController::sync() qui indexe "beneficiaires"
            // par enseignant_id). On bloque des la selection, avec un
            // message clair, plutot que de laisser deux lignes identiques
            // dont une disparaitra sans explication a l'enregistrement.
            if (
              memberRow &&
              estEnseignantDejaBeneficiaire(form, enseignant.id, hiddenInput)
            ) {
              if (typeof window.showToast === "function") {
                window.showToast(
                  "error",
                  '"' +
                    li.textContent +
                    '" est déjà convoqué(e) dans cette convocation. Un bénéficiaire ne peut pas être convoqué plus d\'une fois.',
                );
              }

              return;
            }

            input.value = li.textContent;

            hiddenInput.value = enseignant.id || "";

            suggestions.innerHTML = "";

            suggestions.hidden = true;

            if (memberRow) {
              // Recherche d'un membre du jury : on renseigne le nom et
              // le telephone de CETTE ligne, pas ceux du chef de centre.
              var memberNomInput = memberRow.querySelector("[data-member-nom]");

              var memberTelephoneInput = memberRow.querySelector(
                "[data-member-telephone]",
              );

              var memberCategorieInput = memberRow.querySelector(
                "[data-member-categorie]",
              );

              if (memberNomInput) {
                memberNomInput.value = nom;
              }

              if (memberTelephoneInput && enseignant.telephone) {
                memberTelephoneInput.value = enseignant.telephone;
              }

              // Pre-remplit le statut (fonctionnaire/contractuel/vacataire)
              // s'il est deja renseigne sur la fiche de l'enseignant -
              // sinon laisse "Selectionner" pour que l'utilisateur le
              // complete lui-meme.
              if (memberCategorieInput && enseignant.categorie_personnel) {
                memberCategorieInput.value = enseignant.categorie_personnel;
              }
            } else {
              // Recherche du chef de centre OU du président du jury — deux
              // champs distincts sur la même carte centre, on cible celui
              // qui correspond au conteneur de recherche utilisé.
              var centreCard = input.closest("[data-centre-card]");

              var estPresident = !!input.closest("[data-president-search]");

              var telephoneInput = centreCard
                ? centreCard.querySelector(
                    estPresident
                      ? "[data-president-telephone-input]"
                      : "[data-chef-telephone-input]",
                  )
                : null;

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

  function initPresidentSearch(centre) {
    var container = centre.querySelector("[data-president-search]");

    if (!container) {
      return;
    }

    var input = container.querySelector("[data-president-search-input]");

    var hiddenInput = container.querySelector("[data-president-id-input]");

    var suggestions = container.querySelector("[data-president-suggestions]");

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

    // Fiche "Modifier" : identique a la creation, mais chaque centre/metier
    // deja existant doit renvoyer son id reel (pour que le back sache le
    // METTRE A JOUR plutot que d'en creer un doublon) et les metiers sont
    // serialises en objets {id, metier} plutot qu'en simples chaines - cf.
    // SyncConvocationStructureRequest cote back. La creation garde l'ancien
    // format (chaines), inchange, pour ne pas casser ConvocationCentreController::store().
    var mode =
      form.getAttribute("data-wizard-mode") === "edit" ? "edit" : "create";

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

    // Un centre "papier" (ex: "LTP FXN/THIES JURY 1") peut regrouper
    // plusieurs métiers (MVM, puis FC...), chacun avec ses propres
    // membres, mais le même centre/jury/chef de centre. Une SEULE ligne
    // "centres[]" est envoyée par carte centre (centresIndex n'avance
    // qu'une fois par carte, pas par groupe métier) — chaque métier de
    // cette carte devient une entrée de "centres[i][metiers][]" (cf.
    // ConvocationCentreController::store() côté back, qui crée alors
    // PLUSIEURS lignes convocation_centre_metiers pour CE MÊME centre,
    // au lieu de dupliquer le centre lui-même comme c'était le cas avant
    // ce correctif.
    var centresIndex = 0;

    function addHidden(name, value) {
      var input = document.createElement("input");

      input.type = "hidden";

      input.name = name;

      input.value = value || "";

      input.setAttribute("data-generated-convocation", "true");

      form.appendChild(input);
    }

    centres.forEach(function (centre) {
      var centreInput = centre.querySelector('[data-field="centre"]');

      var juryInput = centre.querySelector('[data-field="jury"]');

      var chefInput = centre.querySelector('[data-field="chef_centre_id"]');

      var telephoneInput = centre.querySelector(
        '[data-field="chef_centre_telephone"]',
      );

      var presidentInput = centre.querySelector(
        '[data-field="president_jury_id"]',
      );

      var presidentTelephoneInput = centre.querySelector(
        '[data-field="president_jury_telephone"]',
      );

      var metierGroups = Array.prototype.slice.call(
        centre.querySelectorAll("[data-metier-group]"),
      );

      if (mode === "edit") {
        var centreIdInput = centre.querySelector('[data-field="id"]');

        addHidden(
          "centres[" + centresIndex + "][id]",
          centreIdInput ? centreIdInput.value : "",
        );
      }

      addHidden(
        "centres[" + centresIndex + "][centre]",
        centreInput ? centreInput.value : "",
      );

      addHidden(
        "centres[" + centresIndex + "][jury]",
        juryInput ? juryInput.value : "",
      );

      addHidden(
        "centres[" + centresIndex + "][chef_centre_id]",
        chefInput ? chefInput.value : "",
      );

      addHidden(
        "centres[" + centresIndex + "][chef_centre_telephone]",
        telephoneInput ? telephoneInput.value : "",
      );

      addHidden(
        "centres[" + centresIndex + "][president_jury_id]",
        presidentInput ? presidentInput.value : "",
      );

      addHidden(
        "centres[" + centresIndex + "][president_jury_telephone]",
        presidentTelephoneInput ? presidentTelephoneInput.value : "",
      );

      // N'avance que pour les groupes avec un NOM de métier renseigné — le
      // back (ConvocationCentreController::store()) ignore lui aussi les
      // noms vides (groupe "général", ex: président de jury sans métier),
      // donc les deux côtés restent alignés sur le même compteur pour
      // retrouver l'id réel du métier une fois créé.
      var metierPosition = 0;

      metierGroups.forEach(function (group) {
        var metierInput = group.querySelector('[data-field="metier"]');
        var metierNom = metierInput ? metierInput.value.trim() : "";
        var aUnMetier = metierNom !== "";

        if (aUnMetier) {
          if (mode === "edit") {
            var metierIdInput = group.querySelector('[data-field="id"]');

            addHidden(
              "centres[" +
                centresIndex +
                "][metiers][" +
                metierPosition +
                "][id]",
              metierIdInput ? metierIdInput.value : "",
            );

            addHidden(
              "centres[" +
                centresIndex +
                "][metiers][" +
                metierPosition +
                "][metier]",
              metierNom,
            );
          } else {
            addHidden(
              "centres[" + centresIndex + "][metiers][" + metierPosition + "]",
              metierNom,
            );
          }
        }

        var membersBody = group.querySelector("[data-members-body]");

        if (membersBody) {
          var rows = membersBody.querySelectorAll(".member-row");

          rows.forEach(function (row) {
            var memberId = row.querySelector("[data-member-id-input]");

            var fonction = row.querySelector("[data-member-fonction]");

            var provenance = row.querySelector("[data-member-provenance]");

            var categorie = row.querySelector("[data-member-categorie]");

            var nom = row.querySelector("[data-member-nom]");

            // Le back (ConvocationBeneficiaireController / pivot
            // convocation_enseignant) ne stocke que enseignant_id, fonction
            // et centre_id - il n'existe pas de colonne pour un nom saisi a
            // la main. Un membre du jury doit donc obligatoirement etre
            // selectionne dans l'autocomplete (data-member-id-input rempli)
            // pour pouvoir etre enregistre.
            if (!memberId || !memberId.value) {
              if (
                nom &&
                nom.value.trim() &&
                typeof window.showToast === "function"
              ) {
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

            addHidden(
              "beneficiaires[" + beneficiaireIndex + "][categorie_personnel]",
              categorie ? categorie.value : "",
            );

            // Rattache ce membre à SON centre ET, s'il en a un, à SON
            // métier précis au sein de ce centre (un centre peut en avoir
            // plusieurs) — cf. commentaire plus haut sur l'alignement des
            // compteurs centresIndex/metierPosition avec le back.
            addHidden(
              "beneficiaires[" + beneficiaireIndex + "][centre_index]",
              String(centresIndex),
            );

            if (aUnMetier) {
              addHidden(
                "beneficiaires[" + beneficiaireIndex + "][metier_index]",
                String(metierPosition),
              );
            }

            beneficiaireIndex++;
          });
        }

        if (aUnMetier) {
          metierPosition++;
        }
      });

      centresIndex++;
    });
  }

  /*
    ============================================================
    PRE-REMPLISSAGE (fiche "Modifier")
    "IL FAUT QUE EDIT SOIT EXACTEMENT COMME CREATE MAIS PREREMPLI" :
    plutot que de reecrire une logique d'ajout separee pour l'edition, on
    reutilise EXACTEMENT celle de la creation - on simule des clics sur les
    boutons "Ajouter un centre" / "Ajouter un groupe métier" / "Ajouter un
    membre" (deja cables par initCentres()) le nombre de fois necessaire,
    puis on remplit les champs du dernier bloc ajoute avec les donnees
    fournies par le back (window.__convocationWizardPrefill, cf.
    ConvocationsController::renderEdit() cote front).
    ============================================================
    */

  function setFieldValue(scope, selector, value) {
    var field = scope.querySelector(selector);

    if (field && value !== null && typeof value !== "undefined") {
      field.value = value;
    }
  }

  function hydrateFromPrefill() {
    var form = getWizard();

    if (!form || form.getAttribute("data-wizard-mode") !== "edit") {
      return;
    }

    var prefill = window.__convocationWizardPrefill;

    if (!Array.isArray(prefill) || prefill.length === 0) {
      return;
    }

    var addCentreButton = form.querySelector("[data-add-centre]");

    var centresContainer = form.querySelector("[data-centres-container]");

    if (!addCentreButton || !centresContainer) {
      return;
    }

    prefill.forEach(function (centreData) {
      addCentreButton.click();

      var centreCards = centresContainer.querySelectorAll(
        "[data-centre-card]",
      );

      var centreCard = centreCards[centreCards.length - 1];

      if (!centreCard) {
        return;
      }

      setFieldValue(centreCard, '[data-field="id"]', centreData.id);
      setFieldValue(centreCard, '[data-field="centre"]', centreData.centre);
      setFieldValue(centreCard, '[data-field="jury"]', centreData.jury);
      setFieldValue(
        centreCard,
        '[data-field="chef_centre_id"]',
        centreData.chef_centre_id,
      );
      setFieldValue(
        centreCard,
        '[data-field="chef_centre_telephone"]',
        centreData.chef_centre_telephone,
      );
      setFieldValue(
        centreCard,
        '[data-field="president_jury_id"]',
        centreData.president_jury_id,
      );
      setFieldValue(
        centreCard,
        '[data-field="president_jury_telephone"]',
        centreData.president_jury_telephone,
      );

      // Le champ visible (texte) du widget de recherche n'est pas
      // "data-field" (seul son id cache l'est) - sans ca le nom du chef de
      // centre / president du jury deja selectionne n'apparaitrait pas.
      var chefSearchInput = centreCard.querySelector(
        "[data-chef-search-input]",
      );

      if (chefSearchInput && centreData.chef_centre_nom) {
        chefSearchInput.value = centreData.chef_centre_nom;
      }

      var presidentSearchInput = centreCard.querySelector(
        "[data-president-search-input]",
      );

      if (presidentSearchInput && centreData.president_jury_nom) {
        presidentSearchInput.value = centreData.president_jury_nom;
      }

      // addCentre() ajoute toujours un premier groupe metier "vide" par
      // defaut (pour coller au papier) - on le retire, la liste reelle des
      // groupes vient entierement de centreData.metiers ci-dessous.
      var metiersContainer = centreCard.querySelector(
        "[data-metiers-container]",
      );

      var metiersEmpty = centreCard.querySelector("[data-metiers-empty]");

      if (metiersContainer) {
        // innerHTML = "" contourne le toggle habituel de
        // updateMetierGroups() (qui tourne uniquement lors d'un ajout/
        // suppression via les boutons) - on remet nous-memes le message
        // "vide" en visible en attendant les groupes reels ci-dessous.
        metiersContainer.innerHTML = "";

        if (metiersEmpty) {
          metiersEmpty.hidden = false;
        }
      }

      var addMetierGroupButton = centreCard.querySelector(
        "[data-add-metier-group]",
      );

      var metiers = Array.isArray(centreData.metiers)
        ? centreData.metiers
        : [];

      metiers.forEach(function (metierData) {
        if (!addMetierGroupButton) {
          return;
        }

        addMetierGroupButton.click();

        var groupNodes = metiersContainer
          ? metiersContainer.querySelectorAll("[data-metier-group]")
          : [];

        var groupNode = groupNodes[groupNodes.length - 1];

        if (!groupNode) {
          return;
        }

        setFieldValue(groupNode, '[data-field="id"]', metierData.id);
        setFieldValue(groupNode, '[data-field="metier"]', metierData.metier);

        var membersBody = groupNode.querySelector("[data-members-body]");

        var addMemberButton = groupNode.querySelector("[data-add-member]");

        var membres = Array.isArray(metierData.membres)
          ? metierData.membres
          : [];

        membres.forEach(function (membre) {
          if (!addMemberButton) {
            return;
          }

          addMemberButton.click();

          var rows = membersBody
            ? membersBody.querySelectorAll(".member-row")
            : [];

          var row = rows[rows.length - 1];

          if (!row) {
            return;
          }

          var searchInput = row.querySelector("[data-member-search-input]");
          var idInput = row.querySelector("[data-member-id-input]");
          var nomInput = row.querySelector("[data-member-nom]");
          var fonctionInput = row.querySelector("[data-member-fonction]");
          var categorieInput = row.querySelector("[data-member-categorie]");
          var provenanceInput = row.querySelector(
            "[data-member-provenance]",
          );
          var telephoneInput = row.querySelector(
            "[data-member-telephone]",
          );

          var nomComplet = (
            (membre.prenom || "") +
            " " +
            (membre.nom || "")
          ).trim();

          if (searchInput) searchInput.value = nomComplet;
          if (idInput) idInput.value = membre.enseignant_id || "";
          if (nomInput) nomInput.value = membre.nom || "";
          if (fonctionInput) fonctionInput.value = membre.fonction || "";

          if (categorieInput) {
            categorieInput.value = membre.categorie_personnel || "";
          }

          if (provenanceInput) {
            provenanceInput.value = membre.provenance || "";
          }

          if (telephoneInput) {
            telephoneInput.value = membre.telephone || "";
          }
        });
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

    hydrateFromPrefill();

    // Verrou explicite (pas seulement "disabled" sur le bouton) : bloque
    // tout second envoi meme si le style "disabled" n'a pas eu le temps de
    // s'afficher (double-clic tres rapide, navigateur lent a repeindre) -
    // le premier "submit" qui passe toutes les validations pose le verrou,
    // tout "submit" suivant tant qu'il est pose est annule avant meme de
    // repartir dans les validations.
    var envoiEnCours = false;

    form.addEventListener("submit", function (event) {
      if (envoiEnCours) {
        event.preventDefault();

        return;
      }

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

      // Chaque convocation doit avoir au moins un centre (le back le
      // refuse de toute facon — StoreConvocationCentresRequest — mais on
      // evite l'aller-retour serveur en le signalant tout de suite).
      var centresContainer = form.querySelector("[data-centres-container]");

      var nbCentres = centresContainer
        ? centresContainer.querySelectorAll("[data-centre-card]").length
        : 0;

      if (nbCentres === 0) {
        event.preventDefault();

        currentStep = 2;
        updateProgressBar();

        if (typeof window.showToast === "function") {
          window.showToast(
            "error",
            "Ajoutez au moins un centre d'examen avant d'enregistrer la convocation.",
          );
        }

        return;
      }

      // "UN BENEFICIAIRE NE PEUT PAS ETRE CONVOQUE PLUS DE UNE FOIS" :
      // dernier filet avant l'envoi - couvre aussi les doublons qui
      // n'auraient pas pu etre bloques a la selection (import CSV,
      // pre-remplissage de la fiche "Modifier"...).
      if (trouverBeneficiairesEnDouble(form).length > 0) {
        event.preventDefault();

        currentStep = 2;
        updateProgressBar();

        if (typeof window.showToast === "function") {
          window.showToast(
            "error",
            "Un(e) même bénéficiaire est ajouté(e) plusieurs fois dans cette convocation. Retirez les doublons avant d'enregistrer : un bénéficiaire ne peut pas être convoqué plus d'une fois.",
          );
        }

        return;
      }

      prepareFormData();

      envoiEnCours = true;

      var submitButton = form.querySelector("[data-wizard-submit]");

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.setAttribute("aria-busy", "true");
        submitButton.dataset.labelOriginal = submitButton.innerHTML;
        submitButton.innerHTML =
          '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enregistrement en cours…';
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
