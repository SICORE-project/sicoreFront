<?php

namespace App\Contracts;

use Illuminate\Http\Client\Response;

/**
 * Contrat de communication entre le frontend SICORE et son backend.
 *
 * Pourquoi cette interface existe :
 * - les contrôleurs dépendent d'un contrat stable et non d'une classe précise ;
 * - le véritable client HTTP peut être remplacé par un faux pendant les tests ;
 * - toutes les opérations autorisées entre les deux projets sont visibles ici.
 *
 * Implémentation actuelle : app/Services/SicoreApi.php.
 * Liaison Laravel : app/Providers/ApiClientServiceProvider.php.
 */
interface SicoreApiClientInterface
{
    /** Authentifie un utilisateur et renvoie son jeton ainsi que son profil. */
    public function login(string $email, string $password): array;

    /** Relit le profil correspondant au jeton Bearer fourni. */
    public function me(string $token): array;

    /** Révoque le jeton utilisé par la session frontend courante. */
    public function logout(string $token): void;

    /**
     * Charge les données nécessaires à une page du module Paie.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function payrollPage(string $token, string $slug, array $filters = []): array;

    /**
     * Transmet une commande métier de paie au backend.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function payrollAction(
        string $token,
        string $action,
        array $payload,
        string $idempotencyKey
    ): array;

    /**
     * Récupère une réponse CSV brute afin de préserver ses en-têtes HTTP.
     *
     * @param  array<string, mixed>  $filters
     */
    public function payrollExport(string $token, string $slug, array $filters = []): Response;

    /** Charge le détail complet d'un bulletin individuel. */
    public function payslip(string $token, int $payslipId): array;
}
