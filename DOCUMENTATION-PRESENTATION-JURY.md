# SICORE
## Documentation du travail réalisé

**Projet :** Système Intégré des COrps Émergents  
**Technologie principale :** Laravel 12 et PHP 8.4  
**Type de travail :** développement frontend connecté à une API backend  
**Version :** août 2026

---

## 1. Présentation du projet

SICORE est une application web destinée à centraliser la gestion administrative et financière des enseignants du Ministère de l'Emploi et de la Formation Professionnelle et Technique.

Le travail réalisé porte principalement sur le frontend Laravel. L'application fournit une interface organisée par modules, communique avec le backend par des services API et applique les droits d'accès selon le rôle et la structure de l'utilisateur.

L'objectif était de construire une base fonctionnelle, maintenable et prête pour les différents parcours métier : administration, paramétrage, enseignants, paie, crédits et indemnités.

## 2. Travail réalisé

### 2.1 Mise en place de l'application Laravel

J'ai mis en place la structure du projet Laravel :

- organisation des contrôleurs, services, middleware et composants Blade ;
- définition des routes principales et des routes par module ;
- création des layouts communs pour les pages invitées et les pages connectées ;
- création d'un menu latéral et d'une barre supérieure réutilisables ;
- intégration des fichiers CSS, JavaScript, images et icônes ;
- centralisation de la navigation et des données d'affichage des modules.

Les principaux emplacements concernés sont :

- `app/Http/Controllers/` ;
- `app/Http/Middleware/` ;
- `app/Services/Api/` ;
- `app/Services/Organisation/` ;
- `app/View/Components/` ;
- `config/navigation.php` ;
- `config/module-pages.php` ;
- `resources/views/` ;
- `routes/modules/`.

### 2.2 Authentification et gestion de session

J'ai développé le parcours de connexion avec appel au service API. Après une connexion réussie, l'application :

1. valide l'adresse e-mail et le mot de passe ;
2. envoie les identifiants au backend via `AuthService` et `ApiClient` ;
3. récupère le jeton d'accès et les informations de l'utilisateur ;
4. régénère la session Laravel ;
5. enregistre le jeton, le rôle et les informations organisationnelles ;
6. redirige l'utilisateur vers le tableau de bord.

La déconnexion appelle le backend lorsque cela est possible, puis invalide toujours la session locale et régénère le token CSRF.

Fichiers principaux :

- `app/Http/Controllers/AuthController.php` ;
- `app/Services/Api/AuthService.php` ;
- `app/Services/Api/ApiClient.php` ;
- `resources/views/pages/auth/login.blade.php`.

### 2.3 Réinitialisation du mot de passe par OTP

J'ai réalisé un parcours de réinitialisation du mot de passe en trois étapes :

1. saisie de l'adresse e-mail ;
2. envoi et vérification d'un code OTP à six chiffres ;
3. saisie et confirmation du nouveau mot de passe.

Le code peut être renvoyé et les routes sont protégées par une limitation du nombre de tentatives. Les informations temporaires nécessaires au parcours sont conservées dans la session et les appels sont envoyés au backend API.

Fichiers concernés :

- `AuthController.php` ;
- `routes/web.php` ;
- `resources/views/pages/auth/forgot-password.blade.php` ;
- `resources/views/pages/auth/verify-otp.blade.php` ;
- `resources/views/pages/auth/reset-password.blade.php`.

### 2.4 Tableau de bord

J'ai intégré un tableau de bord qui affiche :

- les indicateurs principaux ;
- le nombre d'utilisateurs, enseignants et dossiers ;
- les alertes ;
- le taux de comptes actifs ;
- des graphiques de synthèse ;
- le périmètre organisationnel de l'utilisateur connecté.

La vue est adaptée au profil de l'utilisateur. Un administrateur global et un gestionnaire IA ou IEF ne voient pas nécessairement les mêmes indicateurs.

Fichiers principaux :

- `app/Http/Controllers/DashboardController.php` ;
- `resources/views/pages/dashboard/index.blade.php` ;
- `resources/views/components/topbar.blade.php`.

### 2.5 Administration des utilisateurs, rôles et permissions

J'ai développé les écrans et les routes d'administration pour :

- consulter les utilisateurs ;
- rechercher et filtrer les utilisateurs ;
- créer un utilisateur ;
- modifier ses informations ;
- activer ou désactiver un compte ;
- supprimer un utilisateur ;
- gérer les profils et rôles ;
- consulter et gérer les permissions ;
- synchroniser les permissions avec le backend ;
- associer un utilisateur à une structure nationale, une IA ou une IEF.

Les formulaires utilisent des validations et affichent les erreurs renvoyées par l'API. Les informations réelles sont chargées au moyen des services backend.

