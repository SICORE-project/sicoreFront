# Consultation des bulletins

La route `/paie/bulletins` affiche seulement les critères de consultation : corps
(statut PC/VAC), année académique, période, IA, IEF, lieu de paiement et matricule.
Le matricule exact ouvre un bulletin ; sans matricule, la case « Édition bulletin
avec le groupe » ouvre tous les bulletins correspondant aux critères, dans un
document imprimable avec un saut de page entre enseignants.

`PayrollBulletinController` appelle uniquement les API de lecture existantes :

1. `payroll/pages/paie-etat-salaires` : source des références autorisées et des
   critères de l'état mensuel. Une ligne absente de ce résultat ne peut pas être
   consultée depuis le formulaire.
2. `payroll/pages/paie-bulletins` : correspondance des références avec les IDs
   consultables, sans afficher son ancien tableau ni ses actions.
3. `payroll/payslips/{id}` : détail du bulletin enregistré.

La recherche AJAX reprend les mêmes filtres et suggère jusqu'à 15 matricules
disponibles par recherche. Le choix individuel exige ensuite un matricule exact.
Le périmètre de session remplace les critères organisationnels du navigateur ;
les contrôles d'autorisation du backend restent appliqués à chaque lecture.
Une erreur de lecture interrompt l'ensemble du document plutôt que d'imprimer
un groupe incomplet. Les réponses de suggestion et d'édition ne sont pas mises
en cache.

Le rendu individuel et le rendu groupé partagent
`pages/paie/partials/bulletin-document.blade.php`, extrait du modèle existant.
Aucun recalcul, paiement ou génération n'est exécuté dans ce parcours.
Le contrôleur, le trait, les vues et les styles de l'état des salaires ne sont
pas modifiés. Ses limites actuelles de lecture (notamment 500 bulletins par
période dans le service partagé) restent celles de la source.

Le backend issu de main nécessite ses migrations. Localement, la migration
`2026_11_03_111246_add_statut_enseignant_id_to_rubrique_paies_table` a été appliquée
pour rendre la lecture de l'état des salaires compatible avec le schéma.
Aucun seeder n'est nécessaire pour cette consultation.

Validation : `php artisan test --filter=PayrollBulletinConsultationTest`.
