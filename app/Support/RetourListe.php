<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Mémorise la dernière liste consultée (filtres et page compris) pour que
 * « Retour à la liste » ramène exactement là où l'utilisateur se trouvait.
 */
class RetourListe
{
    public static function memoriser(string $cle, Request $request): void
    {
        // Les rafraîchissements AJAX du tableau passent par la même adresse : même résultat.
        session(['retour_liste.'.$cle => $request->fullUrl()]);
    }

    /** Adresse de la dernière liste consultée, à défaut la liste (éventuellement filtrée). */
    public static function url(string $cle, string $route, array $parDefaut = []): string
    {
        return session('retour_liste.'.$cle) ?: route($route, array_filter($parDefaut));
    }
}
