<?php

return [
    /*
    | Matrice rôle–structure.
    |
    | Les clés correspondent au niveau retourné par l'API des rôles. Un niveau
    | absent de cette matrice ne peut recevoir aucune structure (refus sûr).
    */
    'role_levels' => [
        'systeme' => ['national'],
        'admin_metier' => ['national'],
        'gestionnaire' => ['national', 'ia', 'ief'],
    ],

    // Une règle par slug est prioritaire sur la règle générale du niveau.
    'role_slugs' => [
        'super_admin' => ['national'],
        'admin' => ['national'],
        'gestionnaire_ia' => ['ia'],
        'gestionnaire_ief' => ['ief'],
        'agent_ia' => ['ia'],
        'agent_ief' => ['ief'],
        'ia' => ['ia'],
        'ief' => ['ief'],
        'dage' => ['national'],
        'decpc' => ['national'],
        'drh' => ['national'],
        'gestionnaire_paie' => ['national'],
        'gestionnaire_budget' => ['national'],
    ],
];
