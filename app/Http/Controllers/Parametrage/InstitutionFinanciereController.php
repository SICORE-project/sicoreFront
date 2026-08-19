<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\InstitutionFinanciereService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionFinanciereController extends Controller
{
    public function index(Request $request, InstitutionFinanciereService $service): View|RedirectResponse
    {
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

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
            'code' => ['required', 'string', 'max:30'],
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['required', 'string', 'max:30'],
            'type_institution' => ['required', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'statut' => ['required', 'in:actif,inactif'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L’adresse e-mail doit être valide.',
        ]);

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
            'code' => ['required', 'string', 'max:30'],
            'nom' => ['required', 'string', 'max:255'],
            'sigle' => ['required', 'string', 'max:30'],
            'type_institution' => ['required', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L’adresse e-mail doit être valide.',
        ]);

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
}