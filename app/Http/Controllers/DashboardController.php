<?php

namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use App\Services\Organisation\OrganisationContext;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected OrganisationContext $organisation,
    ) {}

    public function index(): View
    {
        if (app(\App\Services\Organisation\DrhAccess::class)->isDrh()) {
            return $this->drhDashboard();
        }

        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        if ($access->usesPermissions()) {
            return view('pages.dashboard.workspace', [
                'navigation' => $access->navigation(config('navigation', [])),
                'scopeLabel' => $this->organisation->label(),
            ]);
        }

        $metrics = [];
        $role = session('sicore_user.role_slug') ?: session('sicore_user.role', '');
        $roleSlug = Str::slug(is_string($role) ? $role : '', '_');
        $isGlobalAdmin = in_array($roleSlug, ['admin', 'super_admin', 'administrateur', 'super_administrateur'], true);

        if ($isGlobalAdmin) {
            $metrics = $this->globalAdministrationMetrics();
        } else {
            try {
                $response = $this->api->get('pages.dashboard.index');
                if ($response->successful()) $metrics = $response->json('data', []);
            } catch (ConnectionException) {
                // Le tableau reste disponible avec des valeurs neutres.
            }
        }

        return view('pages.dashboard.index', [
            'metrics' => is_array($metrics) ? $metrics : [],
            'scopeLabel' => $this->organisation->label(),
            'isScoped' => $this->organisation->isScoped(),
            'isGlobalAdmin' => $isGlobalAdmin,
        ]);
    }

    private function drhDashboard(): View
    {
        $metrics = [];
        $error = null;
        $canConsult = app(\App\Services\Organisation\DrhAccess::class)->allowsRoute('enseignants.index');
        if (! $this->organisation->isScoped()) {
            $error = 'Votre périmètre organisationnel doit être défini pour accéder aux dossiers.';
        } elseif (! $canConsult) {
            $error = 'La consultation du personnel ne vous est pas autorisée.';
        } else {
            try {
                $response = $this->api->get('pages.dashboard.index');
                if ($response->successful() && is_array($response->json('data'))) {
                    $metrics = $response->json('data');
                } else {
                    $error = 'Les indicateurs sont indisponibles pour le moment.';
                }
            } catch (ConnectionException) {
                $error = 'Le service est momentanément inaccessible.';
            }
        }

        return view('pages.dashboard.drh', [
            'metrics' => $metrics,
            'error' => $error,
            'canConsult' => $canConsult,
            'scopeLabel' => $this->organisation->isScoped() ? $this->organisation->label() : 'Périmètre non défini',
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
                        || filter_var(data_get($user, 'statut'), FILTER_VALIDATE_BOOLEAN)
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
