# Où se trouve le code des pages Paie ?

Les fichiers Blade de ce dossier sont courts volontairement. Chaque fichier
déclare le titre, le layout et le `slug`, puis délègue le rendu au composant :

```text
resources/views/components/module-page.blade.php
```

Les données, colonnes, lignes, statistiques et boutons proviennent du backend :

```text
sicoreBack/app/Services/PayrollPageService.php
```

Repères rapides :

- URL des pages : `sicore-front/routes/web.php` ;
- composant visuel : `resources/views/components/module-page.blade.php` ;
- champs des formulaires : `sicore-front/config/payroll-forms.php` ;
- JavaScript : `sicore-front/public/assets/js/payroll.js` ;
- communication API : `sicore-front/app/Services/SicoreApi.php` ;
- routes API : `sicoreBack/routes/api.php` ;
- actions métier : `sicoreBack/app/Services/PayrollActionService.php` ;
- calculs : `sicoreBack/app/Services/PayrollCalculationService.php` ;
- bulletin individuel : `payslip.blade.php`.

Voir la
[cartographie complète](../../../../../docs/CARTOGRAPHIE-MODULE-PAIE.md).
