@extends('layouts.app')

@section('title', 'SICORE - Créer une permission')

@section('content')
<main class="main-content">
    <x-topbar title="Créer une permission" subtitle="Gestion Utilisateur > Permissions > Nouvelle" icon="fa-solid fa-lock" />

    <section class="content-area">
        @if(session('info')) <div class="alert alert-info">{{ session('info') }}</div> @endif
        <section class="table-card" style="padding: 24px;">

            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required class="form-control">
                    @error('nom') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="filter-panel" aria-label="Groupe et module" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="groupe">Groupe *</label>
                        <select id="groupe" name="groupe" required class="form-control">
                            <option value="">Sélectionner un groupe</option>
                            @foreach($groupes as $groupe)
                                <option value="{{ $groupe['value'] }}" @selected(old('groupe', request('groupe')) === $groupe['value'])>{{ $groupe['label'] }}</option>
                            @endforeach
                        </select>
                        @error('groupe') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="module">Module *</label>
                        <select id="module" name="module" required class="form-control">
                            <option value="">Sélectionner un module</option>
                            @foreach($modules as $module)
                                <option value="{{ $module['value'] }}" @selected(old('module', request('module')) === $module['value'])>{{ $module['label'] }}</option>
                            @endforeach
                        </select>
                        @error('module') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="action">Action *</label>
                    <select id="action" name="action" required class="form-control">
                        <option value="">Sélectionner une action</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(old('action') === $action)>{{ \Illuminate\Support\Str::of($action)->replace(['_', '-'], ' ')->title() }}</option>
                        @endforeach
                    </select>
                    @error('action') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    @error('description') <p style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="est_actif" value="1" checked>
                        <span style="font-size: 14px; font-weight: 500;">Actif</span>
                    </label>
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
