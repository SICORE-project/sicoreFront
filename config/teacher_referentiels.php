<?php

return [
    'corps' => [
        'code' => 'required|string|max:50',
        'libelle' => 'required|string|max:255',
    ],
    'categorie' => [
        'libelle' => 'required|string|max:255',
        'corps_id' => 'required|integer|min:1',
        'ordre' => 'nullable|integer|min:0',
        'description' => 'nullable|string',
        'est_actif' => 'required|boolean',
    ],
    'discipline' => [
        'libelle' => 'required|string|max:150',
        'description' => 'nullable|string|max:500',
    ],
    'diplome' => [
        'libelle' => 'required|string|max:100',
        'categorie_id' => 'required|integer|min:1',
        'salaire_brut' => 'required|numeric|min:0',
    ],
    'lieu_service' => [
        'libelle' => 'required|string|max:100',
        'ia_id' => 'required|integer|min:1',
        'ief_id' => 'required|integer|min:1',
        'telephone' => 'nullable|string|max:20',
    ],
    'banque' => [
        'libelle' => 'required|string|max:150',
        'sigle' => 'nullable|string|max:30',
        'type_institution' => 'required|string|max:50',
        'adresse' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:20',
        'email' => 'nullable|email:rfc|max:100',
        'code_banque' => 'nullable|string|max:5',
        'code_guichet' => 'nullable|string|max:5',
    ],
    'ia' => [
        'code' => 'required|string|max:50',
        'libelle' => 'required|string|max:200',
        'region_id' => 'required|integer|min:1',
    ],
    'ief' => [
        'code' => 'required|string|max:20',
        'libelle' => 'required|string|max:100',
        'ia_id' => 'required|integer|min:1',
        'adresse' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:100',
        'responsable' => 'nullable|string|max:100',
        'est_actif' => 'required|boolean',
    ],
];
