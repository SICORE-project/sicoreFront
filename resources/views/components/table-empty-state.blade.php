@props(['error' => false])

<span class="table-empty-state" role="{{ $error ? 'alert' : 'status' }}">
    <span class="table-empty-state-icon" aria-hidden="true"><i class="fa-solid {{ $error ? 'fa-triangle-exclamation' : 'fa-inbox' }}"></i></span>
    <span class="table-empty-state-title">{{ $slot }}</span>
    <span class="table-empty-state-description">{{ $error ? 'Veuillez réessayer de charger la page.' : 'Les résultats s’afficheront ici dès qu’ils seront disponibles.' }}</span>
</span>
