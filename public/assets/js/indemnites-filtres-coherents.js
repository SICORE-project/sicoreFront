/**
 * Filtres coherents (Session/Objet/Metier/Centre/Date) du module Indemnites.
 *
 * Demande utilisatrice : "quand je selectionne un element du select, le
 * select suivant doit afficher les infos relatif a l'element selectionne,
 * instantanement sans recharger la page (comme la page Etats de paie)".
 *
 * Marquer un <form> avec `data-filtres-coherents="<url des options>"` : au
 * changement de n'importe quel <select name="date|objet|metier|centre|session">
 * a l'interieur, une requete AJAX recupere les options scopees (cote back,
 * voir ConvocationsController::optionsFiltres()) et reconstruit les AUTRES
 * selects avec ces valeurs. Le select qu'on vient de changer n'est jamais
 * reconstruit lui-meme (sa propre valeur reste toujours proposee par le
 * back, cf. le parametre "exclure" server-side) — le reconstruire aurait pu
 * fermer/perturber le menu que l'utilisatrice vient d'ouvrir.
 *
 * Ce script ne s'occupe QUE des options des selects. Le rafraichissement du
 * tableau de resultats lui-meme (sans recharger la page non plus, demande
 * utilisatrice reiteree : "je ne veux pas que les filtres rechargent la
 * page") est gere separement par indemnites-ajax-resultats.js, sur les
 * memes formulaires (marques `data-filtres-instantanes`) — les deux
 * scripts ecoutent le meme evenement "change" independamment, sans se
 * marcher dessus.
 */
(function () {
    "use strict";

    var CLE_PAR_CHAMP = {
        date: "dates",
        objet: "objets",
        metier: "metiers",
        centre: "centres",
        session: "sessions",
    };

    function formatDateFr(iso) {
        var parties = String(iso).split("-");

        if (parties.length !== 3) {
            return iso;
        }

        return parties[2] + "/" + parties[1] + "/" + parties[0];
    }

    function libelleOption(nomChamp, valeur) {
        return nomChamp === "date" ? formatDateFr(valeur) : valeur;
    }

    function reconstruireSelect(select, valeurs) {
        var valeurActuelle = select.value;
        var placeholder = select.options.length ? select.options[0] : null;

        select.innerHTML = "";

        if (placeholder) {
            select.appendChild(placeholder);
        }

        valeurs.forEach(function (valeur) {
            var option = document.createElement("option");
            option.value = valeur;
            option.textContent = libelleOption(select.name, valeur);
            select.appendChild(option);
        });

        if (valeurActuelle && valeurs.indexOf(valeurActuelle) !== -1) {
            select.value = valeurActuelle;
        }
    }

    document.querySelectorAll("[data-filtres-coherents]").forEach(function (form) {
        var url = form.getAttribute("data-filtres-coherents");

        if (!url) {
            return;
        }

        var champs = Array.prototype.filter.call(
            form.querySelectorAll("select[name]"),
            function (select) {
                return CLE_PAR_CHAMP.hasOwnProperty(select.name);
            }
        );

        if (!champs.length) {
            return;
        }

        function rafraichirLesAutres(champModifie) {
            var params = new URLSearchParams();

            champs.forEach(function (select) {
                if (select.value) {
                    params.set(select.name, select.value);
                }
            });

            fetch(url + "?" + params.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            })
                .then(function (reponse) { return reponse.json(); })
                .then(function (payload) {
                    if (!payload.success) {
                        return;
                    }

                    champs.forEach(function (select) {
                        if (select === champModifie) {
                            return;
                        }

                        var cle = CLE_PAR_CHAMP[select.name];
                        var valeurs = (payload.data && payload.data[cle]) || [];
                        reconstruireSelect(select, valeurs);
                    });
                })
                .catch(function () {});
        }

        champs.forEach(function (select) {
            select.addEventListener("change", function () {
                rafraichirLesAutres(select);
            });
        });
    });
})();
