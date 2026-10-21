<?php

/**
 * Formate un montant monétaire de façon uniforme dans toutes les vues SICORE.
 *
 * Exemple : format_fcfa(150000) retourne « 150 000 FCFA ».
 * Le garde function_exists évite une redéclaration pendant certains tests.
 */
if (! function_exists('format_fcfa')) {
    function format_fcfa(int|float|string|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 0, ',', ' ').' FCFA';
    }
}
