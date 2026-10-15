@extends('layouts.app')

@section('title', 'SICORE - Import des données')

@section('content')

@php
    $rapport = session('rapport_import');
    $choisi = collect($referentiels)->firstWhere('cle', $entite);
@endphp

<main class="main-content">

    <x-topbar
        title="Import des données de base"
        subtitle="Paramétrage > Import des données"
        icon="fa-solid fa-file-import"
    />

    <section class="content-area">

        <p class="info-import" role="note">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            Importez un référentiel à partir d'un fichier <strong>CSV</strong> (depuis Excel : « Enregistrer sous » → CSV).
            Contrôlez d'abord le fichier : <strong>rien n'est écrit si une seule ligne est en erreur</strong>.
            Réimporter un fichier met à jour les lignes existantes (même code) sans créer de doublon.
            Importez dans l'ordre : IA → IEF, Catégories → Corps, Années académiques → Périodes de paie.
        </p>

        <section class="panel">
            <form method="POST" action="{{ route('systeme.import.traiter') }}" enctype="multipart/form-data" data-import-form>
                @csrf

                <div class="form-grid form-grid--balanced">
                    <div class="form-group">
                        <label for="import-entite">Référentiel</label>
                        <select class="form-control" id="import-entite" name="entite" required data-import-entite>
                            <option value="">Sélectionner…</option>
                            @foreach ($referentiels as $referentiel)
                                <option value="{{ $referentiel['cle'] }}" @selected($entite === $referentiel['cle'])>{{ $referentiel['libelle'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="import-fichier">Fichier CSV <span class="form-group-hint">(5 Mo max)</span></label>
                        <input class="form-control" id="import-fichier" type="file" name="fichier" accept=".csv,.txt" required>
                        @error('fichier')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                @foreach ($referentiels as $referentiel)
                    <div class="import-colonnes" data-import-colonnes="{{ $referentiel['cle'] }}" @if ($entite !== $referentiel['cle']) hidden @endif>
                        <p>
                            Colonnes du fichier (clé de mise à jour : <strong>{{ $referentiel['cle_unicite'] }}</strong>) :
                            <a href="{{ route('systeme.import.modele', $referentiel['cle']) }}">télécharger le modèle</a>
                        </p>
                        <ul>
                            @foreach ($referentiel['colonnes'] as $colonne)
                                <li><code>{{ $colonne['nom'] }}</code> — {{ $colonne['type'] }}{{ $colonne['requis'] ? ', obligatoire' : '' }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div class="actions-group">
                    <button class="btn-secondary" type="submit" name="mode" value="apercu">Contrôler le fichier</button>
                    <button class="btn-primary" type="submit" name="mode" value="importer" data-import-confirmer>Importer</button>
                </div>
            </form>
        </section>

        @if ($rapport)
            <section class="panel">
                <h2>
                    {{ ($rapport['mode'] ?? '') === 'importer' && ($rapport['importe'] ?? false) ? 'Résultat de l\'import' : 'Résultat du contrôle' }}
                </h2>
                <p>
                    {{ $rapport['total'] }} ligne(s) —
                    <strong>{{ $rapport['crees'] }}</strong> à créer,
                    <strong>{{ $rapport['mis_a_jour'] }}</strong> à mettre à jour,
                    <strong>{{ $rapport['inchanges'] }}</strong> inchangée(s),
                    <strong class="{{ $rapport['erreurs'] ? 'texte-erreur' : '' }}">{{ count($rapport['erreurs']) }}</strong> erreur(s).
                </p>

                @if ($rapport['erreurs'])
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Ligne</th><th>Problème</th></tr></thead>
                            <tbody>
                                @foreach ($rapport['erreurs'] as $erreur)
                                    <tr><td>{{ $erreur['ligne'] }}</td><td>{{ $erreur['message'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif

    </section>
</main>

@endsection

@push('styles')
<style>
    .info-import { margin: 0 0 16px; padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; color: #1e3a8a; font-size: 14px; }
    .import-colonnes { margin: 0 0 16px; font-size: 14px; }
    .import-colonnes ul { margin: 6px 0 0; padding-left: 20px; columns: 2; }
    .texte-erreur { color: #b91c1c; }
    .panel + .panel { margin-top: 20px; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var entite = document.querySelector('[data-import-entite]');
        var blocs = document.querySelectorAll('[data-import-colonnes]');

        function afficherColonnes() {
            blocs.forEach(function (bloc) { bloc.hidden = bloc.getAttribute('data-import-colonnes') !== entite.value; });
        }

        entite.addEventListener('change', afficherColonnes);

        document.querySelector('[data-import-confirmer]').addEventListener('click', function (evenement) {
            if (!window.confirm('Importer ce fichier ? Les lignes existantes (même clé) seront mises à jour.')) {
                evenement.preventDefault();
            }
        });
    })();
</script>
@endpush
