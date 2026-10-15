{{--
  Lien « Retour à la liste » : même présentation partout, et retour à la liste
  consultée (filtres et page compris, voir App\Support\RetourListe).
  Usage : <x-retour-liste cle="frais" route="indemnites.frais-deplacement" />
          variante="bouton" pour la rangée d'actions en bas de page.
--}}
@props(['cle', 'route', 'parDefaut' => [], 'variante' => 'lien', 'label' => 'Retour à la liste'])

<a
    href="{{ \App\Support\RetourListe::url($cle, $route, $parDefaut) }}"
    @class(['retour-liste', 'btn-secondary' => $variante === 'bouton'])
>
    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    {{ $label }}
</a>
