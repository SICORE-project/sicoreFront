@extends('layouts.app')

@section('title', 'SICORE - Modifier la convocation')

@section('content')

<main class="main-content">

<x-topbar
    title="Modifier la convocation"
    subtitle="Indemnites > Convocations > Modifier"
    icon="fa-solid fa-envelope-open-text"
/>

<section class="content-area">

    <section class="form-card">

        <div class="form-card-header">
            <div>
                <h2>Modifier la convocation</h2>
                <p class="breadcrumb">{{ $convocation->objet ?? '—' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('indemnites.convocations.update', $id) }}">

            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="form-errors" role="alert">
                    <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section">

                <div class="form-grid">

                    <div class="form-group full">
                        <label for="objet">
                            Objet <span class="required">*</span>
                        </label>
                        <input
                            class="form-control @error('objet') is-invalid @enderror"
                            id="objet"
                            name="objet"
                            type="text"
                            value="{{ old('objet', $convocation->objet ?? '') }}"
                            required
                        >
                        @error('objet')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group full">
                        <label for="type_convocation_id">Type de convocation</label>
                        <select
                            class="form-control @error('type_convocation_id') is-invalid @enderror"
                            id="type_convocation_id"
                            name="type_convocation_id"
                        >
                            <option value="">Sélectionner</option>
                            @foreach ($typesConvocation ?? [] as $type)
                                <option
                                    value="{{ $type['id'] }}"
                                    @selected((string) old('type_convocation_id', $convocation->typeConvocation['id'] ?? '') === (string) $type['id'])
                                >
                                    {{ $type['libelle'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_convocation_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="date_emission">
                            Date d'émission <span class="required">*</span>
                        </label>
                        <input
                            class="form-control @error('date_emission') is-invalid @enderror"
                            id="date_emission"
                            name="date_emission"
                            type="date"
                            value="{{ old('date_emission', optional($convocation->date_emission ?? null)->format('Y-m-d')) }}"
                            required
                        >
                        @error('date_emission')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select class="form-control @error('statut') is-invalid @enderror" id="statut" name="statut">
                            @foreach (['brouillon' => 'Brouillon', 'emise' => 'Émise', 'envoyee' => 'Envoyée', 'cloturee' => 'Clôturée'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('statut', $convocation->statut ?? 'brouillon') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('statut')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="date_debut">Du</label>
                        <input
                            class="form-control @error('date_debut') is-invalid @enderror"
                            id="date_debut"
                            name="date_debut"
                            type="date"
                            value="{{ old('date_debut', optional($convocation->date_debut ?? null)->format('Y-m-d')) }}"
                        >
                        @error('date_debut')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="date_fin">Au</label>
                        <input
                            class="form-control @error('date_fin') is-invalid @enderror"
                            id="date_fin"
                            name="date_fin"
                            type="date"
                            value="{{ old('date_fin', optional($convocation->date_fin ?? null)->format('Y-m-d')) }}"
                        >
                        @error('date_fin')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="heure_debut">À partir de</label>
                        <input
                            class="form-control @error('heure_debut') is-invalid @enderror"
                            id="heure_debut"
                            name="heure_debut"
                            type="time"
                            value="{{ old('heure_debut', $convocation->heure_debut ?? '') }}"
                        >
                        @error('heure_debut')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="lieu_examen">Lieu d'examen</label>
                        <input
                            class="form-control @error('lieu_examen') is-invalid @enderror"
                            id="lieu_examen"
                            name="lieu_examen"
                            type="text"
                            value="{{ old('lieu_examen', $convocation->lieu_examen ?? '') }}"
                        >
                        @error('lieu_examen')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="lieu_affectation">Lieu d'affectation</label>
                        <input
                            class="form-control @error('lieu_affectation') is-invalid @enderror"
                            id="lieu_affectation"
                            name="lieu_affectation"
                            type="text"
                            value="{{ old('lieu_affectation', $convocation->lieu_affectation ?? '') }}"
                        >
                        @error('lieu_affectation')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group full">
                        <label class="checkbox-label" for="ordre_de_mission">
                            <input
                                id="ordre_de_mission"
                                name="ordre_de_mission"
                                type="checkbox"
                                value="1"
                                @checked(old('ordre_de_mission', $convocation->ordre_de_mission ?? false))
                            >
                            Joindre un ordre de mission
                        </label>
                    </div>

                </div>

            </div>

            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
                    Annuler
                </a>
                <button class="btn-primary" type="submit">
                    Enregistrer les modifications
                </button>
            </div>

        </form>

    </section>

</section>

</main>

@endsection
