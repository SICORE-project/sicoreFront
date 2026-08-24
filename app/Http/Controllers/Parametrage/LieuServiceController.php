<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\CompteBancaireEnseignantService;
use App\Services\Parametrage\InspectionAcademieService;
use App\Services\Parametrage\LieuServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LieuServiceController extends Controller
{
    public function index(Request $request, LieuServiceService $service, InspectionAcademieService $academieService, CompteBancaireEnseignantService $enseignantService): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'ia_id' => ['nullable', 'integer'],
            'ief_id' => ['nullable', 'integer'],
            'statut' => ['nullable', 'in:actif,inactif'],
            'sort' => ['nullable', 'in:code,libelle'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10, $filters);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $items = collect($result['items']);
        $isActive = static function (array $item): bool {
            $status = data_get($item, 'statut', data_get($item, 'status', data_get($item, 'est_actif', data_get($item, 'actif', true))));
            $status = is_string($status) ? mb_strtolower(trim($status)) : $status;

            return in_array($status, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
        };
        $isConsistent = static function (array $item): bool {
            $iaId = data_get($item, 'ia.id', data_get($item, 'inspection_academie.id', data_get($item, 'inspection_academie_id')));
            $iefIaId = data_get($item, 'ief.inspection_academie_id', data_get($item, 'ief.ia_id', data_get($item, 'ief.ia.id')));

            if ($iaId === null && $iefIaId === null) {
                return true;
            }
            if ($iaId === null || $iefIaId === null) {
                return false;
            }

            return (string) $iaId === (string) $iefIaId;
        };

        $result['activeCount'] = $items->filter($isActive)->count();
        $result['inactiveCount'] = $items->count() - $result['activeCount'];
        $result['inconsistentCount'] = $items->reject($isConsistent)->count();
        $result['teachers'] = $enseignantService->getTeachers();
        $result['filters'] = $filters;

        $academiesResult = $academieService->getAll(1, 100);
        $result['academies'] = $academiesResult['items'];
        $result['iefs'] = [];
        foreach ($result['academies'] as $academy) {
            $academyId = data_get($academy, 'id', data_get($academy, 'uuid'));
            if ($academyId === null) {
                continue;
            }
            foreach ($academieService->getIefs($academyId)['items'] as $ief) {
                $ief['ia_id'] = data_get($ief, 'ia_id', data_get($ief, 'inspection_academie_id', $academyId));
                $result['iefs'][] = $ief;
            }
        }

        return view('pages.parametres.lieux-service', $result);
    }

    public function store(Request $request, LieuServiceService $service): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'libelle' => ['required', 'string', 'max:255'],
            'ia_id' => ['required'],
            'ief_id' => ['required'],
        ], ['required' => 'Le champ :attribute est obligatoire.'], [
            'code' => 'code', 'libelle' => 'libellé', 'ia_id' => 'IA', 'ief_id' => 'IEF',
        ]);

        $result = $service->create($data);
        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            $redirect = back()->withInput()->withErrors($result['errors'] ?? [])->with('lieu_form_open', true);

            return empty($result['errors']) ? $redirect->with('error', $result['message']) : $redirect;
        }

        return redirect()->route('parametres.lieux-service.index')->with('success', $result['message']);
    }

    public function update(Request $request, string $lieu, LieuServiceService $service): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:30'],
            'libelle' => ['required', 'string', 'max:255'],
            'ia_id' => ['required'],
            'ief_id' => ['required'],
        ], ['required' => 'Le champ :attribute est obligatoire.'], [
            'code' => 'code', 'libelle' => 'libellé', 'ia_id' => 'IA', 'ief_id' => 'IEF',
        ]);
        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator, 'updateLieu')
                ->with(['lieu_edit_form_open' => true, 'lieu_edit_id' => $lieu]);
        }
        $data = $validator->validated();

        $result = $service->update($lieu, $data);
        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            $redirect = back()->withInput()->withErrors($result['errors'] ?? [], 'updateLieu')
                ->with(['lieu_edit_form_open' => true, 'lieu_edit_id' => $lieu]);

            return empty($result['errors']) ? $redirect->with('error', $result['message']) : $redirect;
        }

        return redirect()->route('parametres.lieux-service.index')->with('success', $result['message']);
    }

    public function updateStatus(Request $request, string $lieu, LieuServiceService $service): RedirectResponse
    {
        $validator = Validator::make($request->all(), ['actif' => ['required', 'boolean']], [
            'required' => 'Le statut est obligatoire.',
            'boolean' => 'Le statut transmis est invalide.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'statusLieu');
        }

        $active = $request->boolean('actif');
        $result = $service->updateStatus($lieu, $active);
        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            return back()->with('error', $result['message']);
        }

        return redirect()->route('parametres.lieux-service.index')->with('success', $result['message']);
    }

    public function storeAssignment(Request $request, string $lieu, LieuServiceService $service): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'enseignant_id' => ['required'],
            'date_debut' => ['required', 'date'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'date' => 'La date de début doit être une date valide.',
        ], ['enseignant_id' => 'enseignant', 'date_debut' => 'date de début']);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator, 'affectationLieu')
                ->with(['affectation_form_open' => true, 'affectation_lieu_id' => $lieu]);
        }

        $data = $validator->validated();
        $result = $service->assignTeacher($lieu, $data['enseignant_id'], $data);
        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            $redirect = back()->withInput()->withErrors($result['errors'] ?? [], 'affectationLieu')
                ->with(['affectation_form_open' => true, 'affectation_lieu_id' => $lieu]);

            return empty($result['errors']) ? $redirect->with('error', $result['message']) : $redirect;
        }

        return redirect()->route('parametres.lieux-service.index')->with('success', $result['message']);
    }
}
