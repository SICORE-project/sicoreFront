<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\LieuServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LieuServiceController extends Controller
{
    public function index(Request $request, LieuServiceService $service): View|RedirectResponse
    {
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $items = collect($result['items']);
        $isActive = static function (array $item): bool {
            $status = data_get($item, 'statut', data_get($item, 'status', data_get($item, 'est_actif', data_get($item, 'actif'))));
            $status = is_string($status) ? mb_strtolower(trim($status)) : $status;

            return in_array($status, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
        };
        $isConsistent = static function (array $item): bool {
            $iaId = data_get($item, 'ia.id', data_get($item, 'inspection_academie.id', data_get($item, 'inspection_academie_id')));
            $iefIaId = data_get($item, 'ief.inspection_academie_id', data_get($item, 'ief.ia_id', data_get($item, 'ief.ia.id')));

            return $iaId === null || $iefIaId === null || (string) $iaId === (string) $iefIaId;
        };

        $result['activeCount'] = $items->filter($isActive)->count();
        $result['inactiveCount'] = $items->count() - $result['activeCount'];
        $result['inconsistentCount'] = $items->reject($isConsistent)->count();

        return view('pages.parametres.lieux-service', $result);
    }
}
