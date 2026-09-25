<?php

namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    public function __construct(protected ApiClient $api) {}

    public function index()
    {
        $dashboard = null;
        $error = null;
        try {
            $response = $this->api->get('dashboard');
            if ($response->unauthorized()) {
                session()->forget(['access_token', 'sicore_user']);
                return redirect()->route('login')->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
            }
            if ($response->successful()) {
                $dashboard = $response->json('data');
            } else {
                $error = $response->forbidden()
                    ? 'Votre compte ne dispose pas d’un profil actif. Contactez votre administrateur.'
                    : 'Les indicateurs sont momentanément indisponibles. Réessayez dans quelques instants.';
            }
        } catch (ConnectionException) {
            $error = 'Le service est momentanément inaccessible. Aucun indicateur n’a pu être chargé.';
        }

        // Only shortcuts authorized by the API are exposed on this dashboard.
        $actions = collect($dashboard['actions'] ?? [])->filter(fn ($action) => Route::has($action['route']))->values()->all();
        if ($dashboard) $dashboard['actions'] = $actions;
        $allowedRoutes = array_merge(['dashboard'], array_column($actions, 'route'));
        $filter = function (array $items) use (&$filter, $allowedRoutes): array {
            $visible = [];
            foreach ($items as $item) {
                if (isset($item['links'])) {
                    $item['links'] = $filter($item['links']);
                    if ($item['links'] !== []) $visible[] = $item;
                } elseif (in_array($item['route'] ?? '', $allowedRoutes, true)) {
                    $visible[] = $item;
                }
            }
            return $visible;
        };
        config()->set('navigation', $filter(config('navigation', [])));

        return view('pages.dashboard.index', compact('dashboard', 'error'));
    }
}