Fichiers principaux :

- `app/Http/Controllers/Admin/UserController.php` ;
- `app/Http/Controllers/Admin/RoleController.php` ;
- `app/Http/Controllers/Admin/PermissionController.php` ;
- `app/Http/Controllers/Admin/TypeRoleController.php` ;
- `resources/views/pages/administration/` ;
- `routes/modules/administration.php`.

### 2.6 Gestion des enseignants

J'ai réalisé :

- le tableau de bord des enseignants ;
- l'accès au dossier d'un enseignant ;
- le formulaire progressif de création d'un enseignant ;
- la sélection d'une IA puis d'une IEF rattachée ;
- la saisie des informations personnelles, du contact et de l'affectation ;
- l'association d'une ou plusieurs disciplines ;
- la définition d'une discipline principale ;
- le chargement des disciplines actives disponibles.

Le formulaire de création est organisé en trois étapes : informations de l'enseignant, contact, puis profession et affectation.

Fichiers principaux :

- `resources/views/pages/enseignants/index.blade.php` ;
- `resources/views/pages/enseignants/create.blade.php` ;
- `resources/views/pages/enseignants/show.blade.php` ;
- `app/Http/Controllers/Parametrage/EnseignantDisciplineController.php` ;
- `app/Services/Parametrage/EnseignantDisciplineService.php` ;
- `public/assets/js/form-wizard.js` ;
- `public/assets/js/education-structures.js`.

### 2.7 Module de paramétrage

J'ai développé les écrans de gestion des référentiels utilisés par l'application :

- inspections d'académie IA ;
- inspections de l'éducation et de la formation IEF ;
- corps enseignants ;
- catégories ;
- diplômes ;
- disciplines ;
- institutions financières ;
- syndicats ;
- années académiques ;
- périodes de paie ;
- rubriques de paie ;
- lieux de service.

Les opérations réalisées comprennent généralement la consultation, la recherche, la pagination, la création, la modification, la suppression et la gestion du statut actif ou inactif.

La gestion des disciplines comprend en plus :

- le tri par code, libellé, description ou statut ;
- le filtrage par recherche et statut ;
- la validation du format du code ;
- l'activation et la désactivation ;
- le traitement des erreurs d'unicité renvoyées par l'API.

Fichiers représentatifs :

- `app/Http/Controllers/Parametrage/DisciplineController.php` ;
- `app/Services/Parametrage/DisciplineService.php` ;
- `app/Services/Parametrage/CorpsService.php` ;
- `app/Services/Parametrage/PeriodePaieService.php` ;
- `app/Services/Parametrage/RubriquePaieService.php` ;
- `resources/views/pages/parametres/` ;
- `routes/modules/parametrage.php`.

### 2.8 Gestion de la paie et des crédits

J'ai intégré les écrans de présentation et de suivi des opérations de paie :

- états de présence ;
- avances Tabaski ;
- retenues Tabaski ;
- retenues de rappel ;
- exemptions par enseignant ;
- travaux périodiques ;
- cotisations sociales ;
- états des salaires ;
- paie générée par IEF ;
- paie non générée ;
- salaires par banque ;
- bulletins de salaire ;
- effectifs par corps ;
- sommes perçues ;
- fermeture de période.

Le module crédits comprend les délégations de crédits, l'édition des délégations et l'édition des engagements.

Chaque écran dispose d'une structure commune avec objectifs métier, statistiques, filtres, tableaux, statuts et actions. Les pages génériques utilisent le composant `ModulePage` et les informations sont centralisées dans `config/module-pages.php`.

### 2.9 Gestion des indemnités et convocations

J'ai développé le module des convocations avec :

- liste paginée des convocations ;
- filtres par date, objet, métier et centre ;
- recherche d'enseignants ;
- création et modification d'une convocation ;
- gestion des bénéficiaires ;
- gestion des centres d'examen ;
- gestion des métiers et membres du jury ;
- import d'une convocation depuis un fichier Word ;
- téléchargement d'un modèle Word ;
- génération et téléchargement du PDF ;
- envoi des convocations par e-mail ;
- relance des envois en échec ;
- consultation du suivi des envois ;
- dépôt et téléchargement des pièces justificatives.

Une attention particulière a été portée à la distinction entre une convocation, ses centres, ses métiers et ses bénéficiaires. La liste peut ainsi présenter une ligne par centre et conserver une navigation précise vers la fiche concernée.

Fichiers principaux :

- `app/Http/Controllers/Indemnites/ConvocationsController.php` ;
- `app/Services/Api/Indemnites/ConvocationService.php` ;
- `resources/views/pages/indemnites/convocations/` ;
- `resources/views/pages/indemnites/pieces-justificatives.blade.php` ;
- `routes/modules/indemnites.php`.

