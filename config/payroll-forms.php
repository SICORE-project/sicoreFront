<?php

/*
| FORMULAIRES DES ACTIONS DE PAIE
| payroll.js construit la modale avec ces définitions. Toute modification d'un
| champ doit aussi être faite dans PayrollActionRequest.php (validation) et
| PayrollActionService.php (traitement), tous deux dans sicoreBack/app/.
*/

/*
| Les opérations Tabaski sont collectives. Elles ciblent tous les enseignants
| actifs du corps et de l'IEF choisis ; aucun matricule ni catégorie salariale
| ne doit donc apparaître dans ces deux formulaires.
*/
$collectiveTabaskiFields = [
    [
        'name' => 'type_engagement',
        'label' => 'Corps d’enseignement',
        'type' => 'select',
        'required' => true,
        'options' => [
            ['value' => 'contractuel', 'label' => 'PC — Professeurs contractuels'],
            ['value' => 'vacataire', 'label' => 'Vacataire'],
        ],
    ],
    ['name' => 'ia_id', 'label' => 'Inspection académique (IA)', 'type' => 'academic_inspection', 'required' => true],
    ['name' => 'ief_id', 'label' => 'Inspection de l’Éducation et de la Formation (IEF)', 'type' => 'education_inspection', 'required' => true],
    [
        'name' => 'academic_year',
        'label' => 'Année académique',
        'type' => 'academic_year',
        'required' => true,
    ],
    [
        'name' => 'payroll_period_id',
        'label' => 'Mois d’application',
        'type' => 'period',
        'required' => true,
        'open_only' => true,
    ],
    ['name' => 'amount', 'label' => 'Montant (FCFA)', 'type' => 'number', 'required' => true, 'min' => '1', 'step' => '1'],
];

