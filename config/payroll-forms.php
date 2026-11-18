<?php

/*
| FORMULAIRES DES ACTIONS DE PAIE
| payroll.js construit la modale avec ces définitions. Toute modification d'un
| champ doit aussi être faite dans PayrollActionRequest.php (validation) et
| PayrollActionService.php (traitement), tous deux dans sicoreBack/app/.
*/

/*
| Les opérations Tabaski sont collectives. Elles ciblent tous les enseignants
| actifs du statut et des IA choisis. IEF et matricule permettent de limiter
| facultativement la sélection sans modifier les échéanciers existants.
*/
$collectiveTabaskiFields = [
    ['name' => 'statut_enseignant_id', 'label' => 'Statut enseignant', 'type' => 'teaching_statuses', 'required' => true],
    [
        'name' => 'ia_ids',
        'label' => 'Inspections académiques (IA)',
        'type' => 'checkbox_group',
        'source' => 'academic_inspections',
        'required' => true,
        'full_width' => true,
        'select_all' => true,
    ],
    ['name' => 'ief_id', 'label' => 'IEF rattachée (facultatif)', 'type' => 'education_inspection', 'help' => 'Laisser vide pour toutes les IEF des IA cochées.'],
    ['name' => 'matricule', 'label' => 'Matricule du formateur (facultatif)', 'type' => 'matricule', 'full_width' => true, 'help' => 'Saisir le matricule exact pour un seul formateur ; laisser vide pour le groupe.'],
    ['name' => 'start_month', 'label' => 'À partir du mois', 'type' => 'month', 'required' => true, 'help' => 'Choisir un mois après la dernière période déjà calculée. Les périodes futures ne doivent pas encore exister.'],
];

return [
    'configure-teacher-payroll' => [
        'title' => 'Configurer le profil de paie',
        'confirmation' => 'La grille applicable détermine le salaire de base. IMPR et TRIMF, ainsi que IPM et Check-off pour les PC, sont repris automatiquement depuis les montants de Paramétrage → Rubriques de paie. Aucune saisie individuelle de ces montants ici.',
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
        'title' => 'Programmer une avance Tabaski',
        'confirmation' => '100 000 FCFA seront ajoutés au prochain bulletin éligible de chaque bénéficiaire. La liste des enseignants est figée lors de cette application.',
        'fields' => [
            ...$collectiveTabaskiFields,
            ['name' => 'amount', 'label' => 'Montant de l’avance (FCFA)', 'type' => 'number', 'default' => 100000, 'required' => true, 'readonly' => true, 'min' => 100000, 'max' => 100000, 'step' => 1],
        ],
    ],
    'apply-tabaski-deduction' => [
        'title' => 'Activer les retenues Tabaski',
        'confirmation' => 'Uniquement les bénéficiaires d’une avance recevront 10 retenues mensuelles de 10 000 FCFA. La première retenue commence au plus tôt le mois suivant le versement de l’avance. Les nouveaux enseignants sans avance sont exclus.',
        'fields' => [
            ...$collectiveTabaskiFields,
            ['name' => 'amount', 'label' => 'Montant mensuel de la retenue (FCFA)', 'type' => 'number', 'default' => 10000, 'required' => true, 'readonly' => true, 'min' => 10000, 'max' => 10000, 'step' => 1, 'help' => '10 000 FCFA par mois pendant 10 mois, soit 100 000 FCFA au total.'],
        ],
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
