@extends('layouts.app')

@section('title', 'SICORE - Ventilations de crédits')

@php
    $delegationId = (int) data_get($delegation, 'id', request()->route('delegation'));
    $annee = data_get($delegation, 'annee_academique.libelle', data_get($delegation, 'anneeAcademique.libelle', '—'));
    $records = is_array($ventilations ?? null) ? $ventilations : [];
    $corps = data_get($options ?? [], 'corps_enseignants', []);
    $ias = data_get($options ?? [], 'ias', []);
    $iefs = data_get($options ?? [], 'iefs', []);
    $centresExecution = data_get($options ?? [], 'centres_execution', []);
    $budgets = data_get($options ?? [], 'budgets', []);
    $activites = data_get($options ?? [], 'activites', []);
    $canWrite = \App\Support\SicoreAccess::can(request(), 'paie.bulletins.write');
    $storeUrl = route('paie.delegation-credits.ventilations.store', ['delegation' => $delegationId]);
    $updateUrlTemplate = route('paie.delegation-credits.ventilations.update', ['delegation' => $delegationId, 'ventilation' => '__ID__']);
    $total = (float) data_get($stats ?? [], 'montant_total', collect($records)->sum('montant'));
    $lookup = function ($items, $id) {
        foreach ($items as $item) {
            if ((string) data_get($item, 'id') === (string) $id) {
                return data_get($item, 'libelle', '—');
            }
        }
        return '—';
    };
@endphp

@section('content')
<main class="main-content dv-page">
    <x-topbar title="Ventilations de crédits" subtitle="Gestion de la paie > Délégations de crédits > Ventilations" icon="fa-solid fa-layer-group" :search-id="null" />
    <section class="content-area">
        <div class="dv-toolbar">
            <a href="{{ route('paie.delegation-credits') }}" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Retour aux délégations</a>
            @if($canWrite)
                <button type="button" class="btn-primary" id="dv-add"><i class="fa-solid fa-plus"></i> Ajouter une ventilation</button>
            @endif
        </div>

        @if(session('success'))<div class="dv-alert dv-success" role="status">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="dv-alert dv-error" role="alert">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="dv-alert dv-error" role="alert"><strong>Veuillez vérifier le formulaire :</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="stats-grid four">
            <article class="stat-card"><div><p class="stat-label">Référence</p><p class="stat-value dv-stat-text">{{ data_get($delegation, 'reference_lettre', '—') }}</p><p class="stat-note">Délégation de crédits</p></div><span class="stat-icon blue"><i class="fa-solid fa-file-lines"></i></span></article>
            <article class="stat-card"><div><p class="stat-label">Année académique</p><p class="stat-value dv-stat-text">{{ $annee }}</p><p class="stat-note">Héritée de la délégation</p></div><span class="stat-icon green"><i class="fa-solid fa-calendar"></i></span></article>
            <article class="stat-card"><div><p class="stat-label">Ventilations</p><p class="stat-value">{{ count($records) }}</p><p class="stat-note">Répartitions enregistrées</p></div><span class="stat-icon purple"><i class="fa-solid fa-layer-group"></i></span></article>
            <article class="stat-card"><div><p class="stat-label">Montant ventilé</p><p class="stat-value">{{ number_format($total, 0, ',', ' ') }} <small>FCFA</small></p><p class="stat-note">Somme des ventilations</p></div><span class="stat-icon yellow"><i class="fa-solid fa-coins"></i></span></article>
        </div>

        <section class="dv-panel">
            <div class="dv-panel-heading"><h2>Liste des ventilations</h2><span>{{ count($records) }} résultat(s)</span></div>
            <div class="dv-table-scroll">
                <table class="dv-table">
                    <thead><tr><th>N° Carton</th><th>Corps d'enseignant</th><th>IA </th><th>Montant</th><th>N° Autorisation</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($records as $item)
                            <tr>
                                <td>{{ data_get($item, 'numero_carton') ?: '—' }}</td>
                                <td>{{ $lookup($corps, data_get($item, 'corps_enseignant_id')) }}</td>
                                <td>{{ $lookup($ias, data_get($item, 'ia_id')) }} / {{ $lookup($iefs, data_get($item, 'ief_id')) }}</td>

                                <td class="dv-amount">{{ number_format((float) data_get($item, 'montant', 0), 0, ',', ' ') }} FCFA</td>
                                
                                <td>{{ data_get($item, 'numero_autorisation') ?: '—' }}</td>
                                <td><div class="dv-actions">
                                    <button type="button" class="icon-action" data-dv-view="{{ data_get($item, 'id') }}" title="Consulter" aria-label="Consulter"><i class="fa-solid fa-eye"></i></button>
                                    @if($canWrite)
                                        <button type="button" class="icon-action" data-dv-edit="{{ data_get($item, 'id') }}" title="Modifier" aria-label="Modifier"><i class="fa-solid fa-pen"></i></button>
                                        <form action="{{ route('paie.delegation-credits.ventilations.destroy', ['delegation' => $delegationId, 'ventilation' => data_get($item, 'id')]) }}" method="POST" class="dv-delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="icon-action icon-action-danger" title="Supprimer" aria-label="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="dv-empty"><x-table-empty-state>Aucune ventilation enregistrée pour cette délégation.</x-table-empty-state></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</main>

