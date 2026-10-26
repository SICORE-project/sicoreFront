<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Pagination d'un tableau de lignes déjà construit (10 lignes par page par défaut).
 */
class PaginationLignes
{
    public const PAR_PAGE = 10;

    public static function paginer(array $lignes, Request $request, int $parPage = self::PAR_PAGE): LengthAwarePaginator
    {
        $total = count($lignes);
        $derniere = max(1, (int) ceil($total / $parPage));
        $page = min(max(1, (int) $request->query('page', 1)), $derniere);

        return new LengthAwarePaginator(
            array_slice(array_values($lignes), ($page - 1) * $parPage, $parPage),
            $total,
            $parPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
