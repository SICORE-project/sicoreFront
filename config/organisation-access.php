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
];
