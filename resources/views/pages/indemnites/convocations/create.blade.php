@extends('layouts.app')

@section('title', 'SICORE - Nouvelle convocation')
@section('content')
<main class="main-content">
  <x-topbar
    title="Nouvelle convocation"
    subtitle="Indemnites > Convocations > Nouvelle convocation"
    icon="fa-solid fa-envelope-open-text"
  />

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb">Indemnites &gt; Convocations &gt; Nouvelle</p>
      <div class="actions-group">
        <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          Retour a la liste
        </a>
      </div>
    </div>

    <section class="form-card convocation-form-card">
      <div class="form-card-header">
        <div>
          <h2>Informations de la convocation</h2>
          <p class="breadcrumb">Objet, lieu, dates et membres du jury a convoquer</p>
        </div>
        <span class="badge badge-primary">Brouillon</span>
      </div>

      <form
        id="convocationForm"
        class="convocation-form"
        role="form"
        method="POST"
        action="{{ route('indemnites.convocations.store') }}"
        data-convocation-form
        data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}"
        aria-describedby="{{ $errors->any() ? 'form-errors' : '' }}"
        novalidate
      >
        @csrf

        @if ($errors->any())
          <div id="form-errors" class="form-errors" role="alert">
            <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

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
                value="{{ old('objet') }}"
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
                value="{{ old('date_emission') }}"
                required
              >
              @error('date_emission')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
              <label for="statut">Statut</label>
              <select class="form-control @error('statut') is-invalid @enderror" id="statut" name="statut">
                <option value="brouillon" selected>Brouillon</option>
                <option value="emise" @selected(old('statut') === 'emise')>Emise</option>
                <option value="envoyee" @selected(old('statut') === 'envoyee')>Envoyee</option>
                <option value="cloturee" @selected(old('statut') === 'cloturee')>Cloturee</option>
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
                value="{{ old('lieu_examen') }}"
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
                value="{{ old('lieu_affectation') }}"
              >
              @error('lieu_affectation')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group full">
              <label class="checkbox-label" for="ordre_de_mission">
                <input id="ordre_de_mission" name="ordre_de_mission" type="checkbox" value="1" @checked(old('ordre_de_mission'))>
                Joindre un ordre de mission
              </label>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="panel-header">
            <div>
              <h3>Membres du jury a convoquer</h3>
              <p>Recherchez un enseignant, puis precisez sa fonction pour cette convocation</p>
            </div>
            <button class="btn-secondary" type="button" data-add-beneficiaire>
              <i class="fa-solid fa-plus" aria-hidden="true"></i>
              Ajouter un membre
            </button>
          </div>

          <div class="table-responsive">
            <table class="table" id="beneficiairesTable">
              <thead>
                <tr>
                  <th>Enseignant</th>
                  <th>Fonction</th>
                  <th class="actions-cell">Retirer</th>
                </tr>
              </thead>
              <tbody data-beneficiaires-body>
                {{-- Les lignes ajoutees dynamiquement sont clonees depuis le template ci-dessous --}}
              </tbody>
            </table>
          </div>
          <p class="empty-message" data-beneficiaires-empty>Aucun membre ajoute pour le moment.</p>
          @error('beneficiaires')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <template data-beneficiaire-row-template>
          <tr class="beneficiaire-row">
            <td>
              <div class="enseignant-search" data-enseignant-search>
                <input
                  class="form-control"
                  type="text"
                  placeholder="Rechercher un enseignant (nom, prenom, matricule)…"
                  data-enseignant-search-input
                  autocomplete="off"
                >
                <input type="hidden" name="beneficiaires[][enseignant_id]" data-enseignant-id-input>
                <ul class="enseignant-suggestions" data-enseignant-suggestions hidden></ul>
              </div>
            </td>
            <td>
              <select class="form-control" name="beneficiaires[][fonction]" data-fonction-select>
                <option value="">Selectionner une fonction</option>
                <option value="President de jury">President de jury</option>
                <option value="Membre du jury">Membre du jury</option>
                <option value="Surveillant/correcteur">Surveillant/correcteur</option>
                <option value="Chef de centre">Chef de centre</option>
              </select>
            </td>
            <td class="actions-cell">
              <button class="icon-action" type="button" title="Retirer" data-remove-beneficiaire>&#128465;</button>
            </td>
          </tr>
        </template>

        <p class="form-status" data-form-status aria-live="polite"></p>
        <div class="form-actions">
          <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">Annuler</a>
          <button class="btn-primary" type="submit">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Enregistrer la convocation
          </button>
        </div>
      </form>
    </section>
  </section>
</main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/indemnites/convocation-form.js') }}" defer></script>
@endpush

@push('styles')
  <style>
    .convocation-form-card {
      max-width: 980px;
      margin: 12px auto 0;
    }

    @media (min-width: 1200px) {
      .convocation-form-card {
        max-width: 1200px;
      }
      .convocation-form-card .form-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
      }
    }
  </style>
@endpush