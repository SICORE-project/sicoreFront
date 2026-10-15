@extends('layouts.app')
@section('title', 'SICORE - Mon espace')
@section('content')
<main class="main-content">
    <x-topbar title="Mon espace" subtitle="Vos modules disponibles" icon="fa-solid fa-gauge-high" />
    <section class="content-area">
        <section class="objective-card">
            <h2>{{ session('sicore_user.name', session('sicore_user.email', 'Bienvenue')) }}</h2>
            <p>Profil : {{ session('sicore_user.role', 'Utilisateur') }}</p>
            <p>Périmètre : {{ $scopeLabel }}</p>
        </section>
        @forelse (collect($navigation)->reject(fn ($item) => ($item['route'] ?? '') === 'dashboard') as $item)
            <section class="panel">
                <div class="panel-header"><h2>{{ $item['label'] }}</h2></div>
                @include('pages.dashboard.workspace-links', ['items' => isset($item['links']) ? $item['links'] : [$item]])
            </section>
        @empty
            <section class="objective-card"><p>Aucun module n’est encore attribué à votre rôle.</p></section>
        @endforelse
    </section>
</main>
@endsection
