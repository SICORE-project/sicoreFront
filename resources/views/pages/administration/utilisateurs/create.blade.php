@extends('layouts.app')

@section('title', 'SICORE - Créer un utilisateur')

@section('content')
    <main class="main-content">

        <x-topbar
            title="Créer un utilisateur"
            subtitle="Ajouter un nouvel utilisateur au système"
            icon="fa-solid fa-user-plus"
        />

        <section class="content-area">
            <section class="form-card">

                <div class="form-card-header">
                    <div>
                        <h2>Informations du compte</h2>
                        <p class="breadcrumb">
                            Saisie des informations personnelles et des accès
                        </p>
                    </div>

                    <span class="badge badge-primary">Compte</span>
                </div>

                <p class="form-required-note"><span class="required">*</span> Champs obligatoires</p>

                {{-- Message d'erreur général --}}
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Erreurs de validation --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Veuillez corriger les erreurs suivantes :</strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    class="teacher-form"
                    id="createUserForm"
                    method="POST"
                    action="{{ route('utilisateurs.store') }}"
                    novalidate
                >
                    @csrf

                    {{-- Informations personnelles --}}
                    <div class="form-section">
                        <h3>Informations personnelles</h3>

                        <div class="form-grid">

                            {{-- Nom --}}
                            <div class="form-group">
                                <label for="nom">
                                    Nom <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control @error('nom') is-invalid @enderror"
                                    id="nom"
                                    name="nom"
                                    type="text"
                                    value="{{ old('nom') }}"
                                    maxlength="100"
                                    required
                                    autocomplete="family-name"
                                    placeholder="Ex. Diop"
                                    aria-describedby="nom-error"
                                >

                                @error('nom')
                                    <div class="invalid-feedback" id="nom-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Prénom --}}
                            <div class="form-group">
                                <label for="prenom">
                                    Prénom <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control @error('prenom') is-invalid @enderror"
                                    id="prenom"
                                    name="prenom"
                                    type="text"
                                    value="{{ old('prenom') }}"
                                    maxlength="100"
                                    required
                                    autocomplete="given-name"
                                    placeholder="Ex. Aminata"
                                    aria-describedby="prenom-error"
                                >

                                @error('prenom')
                                    <div class="invalid-feedback" id="prenom-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="form-group full">
                                <label for="email">
                                    Adresse e-mail <span class="required">*</span>
                                </label>

                                <input
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    maxlength="255"
                                    required
                                    autocomplete="email"
                                    inputmode="email"
                                    placeholder="nom@exemple.sn"
                                    aria-describedby="email-help email-error"
                                >

                                <small class="form-text" id="email-help">Cette adresse servira d'identifiant de connexion.</small>

                                @error('email')
                                    <div class="invalid-feedback" id="email-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Accès au système --}}
                    <div class="form-section">
                        <h3>Accès au système</h3>

                        <div class="form-grid">

                            {{-- Rôle --}}
                            <div class="form-group">
                                <label for="role_id">
                                    Rôle <span class="required">*</span>
                                </label>

                                <select
                                    class="form-control @error('role_id') is-invalid @enderror"
                                    id="role_id"
                                    name="role_id"
                                    required
                                    aria-describedby="role_id-error"
                                >
                                    <option value="">
                                        Sélectionner un rôle
                                    </option>

                                    @forelse ($roles as $role)
                                        <option
                                            value="{{ $role['id'] }}"
                                            @selected(old('role_id') == $role['id'])
                                        >
                                            {{ $role['nom'] }}
                                        </option>
                                    @empty
                                        <option value="" disabled>
                                            Aucun rôle disponible
                                        </option>
                                    @endforelse
                                </select>

                                @error('role_id')
                                    <div class="invalid-feedback" id="role_id-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Statut --}}
                            <div class="form-group">
                                <label for="statut">
                                    Statut <span class="required">*</span>
                                </label>

                                <select
                                    class="form-control @error('statut') is-invalid @enderror"
                                    id="statut"
                                    name="statut"
                                    required
                                    aria-describedby="statut-help statut-error"
                                >
                                    <option
                                        value="actif"
                                        @selected(old('statut', 'actif') === 'actif')
                                    >
                                        Actif
                                    </option>

                                    <option
                                        value="inactif"
                                        @selected(old('statut') === 'inactif')
                                    >
                                        Inactif
                                    </option>
                                </select>

                                <small class="form-text" id="statut-help">Un compte inactif ne peut pas se connecter.</small>

                                @error('statut')
                                    <div class="invalid-feedback" id="statut-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Sécurité --}}
                    <div class="form-section">
                        <h3>Sécurité</h3>

                        <div class="form-grid">

                            {{-- Mot de passe --}}
                            <div class="form-group">
                                <label for="password">
                                    Mot de passe <span class="required">*</span>
                                </label>

                                <div class="password-field">
                                    <input
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        type="password"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        aria-describedby="password-help password-error"
                                    >
                                    <button class="password-toggle" type="button" data-password-toggle="#password">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>

                                <small class="form-text" id="password-help">
                                    Le mot de passe doit contenir au moins 8 caractères.
                                </small>

                                @error('password')
                                    <div class="invalid-feedback" id="password-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Confirmation --}}
                            <div class="form-group">
                                <label for="password_confirmation">
                                    Confirmation du mot de passe
                                    <span class="required">*</span>
                                </label>

                                <div class="password-field">
                                    <input
                                        class="form-control"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        aria-describedby="password-confirmation-feedback"
                                    >
                                    <button class="password-toggle" type="button" data-password-toggle="#password_confirmation">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <small class="form-text" id="password-confirmation-feedback" aria-live="polite"></small>
                            </div>

                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="form-actions">
                        <a
                            class="btn-secondary"
                            href="{{ route('utilisateurs.index') }}"
                        >
                            Annuler
                        </a>

                        <button
                            class="btn-primary"
                            type="submit"
                            data-submit-button
                        >
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            Enregistrer
                        </button>
                    </div>

                </form>

            </section>
        </section>

    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('createUserForm');
            const password = document.getElementById('password');
            const confirmation = document.getElementById('password_confirmation');
            const feedback = document.getElementById('password-confirmation-feedback');

            function checkPasswordConfirmation() {
                if (!confirmation.value) {
                    feedback.textContent = '';
                    feedback.className = 'form-text';
                    return;
                }

                const matches = password.value === confirmation.value;
                feedback.textContent = matches ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
                feedback.className = matches ? 'form-text text-success' : 'form-text text-danger';
            }

            password.addEventListener('input', checkPasswordConfirmation);
            confirmation.addEventListener('input', checkPasswordConfirmation);

            form.addEventListener('submit', function () {
                if (!form.checkValidity()) {
                    return;
                }

                const button = form.querySelector('[data-submit-button]');
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enregistrement…';
            });
        });
    </script>
@endpush
