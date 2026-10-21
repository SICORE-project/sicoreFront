<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Construit une destination locale et sûre pour reprendre une page de paie
 * après une reconnexion. Aucune URL externe ni aucun autre module n'est admis.
 */
final class PayrollReturnUrl
{
    private const MAX_LENGTH = 2048;

    public static function capture(Request $request): ?string
    {
        $headerTarget = self::sanitize($request->header('X-SICORE-NEXT'));
        if ($headerTarget) {
            return $headerTarget;
        }

        $currentTarget = self::sanitize($request->getRequestUri());
        $refererTarget = self::fromReferer($request);

        // Une exportation ou une action n'est pas un écran à restaurer : le
        // navigateur revient à la page de paie qui a déclenché la requête.
        if (
            ! $request->isMethod('GET')
            || $request->is('paie/export/*')
            || $request->is('paie/actions/*')
        ) {
            return $refererTarget ?? $currentTarget;
        }

        return $currentTarget ?? $refererTarget;
    }

    public static function sanitize(?string $target): ?string
    {
        if (! is_string($target)) {
            return null;
        }

        $target = trim($target);
        if ($target === '' || strlen($target) > self::MAX_LENGTH) {
            return null;
        }

        $decodedTarget = rawurldecode($target);
        if (
            preg_match('/[\x00-\x1F\x7F]/', $decodedTarget)
            || str_contains($decodedTarget, '\\')
            || str_starts_with($decodedTarget, '//')
        ) {
            return null;
        }

        $parts = parse_url($target);
        if ($parts === false) {
            return null;
        }

        foreach (['scheme', 'host', 'port', 'user', 'pass', 'fragment'] as $externalPart) {
            if (array_key_exists($externalPart, $parts)) {
                return null;
            }
        }

        $path = (string) ($parts['path'] ?? '');
        $decodedPath = rawurldecode($path);
        if (
            ($decodedPath !== '/paie' && ! str_starts_with($decodedPath, '/paie/'))
            || str_contains($decodedPath, '//')
            || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $decodedPath)
        ) {
            return null;
        }

        $query = (string) ($parts['query'] ?? '');

        return $path.($query !== '' ? '?'.$query : '');
    }

    private static function fromReferer(Request $request): ?string
    {
        $referer = trim((string) $request->headers->get('referer'));
        if ($referer === '') {
            return null;
        }

        $parts = parse_url($referer);
        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && ! hash_equals(
            mb_strtolower($request->getHost()),
            mb_strtolower((string) $parts['host'])
        )) {
            return null;
        }

        $target = (string) ($parts['path'] ?? '');
        if (isset($parts['query']) && $parts['query'] !== '') {
            $target .= '?'.$parts['query'];
        }

        return self::sanitize($target);
    }
}
