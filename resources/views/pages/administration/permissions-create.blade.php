@extends('layouts.app')

@section('title', 'SICORE - Créer une permission')

@section('content')
<main class="main-content">
    <x-topbar title="Créer une permission" subtitle="Gestion Utilisateur > Permissions > Nouvelle" icon="fa-solid fa-lock" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required class="form-control">
                    @error('nom') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="actions-group" style="justify-content: flex-end;">
                    <a href="{{ route('admin.permissions.index') }}" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>

        </section>
    </section>
</main>
@endsection