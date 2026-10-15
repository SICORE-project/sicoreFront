# ADM-INT-002 — intégration DECPC

Le dépôt présent est le frontend. Le rôle API, les politiques d'accès aux dossiers et le journal d'audit métier doivent être implémentés et testés dans le backend avant de valider la carte complète.

## Session et permissions

La connexion doit retourner `user.role.slug = agent_decpc` (alias historique `decpc` accepté), les permissions explicites dans `user.permissions`, et `decpc.perimetre = {"type":"lieu_service_id","id":7}`. L'identifiant est celui de la structure DECPC attribuée par le serveur, jamais une valeur choisie dans le navigateur. Le rattachement `user.acces_organisationnel.lieu_service_id` ou `structure.id` est également accepté. Sans rattachement valide, les modules sont bloqués.

Permissions minimales à associer au rôle : `enseignants.read`, `indemnites.read`. Les mutations restent autorisées individuellement par leur nom de route, par exemple `indemnites.pieces-justificatives.deposer`, `indemnites.pieces-justificatives.modifier`, `indemnites.pieces-justificatives.valider` et `indemnites.pieces-justificatives.rejeter`. Ne pas attribuer automatiquement les droits de validation ; ils dépendent du circuit métier. Les autres modules requièrent leurs permissions complémentaires existantes.

## Tableau de bord

Le frontend attend `GET /decpc/dashboard`, authentifié par Bearer token, avec `lieu_service_id` imposé depuis la session. Cet endpoint est un contrat à implémenter côté API, pas un endpoint vérifié dans ce dépôt.

Réponse attendue (les valeurs absentes sont affichées comme indisponibles) :

```json
{"data": {
  "total_agents": 42,
  "dossiers_en_attente": 3,
  "dossiers_valides": 8,
  "dossiers_rejetes_retournes": 2,
  "montant_en_cours": 125000,
  "indemnites_par_type": [{"libelle":"Correction","total":3,"montant":125000}],
  "derniers_dossiers": [{"reference":"DECPC-001","type":"Correction","statut":"En attente","updated_at":"2026-09-21"}]
}}
```

Les montants sont en FCFA. Le backend doit filtrer aussi les agrégats et les dossiers récents selon le périmètre et les permissions du token. Le frontend masque les indicateurs du personnel ou des indemnités lorsque la permission correspondante manque.

## Contrôles backend restant nécessaires

- Résoudre le périmètre depuis l'utilisateur authentifié ; ne jamais faire confiance au paramètre `lieu_service_id` seul.
- Appliquer ce périmètre aux listes, détails, recherches, téléchargements, exports et mutations, y compris aux identifiants des bénéficiaires et pièces jointes.
- Vérifier chaque permission et transition du circuit de validation, même lors d'un appel API direct.
- Enregistrer chaque mutation dans un audit persistant : acteur, opération, dossier, date, périmètre et changements, dans la transaction métier. Un journal frontend ne prouverait pas la réussite d'un traitement distant.
- Tester deux structures distinctes, les accès par identifiant hors périmètre, les filtres falsifiés et les transitions interdites.

Les routes frontend sont contrôlées côté serveur. Les écrans métier existants conservent leurs formulaires ; l'harmonisation complète de la visibilité de leurs boutons avec les permissions reste à réaliser. Les actions non autorisées sont refusées par le middleware.
