/**
 * Filtrage SANS aucun rechargement de page (module Indemnites).
 *
 * Demande utilisatrice (reiteree) : "je ne veux pas que les filtres
 * rechargent la page... revoit toutes les vues de sorte que la page ne se
 * recharge pas". Le cascade AJAX des OPTIONS de select (voir
 * indemnites-filtres-coherents.js) ne suffisait pas : il fallait aussi que
 * le tableau de resultats, les cartes de stats, la pagination et le
 * message "aucune donnee" se mettent a jour sans navigation.
 *
 * Principe (aucune route/endpoint JSON dediee necessaire cote back) :
 * chaque page marque les zones qui dependent du filtre avec
 * `data-ajax-region="nom"`. Au changement d'un select, a la soumission du
 * formulaire (bouton "Filtrer"), ou au clic sur un lien marque
 * `data-ajax-lien` (pagination, "Reinitialiser"), ce script recupere en
 * fetch() le HTML de la MEME route (celle que la navigation normale aurait
 * chargee), y retrouve les memes zones marquees, et remplace UNIQUEMENT
 * leur contenu dans la page actuelle — sans jamais naviguer. L'URL du
 * navigateur est mise a jour via history.pushState() (rechargement/
 * partage de lien/bouton "Precedent" restent corrects).
 *
 * Un evenement "sicore:ajax-regions-mises-a-jour" est declenche apres
 * chaque remplacement pour que le JS specifique a une page (ouverture de
 * modales, etat de la case "tout selectionner"... generalement lie une
 * seule fois au chargement) puisse re-attacher ce qui doit l'etre sur le
 * nouveau contenu injecte.
 *
 * Degradation gracieuse : si fetch echoue (reseau coupe), navigation
 * normale vers l'URL cible — rien n'est jamais bloque.
 */
(function () {
    "use strict";

    function regionsDe(racine) {
        var carte = {};

        racine.querySelectorAll("[data-ajax-region]").forEach(function (element) {
            carte[element.getAttribute("data-ajax-region")] = element;
        });

        return carte;
    }

    function remplacerRegions(url) {
        return fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(function (reponse) {
                if (!reponse.ok) {
                    throw new Error("HTTP " + reponse.status);
                }

                return reponse.text();
            })
            .then(function (html) {
                var docDistant = new DOMParser().parseFromString(html, "text/html");
                var regionsActuelles = regionsDe(document);
                var regionsDistantes = regionsDe(docDistant);

                Object.keys(regionsActuelles).forEach(function (nom) {
                    if (!regionsDistantes[nom]) {
                        return;
                    }

                    regionsActuelles[nom].innerHTML = regionsDistantes[nom].innerHTML;
                    // Reporte aussi les classes (ex: .empty-message.show
                    // qui bascule l'affichage selon qu'il y a des lignes).
                    regionsActuelles[nom].className = regionsDistantes[nom].className;
                });

                window.history.pushState({ sicoreAjax: true }, "", url);

                // Meme mecanisme de re-enrichissement que le reste de
                // l'application (icones, etc. — voir app.js) apres une
                // injection de contenu dynamique.
                if (window.SICOREApp && typeof window.SICOREApp.refresh === "function") {
                    window.SICOREApp.refresh();
                }

                document.dispatchEvent(new CustomEvent("sicore:ajax-regions-mises-a-jour"));
            });
    }

    function naviguerVersUrl(url) {
        window.location.href = url;
    }

    function urlDepuisFormulaire(form) {
        var params = new URLSearchParams(new FormData(form));
        var action = form.getAttribute("action") || window.location.pathname;
        var requete = params.toString();

        return requete ? action + "?" + requete : action;
    }

    document.querySelectorAll("[data-filtres-instantanes]").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            var url = urlDepuisFormulaire(form);

            remplacerRegions(url).catch(function () { naviguerVersUrl(url); });
        });

        form.querySelectorAll("select, input").forEach(function (champ) {
            champ.addEventListener("change", function () {
                var url = urlDepuisFormulaire(form);

                remplacerRegions(url).catch(function () { naviguerVersUrl(url); });
            });
        });
    });

    // Pagination + "Reinitialiser" : delegation sur le document, car ces
    // liens sont recrees a chaque remplacement de region (data-ajax-region)
    // — un binding direct serait perdu des le premier changement de page.
    document.addEventListener("click", function (event) {
        var lien = event.target.closest("[data-ajax-lien]");

        if (!lien) {
            return;
        }

        var url = lien.getAttribute("href");

        if (!url || url === "#") {
            return;
        }

        event.preventDefault();

        remplacerRegions(url).catch(function () { naviguerVersUrl(url); });
    });

    // Bouton/touche "Precedent" du navigateur : revient a l'etat AJAX
    // precedent, toujours sans recharger.
    window.addEventListener("popstate", function () {
        remplacerRegions(window.location.href).catch(function () {
            window.location.reload();
        });
    });
})();
