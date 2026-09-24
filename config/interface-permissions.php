<?php

// Une correspondance par fonctionnalité, commune à tous les rôles présents et futurs.
$routes = [
    'recruitment.index' => 'recruitment.read',
    'recruitment.show' => 'recruitment.read',
    'recruitment.document' => 'recruitment.read',
    'recruitment.create' => 'recruitment.import',
    'recruitment.preview' => 'recruitment.import',
    'recruitment.store' => 'recruitment.import',
    'recruitment.cancel' => 'recruitment.import',
    'recruitment.template' => 'recruitment.import',
    'recruitment.os' => 'recruitment.import',
    'recruitment.transmit' => 'recruitment.transmit',
    'recruitment.service-form' => 'recruitment.service',
    'recruitment.service' => 'recruitment.service',
    'recruitment.transition' => 'recruitment.transition',
    'recruitment.read' => 'recruitment.read',
    'utilisateurs.index' => 'administration.users.read',
    'utilisateurs.profils-roles' => 'administration.roles.read',
    'utilisateurs.permissions' => 'administration.permissions.read',
    'parametres.ia.index' => 'parametrage.ia.read',
    'parametres.ief.index' => 'parametrage.ief.read',
    'parametres.diplomes.index' => 'parametrage.diplomes.read',
    'parametres.corps.index' => 'parametrage.corps.read',
    'parametres.categories.index' => 'parametrage.categories.read',
    'parametres.institutions-financieres' => 'parametrage.institutions_financieres.read',
    'parametres.disciplines.index' => 'parametrage.specialites.read',
    'parametres.syndicats.index' => 'parametrage.syndicats.read',
    'parametres.lieux-service.index' => 'parametrage.lieux_service.read',
    'parametres.periodes-paie.index' => 'paie.bulletins.read',
    'parametres.rubriques-paie.index' => 'paie.rubriques.read',
];
$walk = function (array $items) use (&$walk, &$routes): void {
    foreach ($items as $item) {
        if (isset($item['links'])) { $walk($item['links']); continue; }
        $route = $item['route'] ?? '';
        if (str_starts_with($route, 'indemnites.')) $routes[$route] = 'indemnites.read';
        if (str_starts_with($route, 'paie.')) $routes[$route] = 'paie.bulletins.read';
    }
};
$walk(require __DIR__.'/navigation.php');
// Consultation des détails et des filtres : aucune permission d'écriture implicite.
foreach ([
    'filtres-options', 'convocations.show', 'convocations.centres.show',
    'convocations.suivi', 'convocations.pdf', 'convocations.enseignants.rechercher',
    'pieces-justificatives.telecharger', 'calcul.groupe', 'calcul-surveillance.groupe',
    'frais-deplacement.show', 'frais-deplacement.calcul-groupe', 'frais-deplacement.pdf',
    'frais-deplacement.justificatifs.telecharger', 'etats-paie.centres',
    'etats-paie.membres', 'etats-paie.membres.rib', 'etats-paie.existants',
] as $route) {
    $routes['indemnites.'.$route] = 'indemnites.read';
}
return ['routes' => $routes, 'resources' => [
    'utilisateurs' => 'administration.users',
    'admin.roles' => 'administration.roles',
    'admin.permissions' => 'administration.permissions',
    'parametres.ia' => 'parametrage.ia',
    'parametres.ief' => 'parametrage.ief',
    'parametres.diplomes' => 'parametrage.diplomes',
    'parametres.corps' => 'parametrage.corps',
    'parametres.categories' => 'parametrage.categories',
    'parametres.lieux-service' => 'parametrage.lieux_service',
]];
