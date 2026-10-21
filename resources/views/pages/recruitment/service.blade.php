@extends('layouts.app')
@section('title', 'SICORE - Prise de service')
@section('content')
<main class="main-content recruitment-page">
    @include('pages.recruitment.header', ['heading' => 'Enregistrer une prise de service'])
    <section class="content-area">
        <a href="{{ route('recruitment.show', $batch['id']) }}">← Retour au lot {{ $batch['reference'] }}</a>
        <section class="panel recruitment-panel">
            <h2>{{ $teacher['prenom'] }} {{ $teacher['nom'] }}</h2>
            <p>L’IA enregistre la date effective de prise de service et le certificat délivré par le chef d’établissement. La validation rend le dossier actif et fixe le point de départ des deux ans.</p>
            @if ($teacher['service_date'])<p>Prise de service déjà enregistrée le {{ $teacher['service_date'] }}.</p>
            @elseif (!$batch['transmitted_at'])<p>Ce lot doit être transmis avant l’enregistrement des prises de service.</p>
            @elseif ($error)<p class="recruitment-errors" role="alert">{{ $error }}</p>
            @elseif (!count($options))<p>Aucun établissement disponible dans votre périmètre.</p>
            @else
                <form method="POST" action="{{ route('recruitment.service', [$batch['id'], $teacher['id']]) }}" enctype="multipart/form-data" class="recruitment-form">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group"><label for="service_date">Date effective de prise de service *</label><input class="form-control" type="date" name="service_date" id="service_date" min="{{ $batch['recruited_at'] }}" max="{{ now()->toDateString() }}" value="{{ old('service_date') }}" required></div>
                        <div class="form-group"><label for="lieu_service_id">Établissement *</label><select class="form-control" name="lieu_service_id" id="lieu_service_id" required><option value="">Sélectionner un établissement</option>
                            @foreach ($options as $lieu)<option value="{{ $lieu['id'] }}" @selected((string) old('lieu_service_id', $teacher['lieu_service_id']) === (string) $lieu['id'])>{{ $lieu['libelle'] ?? $lieu['nom'] ?? $lieu['code'] }}</option>@endforeach
                        </select></div>
                        <div class="form-group"><label for="certificate">Certificat de prise de service — PDF, 10 Mo *</label><input class="form-control" type="file" name="document" id="certificate" accept="application/pdf" required></div>
                    </div>
                    <button class="btn-primary" type="submit">Valider la prise de service et activer le dossier</button>
                </form>
            @endif
        </section>
    </section>
</main>
@endsection
