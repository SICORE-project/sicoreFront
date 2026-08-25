<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\AnneeAcademiqueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AnneeAcademiqueController extends Controller
{
    public function index(Request $request, AnneeAcademiqueService $service): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', 'in:active,inactive,closed'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $service->getAll($filters['search'] ?? null);
        if ($result['unauthorized']) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $allItems = collect($result['items']);
        $stats = [
            'total' => $allItems->count(),
            'active' => $allItems->where('est_active', true)->count(),
            'closed' => $allItems->where('est_cloturee', true)->count(),
        ];

        $items = $allItems->when($filters['statut'] ?? null, function ($items, string $status) {
            return $items->filter(fn (array $item): bool => match ($status) {
                'active' => (bool) ($item['est_active'] ?? false),
                'closed' => (bool) ($item['est_cloturee'] ?? false),
                'inactive' => ! ($item['est_active'] ?? false) && ! ($item['est_cloturee'] ?? false),
            });
        })->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('pages.parametres.annees-academiques', [
            'annees' => $paginator,
            'error' => $result['error'],
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request, AnneeAcademiqueService $service): RedirectResponse
    {
        $data = $this->validateData($request);
        $result = $service->create($data);

        if ($result['success']) {
            return redirect()->route('parametres.annees-academiques.index')->with('success', $result['message']);
        }

        return back()->withInput()
            ->with('annee_create_form_open', true)
            ->withErrors($result['errors'] ?: ['api' => $result['message']]);
    }

    public function update(Request $request, string $annee, AnneeAcademiqueService $service): RedirectResponse
    {
        $data = $request->validateWithBag('updateAnnee', $this->rules());
        $result = $service->update($annee, $data);

        if ($result['success']) {
            return redirect()->route('parametres.annees-academiques.index')->with('success', $result['message']);
        }

        return back()->withInput()
            ->with('annee_update_form_open', true)
            ->with('annee_update_id', $annee)
            ->withErrors($result['errors'] ?: ['api' => $result['message']], 'updateAnnee');
    }

    public function activate(string $annee, AnneeAcademiqueService $service): RedirectResponse
    {
        return $this->redirectResult($service->activate($annee));
    }

    public function deactivate(string $annee, AnneeAcademiqueService $service): RedirectResponse
    {
        return $this->redirectResult($service->deactivate($annee));
    }

    public function close(string $annee, AnneeAcademiqueService $service): RedirectResponse
    {
        return $this->redirectResult($service->close($annee));
    }

    public function destroy(string $annee, AnneeAcademiqueService $service): RedirectResponse
    {
        return $this->redirectResult($service->delete($annee));
    }

    private function validateData(Request $request): array
    {
        return $request->validate($this->rules());
    }

    private function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:100'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function redirectResult(array $result): RedirectResponse
    {
        return redirect()->route('parametres.annees-academiques.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
