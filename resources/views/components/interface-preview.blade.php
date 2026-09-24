@props(['permissions'])
@php
    $access = app(\App\Services\Organisation\InterfaceAccess::class);
    $catalogue = collect($permissions)->flatten(1)->mapWithKeys(fn ($permission) => [
        (string) $permission['id'] => $permission['slug'] ?? $permission['nom'] ?? '',
    ])->all();
    $flattenNavigation = function ($items, $parent = '') use (&$flattenNavigation, $access) {
        $pages = [];
        foreach ($items as $item) {
            $label = $parent === '' ? $item['label'] : $parent.' / '.$item['label'];
            if (isset($item['links'])) {
                $pages = array_merge($pages, $flattenNavigation($item['links'], $label));
            } elseif (($item['route'] ?? '') !== 'dashboard') {
                $pages[] = ['label' => $label, 'permissions' => $access->permissionsForRoute($item['route'])];
            }
        }
        return $pages;
    };
    $pages = $flattenNavigation(config('navigation', []));
@endphp
<section class="objective-card" data-interface-preview data-catalogue='@json($catalogue)' data-pages='@json($pages)' aria-live="polite">
    <h3>Interface associée à ce rôle</h3>
    <p>Les permissions cochées déterminent les modules visibles pour les comptes auxquels vous attribuez ce rôle, dès leur prochaine connexion.</p>
    <ul data-interface-pages><li>Tableau de bord</li></ul>
    <p data-interface-empty>Aucun autre module sélectionné.</p>
    <p>Les droits de création et de modification complètent la consultation. Les dossiers restent limités au périmètre du compte.</p>
</section>
@once
    @push('scripts')
        <script src="{{ asset('assets/js/interface-preview.js') }}" defer></script>
    @endpush
@endonce
