# Intégration future du backend SICORE

Cette archive utilise volontairement une authentification locale de test. Aucun appel API n’est actif.

Compte temporaire :

```text
admin@sicore.sn / Sicore@2026
```

Lors de l’intégration du backend, les éléments à remplacer seront principalement :

1. `app/Http/Controllers/AuthController.php` pour envoyer les identifiants au backend.
2. La session Laravel pour stocker de manière sécurisée le jeton retourné.
3. Les formulaires et tableaux pour charger ou enregistrer les données par API.

Les layouts, le sidebar, la navigation, les composants Blade et les routes d’affichage peuvent être conservés.
