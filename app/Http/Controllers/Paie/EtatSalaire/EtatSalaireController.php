<?php

namespace App\Http\Controllers\Paie\EtatSalaire;

use App\Exceptions\SicoreApiException;
use App\Http\Controllers\IaWorkspaceController;
use App\Http\Controllers\PayrollController;
use App\Services\Organisation\InterfaceAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page « État des salaires » (/paie/etat-salaires) et son export CSV.
 *
 * Filtres validés ici puis transmis à l'API (EtatSalaireController du backend).
 * Vues : resources/views/pages/paie/etat-salaire/ (index = filtres + tableau,
 * document = état à imprimer ouvert par « Générer l'état »).
 * L'appel API, le périmètre de session et la gestion des erreurs sont hérités
 * de PayrollController.
 */
class EtatSalaireController extends PayrollController
{
    private const SLUG = 'paie-etat-salaires';

    public function index(Request $request): View|RedirectResponse
    {
        if (app(InterfaceAccess::class)->isIa()) {
            return app(IaWorkspaceController::class)->payroll($request, self::SLUG);
        }
        $filters = $this->scopedFilters($this->filters($request));

        try {
            $moduleData = $this->api->payrollPage($this->token($request), self::SLUG, array_filter($filters));
            $apiError = null;
        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                return $this->expired($request);
            }
            $moduleData = [];
            $apiError = $this->apiErrorDetails($request, $exception);
        }

        return view(self::viewFor(self::SLUG, $request->boolean('document')), compact('moduleData', 'apiError'));
    }

    public function download(Request $request): Response
    {
        return $this->downloadCsv($request, self::SLUG, $this->scopedFilters($this->filters($request)));
    }

    /**
     * Critères du formulaire de l'état ; un critère absent de cette liste
     * est ignoré et n'atteint pas l'API.
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return $request->validate([
            'period_id' => ['nullable', 'integer', 'min:1'],
            'academic_year_id' => ['nullable', 'integer', 'min:1'],
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
        ]);
    }
}
