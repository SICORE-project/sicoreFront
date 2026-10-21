# ADM-INT-001 — Interface agent DRH

## Interface attribuée par rôle et permissions

`InterfaceAccess` centralise désormais les règles de navigation et d'accès aux pages. `DrhAccess` conserve la compatibilité avec les vues existantes. Les nouveaux comptes et rôles reçoivent automatiquement les modules autorisés par les permissions de la réponse de connexion, sans ajout de code par compte ou par rôle. Les rôles administrateurs gardent leur accès global. Les anciennes sessions sans catalogue de permissions conservent leur comportement jusqu'à reconnexion ; les nouvelles connexions enregistrent toujours un catalogue, même vide.

Les correspondances des fonctionnalités avec les permissions sont dans `config/interface-permissions.php`. Les permissions existantes `enseignants.read/create/update/delete` sont reconnues, ainsi que les anciennes permissions `personnel.consulter/creer/modifier/supprimer`. Les nouveaux rôles utilisant ces fonctionnalités ne nécessitent aucune nouvelle correspondance. Une nouvelle fonctionnalité peut nécessiter une correspondance avec son contrat backend.

Les écrans de création, modification et permissions d'un rôle affichent un aperçu des pages qui seront visibles selon les cases cochées. Attribuer ce rôle à un compte suffit ; les droits sont pris en compte à la prochaine connexion. Les changements ne sont pas propagés en temps réel aux sessions déjà ouvertes.

La page Enseignants autorisée s'ouvre même quand aucun dossier n'existe. Sans périmètre DRH, elle affiche une liste vide et un message explicatif, sans charger de données ni autoriser les mutations. Le périmètre national explicitement renvoyé dans `drh.perimetre.type` est reconnu.

## Frontend livré

Le rôle `agent_drh` (ou le libellé `Agent DRH`) déclenche le tableau de bord DRH à `/dashboard`. La connexion conserve les permissions effectives et ignore le retour vers une page de paie pour ce profil.

Le menu et les routes utilisent la même règle d'accès. Le rôle existant `drh` est également reconnu. Les modules non autorisés sont absents du menu ; un ancien lien vers une page non autorisée ramène au tableau de bord sans afficher de page « accès interdit ». Les requêtes JSON et les mutations restent refusées avec un statut 403. La consultation du personnel requiert `personnel.consulter` et un périmètre organisationnel. La création, la modification et la suppression requièrent respectivement `personnel.creer`, `personnel.modifier`, `personnel.supprimer`, en plus de la consultation. Aucun de ces droits n'est implicitement attribué par le frontend.

La création de référentiels depuis le formulaire enseignant requiert `parametrage.modifier`. L'association de disciplines conserve sa permission existante `enseignants.disciplines.associer`. Les autres pages nécessitent une permission complémentaire égale au nom exact de leur route (par exemple `personnel.reclassement`). Les contrôles antérieurs des autres profils restent inchangés.

Le périmètre provient de `user.acces_organisationnel` (ou `organisation_access`) dans la réponse de connexion : `ief_id`, `ia_id` ou `lieu_service_id`, avec les objets `ief`, `ia`, `structure` pour le libellé. Sans périmètre, aucune requête de statistiques ou de dossiers DRH n'est envoyée.

## Contrat backend à confirmer / compléter

Ce dépôt contient uniquement le frontend Laravel. Le rôle, les permissions en base, la sécurité de l'API et l'audit ne sont pas créés ici.

La réponse de connexion doit fournir `user.permissions` comme liste des droits effectifs (chaînes ou objets avec `slug`/`nom`). Si cette propriété est absente, `user.role.permissions` est utilisé.

L'endpoint existant `GET pages.dashboard.index` doit retourner, sous `data`, les agrégats suivants calculés par le backend sur le périmètre du jeton :

```json
{
  "data": {
    "agents_total": 120,
    "enseignants_fonctionnaires": 80,
    "enseignants_non_fonctionnaires": 40,
    "dossiers_actifs": 115,
    "dossiers_incomplets": 7,
    "agents_par_lieu_service": [{"libelle": "École exemple", "total": 12}],
    "derniers_dossiers": [{"prenom": "Amina", "nom": "Diop", "matricule": "EX001", "updated_at": "2026-09-21 10:00"}]
  }
}
```

Il s'agit du contrat attendu par la nouvelle vue, pas d'une réponse vérifiée sur une API réelle. Les indicateurs absents affichent « Indicateur indisponible ». Les règles de dossier actif/incomplet doivent être définies côté métier et backend.

La liste et les mutations réutilisent `admin/personnel/enseignants`. Le client transmet le jeton et impose son filtre organisationnel aux lectures. Le backend doit déterminer les droits et le périmètre à partir du jeton, sans faire confiance aux paramètres transmis, y compris pour un ID de dossier, une affectation ou une mutation.

Les consultations sensibles (y compris les données incluses dans la liste et les statistiques) et les mutations doivent être auditées par le backend : utilisateur, dossier, action, date, résultat et changements pertinents. La fiche actuelle s'ouvre depuis les données déjà reçues dans la liste ; son ouverture n'émet pas d'événement d'audit supplémentaire. Un audit individuel à chaque ouverture nécessite un endpoint de détail audité et une adaptation de cette fiche.

## Validation

`php artisan test --filter=DrhInterfaceTest`

Les tests utilisent une API simulée : connexion, menu, indicateurs, refus des URL et mutations sans droit, absence de périmètre, permission complémentaire, indisponibilité API et filtre imposé malgré une URL modifiée. Un essai avec un compte DRH réel et les contrôles backend reste nécessaire avant de valider toute la carte.
