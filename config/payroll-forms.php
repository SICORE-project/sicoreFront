<?php

/*
| FORMULAIRES DES ACTIONS DE PAIE
| payroll.js construit la modale avec ces définitions. Toute modification d'un
| champ doit aussi être faite dans PayrollActionRequest.php (validation) et
| PayrollActionService.php (traitement), tous deux dans sicoreBack/app/.
*/

/*
| Les opérations Tabaski sont collectives. Elles ciblent tous les enseignants
| actifs du statut et de l'IEF choisis ; aucun matricule ni catégorie salariale
| ne doit donc apparaître dans ces deux formulaires.
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
    ['name' => 'annee_academique_id', 'label' => 'Année académique', 'type' => 'academic_year', 'required' => true],
];

return [
    'generate-payslips' => [
        'title' => 'Générer les bulletins de salaire',
        'confirmation' => 'Les dossiers sont repris depuis Personnel. Les montants des retenues viennent de Rubriques de paie. Seule la sélection sera recalculée ; les autres bulletins sont conservés.',
        'fields' => [
            ['name' => 'generation_scope', 'label' => 'Générer pour', 'type' => 'select', 'required' => true, 'default' => 'ia', 'full_width' => true,
                'options' => [['value' => 'ia', 'label' => 'Toute une IA'], ['value' => 'teacher', 'label' => 'Un enseignant identifié par son matricule']]],
            ['name' => 'enseignant_id', 'label' => 'Enseignant / matricule', 'type' => 'teacher', 'full_width' => true],
            ['name' => 'ia_id', 'label' => 'Inspection académique (IA)', 'type' => 'academic_inspection'],
            ['name' => 'ief_id', 'label' => 'IEF (facultatif)', 'type' => 'education_inspection', 'help' => 'Laisser vide pour toutes les IEF de l’IA.'],
            ['name' => 'statut_enseignant_id', 'label' => 'Corps / statut enseignant (facultatif)', 'type' => 'teaching_statuses', 'full_width' => true, 'help' => 'Laisser vide pour les PC et VAC de la sélection.'],
            ['name' => 'annee_academique_id', 'label' => 'Année académique', 'type' => 'academic_year', 'required' => true],
            ['name' => 'month', 'label' => 'Mois de paie', 'type' => 'payroll_month', 'required' => true],
        ],
    ],
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
        'title' => 'Appliquer une avance Tabaski',
        'confirmation' => 'L’avance sera appliquée aux enseignants actifs du statut et des IA sélectionnés, sur un seul mois.',
        'fields' => [
            ...$collectiveTabaskiFields,
            ['name' => 'month', 'label' => 'Mois d’application', 'type' => 'payroll_month', 'required' => true],
            ['name' => 'amount', 'label' => 'Montant (FCFA)', 'type' => 'number', 'required' => true, 'min' => '1', 'step' => '1', 'default' => 100000],
        ],
    ],
    'apply-tabaski-deduction' => [
        'title' => 'Appliquer une retenue Tabaski',
        'confirmation' => 'La retenue sera appliquée aux enseignants actifs du statut et des IA sélectionnés sur exactement 10 mois. Le montant saisi reste inchangé pour chaque mois.',
        'fields' => [
            ...$collectiveTabaskiFields,
            [
                'name' => 'months',
                'label' => 'Mois de retenue (exactement 10)',
                'type' => 'checkbox_group',
                'source' => 'payroll_months',
                'required' => true,
                'exact' => 10,
                'full_width' => true,
            ],
            ['name' => 'amount', 'label' => 'Montant mensuel (FCFA)', 'type' => 'number', 'required' => true, 'min' => '1', 'step' => '1', 'default' => 10000],
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
