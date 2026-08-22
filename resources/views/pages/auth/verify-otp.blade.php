@extends('layouts.guest')

@section('title', 'SICORE - Vérification du code')
@section('content')
<main class="login-wrapper">
<section class="login-card" aria-label="Vérification du code SICORE">
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
        <span class="done"></span>
        <span class="current"></span>
        <span></span>
    </div>

    <span class="auth-kicker">Vérification</span>
    <p class="auth-subtitle">Saisissez le code à 6 chiffres envoyé à <strong>{{ $email }}</strong>.</p>

    @if (session('status'))
    <div class="login-success" role="status">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <span>{{ session('status') }}</span>
    </div>
    @endif

    @if ($errors->any())
    <div class="login-errors" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('password.reset.otp.submit') }}" novalidate>
        @csrf
        <div class="field-group">
            <label for="otp">Code de vérification</label>
            <div class="input-shell">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                <input class="login-input otp-input @error('otp') is-invalid @enderror" id="otp" type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" autocomplete="one-time-code" required autofocus>
            </div>
            <p class="reset-hint">Le code expire dans 10 minutes.</p>
        </div>
        <button class="login-button" type="submit">
            <i class="fa-solid fa-check" aria-hidden="true"></i>
            <span>Vérifier le code</span>
        </button>
    </form>

    <form method="POST" action="{{ route('password.reset.otp.resend') }}" class="resend-form">
        @csrf
        <button class="reset-ghost-btn" type="submit">Renvoyer le code</button>
    </form>

    <p class="auth-footer">
        <a href="{{ route('password.reset.form') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Changer d'adresse e-mail</a>
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
.otp-input{ letter-spacing:8px; font-weight:700; font-size:20px; text-align:center; }
.reset-hint{ font-size:12.5px; color:#6b7280; margin-top:8px; }
.resend-form{ margin:0; }
.reset-ghost-btn{
    width:100%; background:transparent; border:none; color:#6b7280;
    font-size:13px; padding:10px 0 0; cursor:pointer; text-decoration:underline;
    text-underline-offset:3px;
}
.reset-ghost-btn:hover{ color:#111827; }
</style>
@endsection