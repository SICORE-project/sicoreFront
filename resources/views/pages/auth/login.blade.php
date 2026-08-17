@extends('layouts.guest')

{{--
  PAGE : Connexion — URL GET / et envoi POST /login.
  Routes : routes/web.php. Traitement : app/Http/Controllers/AuthController.php.
  Appel backend : app/Services/SicoreApi.php → sicoreBack /api/login.
  Layout sans sidebar : resources/views/layouts/guest.blade.php.
  Styles : public/assets/css/style.css et responsive.css.
--}}
@section('title', 'SICORE - Connexion')
@section('content')
<main class="login-wrapper">
    <section class="login-card" aria-label="Connexion SICORE">
      {{-- Colonne institutionnelle : identité visuelle et contexte SICORE. --}}
      <div class="login-left">
        <div class="login-left-content">
          <div class="republic-block">
            <img class="flag-image" src="{{ asset('assets/images/flag-senegal.svg') }}" alt="Drapeau du S&eacute;n&eacute;gal">
            <div>
              <p class="republic-title">R&Eacute;PUBLIQUE DU S&Eacute;N&Eacute;GAL</p>
              <p class="republic-motto">Un Peuple &ndash; Un But &ndash; Une Foi</p>
            </div>
          </div>
          <p class="ministry">Minist&egrave;re de l&rsquo;Emploi et de la Formation<br>Professionnelle et Technique</p>
        </div>

        <div class="login-brand">
          <span class="brand-emblem">
            <img src="{{ asset('assets/images/image-fcfa.png') }}" alt="Logo SICORE - Syst&egrave;me Int&eacute;gr&eacute; des COrps &Eacute;mergents">
          </span>
          <h1>SICORE</h1>
          <p>Syst&egrave;me Int&eacute;gr&eacute; des COrps &Eacute;mergents</p>
        </div>
      </div>

      {{-- Colonne fonctionnelle : formulaire réellement envoyé à AuthController. --}}
      <div class="login-right">
        <div class="auth-panel">
          <span class="auth-kicker">Bienvenue sur SICORE</span>
          <p class="auth-subtitle">Connectez-vous avec vos identifiants pour acc&eacute;der &agrave; votre espace s&eacute;curis&eacute;.</p>

          {{-- @csrf protège le formulaire contre les soumissions externes. --}}
          <form class="form-stack" method="POST" action="{{ route('login.submit') }}" novalidate>
            @csrf
            <div class="field-group">
              <label for="email">Adresse e-mail</label>
              <div class="input-shell">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                <input class="login-input @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="adresse@sicore.sn" autocomplete="username" required autofocus>
              </div>
            </div>

            <div class="field-group">
              <label for="password">Mot de passe</label>
              <div class="password-field">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                <input class="login-input @error('password') is-invalid @enderror" id="password" type="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="Afficher le mot de passe" aria-pressed="false">
                  <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="form-options">
              <label class="check-label">
                <input type="checkbox" name="remember" value="1" {{ old('remember', true) ? 'checked' : '' }}>
                <span>Se souvenir de moi</span>
              </label>
              <a class="forgot-link" href="#">Mot de passe oubli&eacute; ?</a>
            </div>

            @if ($errors->any())
              <div class="login-errors" role="alert">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <span>{{ $errors->first() }}</span>
              </div>
            @endif
            <button class="login-button" type="submit">
              <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
              <span>Se connecter</span>
            </button>
          </form>

          <p class="auth-footer">Besoin d&rsquo;aide pour acc&eacute;der &agrave; votre compte ? <a href="#">Contactez l&rsquo;administrateur du syst&egrave;me.</a></p>
          <p class="bootstrap-credentials">
            Compte initial : <strong>{{ config('sicore.bootstrap.email') }}</strong> / <strong>{{ config('sicore.bootstrap.password') }}</strong>
          </p>
        </div>
      </div>
    </section>
  </main>
@endsection
