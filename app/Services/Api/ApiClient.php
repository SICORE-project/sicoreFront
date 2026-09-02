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
        // 10 s etait trop juste en developpement : sur Windows, php artisan
        // serve ne traite qu'une requete a la fois, et le hachage bcrypt de la
        // connexion prend deja 1 a 3 s. Deux requetes qui se croisent
        // depassaient le delai, ce qui remontait en erreur 500 cote front.
        $request = Http::acceptJson()
            ->timeout((int) config('services.backend.timeout', 30));


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
}