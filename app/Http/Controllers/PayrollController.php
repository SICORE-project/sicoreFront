<?php

namespace App\Http\Controllers;

use App\Contracts\SicoreApiClientInterface;
use App\Exceptions\SicoreApiException;
use App\Services\Organisation\OrganisationContext;
use App\Support\PayrollReturnUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Point d'entrée frontend de toutes les pages dynamiques de Gestion de la paie.
 *
 * Ce contrôleur ne calcule aucun salaire. Il joue le rôle de BFF
 * (Backend For Frontend) : il reçoit la demande du navigateur, appelle l'API
 * avec le client API, puis fournit la réponse aux vues Blade.
 *
 * Fichiers liés :
 * - routes/web.php pour les URL /paie/* ;
 * - app/Contracts/SicoreApiClientInterface.php pour le contrat des appels ;
 * - app/Services/SicoreApi.php pour leur implémentation HTTP ;
 * - resources/views/pages/paie/*.blade.php pour les pages ;
 * - config/payroll-forms.php pour les formulaires d'action.
 */
class PayrollController extends Controller
{
    /** Le contrat API est résolu par app/Providers/ApiClientServiceProvider.php. */
    public function __construct(
        private readonly SicoreApiClientInterface $api,
        private readonly OrganisationContext $organisationContext,
    ) {}

    /**
     * Charge une page de paie à partir de son slug.
     *
     * Exemple : le slug paie-bulletins appelle le backend, puis ouvre la vue
     * resources/views/pages/paie/bulletins.blade.php.
     */
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        if (app(\App\Services\Organisation\InterfaceAccess::class)->isIa()) {
            return app(IaWorkspaceController::class)->payroll($request, $slug);
        }

         // Traitement spécifique aux délégations de crédits.
    if ($slug === 'paie-delegation-credits') {
        return $this->showDelegationCredits($request);
    }

        $filters = $this->scopedFilters($this->validatedPageFilters($request, $slug));
              

        try {
            // Le backend renvoie statistiques, filtres, colonnes, lignes et actions.
            $moduleData = $this->api->payrollPage(
                $this->token($request),
                $slug,
                array_filter($filters)
            );
            $apiError = null;
        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }
            $moduleData = [];
            $apiError = $this->apiErrorDetails($request, $exception);
        }

        return view(self::viewFor($slug, $request->boolean('document')), compact(
            'moduleData',
            'apiError'
        ));
    }



/**
 * Affiche les délégations de crédits.
 */
