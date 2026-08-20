<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\InspectionAcademieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionAcademieController extends Controller
{
    public function index(Request $request, InspectionAcademieService $service): View|RedirectResponse
    {
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $result['usingDemoData'] = false;
        if ($result['error'] && empty($result['items'])) {
            $result['items'] = [
                ['id' => 1, 'code' => 'IA-DKR', 'libelle' => 'Inspection d’académie de Dakar', 'region' => ['nom' => 'Dakar'], 'responsable' => ['nom' => 'Aminata Diop'], 'telephone' => '33 800 00 01', 'email' => 'ia.dakar@sicore.sn', 'statut' => 'actif'],
                ['id' => 2, 'code' => 'IA-THS', 'libelle' => 'Inspection d’académie de Thiès', 'region' => ['nom' => 'Thiès'], 'responsable' => ['nom' => 'Moussa Fall'], 'telephone' => '33 800 00 02', 'email' => 'ia.thies@sicore.sn', 'statut' => 'actif'],
                ['id' => 3, 'code' => 'IA-SLG', 'libelle' => 'Inspection d’académie de Saint-Louis', 'region' => ['nom' => 'Saint-Louis'], 'responsable' => ['nom' => 'Fatou Ndiaye'], 'telephone' => '33 800 00 03', 'email' => 'ia.saint-louis@sicore.sn', 'statut' => 'inactif'],
            ];
            $result['pagination'] = ['current_page' => 1, 'last_page' => 1, 'total' => count($result['items']), 'per_page' => 10];
            $result['usingDemoData'] = true;
        }

        $items = collect($result['items']);
        $isActive = static function (array $ia): bool {
            $status = data_get($ia, 'statut', data_get($ia, 'status', data_get($ia, 'est_actif', data_get($ia, 'actif'))));
            $status = is_string($status) ? mb_strtolower(trim($status)) : $status;

            return in_array($status, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
        };

        $result['activeCount'] = $items->filter($isActive)->count();
        $result['inactiveCount'] = $items->count() - $result['activeCount'];
        $result['regionCount'] = $items->map(fn (array $ia) => data_get($ia, 'region.libelle', data_get($ia, 'region.nom', data_get($ia, 'region'))))
            ->filter()->unique()->count();

        return view('pages.parametres.ia-index', $result);
    }
}
