<?php

return [
    [
        'type' => 'link',
        'label' => 'Tableau de bord',
        'icon' => 'fa-solid fa-gauge-high',
        'route' => 'dashboard',
    ],
    [
        'type' => 'group',
        'label' => 'Gestion des indemnités',
        'icon' => 'fa-solid fa-coins',
        'active' => 'indemnites.*',
        'links' => [
            ['label' => 'Convocations', 'route' => 'indemnites.convocations', 'icon' => 'fa-solid fa-calendar-check'],
            ['label' => 'Pièces justificatives', 'route' => 'indemnites.pieces-justificatives', 'icon' => 'fa-solid fa-folder-open'],
        ],
    ],
    [
        'type' => 'group',
        'label' => 'Paramétrage',
        'icon' => 'fa-solid fa-gears',
        'active' => 'parametres.*',
        'links' => [
            ['label' => 'IA', 'route' => 'parametres.ia.index', 'icon' => 'fa-solid fa-building-columns'],
            ['label' => 'IEF', 'route' => 'parametres.ief.index', 'icon' => 'fa-solid fa-sitemap'],
            ['label' => 'Diplômes', 'route' => 'parametres.diplomes.index', 'icon' => 'fa-solid fa-graduation-cap'],
            ['label' => 'Corps', 'route' => 'parametres.corps.index', 'icon' => 'fa-solid fa-users-line'],
            ['label' => 'Catégories', 'route' => 'parametres.categories.index', 'icon' => 'fa-solid fa-layer-group'],
            ['label' => 'Institutions financières', 'route' => 'parametres.institutions-financieres', 'icon' => 'fa-solid fa-building-columns'],
            ['label' => 'Disciplines', 'route' => 'parametres.disciplines.index', 'icon' => 'fa-solid fa-book-open'],
            ['label' => 'Syndicats', 'route' => 'parametres.syndicats.index', 'icon' => 'fa-solid fa-handshake'],
            ['label' => 'Année académique', 'route' => 'parametres.annees-academiques.index', 'icon' => 'fa-solid fa-calendar-days'],
            ['label' => 'Période de paie', 'route' => 'parametres.periodes-paie.index', 'icon' => 'fa-solid fa-calendar-week'],
            ['label' => 'Rubriques de paie', 'route' => 'parametres.rubriques-paie.index', 'icon' => 'fa-solid fa-list-ul'],
            ['label' => 'Lieux de service', 'route' => 'parametres.lieux-service.index', 'icon' => 'fa-solid fa-location-dot'],
        ],
    ],
    [
        'type' => 'group',
        'label' => 'Gestion utilisateur',
        'icon' => 'fa-solid fa-users-gear',
        'active' => ['utilisateurs.*'],
        'links' => [
            ['label' => 'Utilisateurs', 'route' => 'utilisateurs.index', 'icon' => 'fa-solid fa-user-shield'],
            ['label' => 'Profils / Rôles', 'route' => 'utilisateurs.profils-roles', 'icon' => 'fa-solid fa-id-badge'],
            ['label' => 'Permissions', 'route' => 'utilisateurs.permissions', 'icon' => 'fa-solid fa-key'],
        ],
    ],
];
