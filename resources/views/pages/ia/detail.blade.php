@extends('layouts.app')
@section('title', 'SICORE - '.$title)
@section('content')
<main class="main-content">
    <x-topbar :title="$title" :subtitle="$scopeLabel" icon="fa-solid fa-file-lines" />
    <section class="content-area">
        <a href="{{ route($backRoute) }}">← Retour à la liste</a>
        @if ($error)<p class="alert alert-warning" role="alert">{{ $error }}</p>@endif
        <section class="objective-card"><h2>{{ $record['prenom'] ?? '' }} {{ $record['nom'] ?? '' }}</h2><p>Périmètre : {{ $scopeLabel }}</p>
            <dl class="ia-details">@foreach ($fields as $key => $label)
                <div><dt>{{ $label }}</dt><dd>{{ $key === 'payment_status' ? (['paid' => 'Payé', 'pending' => 'En attente', 'rejected' => 'Rejeté'][$record[$key] ?? ''] ?? '—') : ($record[$key] ?? '—') }}</dd></div>
            @endforeach</dl>
        </section>
    </section>
</main>
@endsection
@include('pages.ia.styles')
