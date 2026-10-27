@extends('layouts.app')

@section('title', 'SICORE - Import des données')

@section('content')

@php
    $rapport = session('rapport_import');
@endphp

<main class="main-content">

    <x-topbar
        title="Import des données de base"
        subtitle="Paramétrage > Import des données"
        icon="fa-solid fa-file-import"
    />

    <section class="content-area">

        <section class="form-card import-card">

            <div class="form-card-header">
                <div>
                    <h2>Import des données de base</h2>
                    <p class="breadcrumb">Référentiels du paramétrage, à partir d'un fichier CSV</p>
                </div>
            </div>

            <form class="teacher-form" method="POST" action="{{ route('systeme.import.traiter') }}" enctype="multipart/form-data" data-import-form>
                @csrf

                <p class="info-import" role="note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    Depuis Excel : « Enregistrer sous » → CSV. Les lignes valides sont importées, les lignes en erreur sont signalées sans bloquer l'import.
                    Les lignes dont la clé existe déjà sont <strong>ignorées</strong> (aucune mise à jour, aucun doublon créé).
                </p>

                <section class="form-section">
                    <h3>1. Choisir le référentiel et le fichier</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="import-entite">Référentiel <span class="required">*</span></label>
                            <select class="form-control" id="import-entite" name="entite" required data-import-entite>
                                <option value="">Sélectionner…</option>
                                @foreach ($referentiels as $referentiel)
                                    <option value="{{ $referentiel['cle'] }}" @selected($entite === $referentiel['cle'])>{{ $referentiel['libelle'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="import-fichier">Fichier CSV <span class="required">*</span> <span class="form-group-hint">(5 Mo max)</span></label>
                            <input class="form-control" id="import-fichier" type="file" name="fichier" accept=".csv,.txt" required>
                            @error('fichier')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="form-section" data-import-aide>
                    <h3>2. Colonnes attendues</h3>

                    <p class="import-vide" data-import-vide @if ($entite) hidden @endif>Sélectionnez un référentiel pour voir les colonnes du fichier.</p>

                    @foreach ($referentiels as $referentiel)
                        <div class="import-colonnes" data-import-colonnes="{{ $referentiel['cle'] }}" @if ($entite !== $referentiel['cle']) hidden @endif>
                            <p>
                                Clé de mise à jour : <strong>{{ $referentiel['cle_unicite'] }}</strong> —
                                <a href="{{ route('systeme.import.modele', $referentiel['cle']) }}"><i class="fa-solid fa-download" aria-hidden="true"></i> Télécharger le modèle CSV</a>
                            </p>
                            <ul>
                                @foreach ($referentiel['colonnes'] as $colonne)
                                    <li><code>{{ $colonne['nom'] }}</code> — {{ $colonne['type'] }}{{ $colonne['requis'] ? ', obligatoire' : '' }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                    <p class="import-ordre">Ordre conseillé : Régions → Départements → Communes ; Régions → IA → IEF → Établissements ; Corps → Catégories → Diplômes. Une cellule vide dans « est_actif » garde la valeur par défaut (actif).</p>
                </section>

                <div class="form-actions">
                    <button class="btn-secondary" type="submit" name="mode" value="apercu">Contrôler le fichier</button>
                    <button class="btn-primary" type="submit" name="mode" value="importer" data-import-confirmer>Importer</button>
                </div>
            </form>
        </section>

        @if ($rapport)
            <section class="form-card import-card import-rapport">
                <div class="form-card-header">
                    <h2>{{ match ($rapport['statut'] ?? '') {
                        'ok' => ($rapport['importe'] ?? false) ? 'Import terminé' : 'Contrôle terminé',
                        'partiel' => ($rapport['importe'] ?? false) ? 'Import partiel' : 'Contrôle terminé (avec erreurs)',
                        default => 'Aucune ligne importée',
                    } }}</h2>
                </div>

                <div class="teacher-form">
                    <div class="import-chiffres">
                        <div><strong>{{ $rapport['total'] }}</strong><span>ligne(s)</span></div>
                        <div><strong>{{ $rapport['crees'] }}</strong><span>créée(s)</span></div>
                        <div><strong>{{ $rapport['ignores'] }}</strong><span>ignorée(s) (doublon)</span></div>
                        <div class="{{ $rapport['erreurs'] ? 'texte-erreur' : '' }}"><strong>{{ count($rapport['erreurs']) }}</strong><span>erreur(s)</span></div>
                    </div>

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
                </div>
            </section>
        @endif

    </section>
</main>

@endsection

@push('styles')
<style>
    .import-card { max-width: 900px; margin-bottom: 20px; }
    .info-import { margin: 0; padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; color: #1e3a8a; font-size: 14px; }
    .import-colonnes, .import-vide, .import-ordre { margin: 0; font-size: 14px; }
    .import-ordre { color: #6b7280; }
    .import-colonnes ul { margin: 8px 0 0; padding-left: 20px; columns: 2; }
    .import-chiffres { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 12px; }
    .import-chiffres div { display: grid; gap: 2px; padding: 12px; border: 1px solid var(--border-soft, #e5e7eb); border-radius: 10px; text-align: center; }
    .import-chiffres strong { font-size: 22px; }
    .import-chiffres span { color: #6b7280; font-size: 13px; }
    .texte-erreur strong { color: #b91c1c; }
    @media (max-width: 720px) {
        .import-colonnes ul { columns: 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var entite = document.querySelector('[data-import-entite]');
        var blocs = document.querySelectorAll('[data-import-colonnes]');
        var vide = document.querySelector('[data-import-vide]');

        function afficherColonnes() {
            blocs.forEach(function (bloc) { bloc.hidden = bloc.getAttribute('data-import-colonnes') !== entite.value; });
            vide.hidden = entite.value !== '';
        }

        entite.addEventListener('change', afficherColonnes);

        document.querySelector('[data-import-confirmer]').addEventListener('click', function (evenement) {
            if (!window.confirm('Importer ce fichier ? Les lignes valides seront créées ; celles dont la clé existe déjà seront ignorées.')) {
                evenement.preventDefault();
            }
        });
    })();
</script>
@endpush