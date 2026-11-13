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
    public function __construct(
        private ApiClient $api,
        private OrganisationContext $organisation
    ) {}

    /**
     * Valide les filtres et sécurise le périmètre IA.
     */
    private function filters(Request $request): array
    {
        abort_unless(
            app(InterfaceAccess::class)->isIa(),
            403
        );

        $scope = $this->organisation->query();

        // Empêcher le gestionnaire IA de consulter une autre IA.
        if ($request->has('ia_id')) {
            abort_unless(
                is_scalar($request->input('ia_id'))
                && (string) $request->input('ia_id')
                    === (string) ($scope['ia_id'] ?? ''),
                403
            );
        }

        return $request->validate([

            // Recherche et pagination.
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Période de paie.
            'period_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Structure administrative.
            'ief_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'lieu_service_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Engagement.
            'engagement' => [
                'nullable',
                'in:non_fonctionnaires',
            ],

            // Ancien filtre année académique.
            'academic_year_id' => [
                'nullable',
                'integer',
            ],

            // CORRECTION :
            // Année académique envoyée par paie-edition.
            'annee_academique_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Corps d'enseignant.
            'corps_id' => [
                'nullable',
                'integer',
            ],

            // Ancien filtre statut enseignant.
            'statut_enseignant_id' => [
                'nullable',
                'integer',
            ],

            // CORRECTION :
            // Filtre Vacataire / Contractuel.
            'statuts_enseignant_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Matricule.
            'matricule' => [
                'nullable',
                'string',
                'max:50',
            ],

            // Autres filtres de paie.
            'payment_place_id' => [
                'nullable',
                'integer',
            ],

            'training_center_id' => [
                'nullable',
                'integer',
            ],

            'tabaski_only' => [
                'nullable',
                'boolean',
            ],

            'with_signature' => [
                'nullable',
                'boolean',
            ],

            'without_service_done' => [
                'nullable',
                'boolean',
            ],

            'dage_signatory' => [
                'nullable',
                'boolean',
            ],

        ]);
    }

    /**
     * Exécute une requête GET vers le backend SICORE.
     */
    private function get(
        string $endpoint,
        array $filters
    ): array {
        try {
            $response = $this->api->get(
                $endpoint,
                $filters
            );

            abort_if(
                in_array(
                    $response->status(),
                    [401, 403, 404, 422]
                ),
                $response->status()
            );

            if (
                $response->successful()
                && is_array($response->json())
            ) {
                return [
                    $response->json(),
                    null,
                ];
            }

        } catch (ConnectionException) {
            // Garder la page disponible sans inventer de données.
        }

        return [
            [],
            'Les données sont momentanément indisponibles. Veuillez réessayer.',
        ];
    }

    /**
     * Liste des enseignants du périmètre IA.
     */
    public function teachers(Request $request): View
    {
        $filters = $this->filters($request);

        [$data, $error] = $this->get(
            'ia/enseignants',
            $filters
        );

        [$references, $referenceError] = $this->get(
            'ia/referentiels',
            []
        );

        return view('pages.ia.teachers', [
            'data' => $data,

            'error' => $error ?: $referenceError,

            'references' => $references['data'] ?? [],

            'filters' => $filters,

            'scopeLabel' => $this->organisation->label(),
        ]);
    }

    /**
     * Formulaire de création d'un enseignant.
     */
    public function createTeacher(Request $request): View
    {
        $this->filters($request);

        [$response, $error] = $this->get(
            'ia/teacher-options',
            []
        );

        abort_if(
            $error !== null,
            503,
            $error ?? ''
        );

        $options = $response['data'] ?? [];

        return view(
            'pages.enseignants.index',
            array_merge($options, [

                'items' => [],

                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                ],

                'filterIefs' => $options['iefOptions'] ?? [],

                'error' => null,

                'iaSubmissionForm' => true,

            ])
        );
    }

    /**
     * Soumission d'un nouvel enseignant.
     */
    public function submitTeacher(Request $request)
    {
        $this->filters($request);

        try {
            $response = $this->api->post(
                'ia/teacher-submissions',
                $request->except([
                    '_token',
                    '_method',
                ])
            );

        } catch (ConnectionException $exception) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Le service est indisponible. Veuillez réessayer.'
                );
        }

        if (! $response->successful()) {
            return back()
                ->withInput()
                ->withErrors(
                    $response->json('errors', [])
                )
                ->with(
                    'error',
                    $response->json(
                        'message',
                        'Soumission impossible.'
                    )
                );
        }

        return redirect()
            ->route('ia.teachers.submissions')
            ->with(
                'success',
                $response->json('message')
            );
    }

    /**
     * Liste des soumissions d'enseignants.
     */
    public function submissions(Request $request): View
    {
        $this->filters($request);

        [$response, $error] = $this->get(
            'ia/teacher-submissions',
            $request->only('page')
        );

        return view(
            'pages.enseignants.submissions',
            [
                'data' => $response['data'] ?? [],

                'error' => $error,

                'reviewing' => false,
            ]
        );
    }

    /**
     * Dossier d'un enseignant.
     */
    public function teacher(
        Request $request,
        int $id
    ): View {
        [$data, $error] = $this->get(
            'ia/enseignants/'.$id,
            $this->filters($request)
        );

        return view('pages.ia.teacher-show', [

            'record' => $data['data'] ?? [],

            'error' => $error,

            'title' => 'Dossier enseignant',

            'backRoute' => 'enseignants.index',

            'scopeLabel' => $this->organisation->label(),

            'fields' => [
                'matricule' => 'Matricule',
                'prenom' => 'Prénom',
                'nom' => 'Nom',
                'statut' => 'Statut',
                'type_engagement' => 'Engagement',
                'date_prise_service' => 'Date de prise de service',
            ],

        ]);
    }

    /**
     * Affiche une page de gestion de la paie.
     *
     * CORRECTION :
     * Tous les filtres de paie-edition sont transmis
     * au backend, y compris le statut enseignant
     * et l'année académique.
     */
    public function payroll(
        Request $request,
        string $slug
    ): View {
        // Récupérer les filtres validés.
        $filters = $this->filters($request);

        // Nettoyer uniquement les filtres vides
        // de l'édition de la paie.
        if ($slug === 'paie-edition') {

            $filters = array_filter(
                $filters,
                static fn ($value) =>
                    $value !== null && $value !== ''
            );

        }

        // Transmettre les filtres au backend IA.
        [$response, $error] = $this->get(
            'ia/payroll/pages/'.$slug,
            $filters
        );

        // Récupérer les données retournées par l'API.
        $moduleData = $response['data'] ?? [

            'stats' => [],

            'filters' => [],

            'actions' => [],

            'columns' => [],

            'rows' => [],

        ];

        // Adapter les liens d'export au périmètre IA.
        foreach ($moduleData['actions'] as &$action) {

            if (($action['code'] ?? '') === 'export') {

                $action['url'] = route(
                    'ia.payroll.export',
                    array_merge(
                        $filters,
                        [
                            'slug' => $slug,
                        ]
                    )
                );

            }

        }

        unset($action);

        // Adapter les liens de consultation des bulletins.
        foreach ($moduleData['rows'] as &$row) {

            foreach ($row as &$cell) {

                if (
                    ! is_array($cell)
                    || ! isset($cell['actions'])
                ) {
                    continue;
                }

                foreach ($cell['actions'] as &$action) {

                    if (
                        ($action['code'] ?? '')
                        === 'view-payslip'
                    ) {

                        $action['url'] = route(
                            'ia.payroll.show',
                            [
                                'id' => $action['payload']['payroll_payslip_id'],
                            ]
                        );

                    }

                }

                unset($action);

            }

            unset($cell);

        }

        unset($row);

        // Afficher la page de paie correspondante.
        return view(
            PayrollController::viewFor(
                $slug,
                $request->boolean('document')
            ),
            [
                'moduleData' => $moduleData,

                'apiError' => $error,
            ]
        );
    }

    /**
     * Consultation d'un bulletin de paie.
     */
    public function payslip(
        Request $request,
        int $id
    ): View {
        [$response, $error] = $this->get(
            'ia/payroll/payslips/'.$id,
            $this->filters($request)
        );

        abort_if(
            $error !== null,
            503,
            $error ?? ''
        );

        return view('pages.paie.payslip', [
            'data' => $response['data'],
        ]);
    }

    /**
     * Export CSV des données de paie du périmètre IA.
     */
    public function export(Request $request)
    {
        $filters = $this->filters($request);

        try {

            // CORRECTION :
            // Ajout de paie-edition aux exports autorisés.
            $slug = $request->validate([

                'slug' => [
                    'nullable',

                    'in:paie-edition,'
                    .'paie-montants-engages-banque,'
                    .'paie-edition-salaires-banque,'
                    .'paie-elements-saisie-dashboard,'
                    .'paie-recap-elements-corps,'
                    .'paie-cumul-enseignants-ief,'
                    .'paie-effectifs-corps,'
                    .'paie-non-generee,'
                    .'paie-edition-enseignants,'
                    .'paie-edition-fonctionnaires,'
                    .'paie-mutuelles-sante,'
                    .'paie-situation-affectations,'
                    .'paie-prime-scolaire,'
                    .'paie-reliquats,'
                    .'paie-double-flux,'
                    .'paie-directeurs-interim,'
                    .'paie-heures-supplementaires-interim,'
                    .'paie-bulletins,'
                    .'paie-travaux-periodiques,'
                    .'paie-etat-salaires,'
                    .'paie-cotisations-sociales,'
                    .'paie-recap-banque,'
                    .'paie-generee-ief,'
                    .'paie-sommes-percues',
                ],

            ])['slug'] ?? null;

            // Conserver les filtres utilisés dans le tableau.
            if ($slug === 'paie-edition') {

                $filters = array_filter(
                    $filters,
                    static fn ($value) =>
                        $value !== null && $value !== ''
                );

            }

            $response = $this->api->get(
                $slug
                    ? 'ia/payroll/pages/'.$slug.'/export'
                    : 'ia/paie/export',
                $filters
            );

        } catch (ConnectionException) {

            abort(
                503,
                'Export temporairement indisponible.'
            );

        }

        abort_unless(
            $response->successful(),

            in_array(
                $response->status(),
                [401, 403, 422]
            )
                ? $response->status()
                : 503
        );

        return response(
            $response->body(),
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',

                'Content-Disposition' => 'attachment; filename="bulletins-ia.csv"',

                'Cache-Control' => 'no-store, private',
            ]
        );
    }

    /**
     * Référentiels IA et IEF.
     */
    public function references(Request $request): View
    {
        [$data, $error] = $this->get(
            'ia/referentiels',
            $this->filters($request)
        );

        $isIa = $request->routeIs('ia.structure');

        $ia = $data['data']['ia'] ?? null;

        $items = collect(
            $isIa
                ? ($ia ? [$ia] : [])
                : ($data['data']['iefs'] ?? [])
        );

        // Recherche dans les référentiels.
        $search = mb_strtolower(
            trim(
                (string) $request->input('search', '')
            )
        );

        if ($search !== '') {

            $items = $items->filter(
                fn ($item) => str_contains(
                    mb_strtolower(
                        ($item['code'] ?? '')
                        .' '
                        .$item['libelle']
                    ),
                    $search
                )
            );

        }

        // Filtre région.
        if (
            $isIa
            && $request->filled('region_id')
        ) {

            $items = $items->where(
                'region_id',
                $request->input('region_id')
            );

        }

        // Pagination.
        $page = max(
            1,
            $request->integer('page', 1)
        );

        $total = $items->count();

        $items = $items
            ->slice(
                ($page - 1) * 10,
                10
            )
            ->values();

        // Nombre d'établissements par IEF (liste déjà renvoyée par ia/referentiels).
        $etablissementsParIef = collect($data['data']['etablissements'] ?? [])
            ->whereNotNull('ief_id')
            ->countBy('ief_id');
        $iefsDeLIa = collect($data['data']['iefs'] ?? []);
        $stats = [
            'etablissements' => $iefsDeLIa->sum(fn ($ief) => $etablissementsParIef->get($ief['id'] ?? null, 0)),
            'iefs_sans_etablissement' => $iefsDeLIa->filter(fn ($ief) => ! $etablissementsParIef->get($ief['id'] ?? null))->count(),
        ];

        // Associer les IEF à leur IA.
        if (! $isIa) {

            $items = $items->map(
                fn ($item) => array_merge(
                    $item,
                    [
                        'ia' => $ia,
                        'etablissements_count' => $etablissementsParIef->get($item['id'] ?? null, 0),
                    ]
                )
            );

        }

        return view(
            $isIa
                ? 'pages.parametres.ia-index'
                : 'pages.parametres.ief',
            [

                'items' => $items->all(),

                'stats' => $stats,

                'error' => $error,

                'scopeLabel' => $this->organisation->label(),

                'pagination' => [

                    'current_page' => $page,

                    'last_page' => max(
                        1,
                        (int) ceil($total / 10)
                    ),

                    'total' => $total,

                ],

                'regions' => isset($ia['region'])
                    ? [$ia['region']]
                    : [],

                'regionCount' => isset($ia['region'])
                    ? 1
                    : 0,

                'ias' => $ia
                    ? [$ia]
                    : [],

                'iefs' => $ia
                    ? ($data['data']['iefs'] ?? [])
                    : [],

                'usingDemoData' => false,

                'scopedReadOnly' => true,

                'listRoute' => $isIa
                    ? 'ia.structure'
                    : 'ia.iefs',

            ]
        );
    }
}