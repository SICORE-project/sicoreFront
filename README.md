# SICORE Frontend — Laravel 12 — Mode test

Frontend Laravel de **SICORE — Système Intégré des COrps Émergents**.

Cette version fonctionne de manière autonome, sans base de données et sans backend API. Elle permet de tester la connexion, le dashboard, le sidebar et toutes les pages fonctionnelles du prototype dans Laravel.

## Accès au mode test

```text
E-mail : admin@sicore.sn
Mot de passe : Sicore@2026
```

Après validation, Laravel crée une session locale et redirige uniquement vers le tableau de bord. L’authentification par API sera intégrée plus tard dans le projet backend SICORE.

## Prérequis

- PHP 8.2 ou supérieur ; compatible avec PHP 8.2.12.
- Composer 2, seulement si le dossier `vendor` doit être réinstallé.
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

## Connexion API future

Aucune authentification API n’est active dans cette version. Lors de la création du backend SICORE, il faudra remplacer uniquement l’authentification locale du fichier `AuthController.php` par l’appel au backend et conserver les layouts, composants et pages Blade déjà en place.

## Workflow Git — se synchroniser avec `module-indemnite-intermedaire`

Récupérer les derniers changements de la branche partagée `module-indemnite-intermedaire` :

```
git fetch origin module-indemnite-intermedaire
git merge origin/module-indemnite-intermedaire
```

S'il y a des modifications locales non committées qui bloquent le merge :

```
git stash push -u -m "avant fusion module-indemnite-intermedaire"
git fetch origin module-indemnite-intermedaire
git merge origin/module-indemnite-intermedaire
```
Puis, une fois le merge vérifié, remettre les modifications de côté si besoin (`git stash pop`) ou les jeter si ce n'était pas du vrai travail (`git stash drop`).

Committer et pousser uniquement les fichiers réellement modifiés (jamais `git add .`, pour éviter d'envoyer du bruit type changement de fin de ligne sur des fichiers non concernés) :

```
git add <fichier1> <fichier2> ...
git commit -m "message clair"
git fetch origin module-indemnite-intermedaire
git merge origin/module-indemnite-intermedaire
git push origin dev-aminata
```

Si un `.git/index.lock` bloque toutes les commandes git (message "Unable to create '.git/index.lock': File exists") :

1. Fermer tout logiciel git ouvert (VS Code, GitHub Desktop, etc.).
2. Supprimer le fichier :
```
rm -f .git/index.lock
```
3. Relancer la commande git.
