@extends('layouts.guest')

@section('title', 'SICORE - Mot de passe oublié')
@section('content')
<main class="login-wrapper">
<section class="login-card" aria-label="Réinitialisation du mot de passe SICORE">
<div class="login-left">
<div class="login-left-content">
<div class="republic-block">
<img class="flag-image" src="{{ asset('assets/images/flag-senegal.svg') }}" alt="Drapeau du Sénégal">
<div>
<p class="republic-title">RÉPUBLIQUE DU SÉNÉGAL</p>
<p class="republic-motto">Un Peuple &ndash; Un But &ndash; Une Foi</p>
</div>
</div>
<p class="ministry">Ministère de l&rsquo;Emploi et de la Formation<br>Professionnelle et Technique</p>
</div>

<div class="login-brand">
<span class="brand-emblem">
<img src="{{ asset('assets/images/image-fcfa.png') }}" alt="Logo SICORE - Système Intégré des COrps Émergents">
</span>
<h1>SICORE</h1>
<p>Système Intégré des COrps Émergents</p>
</div>
</div>

<div class="login-right">
<div class="auth-panel">

    <div class="reset-steps-track">
        <span class="current"></span>
        <span></span>
        <span></span>
    </div>

    <span class="auth-kicker">Mot de passe oublié</span>
    <p class="auth-subtitle">Entrez l'adresse e-mail associée à votre compte SICORE. Nous vous enverrons un code de vérification.</p>

    @if ($errors->any())
    <div class="login-errors" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('password.reset.send') }}" novalidate>
        @csrf
        <div class="field-group">
            <label for="email">Adresse e-mail</label>
            <div class="input-shell">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                <input class="login-input @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="adresse@sicore.sn" autocomplete="username" required autofocus>
            </div>
        </div>
        <button class="login-button" type="submit">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            <span>Envoyer le code</span>
        </button>
    </form>

    <p class="auth-footer">
        <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour à la connexion</a>
    </p>

</div>
</div>
</section>
</main>

<style>
.reset-steps-track{ display:flex; gap:8px; margin-bottom:18px; }
.reset-steps-track span{ height:3px; flex:1; border-radius:2px; background:rgba(0,0,0,0.08); }
.reset-steps-track span.current{ background:#0f6b3f; opacity:0.55; }
.reset-steps-track span.done{ background:#0f6b3f; }
</style>
@endsection