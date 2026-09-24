@if (isset($pagination['current_page']))
<nav class="ia-filters" aria-label="Pagination">
    <span>{{ $pagination['total'] }} résultat(s) · Page {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}</span>
    @if ($pagination['current_page'] > 1)<a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" rel="prev">Précédent</a>@endif
    @if ($pagination['current_page'] < $pagination['last_page'])<a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" rel="next">Suivant</a>@endif
</nav>
@endif