return [
    'configure-teacher-payroll' => [
        'title' => 'Configurer le profil de paie',
        'confirmation' => 'La grille applicable déterminera automatiquement le salaire de base. Les montants IMPR et TRIMF doivent être ceux validés par le service de la solde.',
        'fields' => [
            ['name' => 'ia_id', 'label' => 'Inspection académique (IA)', 'type' => 'academic_inspection', 'required' => true],
            ['name' => 'ief_id', 'label' => 'Inspection de l’Éducation et de la Formation (IEF)', 'type' => 'education_inspection', 'required' => true],
            ['name' => 'matricule', 'label' => 'Matricule', 'type' => 'matricule', 'required' => true, 'full_width' => true, 'placeholder' => 'Saisir le matricule'],
            ['name' => 'enseignant_id', 'type' => 'hidden'],
            [
                'name' => 'type_engagement',
                'label' => 'Type d’engagement',
                'type' => 'select',
                'required' => true,
                'after_teacher' => true,
                'help' => 'Vacataire : salaire de base automatique de 150 000 FCFA.',
                'options' => [
                    ['value' => 'contractuel', 'label' => 'Professeur contractuel'],
                    ['value' => 'vacataire', 'label' => 'Vacataire'],
                ],
            ],
            [
                'name' => 'payroll_diploma_level',
                'label' => 'Diplôme de paie',
                'type' => 'select',
                'required' => true,
                'after_teacher' => true,
                'show_for' => ['contractuel'],
                'options' => [
                    ['value' => 'CAP', 'label' => 'CAP'],
                    ['value' => 'BEP', 'label' => 'BEP'],
                    ['value' => 'BAC_BT', 'label' => 'BAC / BT'],
                    ['value' => 'BTS_DUEL_DUES', 'label' => 'BTS / DUEL / DUES'],
                    ['value' => 'LICENCE', 'label' => 'Licence'],
                    ['value' => 'MASTER_MAITRISE', 'label' => 'Master / Maîtrise'],
                ],
            ],
            [
                'name' => 'payroll_category_level',
                'label' => 'Catégorie salariale',
                'type' => 'select',
                'required' => true,
                'after_teacher' => true,
                'show_for' => ['contractuel'],
                'options' => array_map(
                    fn (int $category): array => ['value' => $category, 'label' => $category.'e catégorie'],
                    range(1, 12)
                ),
            ],
            ['name' => 'impr_monthly_amount', 'label' => 'IMPR mensuel validé (FCFA)', 'type' => 'number', 'required' => true, 'min' => '0', 'step' => '1', 'after_teacher' => true],
            ['name' => 'trimf_monthly_amount', 'label' => 'TRIMF mensuelle validée (FCFA)', 'type' => 'number', 'required' => true, 'min' => '0', 'step' => '1', 'after_teacher' => true],
            [
                'name' => 'ipm_monthly_amount',
                'label' => 'IPM mensuelle (FCFA)',
                'type' => 'number',
                'required' => false,
                'min' => '0',
                'step' => '1',
                'after_teacher' => true,
                'show_for' => ['contractuel'],
                'help' => 'Facultatif. Mettre 0 si aucune retenue IPM ne s’applique.',
            ],
            [
                'name' => 'union_checkoff_monthly_amount',
                'label' => 'Check-off UES (FCFA)',
                'type' => 'number',
                'required' => false,
                'min' => '0',
                'step' => '1',
                'after_teacher' => true,
                'show_for' => ['contractuel'],
                'help' => 'Facultatif. La retenue syndicale ne doit être renseignée que si elle est autorisée.',
            ],
        ],
    ],
    'create-period' => [
        'title' => 'Créer une période de paie',
        'confirmation' => null,
        'fields' => [
            ['name' => 'code', 'label' => 'Code de période', 'type' => 'month', 'required' => true],
            ['name' => 'label', 'label' => 'Libellé', 'type' => 'text', 'required' => true, 'placeholder' => 'Ex. Août 2026'],
            ['name' => 'start_date', 'label' => 'Date de début', 'type' => 'date', 'required' => true],
            ['name' => 'end_date', 'label' => 'Date de fin', 'type' => 'date', 'required' => true],
        ],
    ],
    'save-attendance' => [
        'title' => 'État de présence',
        'fields' => [
            ['name' => 'ia_id', 'label' => 'Inspection académique (IA)', 'type' => 'academic_inspection', 'required' => true],
            ['name' => 'ief_id', 'label' => 'Inspection de l’Éducation et de la Formation (IEF)', 'type' => 'education_inspection', 'required' => true],
            ['name' => 'matricule', 'label' => 'Matricule', 'type' => 'matricule', 'required' => true, 'full_width' => true, 'placeholder' => 'Saisir le matricule'],
            ['name' => 'enseignant_id', 'type' => 'hidden'],
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true, 'after_teacher' => true],
            ['name' => 'absence_days', 'label' => 'Jours d’absence', 'type' => 'number', 'required' => true, 'step' => '0.5', 'min' => '0', 'max' => '31', 'after_teacher' => true],
            ['name' => 'delay_minutes', 'label' => 'Minutes de retard', 'type' => 'number', 'required' => true, 'min' => '0', 'after_teacher' => true],
            ['name' => 'deduction_amount', 'label' => 'Retenue calculée (FCFA)', 'type' => 'number', 'required' => false, 'min' => '0', 'help' => 'Laissez vide pour un calcul automatique au prorata.', 'after_teacher' => true],
            ['name' => 'notes', 'label' => 'Observations', 'type' => 'textarea', 'required' => false, 'after_teacher' => true],
            ['name' => 'expected_version', 'type' => 'hidden'],
        ],
    ],
    'add-element' => [
        'title' => 'Ajouter un élément variable',
        'fields' => [
            ['name' => 'ia_id', 'label' => 'Inspection académique (IA)', 'type' => 'academic_inspection', 'required' => true],
            ['name' => 'ief_id', 'label' => 'Inspection de l’Éducation et de la Formation (IEF)', 'type' => 'education_inspection', 'required' => true],
            ['name' => 'matricule', 'label' => 'Matricule', 'type' => 'matricule', 'required' => true, 'full_width' => true, 'placeholder' => 'Saisir le matricule'],
            ['name' => 'enseignant_id', 'type' => 'hidden'],
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true, 'after_teacher' => true],
            ['name' => 'code', 'label' => 'Code', 'type' => 'text', 'required' => true, 'placeholder' => 'PRIME_EXCEPTIONNELLE', 'after_teacher' => true],
            ['name' => 'label', 'label' => 'Libellé', 'type' => 'text', 'required' => true, 'after_teacher' => true],
            [
                'name' => 'category',
                'label' => 'Catégorie',
                'type' => 'select',
                'required' => true,
                'after_teacher' => true,
                'options' => [
                    ['value' => 'earning', 'label' => 'Gain'],
                    ['value' => 'deduction', 'label' => 'Retenue'],
                    ['value' => 'contribution', 'label' => 'Cotisation'],
                ],
            ],
            ['name' => 'amount', 'label' => 'Montant (FCFA)', 'type' => 'number', 'required' => true, 'min' => '1', 'step' => '1', 'after_teacher' => true],
            ['name' => 'expected_version', 'type' => 'hidden'],
        ],
    ],
    'apply-tabaski-advance' => [
        'title' => 'Appliquer une avance Tabaski',
        'confirmation' => 'L’avance sera appliquée à tous les enseignants actifs correspondant au corps, à l’IA et à l’IEF sélectionnés. Elle figurera sur les bulletins du mois choisi.',
        'fields' => $collectiveTabaskiFields,
    ],
    'apply-tabaski-deduction' => [
        'title' => 'Appliquer une retenue Tabaski',
        'confirmation' => 'La retenue sera appliquée à tous les enseignants actifs correspondant au corps, à l’IA et à l’IEF sélectionnés. Elle figurera sur les bulletins du mois choisi.',
        'fields' => $collectiveTabaskiFields,
    ],
    'exempt-element' => [
        'title' => 'Accorder une exemption',
        'confirmation' => 'Cette exemption sera journalisée et prise en compte au prochain calcul.',
        'fields' => [
            ['name' => 'payroll_element_id', 'type' => 'hidden'],
            ['name' => 'expected_version', 'type' => 'hidden'],
            ['name' => 'reason', 'label' => 'Motif détaillé', 'type' => 'textarea', 'required' => true, 'minlength' => '10'],
        ],
    ],
    'validate-attendance' => [
        'title' => 'Valider les états de présence',
        'confirmation' => 'Tous les états de présence en brouillon de cette période seront validés.',
        'fields' => [
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true],
        ],
    ],
    'validate-elements' => [
        'title' => 'Valider les éléments variables',
        'confirmation' => 'Tous les éléments variables en brouillon de cette période seront validés.',
        'fields' => [
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true],
        ],
    ],
    'calculate-payroll' => [
        'title' => 'Calculer la paie',
        'confirmation' => 'Le calcul remplacera le calcul précédent de la période, après contrôle des données.',
        'fields' => [
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true],
        ],
    ],
    'validate-payroll' => [
        'title' => 'Valider la paie',
        'confirmation' => 'Après validation, les éléments de la période ne pourront plus être modifiés.',
        'fields' => [
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true],
        ],
    ],
    'close-period' => [
        'title' => 'Clôturer définitivement la période',
        'confirmation' => 'Action irréversible : saisissez le code exact de la période pour confirmer.',
        'fields' => [
            ['name' => 'payroll_period_id', 'label' => 'Période', 'type' => 'period', 'required' => true],
            ['name' => 'confirmation', 'label' => 'Code de confirmation', 'type' => 'text', 'required' => true, 'placeholder' => 'AAAA-MM'],
            ['name' => 'expected_version', 'type' => 'hidden'],
        ],
    ],
    'mark-paid' => [
        'title' => 'Enregistrer le paiement',
        'confirmation' => 'Vérifiez la référence bancaire avant de confirmer le paiement.',
        'fields' => [
            ['name' => 'payroll_payslip_id', 'type' => 'hidden'],
            ['name' => 'expected_version', 'type' => 'hidden'],
            ['name' => 'payment_reference', 'label' => 'Référence de paiement', 'type' => 'text', 'required' => true],
        ],
    ],
];
