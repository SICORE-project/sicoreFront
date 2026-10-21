# Guide débutant du code frontend SICORE

Ce guide permet à une personne qui découvre Laravel de savoir par où commencer,
comment retrouver une page et dans quel fichier effectuer une modification.

## 1. Règle principale

Une page SICORE est généralement construite en trois niveaux :

```text
Layout général
    ↓
Composants partagés
    ↓
Vue propre à la page
```

Exemple :

```text
resources/views/layouts/base.blade.php
    ↓
resources/views/layouts/app.blade.php
    ↓ sidebar.blade.php + topbar.blade.php
    ↓
resources/views/pages/paie/bulletins.blade.php
    ↓
resources/views/components/module-page.blade.php
```

Les fichiers Blade courts sont donc normaux : ils réutilisent une structure
commune au lieu de la recopier.

## 2. Ordre conseillé pour comprendre une page

1. ouvrir `routes/web.php` et rechercher l'URL ;
2. noter le contrôleur ou le chemin de vue ;
3. ouvrir la vue dans `resources/views/pages/` ;
4. lire le commentaire placé en tête de cette vue ;
5. suivre les chemins indiqués vers le composant, le JavaScript ou le backend ;
6. rechercher le slug avec `Ctrl+Shift+F` si la page utilise `<x-module-page>`.

## 3. Répertoires importants

| Dossier | Contenu |
|---|---|
| `app/Contracts/` | contrats stables utilisés par les contrôleurs |
| `app/Http/Controllers/` | reçoit les requêtes du navigateur |
| `app/Http/Requests/` | valide les formulaires avant leur traitement |
| `app/Http/Middleware/` | contrôle l'accès avant le contrôleur |
| `app/Helpers/` | petites fonctions communes comme le format FCFA |
| `app/Providers/` | relie les interfaces à leurs implémentations |
| `app/Services/` | communique avec le backend SICORE |
| `app/View/Components/` | prépare les composants Blade |
| `config/` | menu, pages, formulaires et URL de l'API |
| `resources/views/layouts/` | structure HTML générale |
| `resources/views/components/` | blocs réutilisables |
| `resources/views/pages/` | point d'entrée visuel de chaque page |
| `public/assets/js/` | interactions exécutées dans le navigateur |
| `public/assets/css/` | design et responsive |
| `routes/web.php` | URL accessibles dans le frontend |
| `tests/Feature/` | contrôles automatiques du comportement |

## 4. Modules et vues

| Module | Dossier des vues | Configuration ou logique principale |
|---|---|---|
| Authentification | `pages/auth/` | `AuthController.php` et `SicoreApi.php` |
| Tableau de bord | `pages/dashboard/` | `charts.js` |
| Enseignants | `pages/enseignants/` | `form-wizard.js` et `education-structures.js` |
| Gestion de la paie | `pages/paie/` | `PayrollController`, `payroll.js` et backend Paie |
| Crédits | `pages/credits/` | `config/module-pages.php` |
| Indemnités | `pages/indemnites/` | `config/module-pages.php` |
| Bourses | `pages/bourses/` | `config/module-pages.php` |
| Paramétrage | `pages/parametres/` | vues locales et `app.js` |
| Utilisateurs | `pages/administration/` | `config/module-pages.php` |

## 5. Comprendre les layouts

### `layouts/base.blade.php`

Il contient `<html>`, `<head>`, les feuilles CSS et les scripts communs. Il ne
connaît pas le menu ni la page courante.

### `layouts/app.blade.php`

Il est utilisé après connexion. Il ajoute la sidebar, les messages serveur et
la zone dans laquelle une page place son contenu.

### `layouts/guest.blade.php`

Il est utilisé sans connexion, actuellement pour la page de login.

## 6. Comprendre les composants

### `sidebar.blade.php`

Construit le menu avec `config/navigation.php`. Le lien actif est déterminé
avec le nom de route courant. `app.js` mémorise ensuite le défilement du menu.

### `topbar.blade.php`

Affiche le titre, le sous-titre, l'icône et éventuellement une recherche.

### `module-page.blade.php`

Affiche une page fonctionnelle complète : objectifs, statistiques, actions,
filtres, recherche IA/IEF/matricule, tableau et modale.

Sa classe PHP est `app/View/Components/ModulePage.php`. Elle fusionne :

