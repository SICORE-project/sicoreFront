@extends('layouts.app')
@section('title', 'SICORE - Compte enseignant')
@section('content')
<main class="main-content">
  <x-topbar title="Gestion utilisateurs" subtitle="Créer un compte enseignant" icon="fa-solid fa-user-plus" />
  <dialog id="teacher-account-modal" class="teacher-account-modal" aria-labelledby="teacher-account-title" data-return-url="{{ route('utilisateurs.index') }}">
    <header class="teacher-account-header">
      <span class="teacher-account-icon"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span>
      <a class="teacher-account-close" href="{{ route('utilisateurs.index') }}" aria-label="Fermer">&times;</a>
      <p class="teacher-account-eyebrow">ACCÈS À LA PLATEFORME</p>
      <h1 id="teacher-account-title">Créer un compte enseignant</h1>
      <p>Sélectionnez son dossier et envoyez-lui une invitation pour rejoindre SICORE.</p>
    </header>
    <form method="POST" action="{{ route('utilisateurs.teacher.store') }}" id="teacher-account-form">
      @csrf
      <div class="teacher-account-body">
        @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="teacher-account-field">
          <label for="teacher-candidates">Enseignant <span class="required">*</span></label>
          <div class="teacher-picker" id="teacher-picker">
            <div class="teacher-picker-control">
              <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
              <input id="teacher-candidates" type="text" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="teacher-options" autocomplete="off" maxlength="100" placeholder="Matricule, nom ou prénom" required data-url="{{ route('utilisateurs.teacher.candidates') }}">
              <button type="button" id="teacher-picker-toggle" aria-label="Afficher les enseignants" tabindex="-1"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
            </div>
            <input type="hidden" id="teacher-selected-id" name="enseignant_id">
            <div class="teacher-picker-dropdown" id="teacher-picker-dropdown" hidden>
              <div id="teacher-options" role="listbox" aria-label="Enseignants"></div>
              <p id="teacher-search-status" role="status" aria-live="polite"></p>
              <button id="teacher-load-more" type="button" hidden>Afficher plus de résultats</button>
            </div>
          </div>
          <small>Les enseignants ayant déjà un compte sont grisés et ne peuvent pas être sélectionnés.</small>
        </div>
        <div class="teacher-account-field">
          <label for="teacher-account-email">E-mail du compte <span class="required">*</span></label>
          <input class="form-control" id="teacher-account-email" name="email" type="email" maxlength="255" value="{{ old('email') }}" required autocomplete="email" placeholder="prenom.nom@exemple.sn">
          <small>Vérifiez l’adresse à laquelle envoyer l’invitation.</small>
        </div>
        <aside class="teacher-account-note">
          <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>
          <div><strong>Une invitation pour bien démarrer</strong><p>Le rôle Enseignant est attribué automatiquement. L’e-mail permettra de définir un mot de passe personnel.</p></div>
        </aside>
      </div>
      <footer class="teacher-account-footer">
        <a class="btn-secondary" href="{{ route('utilisateurs.index') }}">Annuler</a>
        <button class="btn-primary" type="submit" id="teacher-account-submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Créer et inviter</button>
      </footer>
    </form>
  </dialog>
</main>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-account.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/js/teacher-account.js') }}" defer></script>
@endpush
