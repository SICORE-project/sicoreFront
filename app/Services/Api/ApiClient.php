<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;

class ApiClient
{
    protected string $baseUrl;

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
        return $this->request()
            ->get(
                $this->baseUrl.'/'.$uri,
                $query
            );
    }


    public function post(string $uri, array $data = [])
    {
        return $this->request()
            ->post(
                $this->baseUrl.'/'.$uri,
                $data
            );
    }


    public function put(string $uri, array $data = [])
    {
        return $this->request()
            ->put(
                $this->baseUrl.'/'.$uri,
                $data
            );
    }


    public function delete(string $uri)
    {
        return $this->request()
            ->delete(
                $this->baseUrl.'/'.$uri
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
    public function postMultipart(string $uri, array $data = [], array $fichiers = [])
    {
        $request = $this->request();

        foreach ($fichiers as $fichier) {
            $request = $request->attach(
                $fichier['name'],
                $fichier['contents'],
                $fichier['filename'] ?? null
            );
        }

        return $request->post($this->baseUrl.'/'.$uri, $data);
    }
}