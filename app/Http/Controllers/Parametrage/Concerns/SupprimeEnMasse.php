<?php

namespace App\Http\Controllers\Parametrage\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait SupprimeEnMasse
{
    protected function supprimerSelection(Request $request, callable $supprimer, string $cible, string $routeListe, bool $feminin = true): RedirectResponse
    {
        $e = $feminin ? 'e' : '';
        $retour = redirect()->route($routeListe);
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('ids', [])))));

        if ($ids === []) {
            return $retour->with('error', "Aucun{$e} {$cible} sélectionné{$e}.");
        }

        $supprimees = 0;
        $refus = [];

        foreach ($ids as $id) {
            $resultat = $supprimer($id);

            if ($resultat['success']) {
                $supprimees++;
            } else {
                $refus[] = $resultat['message'] ?? 'échec';
            }
        }

        $message = $supprimees > 0
            ? "{$supprimees} {$cible} supprimé{$e}(s)."
            : "Aucun{$e} des {$cible} sélectionné{$e}s n'a pu être supprimé{$e}.";

        if ($refus !== []) {
            $message .= ' '.count($refus).' refusé(s) : '.implode(' — ', array_unique($refus));
        }

        return $retour->with($supprimees > 0 && $refus === [] ? 'success' : 'error', $message);
    }
}
