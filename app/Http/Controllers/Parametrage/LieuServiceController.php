<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\InspectionAcademieService;
use App\Services\Parametrage\LieuServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LieuServiceController extends Controller
{
    public function index(Request $request, LieuServiceService $service, InspectionAcademieService $academieService): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'ia_id' => ['nullable', 'integer'],
            'ief_id' => ['nullable', 'integer'],
        ]);
        $pageOptions = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50'],
        ]);
        $perPage = (int) ($pageOptions['per_page'] ?? 10);
        $result = $service->getAll((int) ($pageOptions['page'] ?? 1), $perPage, $filters);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        if (! $result['error'] && $result['pagination']['current_page'] > $result['pagination']['last_page']) {
            return redirect()->route('parametres.lieux-service.index', array_merge($filters, [
                'page' => $result['pagination']['last_page'],
                'per_page' => $perPage,
            ]));
        }

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
            'libelle' => ['required', 'string', 'max:100'],
            'ia_id' => ['required', 'integer', 'min:1'],
            'ief_id' => ['required', 'integer', 'min:1'],
            'telephone' => ['nullable', 'string', 'max:20'],
        ], ['required' => 'Le champ :attribute est obligatoire.'], [
            'libelle' => 'nom de l’établissement', 'ia_id' => 'IA', 'ief_id' => 'IEF',
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
            'libelle' => ['required', 'string', 'max:100'],
            'ia_id' => ['required', 'integer', 'min:1'],
            'ief_id' => ['required', 'integer', 'min:1'],
            'telephone' => ['nullable', 'string', 'max:20'],
        ], ['required' => 'Le champ :attribute est obligatoire.'], [
            'libelle' => 'nom de l’établissement', 'ia_id' => 'IA', 'ief_id' => 'IEF',
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

    public function destroy(Request $request, string $lieu, LieuServiceService $service): RedirectResponse
    {
        $result = $service->delete($lieu);

        if ($result['unauthorized'] ?? false) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
        }

        return redirect()->route('parametres.lieux-service.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
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