<div class="modal-backdrop" id="dv-modal" data-modal hidden>
    <div class="modal-dialog dv-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="dv-modal-title">
        <div class="modal-header"><h2 id="dv-modal-title">Ajouter une ventilation</h2><button type="button" class="modal-close" data-dv-close aria-label="Fermer">&times;</button></div>
        <form id="dv-form" action="{{ $storeUrl }}" method="POST">
            @csrf
            <div id="dv-method"></div>
            <div class="dv-form-grid">
                <div class="form-group"><label for="dv-annee">Année académique</label><input id="dv-annee" class="form-control" value="{{ $annee }}" readonly></div>
                <div class="form-group"><label for="dv-type">Type de ventilation *</label><select class="form-control" id="dv-type" name="type_ventilation" required><option value="">Sélectionner</option><option value="salaire">Salaire</option><option value="prime_scolaire">Prime scolaire</option></select></div>
                <div class="form-group"><label for="dv-corps">Corps d'enseignant *</label><select class="form-control" id="dv-corps" name="corps_enseignant_id" required><option value="">Sélectionner</option>@foreach($corps as $item)<option value="{{ data_get($item, 'id') }}">{{ data_get($item, 'libelle') }}</option>@endforeach</select></div>
                <div class="form-group"><label for="dv-ia">IA *</label><select class="form-control" id="dv-ia" name="ia_id" required><option value="">Sélectionner</option>@foreach($ias as $item)<option value="{{ data_get($item, 'id') }}">{{ data_get($item, 'libelle') }}</option>@endforeach</select></div>
                <div class="form-group"><label for="dv-ief">IEF *</label><select class="form-control" id="dv-ief" name="ief_id"><option value="">Toutes les IEF / Niveau IA uniquement</option>@foreach($iefs as $item)<option value="{{ data_get($item, 'id') }}" data-ia="{{ data_get($item, 'ia_id') }}">{{ data_get($item, 'libelle') }}</option>@endforeach</select></div>
                <div class="form-group dv-full"><label for="dv-centre">Centre d'exécution *</label><div class="dv-code-pair"><select class="form-control" id="dv-centre" name="centre_execution" required><option value="">Sélectionner un code</option>@foreach($centresExecution as $item)<option value="{{ data_get($item, 'code') }}" data-libelle="{{ data_get($item, 'libelle') }}" data-ia-code="{{ data_get($item, 'ia_code') }}">{{ data_get($item, 'code') }}</option>@endforeach</select><input class="form-control" id="dv-centre-libelle" placeholder="Libellé automatique" readonly aria-label="Libellé Centre d'exécution" /></div></div>
                <div class="form-group dv-full"><label for="dv-budget">Budget *</label><div class="dv-code-pair"><select class="form-control" id="dv-budget" name="budget" required><option value="">Sélectionner un code</option>@foreach($budgets as $item)<option value="{{ data_get($item, 'code') }}" data-libelle="{{ data_get($item, 'libelle') }}">{{ data_get($item, 'code') }}</option>@endforeach</select><input class="form-control" id="dv-budget-libelle" placeholder="Libellé automatique" readonly aria-label="Libellé Budget" /></div></div>
                <div class="form-group dv-full"><label for="dv-activite">Activité *</label><div class="dv-code-pair"><select class="form-control" id="dv-activite" name="activite" required><option value="">Sélectionner un code</option>@foreach($activites as $item)<option value="{{ data_get($item, 'code') }}" data-libelle="{{ data_get($item, 'libelle') }}">{{ data_get($item, 'code') }}</option>@endforeach</select><input class="form-control" id="dv-activite-libelle" placeholder="Libellé automatique" readonly aria-label="Libellé Activité" /></div></div>
                <div class="form-group"><label for="dv-imputation">Imputation budgétaire *</label><input class="form-control" id="dv-imputation" name="imputation_budgetaire" maxlength="255" required></div>
                <div class="form-group"><label for="dv-autorisation">N° Autorisation *</label><input class="form-control" id="dv-autorisation" name="numero_autorisation" maxlength="255" required></div>
                <!--div class="form-group"><label for="dv-carton">N° Carton</label><input class="form-control" id="dv-carton" name="numero_carton" maxlength="255"></div-->
                <!--div class="form-group"><label for="dv-engagement">Engagement</label><input class="form-control" id="dv-engagement" name="engagement" maxlength="255"></div-->
                <div class="form-group dv-full"><label for="dv-montant">Montant (FCFA) *</label><input class="form-control" id="dv-montant" name="montant" type="number" min="0.01" step="0.01" required></div>
            </div>
            <div class="dv-modal-actions"><button type="button" class="btn-secondary" data-dv-close>Annuler</button><button type="submit" class="btn-primary" id="dv-submit">Enregistrer</button></div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.dv-page .dv-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.dv-page .dv-stat-text{font-size:1.12rem;overflow-wrap:anywhere}
