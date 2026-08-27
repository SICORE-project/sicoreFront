<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $payload = $response->json();
        $items = $response->successful() ? data_get($payload, 'data', []) : [];
        $items = is_array($items) ? array_values($items) : [];
        $perPage = 10;
        $page = max(1, $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            array_slice($items, ($page - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $permissions = $paginator->toArray();

        $permissionsError = $response->successful()
            ? null
            : $response->json('message', "Impossible de charger les permissions (HTTP {$response->status()}).");

        return view('pages.administration.permissions', compact('permissions', 'permissionsError'));
    }

    public function create()
    {
        return view('pages.administration.permissions-create', $this->permissionOptions());
    }

    public function show($id)
    {
        $permission = $this->findPermission($id);

        if (!$permission) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission non trouvée.');
        }

        return view('pages.administration.permissions-show', compact('permission'));
    }

    public function store(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/permissions', [
                'nom' => $request->nom,
                'slug' => $this->permissionSlug($request),
                'groupe' => $request->groupe,
                'module' => $request->module,
                'action' => $request->action,
                'description' => $request->description,
                'est_actif' => $request->est_actif ?? true,
            ]);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission créée avec succès.');
        }

        return back()->withErrors($response->json()['errors'] ?? ['error' => 'Erreur lors de la création']);
    }

    public function edit($id)
    {
        $permission = $this->findPermission($id);

        if (!$permission) {
            return redirect()->route('admin.permissions.index')->with('error', 'Permission non trouvée');
        }

        return view('pages.administration.permissions-edit', array_merge(
            compact('permission'),
            $this->permissionOptions()
        ));
    }

    public function types(string $type)
    {
        abort_unless(in_array($type, ['modules', 'groupes'], true), 404);

        $endpoint = $type === 'modules' ? 'permission-modules' : 'permission-groupes';
        $response = Http::withToken(session('access_token'))
                ->get(config('services.backend.url') . '/admin/' . $endpoint);
        if ($response->successful()) {
            $data = $response->successful() ? $response->json('data', []) : [];
            $data = is_array(data_get($data, 'data')) ? data_get($data, 'data') : $data;
            $permissionField = $type === 'modules' ? 'module' : 'groupe';
            $permissions = collect($this->existingPermissions());
            $items = collect(is_array($data) ? $data : [])->map(function (array $item) use ($permissions, $permissionField): array {
                $code = (string) ($item['code'] ?? $item['nom'] ?? $item['libelle'] ?? '');
                $matchingPermissions = $permissions->filter(
                    fn (array $permission): bool => Str::lower((string) ($permission[$permissionField] ?? '')) === Str::lower($code)
                );
                $permissionNames = $matchingPermissions->pluck('nom')->filter()->unique()->values()->all();
                $activeNames = $matchingPermissions->filter(
                    fn (array $permission): bool => filter_var($permission['est_actif'] ?? false, FILTER_VALIDATE_BOOLEAN)
                )->pluck('nom')->filter()->unique()->values()->all();

                return [
                    'name' => $item['nom'] ?? $item['libelle'] ?? $item['code'] ?? '-',
                    'permissions' => $permissionNames,
                    'active_permissions' => $activeNames,
                    'usage_status' => $matchingPermissions->isNotEmpty() ? 'Utilisé' : 'Non utilisé',
                ];
            })->all();
            $typesError = null;

            return view('pages.administration.permission-types.index', compact('type', 'items', 'typesError'));
        }

        $field = $type === 'modules' ? 'module' : 'groupe';
        $permissions = $this->allPermissions();
        $items = collect($permissions)
            ->filter(fn (array $permission): bool => filled($permission[$field] ?? null))
            ->groupBy($field)
            ->map(fn ($entries, $name): array => [
                'name' => $name,
                'permissions' => $entries->pluck('nom')->filter()->unique()->values()->all(),
                'active_permissions' => $entries->where('est_actif', true)->pluck('nom')->filter()->unique()->values()->all(),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $typesError = null;
        return view('pages.administration.permission-types.index', compact('type', 'items', 'typesError'));
    }

    public function createType(string $type)
    {
        abort_unless(in_array($type, ['modules', 'groupes'], true), 404);

        return view('pages.administration.permission-types.create', [
            'type' => $type,
        ]);
    }

    public function prepareType(Request $request, string $type)
    {
        abort_unless(in_array($type, ['modules', 'groupes'], true), 404);

        $data = $request->validate([
            'libelle' => ['required', 'string', 'max:150'],
            'est_actif' => ['required', 'boolean'],
        ]);
        $data['code'] = Str::of($data['libelle'])->ascii()->snake()->limit(100, '')->toString();
        $endpoint = $type === 'modules' ? 'permission-modules' : 'permission-groupes';
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/' . $endpoint, $data);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.types.index', $type)
                ->with('success', ($type === 'modules' ? 'Module' : 'Groupe').' créé avec succès.');
        }

        return back()->withInput()->withErrors(
            $response->json('errors', ['error' => $response->json('message', 'Création impossible.')])
        );
    }

    private function findPermission($id): ?array
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        if (!$response->successful()) {
            return null;
        }

        return collect($response->json('data', []))->first(
            fn (array $permission): bool => (string) ($permission['id'] ?? '') === (string) $id
        );
    }

    private function permissionOptions(): array
    {
        $permissions = collect($this->allPermissions());

        return [
            'modules' => $this->referenceOptions('permission-modules', $permissions->pluck('module')),
            'groupes' => $this->referenceOptions('permission-groupes', $permissions->pluck('groupe')),
            'actions' => $permissions->pluck('action')->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
        ];
    }

    private function referenceOptions(string $endpoint, $fallback): array
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/' . $endpoint);
        $data = $response->successful() ? $response->json('data', []) : [];
        $data = is_array(data_get($data, 'data')) ? data_get($data, 'data') : $data;

        if ($response->successful() && is_array($data)) {
            return collect($data)->map(fn (array $item): array => [
                'value' => $item['code'] ?? $item['nom'] ?? $item['libelle'],
                'label' => $item['libelle'] ?? $item['nom'] ?? $item['code'],
            ])->filter(fn (array $item): bool => filled($item['value']))->values()->all();
        }

        return collect($fallback)->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($value): array => ['value' => $value, 'label' => Str::of($value)->replace(['_', '-'], ' ')->title()->toString()])
            ->values()->all();
    }

    private function allPermissions(): array
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        return $response->successful() && is_array($response->json('data'))
            ? $response->json('data')
            : [];
    }

    private function existingPermissions(): array
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions', ['per_page' => 1000]);
        $data = $response->successful() ? $response->json('data', []) : [];
        $data = is_array(data_get($data, 'data')) ? data_get($data, 'data') : $data;

        return is_array($data) ? array_values($data) : [];
    }

    public function update(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url') . '/admin/permissions/' . $id, [
                'nom' => $request->nom,
                'slug' => $this->permissionSlug($request),
                'groupe' => $request->groupe,
                'module' => $request->module,
                'action' => $request->action,
                'description' => $request->description,
                'est_actif' => $request->est_actif ?? true,
            ]);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission mise à jour avec succès.');
        }

        return back()->withErrors($response->json()['errors'] ?? ['error' => 'Erreur lors de la mise à jour']);
    }

    public function destroy($id)
    {
        $response = Http::withToken(session('access_token'))
            ->delete(config('services.backend.url') . '/admin/permissions/' . $id);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission supprimée avec succès.');
        }

        return back()->with('error', $response->json()['message'] ?? 'Erreur lors de la suppression');
    }

    private function permissionSlug(Request $request): string
    {
        return collect([$request->groupe, $request->module, $request->action])
            ->map(fn ($part): string => Str::of((string) $part)
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_')
                ->toString())
            ->filter()
            ->implode('.');
    }

    public function sync()
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/permissions/sync');

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', $response->json()['message'] ?? 'Permissions synchronisées avec succès.');
        }

        return back()->with('error', 'Erreur lors de la synchronisation');
    }
}
