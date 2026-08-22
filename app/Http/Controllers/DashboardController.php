<?php

namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use App\Services\Organisation\OrganisationContext;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected OrganisationContext $organisation,
    ) {}

    public function index(): View
    {
        $metrics = [];
        try {
            $response = $this->api->get('pages.dashboard.index');
            if ($response->successful()) $metrics = $response->json('data', []);
        } catch (ConnectionException) {
            // Le tableau reste disponible avec des valeurs neutres.
        }

        $roleSlug = (string) session('sicore_user.role_slug', '');
        $isGlobalAdmin = in_array($roleSlug, ['admin', 'super_admin'], true);

        if ($isGlobalAdmin && $metrics === []) {
            $metrics = $this->globalAdministrationMetrics();
        }

        return view('pages.dashboard.index', [
            'metrics' => is_array($metrics) ? $metrics : [],
            'scopeLabel' => $this->organisation->label(),
            'isScoped' => $this->organisation->isScoped(),
            'isGlobalAdmin' => $isGlobalAdmin,
        ]);
    }

    private function globalAdministrationMetrics(): array
    {
        try {
            $users = $this->collection($this->api->get('admin/users/all')->json());
            $roles = $this->collection($this->api->get('admin/roles/all')->json());
            $permissions = $this->collection($this->api->get('admin/permissions/all')->json());

            return [
                'utilisateurs' => count($users),
                'utilisateurs_actifs' => collect($users)->filter(
                    fn (array $user): bool => data_get($user, 'statut') === 'actif'
                )->count(),
                'roles' => count($roles),
                'permissions' => count($permissions),
            ];
        } catch (ConnectionException) {
            return [];
        }
    }

    private function collection(array $response): array
    {
        $items = data_get($response, 'data.data', data_get($response, 'data', []));

        return is_array($items) ? array_values($items) : [];
    }
}