.dv-panel{background:var(--surface,#fff);border:1px solid var(--border-color,#e5e7eb);border-radius:12px;margin-top:22px;overflow:hidden}
.dv-panel-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #e5e7eb}
.dv-panel-heading h2{font-size:1.1rem;margin:0}.dv-panel-heading span{color:#64748b;font-size:.9rem}
.dv-table-scroll{overflow-x:auto}.dv-table{width:100%;border-collapse:collapse;min-width:1050px}.dv-table th,.dv-table td{padding:13px 14px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:middle}.dv-table th{font-size:.85rem;background:#f8fafc;color:#475569}.dv-amount{font-weight:700;white-space:nowrap}.dv-actions{display:flex;gap:6px;align-items:center}.dv-delete-form{margin:0}.dv-empty{text-align:center;padding:35px!important}
.dv-alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}.dv-success{background:#ecfdf5;color:#065f46}.dv-error{background:#fef2f2;color:#991b1b}.dv-alert ul{margin:6px 0 0;padding-left:22px}
.dv-modal-dialog{width:min(760px,calc(100vw - 28px));max-height:90vh;overflow-y:auto;padding:24px}.dv-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}.dv-form-grid .dv-full{grid-column:1/-1}.dv-modal-actions{display:flex;justify-content:flex-end;gap:12px;margin-top:24px}.dv-form-grid .form-control{width:100%}.dv-form-grid label{display:block;margin-bottom:6px}.dv-page .btn-secondary,.dv-page .btn-primary{text-decoration:none}.dv-code-pair{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.3fr);gap:10px}.dv-code-pair input[readonly]{background:#f1f5f9;color:#334155}
@media(max-width:760px){.dv-code-pair{grid-template-columns:1fr}.dv-form-grid{grid-template-columns:1fr}.dv-form-grid .dv-full{grid-column:auto}.dv-modal-dialog{padding:18px}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const records = {{ \Illuminate\Support\Js::from($records) }};
    const storeUrl = {{ \Illuminate\Support\Js::from($storeUrl) }};
    const updateTemplate = {{ \Illuminate\Support\Js::from($updateUrlTemplate) }};
    const oldValues = {{ \Illuminate\Support\Js::from(session()->getOldInput()) }};
    const hasErrors = {{ \Illuminate\Support\Js::from($errors->any()) }};
    const modal = document.getElementById('dv-modal');
    const form = document.getElementById('dv-form');
    const method = document.getElementById('dv-method');
    const title = document.getElementById('dv-modal-title');
    const submit = document.getElementById('dv-submit');
    const ia = document.getElementById('dv-ia');
    const ief = document.getElementById('dv-ief');
    const codeFields = ['centre', 'budget', 'activite'];
    function syncLibelles() {
        codeFields.forEach(field => {
            const select = document.getElementById('dv-' + field);
            const label = document.getElementById('dv-' + field + '-libelle');
            label.value = select.selectedOptions[0]?.dataset.libelle || '';
        });
    }
    codeFields.forEach(field => document.getElementById('dv-' + field).addEventListener('change', syncLibelles));
    const fields = ['type_ventilation','corps_enseignant_id','ia_id','ief_id','centre_execution','budget','activite','imputation_budgetaire','numero_autorisation','montant'];
    const byId = id => records.find(row => String(row.id) === String(id));
    const open = () => { modal.hidden = false; };
    const close = () => { modal.hidden = true; };
    function filterIefs(selected = '') {
        const selectedIa = String(ia.value || '');
        let found = false;
        Array.from(ief.options).forEach(option => {
            if (!option.value) return;
            const match = selectedIa !== '' && option.dataset.ia === selectedIa;
            option.hidden = !match;
            option.disabled = !match;
            if (match && option.value === String(selected)) found = true;
        });
        ief.value = found ? String(selected) : '';
    }
    function setReadonly(readonly) {
        form.querySelectorAll('[name]').forEach(input => {
            if (input.name !== '_token' && input.name !== '_method') input.disabled = readonly;
        });
        submit.hidden = readonly;
    }
    function reset() {
        form.reset();
        form.action = storeUrl;
        method.innerHTML = '';
        title.textContent = 'Ajouter une ventilation';
        submit.textContent = 'Enregistrer';
        setReadonly(false);
        filterIefs();
        syncLibelles();
    }
    function populate(row, readonly = false) {
        reset();
        fields.forEach(field => {
            const input = form.elements.namedItem(field);
            if (input) input.value = row[field] ?? '';
        });
        filterIefs(row.ief_id);
        syncLibelles();
        if (readonly) {
            title.textContent = 'Consulter la ventilation';
            setReadonly(true);
        } else {
            title.textContent = 'Modifier la ventilation';
            submit.textContent = 'Enregistrer les modifications';
            form.action = updateTemplate.replace('__ID__', encodeURIComponent(row.id));
            method.innerHTML = '<input type="hidden" name="_method" value="PUT"><input type="hidden" name="_ventilation_id" value="' + String(row.id).replace(/[^0-9]/g, '') + '">';
        }
        open();
    }
    ia.addEventListener('change', () => filterIefs());
    document.getElementById('dv-add')?.addEventListener('click', () => { reset(); open(); });
    document.querySelectorAll('[data-dv-view]').forEach(button => button.addEventListener('click', () => {
        const row = byId(button.dataset.dvView); if (row) populate(row, true);
    }));
    document.querySelectorAll('[data-dv-edit]').forEach(button => button.addEventListener('click', () => {
        const row = byId(button.dataset.dvEdit); if (row) populate(row);
    }));
    document.querySelectorAll('[data-dv-close]').forEach(button => button.addEventListener('click', close));
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });
    document.querySelectorAll('.dv-delete-form').forEach(deleteForm => deleteForm.addEventListener('submit', event => {
        if (!window.confirm('Supprimer définitivement cette ventilation ?')) event.preventDefault();
    }));
    if (hasErrors && Object.keys(oldValues).length) {
        const existingId = oldValues._method === 'PUT' ? String(oldValues._ventilation_id || '') : '';
        const existing = existingId ? byId(existingId) : null;
        if (existing) populate(existing); else reset();
        fields.forEach(field => {
            const input = form.elements.namedItem(field);
            if (input && oldValues[field] !== undefined) input.value = oldValues[field];
        });
        filterIefs(oldValues.ief_id ?? '');
        syncLibelles();
        open();
    }
});
</script>
@endpush
