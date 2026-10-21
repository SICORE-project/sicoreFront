<?php

namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use App\Services\Organisation\InterfaceAccess;
use App\Services\Organisation\OrganisationContext;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IaWorkspaceController extends Controller
{
    public function __construct(private ApiClient $api, private OrganisationContext $organisation) {}

    private function filters(Request $request): array
    {
        abort_unless(app(InterfaceAccess::class)->isIa(), 403);
        $scope = $this->organisation->query();
        if ($request->has('ia_id')) {
            abort_unless(is_scalar($request->input('ia_id')) && (string) $request->input('ia_id') === (string) ($scope['ia_id'] ?? ''), 403);
        }

        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1'],
            'period_id' => ['nullable', 'integer', 'min:1'], 'ief_id' => ['nullable', 'integer', 'min:1'],
            'lieu_service_id' => ['nullable', 'integer', 'min:1'],
            'engagement' => ['nullable', 'in:non_fonctionnaires'],
            'academic_year_id' => ['nullable', 'integer'], 'corps_id' => ['nullable', 'integer'],
            'matricule' => ['nullable', 'string', 'max:50'], 'payment_place_id' => ['nullable', 'integer'],
            'training_center_id' => ['nullable', 'integer'], 'tabaski_only' => ['nullable', 'boolean'],
            'with_signature' => ['nullable', 'boolean'], 'without_service_done' => ['nullable', 'boolean'],
            'dage_signatory' => ['nullable', 'boolean'],
        ]);
    }

    private function get(string $endpoint, array $filters): array
    {
        try {
            $response = $this->api->get($endpoint, $filters);
            abort_if(in_array($response->status(), [401, 403, 404, 422]), $response->status());
            if ($response->successful() && is_array($response->json())) return [$response->json(), null];
        } catch (ConnectionException) {
            // Garder la page disponible sans inventer de données.
        }

        return [[], 'Les données sont momentanément indisponibles. Veuillez réessayer.'];
    }

    public function teachers(Request $request): View
    {
        $filters = $this->filters($request);
        [$data, $error] = $this->get('ia/enseignants', $filters);
        [$references, $referenceError] = $this->get('ia/referentiels', []);
        return view('pages.ia.teachers', [
            'data' => $data, 'error' => $error ?: $referenceError, 'references' => $references['data'] ?? [],
            'filters' => $filters, 'scopeLabel' => $this->organisation->label(),
        ]);
    }

    public function teacher(Request $request, int $id): View
    {
        [$data, $error] = $this->get('ia/enseignants/'.$id, $this->filters($request));
        return view('pages.ia.detail', ['record' => $data['data'] ?? [], 'error' => $error,
            'title' => 'Dossier enseignant', 'backRoute' => 'enseignants.index', 'scopeLabel' => $this->organisation->label(),
            'fields' => ['matricule' => 'Matricule', 'prenom' => 'Prénom', 'nom' => 'Nom', 'statut' => 'Statut',
                'type_engagement' => 'Engagement', 'date_prise_service' => 'Date de prise de service']]);
    }

    public function payroll(Request $request, string $slug): View
    {
        $filters = $this->filters($request);
        [$response, $error] = $this->get('ia/payroll/pages/'.$slug, $filters);
        $moduleData = $response['data'] ?? ['stats' => [], 'filters' => [], 'actions' => [], 'columns' => [], 'rows' => []];
        foreach ($moduleData['actions'] as &$action) {
            if (($action['code'] ?? '') === 'export') {
                $action['url'] = route('ia.payroll.export', array_merge($filters, ['slug' => $slug]));
            }
        }
        unset($action);
        foreach ($moduleData['rows'] as &$row) {
            foreach ($row as &$cell) {
                if (! is_array($cell) || ! isset($cell['actions'])) continue;
                foreach ($cell['actions'] as &$action) {
                    if ($action['code'] === 'view-payslip') {
                        $action['url'] = route('ia.payroll.show', ['id' => $action['payload']['payroll_payslip_id']]);
                    }
                }
                unset($action);
            }
            unset($cell);
        }
        unset($row);
        return view('pages.paie.'.\Illuminate\Support\Str::after($slug, 'paie-'), ['moduleData' => $moduleData, 'apiError' => $error]);
    }

    public function payslip(Request $request, int $id): View
    {
        [$response, $error] = $this->get('ia/payroll/payslips/'.$id, $this->filters($request));
        abort_if($error !== null, 503, $error ?? '');
        return view('pages.paie.payslip', ['data' => $response['data']]);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        try {
            $slug = $request->validate(['slug' => ['nullable', 'in:paie-montants-engages-banque,paie-edition-salaires-banque,paie-elements-saisie-dashboard,paie-recap-elements-corps,paie-cumul-enseignants-ief,paie-effectifs-corps,paie-non-generee,paie-edition-enseignants,paie-edition-fonctionnaires,paie-mutuelles-sante,paie-situation-affectations,paie-prime-scolaire,paie-reliquats,paie-double-flux,paie-directeurs-interim,paie-heures-supplementaires-interim,paie-bulletins,paie-travaux-periodiques,paie-etat-salaires,paie-cotisations-sociales,paie-recap-banque,paie-generee-ief,paie-sommes-percues']])['slug'] ?? null;
            $response = $this->api->get($slug ? 'ia/payroll/pages/'.$slug.'/export' : 'ia/paie/export', $filters);
        } catch (ConnectionException) {
            abort(503, 'Export temporairement indisponible.');
        }
        abort_unless($response->successful(), in_array($response->status(), [401, 403, 422]) ? $response->status() : 503);
        return response($response->body(), 200, ['Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bulletins-ia.csv"', 'Cache-Control' => 'no-store, private']);
    }

    public function references(Request $request): View
    {
        [$data, $error] = $this->get('ia/referentiels', $this->filters($request));
        $isIa = $request->routeIs('ia.structure');
        $ia = $data['data']['ia'] ?? null;
        $items = collect($isIa ? ($ia ? [$ia] : []) : ($data['data']['iefs'] ?? []));
        $search = mb_strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') $items = $items->filter(fn ($item) => str_contains(mb_strtolower(($item['code'] ?? '').' '.$item['libelle']), $search));
        if ($isIa && $request->filled('region_id')) $items = $items->where('region_id', $request->input('region_id'));
        $page = max(1, $request->integer('page', 1));
        $total = $items->count();
        $items = $items->slice(($page - 1) * 10, 10)->values();
        if (! $isIa) $items = $items->map(fn ($item) => array_merge($item, ['ia' => $ia]));

        return view($isIa ? 'pages.parametres.ia-index' : 'pages.parametres.ief', [
            'items' => $items->all(), 'error' => $error, 'scopeLabel' => $this->organisation->label(),
            'pagination' => ['current_page' => $page, 'last_page' => max(1, (int) ceil($total / 10)), 'total' => $total],
            'regions' => isset($ia['region']) ? [$ia['region']] : [], 'regionCount' => isset($ia['region']) ? 1 : 0,
            'ias' => $ia ? [$ia] : [], 'usingDemoData' => false, 'scopedReadOnly' => true,
            'listRoute' => $isIa ? 'ia.structure' : 'ia.iefs',
        ]);
    }

}
