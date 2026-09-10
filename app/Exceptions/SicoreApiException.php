<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Erreur normalisée reçue lors d'un échange avec le backend SICORE.
 *
 * Cette classe permet aux contrôleurs frontend de récupérer trois informations
 * sans connaître les détails du client HTTP : le message, le statut HTTP et
 * les erreurs de validation éventuelles renvoyées par l'API.
 *
 * Utilisée principalement par :
 * - app/Services/SicoreApi.php ;
 * - app/Http/Controllers/AuthController.php ;
 * - app/Http/Controllers/PayrollController.php.
 */
class SicoreApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $errors  Erreurs indexées par nom de champ.
     */
    public function __construct(
        string $message,
        public readonly int $status = 500,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
