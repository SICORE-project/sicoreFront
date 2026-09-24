@extends('layouts.app')
@section('title', 'SICORE - '.$title)
@section('content')
<main class="main-content">
    <x-topbar :title="$title" :subtitle="'Périmètre : '.$scopeLabel" icon="fa-solid fa-building-columns" />
    <section class="content-area">
        @if ($error)<p class="alert alert-warning" role="alert">{{ $error }}</p>@endif
        <section class="panel">
            <div class="panel-header"><h2>{{ $title }}</h2></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th scope="col">Libellé</th></tr></thead>
                <tbody>
                    @forelse ($items as $item)<tr><td>{{ $item['libelle'] }}</td></tr>
                    @empty<tr><td>{{ $error ? 'Liste indisponible.' : 'Aucune donnée disponible.' }}</td></tr>@endforelse
                </tbody>
            </table></div>
        </section>
    </section>
</main>
@endsection
