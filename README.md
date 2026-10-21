# SICORE Frontend — Laravel 12

Frontend Laravel de **SICORE — Système Intégré des COrps Émergents**.

Cette application utilise le backend SICORE pour l'authentification et les
données métier, notamment celles de la gestion de la paie.

## Préparation après un clone ou un changement de branche

```powershell
git fetch origin --prune
git switch dev-bayesaliou
git pull --ff-only origin dev-bayesaliou
composer install
```

Créer `.env` depuis `.env.example` uniquement s'il n'existe pas. Vérifier que
`API_BASE_URL` désigne le backend réellement démarré. Les identifiants sont ceux
créés par les seeders du backend.

## Prérequis

- PHP 8.4.1 ou supérieur (PHP 8.4.12 est recommandé avec Laragon).
- Composer 2, seulement si le dossier `vendor` doit être réinstallé.
- Backend SICORE et MySQL démarrés.
- Extensions PHP habituelles de Laravel : `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `ctype`, `dom`, `xml` et `xmlwriter`.
- Aucun lancement npm ou Vite n’est nécessaire : les assets sont déjà présents dans `public/assets`.

## Démarrage sous Windows

Dans PowerShell, depuis la racine du projet :

```powershell
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8001
```

Puis ouvrir :

```text
http://127.0.0.1:8001
```

Le script suivant peut aussi être utilisé :

```powershell
.\demarrer-sicore.ps1
```

## Architecture frontend

```text
app/
├── Http/Controllers/AuthController.php
├── Http/Middleware/EnsureSicoreAuthenticated.php
└── View/Components/ModulePage.php

config/
├── navigation.php
├── module-pages.php
└── sicore.php

resources/views/
├── components/
│   ├── sidebar.blade.php
│   ├── topbar.blade.php
│   ├── module-page.blade.php
│   └── flash-messages.blade.php
├── layouts/
│   ├── base.blade.php
│   ├── app.blade.php
│   └── guest.blade.php
└── pages/
```

## Pages du sidebar

Les pages suivantes sont rendues directement par Blade/Laravel :

- Tableau de bord.
- Gestion des enseignants et formulaire progressif.
- Gestion de la paie : présences, avances et retenues Tabaski, rappels, exemptions, travaux périodiques, banques, cotisations, salaires, bulletins et états.
- Crédits : délégations et engagements.
- Indemnités : convocations, services faits, justificatifs, accusés, calculs, déplacements et états de paie.
- Bourses et aides.
- Paramétrage et IEF.
- Utilisateurs, profils, rôles et permissions.

Les 33 pages auparavant construites par JavaScript sont maintenant générées côté serveur avec le composant :

```text
resources/views/components/module-page.blade.php
```

Leurs données d’affichage sont centralisées dans :

```text
config/module-pages.php
```

Le sidebar est défini dans :

```text
resources/views/components/sidebar.blade.php
```

Sa navigation est centralisée dans :

```text
config/navigation.php
```

## Vérifications

```powershell
php artisan route:list
php artisan test
```

## Connexion API

L'authentification et les pages de paie appellent l'URL définie par
`API_BASE_URL` (par défaut `http://127.0.0.1:8000/api`). Après un `git pull`,
exécuter `php artisan optimize:clear`, redémarrer le serveur qui écoute sur le
port `8001`, puis actualiser le navigateur. Les assets locaux portent une
version calculée automatiquement afin d'éviter l'ancien CSS conservé en cache.
