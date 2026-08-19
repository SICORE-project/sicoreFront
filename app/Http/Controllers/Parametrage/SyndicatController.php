<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyndicatController extends Controller
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function index(): View
    {
        $syndicats = collect();
        $apiError = null;

        try {
            $response = $this->apiClient->get('syndicats');

            if ($response->successful()) {
                $syndicats = collect($response->json('data') ?? $response->json() ?? []);
            } else {
                $apiError = $response->json('message') ?? 'Impossible de charger les syndicats.';
            }
        } catch (ConnectionException) {
            $apiError = 'Le service backend est momentanément indisponible.';
        }

        return view('pages.parametres.syndicats.index', compact('syndicats', 'apiError'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'libelle' => ['required', 'string', 'max:100'],
            'montant_check_off' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
            'montant_oeuvre_sociale' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
            'est_actif' => ['required', 'boolean'],
        ]);

        $payload = [
            ...$validated,
            'code' => mb_strtoupper(trim($validated['code'])),
            'libelle' => trim($validated['libelle']),
            'est_actif' => (bool) $validated['est_actif'],
        ];

        try {
            $response = $this->apiClient->post('syndicats', $payload);
        } catch (ConnectionException) {
            return back()->withInput()->withErrors([
                'api' => 'Le service backend est momentanément indisponible.',
            ]);
        }

        if ($response->successful()) {
            return redirect()
                ->route('parametres.syndicats.index')
                ->with('success', $response->json('message') ?? 'Syndicat créé avec succès.');
        }

        $errors = $response->json('errors');

        return back()
            ->withInput()
            ->withErrors(is_array($errors) ? $errors : [
                'api' => $response->json('message') ?? 'Erreur lors de la création du syndicat.',
            ]);
    }

    public function checkUniqueness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'in:code,libelle'],
            'value' => ['required', 'string', 'max:100'],
        ]);

        try {
            $response = $this->apiClient->get('syndicats');
        } catch (ConnectionException) {
            return response()->json([
                'available' => null,
                'message' => 'Vérification temporairement indisponible.',
            ], 503);
        }

        if (! $response->successful()) {
            return response()->json([
                'available' => null,
                'message' => 'Vérification temporairement indisponible.',
            ], 503);
        }

        $syndicats = $response->json('data') ?? $response->json() ?? [];
        $field = $validated['field'];
        $value = trim($validated['value']);

        $exists = collect($syndicats)->contains(function (array $syndicat) use ($field, $value): bool {
            return mb_strtolower(trim((string) ($syndicat[$field] ?? '')))
                === mb_strtolower($value);
        });

        return response()->json([
            'available' => ! $exists,
            'message' => $exists
                ? ($field === 'code' ? 'Ce code existe déjà.' : 'Ce libellé existe déjà.')
                : null,
        ]);
    }
}
