<?php

namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use App\Services\Organisation\OrganisationContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected OrganisationContext $organisation,
    ) {}

    public function index(): View|\Symfony\Component\HttpFoundation\Response
    {
        if (app(\App\Services\Organisation\InterfaceAccess::class)->isTeacher()) return app(TeacherPersonalController::class)->page(request());
        if (app(\App\Services\Organisation\InterfaceAccess::class)->isDecpc()) {
            return $this->decpcDashboard();
        }
        $role = session('sicore_user.role_slug') ?: session('sicore_user.role', '');
        if (Str::slug(is_string($role) ? $role : '', '_') === 'gestionnaire_ia') {
            return $this->iaDashboard();
        }

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

        return $this->dashboardView();
    }

    private function decpcDashboard(): View
    {
        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        $metrics = [];
        $error = null;
        $canConsultPersonnel = $access->allowsRoute('enseignants.index');
        $canConsultIndemnites = $access->allows('indemnites.read') && $this->organisation->isScoped();

        if (! $this->organisation->isScoped()) {
            $error = 'Votre périmètre DECPC doit être défini pour accéder aux dossiers.';
        } elseif ($canConsultPersonnel || $canConsultIndemnites) {
            try {
                $response = $this->api->get('decpc/dashboard');
                if ($response->successful() && is_array($response->json('data'))) {
                    $metrics = $response->json('data');
                    foreach ([
                        'total_agents' => 'total_agents',
                        'dossiers_en_attente' => 'indemnites_en_attente',
                        'dossiers_valides' => 'indemnites_validees',
                        'dossiers_rejetes_retournes' => 'indemnites_rejetees',
                        'montant_en_cours' => 'montant_indemnites_en_cours',
                    ] as $displayKey => $apiKey) {
                        $metrics[$displayKey] = data_get($metrics, 'indicateurs.'.$apiKey, $metrics[$displayKey] ?? null);
                    }

                } else {
                    $error = 'Les indicateurs sont indisponibles pour le moment.';
                }
            } catch (ConnectionException) {
                $error = 'Le service est momentanément inaccessible.';
            }
        } else {
            $error = 'Aucune permission de consultation ne vous est attribuée.';
        }

        return view('pages.dashboard.decpc', [
            'metrics' => $metrics,
            'error' => $error,
            'canConsultPersonnel' => $canConsultPersonnel,
            'canConsultIndemnites' => $canConsultIndemnites,
            'scopeLabel' => $this->organisation->label(),
            'navigation' => $access->navigation(config('navigation', [])),
        ]);
    }

    private function iaDashboard(): View
    {
        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        $metrics = [];
        $error = null;

        try {
            $response = $this->api->get('ia/dashboard');
            if ($response->successful() && is_array($response->json('data'))) {
                $metrics = $response->json('data');
            } else {
                $error = match ($response->status()) {
                    401 => 'Votre session a expiré. Veuillez vous reconnecter.',
                    403 => 'Votre accès ou votre rattachement à une IA ne permet pas de consulter ces indicateurs.',
                    default => 'Les indicateurs sont indisponibles pour le moment.',
                };
            }
        } catch (ConnectionException) {
            $error = 'Le service est momentanément inaccessible. Veuillez réessayer.';
        }

        return view('pages.dashboard.ia', [
            'metrics' => $metrics,
            'error' => $error,
            'scopeLabel' => ($name = data_get($metrics, 'ia.libelle'))
                ? (preg_match('/^IA\b/iu', $name) ? $name : 'IA de '.$name) : 'IA non disponible',
            'canConsultPersonnel' => $access->allows('enseignants.read'),
            'canConsultPayroll' => $access->allows('paie.bulletins.read'),
            'canConsultSalaryMass' => $access->allows('paie.masse_salariale.read'),
            'navigation' => $access->navigation(config('navigation', [])),
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
                $response = $this->api->get('drh/dashboard');
                if ($response->successful() && is_array($response->json('data'))) {
                    $metrics = array_merge($response->json('data.indicateurs', []), $response->json('data', []));
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

    private function dashboardView(): View|\Symfony\Component\HttpFoundation\Response
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
