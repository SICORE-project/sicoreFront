<?php

namespace App\Services\Api;

use App\Services\Organisation\OrganisationContext;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiClient
{
    /**
     * Listes de référence mises en cache : elles changent rarement mais sont
     * redemandées par presque chaque page (une page peut en charger dix).
     * Les données métier (enseignants, paie, convocations...) ne le sont jamais.
     */
    private const CACHED_RESOURCES = [
        'ias', 'iefs', 'regions', 'departements', 'communes', 'statuts-enseignant',
        'corps', 'categories', 'grades', 'diplomes', 'syndicats', 'annees-academiques',
        'types-convocation', 'lieux-service', 'parametrage/disciplines',
        'parametrage/institutions-financieres', 'parametrage/lieux-service',
    ];

    private const CACHE_TTL_SECONDS = 600;

    private const CACHE_VERSION_KEY = 'api-client:cache-version';

    protected string $baseUrl;

    /**
     * Toute écriture envoyée au backend (ajout, modification, suppression,
     * import...) vide le cache des listes : on ne montre jamais une liste périmée.
     */
    public static function clearCacheOnWrites(): void
    {
        Event::listen(ResponseReceived::class, function (ResponseReceived $event): void {
            if (! in_array(strtoupper($event->request->method()), ['GET', 'HEAD'], true)) {
                Cache::forever(self::CACHE_VERSION_KEY, Str::random(12));
            }
        });
    }

    public function __construct()
    {
        $this->baseUrl = config('services.backend.url');
    }

    protected function request()
    {
        $request = Http::acceptJson()
            ->timeout(10);

        if (session()->has('access_token')) {
            $request = $request->withToken(
                session('access_token')
            );
        }

        return $request;
    }

    public function get(string $uri, array $query = [])
    {
        $query = array_merge($query, app(OrganisationContext::class)->query());

        if (! $this->isCachedResource($uri)) {
            return $this->request()->get($this->baseUrl . '/' . $uri, $query);
        }

        // Clé propre à la connexion (jeton) : chacun ne voit que ce que son profil autorise.
        $key = 'api-client:'.Cache::rememberForever(self::CACHE_VERSION_KEY, fn () => Str::random(12))
            .':'.sha1(session('access_token', '').'|'.$uri.'|'.serialize($query));

        if (is_array($cached = Cache::get($key))) {
            return new Response(new Psr7Response($cached['status'], $cached['headers'], $cached['body']));
        }

        $response = $this->request()->get($this->baseUrl . '/' . $uri, $query);

        if ($response->status() === 200) {
            Cache::put($key, [
                'status' => 200,
                'headers' => ['Content-Type' => $response->header('Content-Type')],
                'body' => $response->body(),
            ], self::CACHE_TTL_SECONDS);
        }

        return $response;
    }

    private function isCachedResource(string $uri): bool
    {
        $path = trim(strtok($uri, '?'), '/');

        foreach (self::CACHED_RESOURCES as $resource) {
            if ($path === $resource || str_starts_with($path, $resource.'/')) {
                return true;
            }
        }

        return false;
    }

    public function post(string $uri, array $data = [], int $timeout = 10)
    {
        return $this->request()->timeout($timeout)->post(
            $this->baseUrl . '/' . $uri,
            $data
        );
    }

    public function put(string $uri, array $data = [])
    {
        return $this->request()->put(
            $this->baseUrl . '/' . $uri,
            $data
        );
    }

    
    public function delete(string $uri)
    {
        return $this->request()->delete(
            $this->baseUrl . '/' . $uri
        );
    }


    /**
     * POST multipart/form-data (upload de fichier). $fichiers est un
     * tableau de ['name' => ..., 'contents' => resource|string, 'filename' => ...],
     * $data porte les champs de formulaire additionnels (non-fichier).
     *
     * Utilisée par ConvocationService::deposerFichier() et ::importer() —
     * 
     */
    public function postMultipart(string $uri, array $data = [], array $fichiers = [], int $timeout = 10)
    {
        $request = $this->request()->timeout($timeout);

        foreach ($fichiers as $fichier) {
            $request = $request->attach(
                $fichier['name'],
                $fichier['contents'],
                $fichier['filename'] ?? null
            );
        }

        return $request->post($this->baseUrl.'/'.$uri, $data);
    }



    public function patch(string $uri, array $data = [])
    {
        return $this->request()
            ->patch($this->baseUrl.'/'.$uri, $data);
    }
}