# Recrutement par lot, prise de service et carrière

## Parcours disponible

L’import est réservé aux nouveaux enseignants **vacataires**. Le modèle CSV ne demande plus de catégorie : le backend attribue `vacataire` automatiquement. Les anciens fichiers avec une colonne `type_engagement` restent acceptés uniquement si toutes les lignes portent `vacataire`. Les contractuels et fonctionnaires déjà présents sont suivis dans leurs dossiers existants ; ils ne sont pas réimportés ici.

Le sidebar DRH ouvre désormais `/personnel/recrutements`, une page dédiée aux lots, distincte du catalogue général des enseignants.

1. Télécharger le modèle CSV et préparer jusqu’à 5 000 lignes, 5 Mo maximum. Excel doit enregistrer le fichier en CSV UTF-8 ; les fichiers XLSX ne sont pas acceptés par l’API actuelle.
2. Saisir la référence unique du lot et sa date, envoyer la liste pour validation. Le backend contrôle les lignes, doublons et périmètres sans écrire de dossiers. Un aperçu montre les 100 premières lignes et le nombre total validé.
3. Confirmer sous 30 minutes. Le serveur reprend le fichier vérifié conservé dans le stockage privé, sans faire confiance à un fichier ou à une référence modifiés dans le navigateur. Les recrutés sont créés inactifs. Une seconde confirmation est rejetée après réussite.
4. Joindre l’ordre de service PDF du lot (10 Mo maximum), puis transmettre à la DAGE. L’API exige un OS et un destinataire DAGE actif autorisé ; elle verrouille l’OS après transmission.
5. L’IA ouvre le lot, choisit l’enseignant et enregistre la date effective, l’établissement et le certificat PDF fourni par le chef d’établissement. L’API vérifie que le compte est rattaché à une IA active, filtre les établissements de cette IA et active uniquement l’enseignant concerné.
6. Deux ans après la date effective de prise de service, le dossier vacataire est signalé « À examiner ». Le passage à contractuel nécessite une date d’effet et une décision PDF, validées côté backend. Aucun changement automatique de catégorie.

Chaque ligne permet de consulter l’historique : recrutement, prise de service, ancienne/nouvelle catégorie, date d’effet, auteur et justificatif. Les documents passent par une route authentifiée ; leurs chemins privés ne sont pas exposés.

Seul le passage vacataire → contractuel est défini et pris en charge actuellement. Une future transition vers fonctionnaire nécessitera sa règle métier et son autorisation explicites.

## Permissions à attribuer dans l’administration

| Fonction | Permissions |
| --- | --- |
| Consulter les lots et documents | `recruitment.read` |
| Importer et joindre l’OS | `recruitment.read`, `recruitment.import` |
| Transmettre à la DAGE | `recruitment.read`, `recruitment.transmit` |
| Enregistrer la prise de service | `recruitment.read`, `recruitment.service`, rattachement effectif à une IA |
| Valider la décision de carrière | `recruitment.read`, `recruitment.transition` |

Les droits sont lus à la connexion. Réattribuer les permissions du rôle dans l’administration puis reconnecter un compte existant si nécessaire. L’endpoint `recruitment/establishments` utilise uniquement la permission de prise de service et le périmètre du compte, sans accorder le module Paramétrage.

## Exploitation

Le frontend utilise les endpoints réels du module `recruitment` du dépôt voisin `sicoreBack`. La migration `2026_09_22_000001_create_recruitment_workflow` est appliquée dans l’environnement local vérifié.

Le scheduler backend doit exécuter `recruitment:alerts` chaque jour pour les notifications persistantes. La configuration `RECRUITMENT_ANNIVERSARY_BASIS` doit rester à `service`, sa valeur par défaut. Les alertes sur la page du lot sont également recalculées à partir de la date de prise de service.

Les aperçus CSV du frontend expirent après 30 minutes et sont supprimés lors d’une confirmation réussie, d’une annulation ou d’un nouvel aperçu. Le scheduler frontend exécute `recruitment:prune-previews` chaque jour pour retirer les fichiers abandonnés de plus de 24 heures. Il faut activer le scheduler Laravel sur les deux applications en exploitation.

Les appels d’écriture ne sont pas réessayés automatiquement en cas de délai dépassé : l’utilisateur est invité à vérifier le lot ou son historique avant toute nouvelle tentative.

## Vérification

- Frontend : `php artisan test --filter=RecruitmentWorkflowTest` et `RecruitmentInterfaceTest`.
- Backend : `php vendor/phpunit/phpunit/phpunit tests/Feature/RecruitmentWorkflowTest.php --do-not-cache-result`.
- Consultation IA : `php vendor/phpunit/phpunit/phpunit tests/Feature/RecruitmentEstablishmentTest.php --filter=test_ia_can_select --do-not-cache-result`.

Les tests frontend simulent les réponses HTTP ; les tests backend utilisent SQLite en mémoire. Aucun lot réel n’a été importé pour les vérifications.