- les textes de `config/module-pages.php` ;
- les données de l'API pour la Gestion de la paie ;
- les formulaires de `config/payroll-forms.php`.

## 7. Communication frontend/backend

Le frontend utilise une couche BFF : le navigateur ne reçoit jamais directement
le jeton Sanctum.

```text
Navigateur
  → routes/web.php
  → PayrollController.php ou AuthController.php
  → SicoreApiClientInterface.php
  → SicoreApi.php
  → sicoreBack/routes/api.php
```

`SicoreApiClientInterface.php` déclare les opérations disponibles.
`ApiClientServiceProvider.php` fournit ensuite `SicoreApi.php`, qui centralise
l'adresse du backend, les délais, le jeton Bearer et les erreurs. Son adresse
vient de `SICORE_API_URL` dans `.env`.

Cette organisation applique la logique du TP Laravel joint. La correspondance
complète est documentée dans `../docs/ADAPTATION-TP-LARAVEL.md`.

## 8. Scripts JavaScript

| Script | Responsabilité |
|---|---|
| `app.js` | sidebar, lien actif, recherche, confirmations et interactions communes |
| `charts.js` | graphiques Canvas du dashboard |
| `education-structures.js` | listes IA/IEF des pages Enseignants de présentation |
| `form-wizard.js` | assistant en trois étapes du formulaire Enseignant |
| `notifications.js` | messages temporaires accessibles |
| `payroll.js` | formulaires, recherche et actions du module Paie |

Tous ces fichiers utilisent une fonction englobante :

```javascript
(function () {
  "use strict";
  // Le code reste isolé et ne crée pas de variables globales involontaires.
})();
```

## 9. Feuilles de style

- `style.css` : thème global et page de connexion ;
- `app.css` : pages connectées, sidebar, tableaux, Paie et bulletin ;
- `responsive.css` : adaptations selon la largeur de l'écran.

Pour retrouver un style, chercher directement la classe HTML. Exemple :

```text
Ctrl+Shift+F → payslip-document
Ctrl+Shift+F → payroll-live-filter
Ctrl+Shift+F → sidebar-link
```

## 10. Où modifier selon le besoin

| Besoin | Premier fichier à ouvrir |
|---|---|
| Ajouter une URL | `routes/web.php` |
| Ajouter un lien dans le menu | `config/navigation.php` |
| Changer toutes les pages connectées | `layouts/app.blade.php` |
| Changer toutes les pages fonctionnelles | `components/module-page.blade.php` |
| Changer un titre ou objectif | `config/module-pages.php` |
| Changer un formulaire Paie | `config/payroll-forms.php` puis validation backend |
| Changer la recherche Paie | `public/assets/js/payroll.js` |
| Changer la sidebar | `sidebar.blade.php`, `app.js` et `app.css` |
| Changer le bulletin | `pages/paie/payslip.blade.php` et styles `payslip-*` |
| Changer un calcul de paie | backend `PayrollCalculationService.php` uniquement |

## 11. Commenter correctement une future modification

Un commentaire utile explique **pourquoi**, la responsabilité et le fichier
associé. Il ne répète pas simplement le code.

Bon exemple :

```php
// La clé d'idempotence empêche un double calcul après un double clic.
$key = Str::uuid();
```

Pour une nouvelle vue Blade, placer en tête :

```blade
{{--
  PAGE : nom et URL.
  Route/contrôleur : chemin.
  Composants inclus : chemins.
  Données ou backend : chemin.
  Scripts et styles particuliers : chemins.
--}}
```

## 12. Fichiers à ne pas modifier directement

- `vendor/` : dépendances recréées par `composer install` ;
- `storage/framework/views/` : vues compilées automatiquement ;
- `bootstrap/cache/` : cache Laravel ;
- `.env` dans un commit : configuration locale ;
- montants financiers dans Blade ou JavaScript : ils viennent du backend.

## 13. Vérification après modification

Depuis la racine du dépôt :

```powershell
.\tester-module-paie.ps1
```

Pour le frontend uniquement :

```powershell
cd sicore-front
php artisan test
node --check public/assets/js/app.js
node --check public/assets/js/payroll.js
php artisan view:cache
php artisan view:clear
```

La cartographie détaillée de la Paie se trouve dans
`../docs/CARTOGRAPHIE-MODULE-PAIE.md`.
