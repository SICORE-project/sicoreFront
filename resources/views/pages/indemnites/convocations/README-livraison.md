# Blades de convocation — où placer chaque fichier

resources/views/pdf/convocation.blade.php          <- convocation.blade.php
resources/views/pdf/convocation/_styles.blade.php   <- _styles.blade.php
resources/views/pdf/convocation/_entete.blade.php   <- _entete.blade.php
resources/views/pdf/convocation/_jury_examen.blade.php <- _jury_examen.blade.php
resources/views/pdf/convocation/_generique.blade.php   <- _generique.blade.php

app/Http/Controllers/Api/Indemnites/ConvocationPdfController.php
    -> remplace le fichier existant (ajoute l'eager loading nécessaire
       aux blades : typeConvocation, centres.enseignants.lieuService,
       centres.chefCentre, enseignants.lieuService)

database/migrations/2026_08_12_091000_change_objet_to_text_on_convocations_table.php
    -> à faire tourner APRÈS les migrations de la proposition précédente
       (type_convocation, categorie_personnel...)

## Ce qui manquait avant cette livraison

Le contrôleur référençait déjà `Pdf::loadView('pdf.convocation', ...)`
mais AUCUN fichier blade n'existait dans le repo — la génération de PDF
plantait donc (ViewNotFoundException) dès qu'on l'appelait, indépendamment
du typage de convocation.

## Dépendances

Ces blades supposent que les migrations `type_convocation` de la
proposition précédente sont appliquées (`typeConvocation()` sur le modèle
Convocations). Sans elles, retire simplement le `@if` d'aiguillage dans
`convocation.blade.php` et garde uniquement `_jury_examen` ou `_generique`
en dur, selon ton besoin immédiat.

## Hypothèse à valider sur la structure des données

Pour que `_jury_examen.blade.php` reproduise fidèlement formconf.jpeg
(plusieurs blocs "métier" dans un même centre, ex: MVM puis FC), il faut
créer PLUSIEURS lignes `convocation_centres` partageant le même
`centre`/`jury` mais un `metier` différent — le blade les regroupe
visuellement et n'imprime l'en-tête "CENTRE" qu'une fois. Si ton
formulaire front ne permet actuellement qu'un centre = une ligne = un
métier, c'est à ajuster côté saisie, pas côté blade.

## Gap non traité ici

Les dates d'examen ("du 04 au 09 Août 2025") et l'heure ("8 heures
précises") ne sont stockées dans aucune colonne dédiée — seulement dans
le texte libre `objet` (élargi en TEXT par la migration jointe). Si tu
veux les afficher/filtrer indépendamment du texte libre plus tard, il
faudra ajouter des colonnes dédiées (date_debut_examen, date_fin_examen,
heure_convocation) sur `convocations`.
