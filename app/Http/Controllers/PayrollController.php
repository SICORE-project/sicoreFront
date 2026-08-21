<?php

namespace App\Http\Controllers;

use App\Contracts\SicoreApiClientInterface;
use App\Exceptions\SicoreApiException;
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
    public function __construct(private readonly SicoreApiClientInterface $api) {}

    /**
     * Charge une page de paie à partir de son slug.
     *
     * Exemple : le slug paie-bulletins appelle le backend, puis ouvre la vue
     * resources/views/pages/paie/bulletins.blade.php.
     */
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        // period_id est le seul filtre transmis dans l'URL de la page.
        $filters = $request->validate([
            'period_id' => ['nullable', 'integer', 'min:1'],
        ]);

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

        // Le préfixe "paie-" est retiré pour retrouver le nom du fichier Blade.
        return view('pages.paie.'.Str::after($slug, 'paie-'), compact(
            'moduleData',
            'apiError'
        ));
    }

    /**
     * Reçoit une action AJAX du fichier public/assets/js/payroll.js et la
     * transmet au backend. La réponse reste au format JSON pour la modale.
     */
    public function action(Request $request, string $action): JsonResponse
    {
        // Une action absente de payroll-forms.php ne peut jamais être appelée.
        abort_unless(array_key_exists($action, config('payroll-forms', [])), 404);
        $payload = $request->except(['_token']);
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
        $filters = $request->validate([
            'period_id' => ['nullable', 'integer', 'min:1'],
        ]);

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
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('warning', 'Votre session est absente, expirée ou révoquée. Reconnectez-vous pour continuer.');
    }
}
