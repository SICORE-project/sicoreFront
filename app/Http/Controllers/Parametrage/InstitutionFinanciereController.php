<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\CompteBancaireEnseignantService;
use App\Services\Parametrage\InstitutionFinanciereService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionFinanciereController extends Controller
{
    public function index(Request $request, InstitutionFinanciereService $service, CompteBancaireEnseignantService $bankAccounts): View|RedirectResponse
    {
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10, [
            'search' => $request->string('search')->trim()->toString(),
            'type_institution' => $request->string('type_institution')->trim()->toString(),
            'est_actif' => $request->has('est_actif') && $request->input('est_actif') !== '' ? $request->boolean('est_actif') : null,
        ]);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $result['teachers'] = $bankAccounts->getTeachers();
        $result['availableInstitutions'] = $service->getAll(1, 100)['items'];

        $items = collect($result['items']);
        $isActive = static function (array $institution): bool {
            $status = data_get($institution, 'statut', data_get($institution, 'status', data_get($institution, 'actif', data_get($institution, 'active', data_get($institution, 'is_active', data_get($institution, 'est_actif'))))));
            $status = is_string($status) ? mb_strtolower(trim($status)) : $status;

            return in_array($status, [true, 1, '1', 'actif', 'active', 'true', 'oui', 'yes'], true);
        };

        $result['activeCount'] = $items->filter($isActive)->count();
        $result['inactiveCount'] = $items->count() - $result['activeCount'];
        $result['typeCount'] = $items->map(fn (array $institution) => data_get($institution, 'type.nom', data_get($institution, 'type_institution', data_get($institution, 'type'))))
            ->filter()->unique()->count();

        return view('pages.parametres.institutions-financieres', $result);
    }
    public function store(Request $request, InstitutionFinanciereService $service): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'nom' => ['required', 'string', 'max:150'],
            'sigle' => ['required', 'string', 'max:30'],
            'type_institution' => ['required', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'statut' => ['required', 'in:actif,inactif'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L’adresse e-mail doit être valide.',
        ]);

        $data['libelle'] = $data['nom'];
        $data['est_actif'] = $data['statut'] === 'actif';
        unset($data['nom'], $data['statut']);

        $result = $service->create($data);

        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            $redirect = back()->withInput()->withErrors($result['errors'] ?? [])->with('institution_form_open', true);
            if (empty($result['errors'])) {
                $redirect->with('error', $result['message']);
            }

            return $redirect;
        }

        return redirect()->route('parametres.institutions-financieres')
            ->with('success', $result['message']);
    }
    public function update(Request $request, string $institution, InstitutionFinanciereService $service): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'nom' => ['required', 'string', 'max:150'],
            'sigle' => ['required', 'string', 'max:30'],
            'type_institution' => ['required', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'statut' => ['required', 'in:actif,inactif'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L’adresse e-mail doit être valide.',
        ]);

        $data['libelle'] = $data['nom'];
        $data['est_actif'] = $data['statut'] === 'actif';
        unset($data['nom'], $data['statut']);

        $result = $service->update($institution, $data);

        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            $redirect = back()->withInput()->withErrors($result['errors'] ?? [])->with('institution_form_open', true);
            if (empty($result['errors'])) {
                $redirect->with('error', $result['message']);
            }

            return $redirect;
        }

        return redirect()->route('parametres.institutions-financieres')
            ->with('success', $result['message']);
    }
    public function updateStatus(Request $request, string $institution, InstitutionFinanciereService $service): RedirectResponse
    {
        $data = $request->validate([
            'est_actif' => ['required', 'boolean'],
        ]);

        $result = $service->updateStatus($institution, (bool) $data['est_actif']);

        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            return back()->with('error', $result['message']);
        }

        return redirect()->route('parametres.institutions-financieres')
            ->with('success', $result['message']);
    }

    public function destroy(Request $request, string $institution, InstitutionFinanciereService $service): RedirectResponse
    {
        $result = $service->delete($institution);

        if ($result['unauthorized'] ?? false) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
        }

        return redirect()->route('parametres.institutions-financieres')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
    public function storeTeacherBankAccount(Request $request, CompteBancaireEnseignantService $service): RedirectResponse
    {
        $data = $request->validateWithBag('bankAccount', [
            'enseignant_id' => ['required', 'integer'],
            'institut_financier_id' => ['required', 'integer'],
            'numero_compte' => ['required', 'string', 'max:100'],
            'rib' => ['required', 'string', 'max:100'],
            'est_actif' => ['required', 'boolean'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'boolean' => 'Le statut du compte bancaire est invalide.',
        ]);

        $teacherId = (int) $data['enseignant_id'];
        unset($data['enseignant_id']);
        $data['est_actif'] = (bool) $data['est_actif'];
        $result = $service->create($teacherId, $data);

        if (! $result['success']) {
            if ($result['unauthorized'] ?? false) {
                $request->session()->forget(['access_token', 'sicore_user']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');
            }

            $redirect = back()->withInput()->withErrors($result['errors'] ?? [], 'bankAccount')->with('bank_account_form_open', true);
            if (empty($result['errors'])) {
                $redirect->with('error', $result['message']);
            }

            return $redirect;
        }

        return redirect()->route('parametres.institutions-financieres')
            ->with('success', $result['message']);
    }
}
