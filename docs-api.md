# Connexion API du frontend

`App\Services\SicoreApi` centralise les appels HTTP vers le backend.

- `AuthController` ouvre et révoque le token Sanctum.
- `PayrollController` alimente les pages, relaie les actions et les exports.
- `EnsureSicoreAuthenticated` contrôle la présence et l’expiration de la session.
- `public/assets/js/payroll.js` pilote les formulaires modaux sans exposer le token.

Les erreurs API sont normalisées par `SicoreApiException` et affichées dans le
design existant. Les mutations reçoivent une clé d’idempotence générée par le
navigateur et relayée par le BFF.
