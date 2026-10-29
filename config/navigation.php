<?php

/*
| MENU LATÉRAL DU FRONTEND
| Lu par resources/views/components/sidebar.blade.php.
| type=link crée un lien simple ; type=group crée un groupe déroulant.
| route vient de routes/web.php et active garde le bon lien sélectionné.
*/
return [

    [
        'type' => 'link',
        'label' => 'Tableau de bord',
        'icon' => 'fa-solid fa-gauge-high',
        'route' => 'dashboard',
    ],
    [
        'type' => 'group',
        'label' => 'Gestion du personnel',
        'icon' => 'fa-solid fa-users',
        'active' => ['enseignants.*', 'personnel.*', 'recruitment.*'],
        'links' => [
            // ['label' => 'Nouveau recrus', 'route' => 'recruitment.index', 'active' => 'recruitment.*', 'icon' => 'fa-solid fa-user-plus'],
            ['label' => 'Enseignants', 'route' => 'enseignants.index', 'active' => 'enseignants.*', 'icon' => 'fa-solid fa-chalkboard-user', 'permission' => 'enseignants.read'],
            ['label' => 'Reclassement', 'route' => 'personnel.reclassement', 'icon' => 'fa-solid fa-ranking-star'],
            ['label' => 'Impôt sur le revenu', 'route' => 'personnel.impot-sur-le-revenu', 'icon' => 'fa-solid fa-file-invoice-dollar'],
            ['label' => 'Validation enseignants transférés', 'route' => 'personnel.validation-enseignants-transferes', 'icon' => 'fa-solid fa-user-check'],
            // ['label' => 'Heures supplémentaires', 'route' => 'personnel.heures-supplementaires', 'icon' => 'fa-solid fa-clock'],
            // ['label' => 'Principaux intérimaires', 'route' => 'personnel.principaux-interimaires', 'icon' => 'fa-solid fa-user-tie'],
        ],
    ],
    [
    'type'       => 'group',
    'label'      => 'Gestion de la paie',
    'permission' => 'paie.bulletins.read',
    'icon'       => 'fa-solid fa-money-check-dollar',
    'active'     => ['paie.*', 'credits.*'],
    'links' => [

        [
            'type'       => 'group',
            'label'      => 'États de présence',
            'icon'       => 'fa-solid fa-calendar-check',
            'permission' => 'paie.etats_presence.read',
            'active' => [
                'paie.edition',
                'paie.avance-tabaski',
                'paie.retenue-tabaski',
                'paie.retenues-rappel',
                'paie.etats-presence',
                'paie.exemptions',
                'paie.fermeture-periode',
                'paie.sommes-percues',
                'paie.elements-saisie-dashboard',
                'paie.non-generee',
            ],
            'links' => [
                ['label' => 'Édition',                   'route' => 'paie.edition',                'icon' => 'fa-solid fa-pen-to-square',        'permission' => 'paie.edition.read'],
                ['label' => 'Avance Tabaski',                            'route' => 'paie.avance-tabaski',          'icon' => 'fa-solid fa-hand-holding-dollar', 'permission' => 'paie.avance_tabaski.read'],
                ['label' => 'Retenue Tabaski',                           'route' => 'paie.retenue-tabaski',         'icon' => 'fa-solid fa-money-bill-transfer', 'permission' => 'paie.retenue_tabaski.read'],
                ['label' => 'Retenue sur rappel',                        'route' => 'paie.retenues-rappel',         'icon' => 'fa-solid fa-clock-rotate-left',   'permission' => 'paie.retenue_rappel.read'],
                ['label' => 'Saisir le nombre de jours travaillés',      'route' => 'paie.etats-presence',          'icon' => 'fa-solid fa-calendar-check',      'permission' => 'paie.jours_travailles.read'],
                ['label' => 'Exempter un enseignant d\'une avance',      'route' => 'paie.exemptions',              'icon' => 'fa-solid fa-user-shield',         'permission' => 'paie.exemptions.read'],
                ['label' => 'Clôture de la paie',                        'route' => 'paie.fermeture-periode',       'icon' => 'fa-solid fa-lock',                'permission' => 'paie.cloture.read'],
                ['label' => 'Sommes perçues',                            'route' => 'paie.sommes-percues',          'icon' => 'fa-solid fa-wallet',              'permission' => 'paie.sommes_percues.read'],
                ['label' => 'Génération des heures supplémentaires',     'route' => 'paie.elements-saisie-dashboard','icon' => 'fa-solid fa-clock',               'permission' => 'paie.heures_sup.read'],
                ['label' => 'Génération individuelle de la paie',        'route' => 'paie.non-generee',             'icon' => 'fa-solid fa-user-check',          'permission' => 'paie.generation_individuelle.read'],
            ],
        ],
        ['label' => 'Travaux périodiques',       'route' => 'paie.travaux-periodiques',    'icon' => 'fa-solid fa-list-check',           'permission' => 'paie.travaux_periodiques.read'],
        ['label' => 'Récapitulatif par banque',  'route' => 'paie.recap-banque',           'icon' => 'fa-solid fa-building-columns',     'permission' => 'paie.recap_banque.read'],
        ['label' => 'Cotisations sociales',      'route' => 'paie.cotisations-sociales',   'icon' => 'fa-solid fa-people-group',         'permission' => 'paie.cotisations.read'],
        ['label' => 'État des salaires',         'route' => 'paie.etat-salaires',          'icon' => 'fa-solid fa-file-invoice-dollar',  'permission' => 'paie.etat_salaires.read'],
        ['label' => 'Paie générée par IEF',      'route' => 'paie.generee-ief',            'icon' => 'fa-solid fa-sitemap',              'permission' => 'paie.effectifs_ief.read'],
        ['label' => 'Salaires par banque',       'route' => 'paie.edition-salaires-banque','icon' => 'fa-solid fa-building-columns',     'permission' => 'paie.salaires_banque.read'],
        ['label' => 'Bulletins des salaires',    'route' => 'paie.bulletins',              'icon' => 'fa-solid fa-file-lines',           'permission' => 'paie.bulletins_salaires.read'],
        ['label' => 'Effectifs par corps',       'route' => 'paie.effectifs-corps',        'icon' => 'fa-solid fa-users',                'permission' => 'paie.effectifs_corps.read'],
        ['label' => 'Délégation de crédit',      'route' => 'credits.delegation',          'icon' => 'fa-solid fa-scale-balanced',       'permission' => 'paie.delegations.read'],
        ['label' => 'Édition des délégations',   'route' => 'credits.edition-delegations', 'icon' => 'fa-solid fa-file-signature',       'permission' => 'paie.delegations.edit'],
        ['label' => 'Édition des engagements',   'route' => 'credits.edition-engagements', 'icon' => 'fa-solid fa-clipboard-check',      'permission' => 'paie.engagements.edit'], 
    ],
],
    [
        'type' => 'group',
        'label' => 'Gestion des indemnités',
        'icon' => 'fa-solid fa-coins',
        'active' => 'indemnites.*',
        'links' => [
            ['label' => 'Mes pièces', 'route' => 'indemnites.mes-pieces', 'icon' => 'fa-solid fa-folder-open', 'permission' => 'mes_pieces.read', 'enseignant_requis' => true],
            ['label' => 'Convocations', 'route' => 'indemnites.convocations', 'icon' => 'fa-solid fa-calendar-check', 'permission' => ['indemnites.read', 'convocations.read']],
            ['label' => 'Pièces justificatives', 'route' => 'indemnites.pieces-justificatives', 'icon' => 'fa-solid fa-folder-open', 'permission' => ['indemnites.read', 'pieces.read']],
            ['label' => 'Frais de déplacement', 'route' => 'indemnites.frais-deplacement', 'icon' => 'fa-solid fa-route', 'permission' => 'indemnites.read'],
            [
                'type' => 'group',
                'label' => 'Indemnité',
                'icon' => 'fa-solid fa-hand-holding-dollar',
                'active' => ['indemnites.calcul*', 'indemnites.calcul-surveillance*'],
                'links' => [
                    ['label' => 'Indemnités de correction', 'route' => 'indemnites.calcul', 'icon' => 'fa-solid fa-calculator', 'permission' => 'indemnites.read'],
                    ['label' => 'Indemnité de surveillance', 'route' => 'indemnites.calcul-surveillance', 'icon' => 'fa-solid fa-user-shield', 'permission' => 'indemnites.read'],
                ],
            ],
            ['label' => 'États de paie', 'route' => 'indemnites.etats-paie', 'icon' => 'fa-solid fa-file-export', 'permission' => 'indemnites.read'],
        ],
    ],
  
    [
        'type' => 'group',
        'label' => 'Paramétrage',
        'icon' => 'fa-solid fa-gears',
        'active' => ['parametres.*', 'systeme.*'],
        'links' => [
            ['label' => 'IA', 'route' => 'parametres.ia.index', 'icon' => 'fa-solid fa-building-columns', 'permission' => 'parametrage.ia.read'],
            ['label' => 'IEF', 'route' => 'parametres.ief.index', 'icon' => 'fa-solid fa-sitemap', 'permission' => 'parametrage.ief.read'],
            ['label' => 'Diplômes', 'route' => 'parametres.diplomes.index', 'icon' => 'fa-solid fa-graduation-cap', 'permission' => 'parametrage.diplomes.read'],
           // ['label' => 'Enseignants', 'route' => 'enseignants.index', 'icon' => 'fa-solid fa-chalkboard-user', 'permission' => 'enseignants.read'],
            ['label' => 'Corps', 'route' => 'parametres.corps.index', 'icon' => 'fa-solid fa-users-line', 'permission' => 'parametrage.corps.read'],
            ['label' => 'Catégories', 'route' => 'parametres.categories.index', 'icon' => 'fa-solid fa-layer-group', 'permission' => 'parametrage.categories.read'],
            ['label' => 'Banques', 'route' => 'parametres.institutions-financieres', 'icon' => 'fa-solid fa-building-columns', 'permission' => 'parametrage.institutions_financieres.read'],
            ['label' => 'Spécialités', 'route' => 'parametres.disciplines.index', 'icon' => 'fa-solid fa-book-open', 'permission' => 'parametrage.disciplines.read'],
            ['label' => 'Syndicats', 'route' => 'parametres.syndicats.index', 'icon' => 'fa-solid fa-handshake', 'permission' => 'parametrage.syndicats.read'],
            ['label' => 'Année académique', 'route' => 'parametres.annees-academiques.index', 'icon' => 'fa-solid fa-calendar-days', 'permission' => 'parametrage.annees_academiques.read'],
            ['label' => 'Période de paie', 'route' => 'parametres.periodes-paie.index', 'icon' => 'fa-solid fa-calendar-week', 'permission' => 'parametrage.periodes_paie.read'],
            ['label' => 'Rubriques de paie', 'route' => 'parametres.rubriques-paie.index', 'icon' => 'fa-solid fa-list-ul', 'permission' => 'paie.rubriques.read'],
            ['label' => 'Établissements', 'route' => 'parametres.lieux-service.index', 'icon' => 'fa-solid fa-location-dot', 'permission' => 'parametrage.lieux_service.read'],
            ['label' => 'Régions', 'route' => 'parametres.regions.index', 'icon' => 'fa-solid fa-map-location-dot', 'permission' => 'parametrage.regions.read'],
            ['label' => 'Départements', 'route' => 'parametres.departements.index', 'icon' => 'fa-solid fa-map-location-dot', 'permission' => 'parametrage.departements.read'],
            ['label' => 'Communes', 'route' => 'parametres.communes.index', 'icon' => 'fa-solid fa-map-location-dot', 'permission' => 'parametrage.communes.read'],
            ['label' => 'Import des données', 'route' => 'systeme.import', 'icon' => 'fa-solid fa-file-import', 'permission' => 'systeme.import'],
        ],
    ],
    [
        'type' => 'group',
        'label' => 'Gestion utilisateur',
        'icon' => 'fa-solid fa-users-gear',
        'active' => ['utilisateurs.*', 'admin.roles.*', 'admin.type-roles.*', 'admin.permissions.*'],
        'links' => [
            ['label' => 'Utilisateurs', 'route' => 'utilisateurs.index', 'icon' => 'fa-solid fa-user-shield', 'permission' => 'administration.users.read'],
            ['label' => 'Créer un compte enseignant', 'route' => 'utilisateurs.teacher.create', 'icon' => 'fa-solid fa-user-plus', 'permission' => 'administration.users.create'],
            ['label' => 'Profils / Rôles', 'route' => 'utilisateurs.profils-roles', 'icon' => 'fa-solid fa-id-badge', 'permission' => 'administration.roles.read'],
            ['label' => 'Permissions', 'route' => 'utilisateurs.permissions', 'icon' => 'fa-solid fa-key', 'permission' => 'administration.permissions.read'],
        ],
    ],
];
