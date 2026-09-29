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

                <p style="font-size:13px; color:#6b7280; margin: 0 0 20px;">
                    Une permission n'a d'effet que si une route la réclame dans le code
                    (<code>->middleware('permission:votre.slug')</code>). Utilisez le slug exact prévu par l'équipe technique.
                </p>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="slug">Slug (identifiant technique)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" class="form-control" placeholder="ex. rapports.export — laissé vide : généré depuis le nom">
                    @error('slug') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="groupe">Groupe</label>
                    <input type="text" id="groupe" name="groupe" value="{{ old('groupe') }}" class="form-control" placeholder="ex. indemnites">
                    @error('groupe') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="module">Module</label>
                    <input type="text" id="module" name="module" value="{{ old('module') }}" class="form-control" placeholder="ex. rapports">
                    @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="action">Action</label>
                    <input type="text" id="action" name="action" value="{{ old('action') }}" class="form-control" placeholder="ex. export">
                    @error('action') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
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