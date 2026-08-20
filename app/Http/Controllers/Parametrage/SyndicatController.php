<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SyndicatController extends Controller
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }
// method to display a listing of the syndicats
    public function index(Request $request): View
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

        $stats = [
            'total' => $syndicats->count(),
            'actifs' => $syndicats->filter(fn (array $item): bool => (bool) ($item['est_actif'] ?? false))->count(),
            'inactifs' => $syndicats->reject(fn (array $item): bool => (bool) ($item['est_actif'] ?? false))->count(),
        ];

        $search = trim((string) $request->query('search', ''));
        $statut = (string) $request->query('est_actif', '');

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $syndicats = $syndicats->filter(function (array $item) use ($needle): bool {
                return str_contains(mb_strtolower((string) ($item['code'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($item['libelle'] ?? '')), $needle);
            });
        }

        if (in_array($statut, ['0', '1'], true)) {
            $isActive = $statut === '1';
            $syndicats = $syndicats->filter(
                fn (array $item): bool => (bool) ($item['est_actif'] ?? false) === $isActive,
            );
        }

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $syndicats = new LengthAwarePaginator(
            $syndicats->values()->forPage($currentPage, $perPage),
            $syndicats->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('pages.parametres.syndicats.index', compact(
            'syndicats',
            'apiError',
            'stats',
            'search',
            'statut',
        ));
    }
// method to show the form for creating a new syndicat
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

    /**
     * Options autorisées lors de la création d'une nouvelle association.
     * Les syndicats inactifs restent visibles dans l'historique, mais ne
     * doivent plus pouvoir être sélectionnés pour une nouvelle adhésion.
     */
    public function associationOptions(): JsonResponse
    {
        try {
            $response = $this->apiClient->get('syndicats');
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Le service backend est momentanément indisponible.',
                'data' => [],
            ], 503);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => $response->json('message') ?? 'Impossible de charger les syndicats.',
                'data' => [],
            ], $response->status());
        }

        $syndicats = collect($response->json('data') ?? $response->json() ?? [])
            ->filter(fn (array $syndicat): bool => (bool) ($syndicat['est_actif'] ?? false))
            ->map(fn (array $syndicat): array => [
                'id' => $syndicat['id'],
                'code' => $syndicat['code'],
                'libelle' => $syndicat['libelle'],
            ])
            ->values();

        return response()->json(['data' => $syndicats]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:100'],
            'montant_check_off' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
            'montant_oeuvre_sociale' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999.99'],
            'est_actif' => ['required', 'boolean'],
        ]);

        $validated['libelle'] = trim($validated['libelle']);
        $validated['est_actif'] = (bool) $validated['est_actif'];

        try {
            $response = $this->apiClient->put("syndicats/{$id}", $validated);
        } catch (ConnectionException) {
            return back()->withInput()->withErrors(['api' => 'Le service backend est momentanément indisponible.']);
        }

        if ($response->successful()) {
            return redirect()->route('parametres.syndicats.index')
                ->with('success', $response->json('message') ?? 'Syndicat modifié avec succès.');
        }

        return back()->withInput()->withErrors(
            $response->json('errors') ?? ['api' => $response->json('message') ?? 'Erreur lors de la modification.'],
        );
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $response = $this->apiClient->delete("syndicats/{$id}");
        } catch (ConnectionException) {
            return back()->with('error', 'Le service backend est momentanément indisponible.');
        }

        if ($response->successful()) {
            return redirect()->route('parametres.syndicats.index')
                ->with('success', 'Syndicat supprimé avec succès.');
        }

        return back()->with(
            'error',
            $response->json('message') ?? 'Erreur lors de la suppression du syndicat.',
        );
    }

}
