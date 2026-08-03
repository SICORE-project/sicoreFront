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
        $request = Http::acceptJson();

        if (session()->has('access_token')) {
            $request->withToken(session('access_token'));
        }

        return $request;
    }

    public function get(string $uri, array $query = [])
    {
        return $this->request()->get($this->baseUrl.'/'.$uri, $query);
    }

    public function post(string $uri, array $data = [])
    {
        return $this->request()->post($this->baseUrl.'/'.$uri, $data);
    }

    public function put(string $uri, array $data = [])
    {
        return $this->request()->put($this->baseUrl.'/'.$uri, $data);
    }

    public function delete(string $uri)
    {
        return $this->request()->delete($this->baseUrl.'/'.$uri);
    }
}