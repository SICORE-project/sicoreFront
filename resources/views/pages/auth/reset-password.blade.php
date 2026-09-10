@extends('layouts.guest')

@section('title', 'SICORE - Nouveau mot de passe')
@section('content')
<main class="login-wrapper">
<section class="login-card" aria-label="Nouveau mot de passe SICORE">
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
        <span class="done"></span>
        <span class="current"></span>
    </div>

    <span class="auth-kicker">Nouveau mot de passe</span>
    <p class="auth-subtitle">Choisissez un nouveau mot de passe pour votre compte.</p>

    @if ($errors->any())
    <div class="login-errors" role="alert">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('password.reset.submit') }}" novalidate>
        @csrf
        <div class="field-group">
            <label for="password">Nouveau mot de passe</label>
            <div class="password-field">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                <input class="login-input @error('password') is-invalid @enderror" id="password" type="password" name="password" placeholder="8 caractères minimum" minlength="8" autocomplete="new-password" required>
                <button class="password-toggle" type="button" data-password-toggle="#password" aria-label="Afficher le mot de passe" aria-pressed="false">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <div class="password-strength" data-password-strength aria-live="polite">
                <div class="password-strength-bars" aria-hidden="true">
                    <span data-strength-bar></span>
                    <span data-strength-bar></span>
                    <span data-strength-bar></span>
                </div>
                <div class="password-strength-status">
                    <span>Sécurité du mot de passe</span>
                    <strong data-strength-label>Non évaluée</strong>
                </div>
            </div>
        </div>
        <div class="field-group">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <div class="password-field">
                <span class="input-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                <input class="login-input" id="password_confirmation" type="password" name="password_confirmation" placeholder="Retapez le mot de passe" autocomplete="new-password" required>
                <button class="password-toggle" type="button" data-password-toggle="#password_confirmation" aria-label="Afficher le mot de passe" aria-pressed="false">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <button class="login-button" type="submit">
            <i class="fa-solid fa-key" aria-hidden="true"></i>
            <span>Réinitialiser le mot de passe</span>
        </button>
    </form>

</div>
</div>
</section>
</main>

<style>
.reset-steps-track{ display:flex; gap:8px; margin-bottom:18px; }
.reset-steps-track span{ height:3px; flex:1; border-radius:2px; background:rgba(0,0,0,0.08); }
.reset-steps-track span.current{ background:#0f6b3f; opacity:0.55; }
.reset-steps-track span.done{ background:#0f6b3f; }
.password-strength{ margin-top:10px; }
.password-strength-bars{ display:grid; grid-template-columns:repeat(3, 1fr); gap:5px; height:5px; }
.password-strength-bars span{ border-radius:3px; background:#e5e7eb; transition:background-color .2s ease; }
.password-strength-bars span.active:nth-child(1){ background:#dc2626; }
.password-strength-bars span.active:nth-child(2){ background:#f59e0b; }
.password-strength-bars span.active:nth-child(3){ background:#16a34a; }
.password-strength-status{ display:flex; justify-content:space-between; gap:12px; margin-top:5px; color:#6b7280; font-size:12px; }
.password-strength-status strong{ color:#6b7280; font-weight:700; }
.password-strength.is-weak .password-strength-status strong{ color:#dc2626; }
.password-strength.is-medium .password-strength-status strong{ color:#b45309; }
.password-strength.is-strong .password-strength-status strong{ color:#15803d; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var password = document.getElementById('password');
    var strength = document.querySelector('[data-password-strength]');

    if (!password || !strength) {
        return;
    }

    var bars = strength.querySelectorAll('[data-strength-bar]');
    var label = strength.querySelector('[data-strength-label]');

    password.addEventListener('input', function () {
        var value = password.value;
        var criteria = [
            value.length >= 8,
            /[a-z]/.test(value),
            /[A-Z]/.test(value),
            /\d/.test(value),
            /[^\w\s]/.test(value)
        ];
        var score = criteria.filter(Boolean).length;
        var level = score === 0 ? 0 : score <= 2 ? 1 : score <= 4 ? 2 : 3;
        var labels = ['Non évaluée', 'Faible', 'Moyen', 'Fort'];

        bars.forEach(function (bar, index) {
            bar.classList.toggle('active', index < level);
        });
        strength.classList.toggle('is-weak', level === 1);
        strength.classList.toggle('is-medium', level === 2);
        strength.classList.toggle('is-strong', level === 3);
        label.textContent = labels[level];
    });
});
</script>
@endsection