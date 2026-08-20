<?php

namespace App\Services;

use App\Contracts\SicoreApiClientInterface;
use App\Exceptions\SicoreApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Client HTTP unique entre le frontend et le backend SICORE.
 *
 * Tous les chemins appelés ici correspondent à des routes déclarées dans
 * sicoreBack/routes/api.php. Centraliser les appels évite de disperser les URL,
 * les délais, le jeton Bearer et la gestion des erreurs dans les contrôleurs.
 */
class SicoreApi implements SicoreApiClientInterface
{
    /**
     * Authentifie l'utilisateur auprès de POST /api/login.
     *
     * @return array<string, mixed> Jeton et profil renvoyés par le backend.
     */
    public function login(string $email, string $password): array
    {
        $credentials = compact('email', 'password');

        return $this->json(
            $this->guard(fn (): Response => $this->client()->post(
                '/api/login',
                $credentials
            ))
        );
    }

    /**
     * Relit l'utilisateur associé au jeton via GET /api/me.
     *
     * @return array<string, mixed>
     */
    public function me(string $token): array
    {
        return $this->json($this->guard(
            fn (): Response => $this->client($token)->get('/api/me')
        ));
    }

    /** Révoque le jeton avec POST /api/logout. */
    public function logout(string $token): void
    {
        $response = $this->guard(
            fn (): Response => $this->client($token)->post('/api/logout')
        );
        if (! $response->successful() && $response->status() !== 401) {
            $this->json($response);
        }
    }

    /**
     * Récupère les données d'une page de paie : cartes, filtres et tableau.
     *
     * @return array<string, mixed>
     */
    public function payrollPage(string $token, string $slug, array $filters = []): array
    {
        $response = $this->guard(
            fn (): Response => $this->client($token)->get('/api/payroll/pages/'.$slug, $filters)
        );

        return $this->json($response)['data'] ?? [];
    }

    /**
     * Envoie une commande de paie avec une clé interdisant le double traitement.
     *
     * @return array<string, mixed>
     */
    public function payrollAction(
        string $token,
        string $action,
        array $payload,
        string $idempotencyKey
    ): array {
        return $this->json(
            $this->guard(
                fn (): Response => $this->client($token)
                    ->withHeader('Idempotency-Key', $idempotencyKey)
                    ->post('/api/payroll/actions/'.$action, $payload)
            )
        );
    }

    /**
     * Demande un export CSV au backend et conserve la réponse HTTP brute pour
     * transmettre correctement le nom et le contenu du fichier au navigateur.
     */
    public function payrollExport(
        string $token,
        string $slug,
        array $filters = []
    ): Response {
        $response = $this->guard(
            fn (): Response => $this->client($token)
                ->withHeaders(['Accept' => 'text/csv'])
                ->get('/api/payroll/exports/'.$slug, $filters)
        );

        if (! $response->successful()) {
            $this->json($response);
        }

        return $response;
    }

    /**
     * Récupère toutes les informations d'un bulletin individuel.
     *
     * @return array<string, mixed>
     */
    public function payslip(string $token, int $payslipId): array
    {
        $response = $this->guard(
            fn (): Response => $this->client($token)->get('/api/payroll/payslips/'.$payslipId)
        );

        return $this->json($response)['data'] ?? [];
    }

    /**
     * Prépare le client Laravel avec l'URL, les délais et éventuellement le
     * jeton Bearer. Les valeurs viennent de config/sicore.php et du fichier .env.
     */
    private function client(?string $token = null): PendingRequest
    {
        $client = Http::baseUrl(rtrim((string) config('sicore.api.url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('sicore.api.timeout', 10))
            ->connectTimeout((int) config('sicore.api.connect_timeout', 3))
            ->retry(2, 200, throw: false);

        return $token ? $client->withToken($token) : $client;
    }

    /** Transforme une panne réseau en SicoreApiException compréhensible. */
    private function guard(callable $request): Response
    {
        try {
            return $request();
        } catch (ConnectionException) {
            throw new SicoreApiException(
                'Impossible de joindre le backend SICORE.',
                503
            );
        }
    }

    /**
     * Retourne le JSON d'une réponse réussie ou lève une erreur normalisée.
     *
     * @return array<string, mixed>
     */
    private function json(Response $response): array
    {
        if ($response->successful()) {
            return (array) $response->json();
        }

        $payload = (array) $response->json();
        throw new SicoreApiException(
            (string) ($payload['message'] ?? 'Le service SICORE est momentanément indisponible.'),
            $response->status(),
            (array) ($payload['errors'] ?? [])
        );
    }
}