### 2.10 Navigation et composants réutilisables

J'ai centralisé la navigation dans `config/navigation.php`. Le menu contient les groupes Tableau de bord, Paie, Indemnités, Paramétrage et Gestion utilisateur.

J'ai également réalisé des composants Blade réutilisables :

- layout général de l'application ;
- layout de connexion ;
- sidebar ;
- topbar ;
- messages de succès et d'erreur ;
- pages génériques de modules ;
- modales et composants de formulaire.

Cette approche rend l'interface homogène et évite de recopier la même structure dans chaque page.

## 3. Sécurité et contrôle des accès

J'ai intégré plusieurs mécanismes de sécurité :

- validation serveur des données reçues ;
- protection CSRF des formulaires ;
- limitation des tentatives de connexion et d'OTP ;
- middleware `sicore.auth` pour protéger les routes privées ;
- middleware `sicore.permission` pour les opérations sensibles ;
- invalidation de session lorsque le token backend expire ;
- contrôle des permissions par rôle ;
- filtrage des données selon le niveau national, IA ou IEF.

Le service `RoleStructureMatrix` détermine les structures accessibles selon le rôle. Par exemple, un gestionnaire IA ne doit pas recevoir les données d'une autre IA ou d'une IEF non autorisée.

## 4. Communication avec le backend

La communication avec le backend est regroupée dans `ApiClient` et dans des services spécialisés. Cette séparation permet aux contrôleurs de rester centrés sur les requêtes HTTP et les vues.

Les services prennent en charge :

- les requêtes GET, POST, PUT, PATCH et DELETE ;
- l'envoi de fichiers multipart ;
- le jeton d'authentification ;
- les réponses JSON ;
- les erreurs de connexion ;
- les erreurs de validation ;
- les réponses non autorisées ;
- la pagination et les statistiques.

## 5. Tests réalisés

Les tests présents vérifient notamment :

- l'accessibilité de la page de connexion ;
- la protection du tableau de bord ;
- la réussite ou l'échec d'une connexion ;
- l'affichage des utilisateurs venant de l'API ;
- la création d'un utilisateur ;
- les droits liés aux disciplines ;
- le périmètre organisationnel IA et IEF ;
- l'association d'une discipline à un enseignant ;
- la gestion des référentiels ;
- les validations et erreurs API.

Les tests se trouvent dans `tests/Feature/` et `tests/Unit/`, notamment `ApplicationTest`, `DisciplineTest`, `OrganisationScopeTest`, `TeacherDisciplineTest` et `RoleStructureMatrixTest`.

## 6. Résultats obtenus

Le travail réalisé a permis d'obtenir :

- une application Laravel structurée par modules ;
- environ 83 routes déclarées ;
- un ensemble complet de pages d'administration et de paramétrage ;
- une interface de paie, crédits et indemnités ;
- une authentification avec session et jeton API ;
- un parcours OTP complet ;
- une gestion des rôles, permissions et structures ;
- des composants Blade réutilisables ;
- des services API séparés des contrôleurs ;
- des tests unitaires et fonctionnels.

## 7. Limites et travail restant

Certaines pages de paie et de crédits sont des écrans de présentation structurés avec des données de démonstration. Les opérations métier définitives dépendent du backend et de ses endpoints.

Les prochaines étapes sont :

1. brancher toutes les pages génériques sur les données réelles ;
2. finaliser les contrats entre frontend et backend ;
3. finaliser la persistance des actions de paie et de validation ;
4. compléter les tests d'intégration ;
5. effectuer la recette avec les utilisateurs nationaux, IA et IEF ;
6. préparer le déploiement en production.

## 8. Démonstration devant le jury

Compte de démonstration :

```text
E-mail : admin@sicore.sn
Mot de passe : Sicore@2026
```

Parcours conseillé :

1. afficher la page de connexion ;
2. se connecter et présenter le tableau de bord ;
3. ouvrir les utilisateurs, les rôles et les permissions ;
4. présenter le formulaire progressif d'un enseignant ;
5. ouvrir le paramétrage des disciplines ou des IEF ;
6. présenter les états de présence et le circuit de validation ;
7. ouvrir les convocations et les pièces justificatives ;
8. expliquer le contrôle des accès selon la structure.

## Conclusion

Les parties réalisées couvrent la structure frontend Laravel, l'authentification, la gestion des sessions, le parcours OTP, le tableau de bord, l'administration, le paramétrage, les enseignants, la paie, les crédits, les indemnités, la communication API et les tests.

Le projet constitue une base fonctionnelle et évolutive. La prochaine étape consiste à poursuivre le branchement des données réelles et la recette métier avec le backend SICORE.