private function showDelegationCredits(Request $request): View|RedirectResponse
{
    $filters = $request->validate([
        'annee_academique_id' => ['nullable', 'integer', 'min:1'],
        'periode_paie' => ['nullable', 'string', 'max:100'],
        'reference_lettre' => ['nullable', 'string', 'max:191'],
        'page' => ['nullable', 'integer', 'min:1'],
        'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
    ]);

    $moduleData = [
        'data' => [],
        'meta' => [],
        'stats' => [],
    ];

    $options = [
        'annees_academiques' => [],
        'ias' => [],
        'iefs' => [],
        'lieux_services' => [],
    ];

    $apiError = null;

    try {
        $token = $this->token($request);

        $moduleData = $this->api->delegationCredits(
            $token,
            array_filter(
                $this->scopedFilters($filters),
                static fn ($value) => $value !== null && $value !== ''
            )
        );

        $options = $this->api->delegationCreditOptions($token);

    } catch (SicoreApiException $exception) {
        if ($exception->status === 401) {
            return $this->expired($request);
        }

        $apiError = $this->apiErrorDetails($request, $exception);
    }

    return view('pages.paie.delegation-credits', compact(
        'moduleData',
        'options',
        'apiError'
    ));
}



    /**
     * Le préfixe "paie-" est retiré pour retrouver le nom du fichier Blade.
     * L'état des salaires a son propre dossier : pages/paie/etat-salaire/ ;
     * avec ?document=1, il s'ouvre seul (document à imprimer, sans filtres).
     */
    public static function viewFor(string $slug, bool $document = false): string
    {
        if ($slug === 'paie-etat-salaires') {
            return $document ? 'pages.paie.etat-salaire.document' : 'pages.paie.etat-salaire.index';
        }

        return 'pages.paie.'.Str::after($slug, 'paie-');
    }

    /**
     * Reçoit une action AJAX du fichier public/assets/js/payroll.js et la
     * transmet au backend. La réponse reste au format JSON pour la modale.
     */
    public function action(Request $request, string $action): JsonResponse
    {
        // Une action absente de payroll-forms.php ne peut jamais être appelée.
        abort_unless(array_key_exists($action, config('payroll-forms', [])), 404);
        $payload = $this->scopedFilters($request->except(['_token']));
        // La clé d'idempotence empêche un double traitement après un double clic.
        $key = (string) ($request->header('Idempotency-Key') ?: Str::uuid());

        try {
            $result = $this->api->payrollAction(
                $this->token($request),
                $action,
                $payload,
                $key
            );

            return response()->json($result);
        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expiredJson($request);
            }

            return response()->json([
                'message' => $this->apiErrorMessage($request, $exception),
                'errors' => $exception->errors,
            ], $exception->status);
        }
    }

    /**
     * Télécharge un rapport CSV généré par le backend sans reconstruire le
     * fichier dans le frontend.
     */
    public function export(Request $request, string $slug): Response
    {
        $filters = $this->scopedFilters($this->validatedPageFilters($request, $slug));

        try {
            $apiResponse = $this->api->payrollExport(
                $this->token($request),
                $slug,
                array_filter($filters)
            );
        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }

            return back()->with('error', $this->apiErrorMessage($request, $exception));
        }

        $disposition = $apiResponse->header('Content-Disposition')
            ?: 'attachment; filename="'.$slug.'.csv"';

        return response($apiResponse->body(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * Le reste du module conserve son filtre de période historique. Les
     * critères supplémentaires sont autorisés uniquement pour l'état mensuel
     * des salaires afin de ne pas modifier le comportement des autres pages.
     *
     * @return array<string, mixed>
     */
   
private function validatedPageFilters(
    Request $request,
    string $slug
): array {
    $rules = [
        'period_id' => ['nullable', 'integer', 'min:1'],
    ];

    // Filtres communs à l'état des salaires
    // et à l'édition de la paie.
    if (in_array($slug, ['paie-etat-salaires', 'paie-edition'], true)) {
        $rules += [
            'academic_year_id' => ['nullable', 'integer', 'min:1'],
            'corps_id' => ['nullable', 'integer', 'min:1'],

            // Ancien filtre : conserver pour les autres pages.
            'statut_enseignant_id' => ['nullable', 'integer', 'min:1'],

            'ia_id' => ['nullable', 'integer', 'min:1'],
            'ief_id' => ['nullable', 'integer', 'min:1'],
            'matricule' => ['nullable', 'string', 'max:50'],
            'payment_place_id' => ['nullable', 'integer', 'min:1'],
            'training_center_id' => ['nullable', 'integer', 'min:1'],
            'tabaski_only' => ['nullable', 'boolean'],
            'with_signature' => ['nullable', 'boolean'],
            'without_service_done' => ['nullable', 'boolean'],
            'dage_signatory' => ['nullable', 'boolean'],
        ];
    }

    // CORRECTION : filtres spécifiques à paie-edition.
    if ($slug === 'paie-edition') {
        $rules += [

            // Déclenchement de la recherche.
            'search' => ['nullable', 'boolean'],

            // Filtre Vacataire / Contractuel.
            'statuts_enseignant_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Filtre année académique.
            'annee_academique_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    return $request->validate($rules);
}


    /** Le périmètre de session prime toujours sur les filtres du navigateur. */
    private function scopedFilters(array $filters): array
    {
        return array_replace($filters, $this->organisationContext->query());
    }

    /**
     * Charge les détails d'un bulletin puis affiche
     * resources/views/pages/paie/payslip.blade.php.
     */
    public function payslip(Request $request, int $payslip): View|RedirectResponse
    {
        try {
            $data = $this->api->payslip($this->token($request), $payslip);
        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }

            return redirect()->route('paie.bulletins')
                ->with('error', $this->apiErrorMessage($request, $exception));
        }

        return view('pages.paie.payslip', compact('data'));
    }

    /** Récupère le jeton API conservé dans la session Laravel du frontend. */
    private function token(Request $request): string
    {
        return (string) $request->session()->get('access_token');
    }

    /**
     * Transforme un statut API en explication directement exploitable par
     * l'utilisateur, sans confondre un refus d'accès avec une panne réseau.
     *
     * @return array{title: string, message: string}
     */
    private function apiErrorDetails(Request $request, SicoreApiException $exception): array
    {
        $title = match ($exception->status) {
            403 => 'Accès refusé pour votre rôle',
            404 => 'Donnée demandée introuvable',
            422 => 'Demande non valide',
            429 => 'Trop de tentatives',
            500, 502, 503, 504 => 'Service SICORE indisponible',
            default => 'Chargement des données impossible',
        };

        return [
            'title' => $title,
            'message' => $this->apiErrorMessage($request, $exception),
        ];
    }

    private function apiErrorMessage(Request $request, SicoreApiException $exception): string
    {
        if ($exception->status === 403) {
            $role = trim((string) $request->session()->get('sicore_user.role'));
            $roleContext = $role !== '' ? " avec le rôle « {$role} »" : '';

            return "Votre compte est bien connecté{$roleContext}, mais il n'est pas autorisé à consulter ces données ou à effectuer cette opération de paie. Contactez un administrateur pour vérifier les droits associés à votre rôle.";
        }

        return match ($exception->status) {
            401 => 'Votre session est absente, expirée ou révoquée. Reconnectez-vous pour continuer.',
            404 => 'La donnée demandée n’existe pas ou n’est plus disponible.',
            429 => 'Trop de tentatives ont été effectuées. Patientez quelques instants avant de réessayer.',
            500, 502, 503, 504 => 'Le service SICORE ne répond pas actuellement. Vérifiez que le backend est démarré, puis réessayez.',
            default => $exception->getMessage(),
        };
    }

    /** Nettoie une session expirée et renvoie l'utilisateur vers la connexion. */
    private function expired(Request $request): RedirectResponse
    {
        $returnUrl = PayrollReturnUrl::capture($request);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', array_filter(['next' => $returnUrl]))
            ->with('warning', 'Votre session est absente, expirée ou révoquée. Reconnectez-vous pour continuer.');
    }

    /** Réponse AJAX cohérente avec le retour next des pages HTML. */
    private function expiredJson(Request $request): JsonResponse
    {
        $returnUrl = PayrollReturnUrl::capture($request);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Votre session est absente, expirée ou révoquée. Reconnectez-vous pour continuer.',
            'next' => $returnUrl,
            'login_url' => route('login', array_filter(['next' => $returnUrl])),
        ], 401);
    }



/**
 * Enregistre une délégation de crédits.
 */



/**
 * Modifie une délégation de crédits existante.
 */
/**
 * Enregistre une délégation de crédits.
 */
public function storeDelegationCredit(Request $request): RedirectResponse
{
    $data = $request->validate([
        'annee_academique_id' => ['required', 'integer', 'min:1'],
        'reference_lettre' => ['required', 'string', 'max:191'],
        'objet' => ['required', 'string', 'max:5000'],
        'periode_paie' => ['required', 'string', 'max:100'],
        'date_delegation' => ['required', 'date'],
    ]);

    try {
        $this->api->createDelegationCredit(
            $this->token($request),
            $data
        );

        return redirect()
            ->route('paie.delegation-credits')
            ->with('success', 'Délégation de crédits enregistrée avec succès.');

    } catch (SicoreApiException $exception) {
        if ($exception->status === 401) {
            return $this->expired($request);
        }

        return back()
            ->withInput()
            ->with('error', $this->apiErrorMessage($request, $exception))
            ->withErrors($exception->errors ?? []);
    }
}


/**
 * Modifie une délégation de crédits existante.
 */
public function updateDelegationCredit(
    Request $request,
    int $delegation
): RedirectResponse {
    $data = $request->validate([
        'annee_academique_id' => ['required', 'integer', 'min:1'],
        'reference_lettre' => ['required', 'string', 'max:191'],
        'objet' => ['required', 'string', 'max:5000'],
        'periode_paie' => ['required', 'string', 'max:100'],
        'date_delegation' => ['required', 'date'],
    ]);

    try {
        $this->api->updateDelegationCredit(
            $this->token($request),
            $delegation,
            $data
        );

        return redirect()
            ->route('paie.delegation-credits')
            ->with('success', 'Délégation de crédits modifiée avec succès.');

    } catch (SicoreApiException $exception) {
        if ($exception->status === 401) {
            return $this->expired($request);
        }

        return back()
            ->withInput()
            ->with('error', $this->apiErrorMessage($request, $exception))
            ->withErrors($exception->errors ?? []);
    }
}


/**
 * Supprime une délégation de crédits.
 */
public function destroyDelegationCredit(
    Request $request,
    int $delegation
): RedirectResponse {
    try {
        $this->api->deleteDelegationCredit(
            $this->token($request),
            $delegation
        );

        return redirect()
            ->route('paie.delegation-credits')
            ->with('success', 'Délégation de crédits supprimée avec succès.');

    } catch (SicoreApiException $exception) {
        if ($exception->status === 401) {
            return $this->expired($request);
        }

        return back()
            ->with('error', $this->apiErrorMessage($request, $exception));
    }
}




    /*
    |--------------------------------------------------------------------------
    | Gestion des ventilations des délégations de crédits
    |--------------------------------------------------------------------------
    */

    /**
     * Affiche les ventilations d'une délégation.
     */
    public function showDelegationVentilations(
        Request $request,
        int $delegation
    ): View|RedirectResponse {
        try {
            $token = $this->token($request);

            $result = $this->api->delegationVentilations(
                $token,
                $delegation
            );

            $options = $this->api->delegationVentilationOptions(
                $token,
                $delegation
            );

            return view('pages.paie.delegation-ventilations', [
                'delegation' => $result['delegation'] ?? [],
                'ventilations' => $result['data'] ?? [],
                'stats' => $result['stats'] ?? [],
                'options' => $options,
                'apiError' => null,
            ]);

        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }

            return redirect()
                ->route('paie.delegation-credits')
                ->with(
                    'error',
                    $this->apiErrorMessage($request, $exception)
                );
        }
    }

    /**
     * Enregistre une ventilation.
     */
    public function storeDelegationVentilation(
        Request $request,
        int $delegation
    ): RedirectResponse {
        $data = $this->validateDelegationVentilation($request);

        try {
            $this->api->createDelegationVentilation(
                $this->token($request),
                $delegation,
                $data
            );

            return redirect()
                ->route(
                    'paie.delegation-credits.ventilations',
                    ['delegation' => $delegation]
                )
                ->with('success', 'Ventilation enregistrée avec succès.');

        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    $this->apiErrorMessage($request, $exception)
                )
                ->withErrors($exception->errors ?? []);
        }
    }

    /**
     * Modifie une ventilation.
     */
    public function updateDelegationVentilation(
        Request $request,
        int $delegation,
        int $ventilation
    ): RedirectResponse {
        $data = $this->validateDelegationVentilation($request);

        try {
            $this->api->updateDelegationVentilation(
                $this->token($request),
                $delegation,
                $ventilation,
                $data
            );

            return redirect()
                ->route(
                    'paie.delegation-credits.ventilations',
                    ['delegation' => $delegation]
                )
                ->with('success', 'Ventilation modifiée avec succès.');

        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    $this->apiErrorMessage($request, $exception)
                )
                ->withErrors($exception->errors ?? []);
        }
    }

    /**
     * Supprime une ventilation.
     */
    public function destroyDelegationVentilation(
        Request $request,
        int $delegation,
        int $ventilation
    ): RedirectResponse {
        try {
            $this->api->deleteDelegationVentilation(
                $this->token($request),
                $delegation,
                $ventilation
            );

            return redirect()
                ->route(
                    'paie.delegation-credits.ventilations',
                    ['delegation' => $delegation]
                )
                ->with('success', 'Ventilation supprimée avec succès.');

        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }

            return back()
                ->with(
                    'error',
                    $this->apiErrorMessage($request, $exception)
                );
        }
    }

    /**
     * Validation commune aux formulaires de ventilation.
     */
    private function validateDelegationVentilation(
        Request $request
    ): array {
        return $request->validate([
            'corps_enseignant_id' => [
                'required', 'integer', 'min:1',
            ],
            'ia_id' => [
                'required', 'integer', 'min:1',
            ],
          'ief_id' => ['nullable', 'integer'],
            'centre_execution' => [
                'required', 'string', 'max:255',
            ],
            'budget' => [
                'required', 'string', 'max:255',
            ],
            'activite' => [
                'required', 'string', 'max:255',
            ],
            'imputation_budgetaire' => [
                'required', 'string', 'max:255',
            ],
            'numero_autorisation' => [
                'required', 'string', 'max:255',
            ],
            'numero_carton' => [
                'nullable', 'string', 'max:255',
            ],
            'engagement' => [
                'nullable', 'string', 'max:255',
            ],
            'type_ventilation' => [
                'required', 'in:salaire,prime_scolaire',
            ],
            'montant' => [
                'required', 'numeric', 'gt:0',
                'decimal:0,2',
                'max:9999999999999.99',
            ],
        ]);
    }







}
