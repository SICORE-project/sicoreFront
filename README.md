# SICORE Frontend

Interface Laravel 12 de SICORE. Le design Blade, le sidebar et les assets
historiques sont conservés. L’authentification et les 17 pages de gestion de la
paie consomment désormais l’API `sicoreBack` via une couche BFF serveur.

Le token Sanctum reste dans la session Laravel chiffrée et n’est jamais exposé
au JavaScript du navigateur.

Pour comprendre les dossiers, retrouver une page et savoir où effectuer une
modification, commencer par [le guide débutant du code frontend](README-CODE-FRONTEND.md).

## Configuration

```text
SICORE_API_URL=http://127.0.0.1:8000
SICORE_TOKEN_LIFETIME=120
SESSION_ENCRYPT=true
```

## Démarrage

Privilégier le script à la racine du workspace :

```powershell
..\demarrer-sicore.ps1
```

Ou démarrer uniquement le frontend :

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

## Tests

```powershell
php artisan test
```
