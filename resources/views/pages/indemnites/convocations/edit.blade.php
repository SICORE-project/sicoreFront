
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
    <div class="actions-row">
      <p class="breadcrumb">Indemnites &gt; Convocations &gt; #{{ $id }} &gt; Modifier</p>
      <div class="actions-group">
        <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          Retour a la fiche
        </a>
      </div>
    </div>

    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>Informations de la convocation</h2>
          <p class="breadcrumb">Objet, lieu, dates et statut</p>
        </div>
        <x-convocation-statut-badge :statut="old('statut', $convocation['statut'] ?? null)" />
      </div>

      <form
        class="convocation-form"
        method="POST"
        action="{{ route('indemnites.convocations.update', $id) }}"
        novalidate
      >
        @csrf
        @method('PUT')

        <div class="form-section">
          <h3>Objet et cadre de la convocation</h3>
          <div class="form-grid">
            <div class="form-group full">
              <label for="objet">Objet <span class="required">*</span></label>
              <input
                class="form-control @error('objet') is-invalid @enderror"
                id="objet"
                name="objet"
                type="text"
                placeholder="Ex : Examen de certification en Brevet de Technicien (BT)"
                value="{{ old('objet', $convocation['objet'] ?? '') }}"
                required
              >
              @error('objet')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
              <label for="date_emission">Date d'emission <span class="required">*</span></label>
              <input
                class="form-control @error('date_emission') is-invalid @enderror"
                id="date_emission"
                name="date_emission"
                type="date"
                value="{{ old('date_emission', isset($convocation['date_emission']) ? \Illuminate\Support\Carbon::parse($convocation['date_emission'])->format('Y-m-d') : '') }}"
                required
              >
              @error('date_emission')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
              <label for="statut">Statut</label>
              @php($statutActuel = old('statut', $convocation['statut'] ?? 'brouillon'))
              <select class="form-control @error('statut') is-invalid @enderror" id="statut" name="statut">
                <option value="brouillon" @selected($statutActuel === 'brouillon')>Brouillon</option>
                <option value="emise" @selected($statutActuel === 'emise')>Emise</option>
                <option value="envoyee" @selected($statutActuel === 'envoyee')>Envoyee</option>
                <option value="cloturee" @selected($statutActuel === 'cloturee')>Cloturee</option>
              </select>
              @error('statut')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
              <label for="lieu_examen">Centre d'examen</label>
              <input
                class="form-control @error('lieu_examen') is-invalid @enderror"
                id="lieu_examen"
                name="lieu_examen"
                type="text"
                placeholder="Ex : Centre LTP FXN/Thies"
                value="{{ old('lieu_examen', $convocation['lieu_examen'] ?? '') }}"
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
                placeholder="Ex : Dakar"
                value="{{ old('lieu_affectation', $convocation['lieu_affectation'] ?? '') }}"
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
                  @checked(old('ordre_de_mission', $convocation['ordre_de_mission'] ?? false))
                >
                Joindre un ordre de mission
              </label>
            </div>
          </div>
        </div>

        <p class="form-status" data-form-status aria-live="polite"></p>
        <div class="form-actions">
          <a class="btn-secondary" href="{{ route('indemnites.convocations.show', $id) }}">Annuler</a>
          <button class="btn-primary" type="submit">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Enregistrer les modifications
          </button>
        </div>
      </form>
    </section>
  </section>
</main>
@endsection
