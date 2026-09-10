<?php

/*
|--------------------------------------------------------------------------
| Rapports complémentaires des travaux périodiques
|--------------------------------------------------------------------------
|
| Ces pages restent dans le seul périmètre Paie. Les données réelles,
| colonnes, lignes, statistiques et exports sont fournis par le backend.
|
*/

$report = static fn (string $title, string $icon): array => [
    'title' => $title,
    'icon' => $icon,
    'breadcrumb' => 'Gestion de la paie > Travaux périodiques > '.$title,
    'stats' => [],
    'filters' => [],
    'actions' => [],
    'columns' => ['Information'],
    'rows' => [],
];

return [
    'paie-edition-enseignants' => $report('Édition des enseignants', 'fa-solid fa-chalkboard-user'),
    'paie-prime-scolaire' => $report('Prime scolaire', 'fa-solid fa-graduation-cap'),
    'paie-reliquats' => $report('Reliquats', 'fa-solid fa-clock-rotate-left'),
    'paie-double-flux' => $report('Double flux / encadreur élève-maître', 'fa-solid fa-people-arrows-left-right'),
    'paie-directeurs-interim' => $report('Directeurs par intérim', 'fa-solid fa-user-shield'),
    'paie-cumul-enseignants-ief' => $report('Cumul des enseignants par IEF', 'fa-solid fa-sitemap'),
    'paie-recap-elements-corps' => $report('Récapitulatif des éléments par corps', 'fa-solid fa-layer-group'),
    'paie-edition-fonctionnaires' => $report('Édition des fonctionnaires', 'fa-solid fa-user-tie'),
    'paie-mutuelles-sante' => $report('Édition mutuelle de santé', 'fa-solid fa-heart-pulse'),
    'paie-situation-affectations' => $report('Situation des affectations', 'fa-solid fa-location-dot'),
    'paie-montants-engages-banque' => $report('Montants engagés par banque', 'fa-solid fa-money-bill-transfer'),
    'paie-heures-supplementaires-interim' => $report('Heures supplémentaires / principaux intérimaires', 'fa-solid fa-business-time'),
];
