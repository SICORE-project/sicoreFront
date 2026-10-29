<?php

namespace App\Http\Middleware;

use App\Services\Organisation\InterfaceAccess;
use App\Support\NavigationAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePageAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('sicore_user');
        $route = $request->route()?->getName();

        if (! is_array($user) || ! $route || $route === 'dashboard') {
            return $next($request);
        }

        $entry = $this->entryFor($route);

        if ($entry && ! $this->allowed($entry, $user)) {
            abort(403, "Cette page n'est pas accessible avec votre profil.");
        }

        return $next($request);
    }

    private function allowed(array $entry, array $user): bool
    {
        return NavigationAccess::filter([$entry], $user) !== []
            && app(InterfaceAccess::class)->allowsRoute($entry['route']);
    }

    private function entryFor(string $route): ?array
    {
        $best = null;
        $bestLength = -1;

        foreach ($this->entries(config('navigation', [])) as $entry) {
            $name = $entry['route'];
            $family = str_ends_with($name, '.index') ? substr($name, 0, -5) : null;

            $length = match (true) {
                $name === $route => PHP_INT_MAX,
                $family !== null && str_starts_with($route, $family) => strlen($family),
                default => -1,
            };

            if ($length > $bestLength) {
                $best = $entry;
                $bestLength = $length;
            }
        }

        return $best;
    }

    private function entries(array $items): array
    {
        $flat = [];

        foreach ($items as $item) {
            if (isset($item['links'])) {
                array_push($flat, ...$this->entries($item['links']));
            } elseif (! empty($item['route']) && $item['route'] !== 'dashboard') {
                $flat[] = $item;
            }
        }

        return $flat;
    }
}
