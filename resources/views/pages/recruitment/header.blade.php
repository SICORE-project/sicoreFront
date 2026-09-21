@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
@endpush
<x-topbar :title="$heading" subtitle="Gestion du personnel > Nouveaux enseignants vacataires" icon="fa-solid fa-user-plus" />
@if ($errors->any())
    <div class="recruitment-errors" role="alert">
        <strong>L’opération n’a pas été validée.</strong>
        <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        <p>Pour un document, sélectionnez à nouveau le fichier avant de réessayer.</p>
    </div>
@endif
