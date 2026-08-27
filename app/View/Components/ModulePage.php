<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModulePage extends Component
{
    /** @var array<string, mixed> */
    public array $page;

    public string $pageIcon;

    /** @var array<string, string> */
    public array $statIconMap;

    public function __construct(public string $slug, public array $roles = [])
    {
        $page = config("module-pages.{$slug}");

        abort_unless(is_array($page), 404, 'Page SICORE introuvable.');

        $this->page = $page;
        $this->statIconMap = $this->statIcons();
        $pageIcon = (string) ($this->page['icon'] ?? '');
        $this->pageIcon = $this->pageIcons()[$this->slug]
            ?? (str_contains($pageIcon, 'fa-') ? $pageIcon : ($this->statIconMap[$pageIcon] ?? 'fa-solid fa-circle'));
    }

    public function render(): View
    {
        return view('components.module-page');
    }

    /** @return array<string, string> */
    private function pageIcons(): array
    {
        return [
            'paie-etats-presence' => 'fa-solid fa-clipboard-user',
            'paie-avance-tabaski' => 'fa-solid fa-hand-holding-dollar',
            'paie-retenue-tabaski' => 'fa-solid fa-money-bill-transfer',
            'paie-retenues-rappel' => 'fa-solid fa-clock-rotate-left',
            'paie-exemptions' => 'fa-solid fa-user-shield',
            'paie-travaux-periodiques' => 'fa-solid fa-gears',
            'paie-recap-banque' => 'fa-solid fa-building-columns',
            'paie-cotisations-sociales' => 'fa-solid fa-people-group',
            'paie-etat-salaires' => 'fa-solid fa-file-invoice-dollar',
            'paie-elements-saisie-dashboard' => 'fa-solid fa-chart-line',
            'paie-generee-ief' => 'fa-solid fa-sitemap',
            'paie-fermeture-periode' => 'fa-solid fa-lock',
            'paie-edition-salaires-banque' => 'fa-solid fa-building-columns',
            'paie-bulletins' => 'fa-solid fa-file-lines',
            'paie-effectifs-corps' => 'fa-solid fa-users',
            'paie-non-generee' => 'fa-solid fa-triangle-exclamation',
            'paie-sommes-percues' => 'fa-solid fa-wallet',
            'credit-delegation' => 'fa-solid fa-scale-balanced',
            'credit-edition-delegations' => 'fa-solid fa-file-signature',
            'credit-edition-engagements' => 'fa-solid fa-clipboard-check',
            'indemnites-convocations' => 'fa-solid fa-calendar-check',
            'indemnites-services-faits' => 'fa-solid fa-list-check',
            'indemnites-pieces-justificatives' => 'fa-solid fa-folder-open',
            'indemnites-accuses-reception' => 'fa-solid fa-receipt',
            'indemnites-calcul' => 'fa-solid fa-calculator',
            'indemnites-frais-deplacement' => 'fa-solid fa-route',
            'indemnites-etats-paie' => 'fa-solid fa-file-export',
            'bourses-enregistrer-demande' => 'fa-solid fa-file-circle-plus',
            'bourses-valider-dossier' => 'fa-solid fa-circle-check',
            'bourses-attribuer-aide' => 'fa-solid fa-hand-holding-heart',
            'utilisateurs' => 'fa-solid fa-user-shield',
            'profils-roles' => 'fa-solid fa-id-badge',
            'permissions' => 'fa-solid fa-key',
        ];
    }

    /** @return array<string, string> */
    private function statIcons(): array
    {
        return [
            'AB' => 'fa-solid fa-user-xmark',
            'AD' => 'fa-solid fa-user-tie',
            'AR' => 'fa-solid fa-receipt',
            'AT' => 'fa-solid fa-clock',
            'BQ' => 'fa-solid fa-building-columns',
            'BR' => 'fa-solid fa-sack-dollar',
            'BS' => 'fa-solid fa-file-lines',
            'CI' => 'fa-solid fa-calculator',
            'CS' => 'fa-solid fa-people-group',
            'CT' => 'fa-solid fa-list-check',
            'DC' => 'fa-solid fa-scale-balanced',
            'DS' => 'fa-solid fa-folder-open',
            'EC' => 'fa-solid fa-users',
            'ED' => 'fa-solid fa-file-signature',
            'EG' => 'fa-solid fa-clipboard-check',
            'EN' => 'fa-solid fa-users',
            'EP' => 'fa-solid fa-file-invoice-dollar',
            'ES' => 'fa-solid fa-file-invoice-dollar',
            'EX' => 'fa-solid fa-user-shield',
            'FC' => 'fa-solid fa-money-bill-wave',
            'FP' => 'fa-solid fa-lock',
            'GC' => 'fa-solid fa-calendar-check',
            'MD' => 'fa-solid fa-wallet',
            'ME' => 'fa-solid fa-file-contract',
            'MC' => 'fa-solid fa-coins',
            'NG' => 'fa-solid fa-triangle-exclamation',
            'NP' => 'fa-solid fa-hand-holding-dollar',
            'OK' => 'fa-solid fa-circle-check',
            'PE' => 'fa-solid fa-calendar-week',
            'PI' => 'fa-solid fa-sitemap',
            'PJ' => 'fa-solid fa-folder-open',
            'PR' => 'fa-solid fa-id-badge',
            'PM' => 'fa-solid fa-key',
            'RJ' => 'fa-solid fa-circle-xmark',
            'RR' => 'fa-solid fa-clock-rotate-left',
            'RT' => 'fa-solid fa-money-bill-transfer',
            'SB' => 'fa-solid fa-building-columns',
            'SF' => 'fa-solid fa-list-check',
            'SP' => 'fa-solid fa-wallet',
            'SR' => 'fa-solid fa-scale-balanced',
            'TP' => 'fa-solid fa-gears',
            'VD' => 'fa-solid fa-circle-check',
        ];
    }
}
