
@extends('layouts.app')

@section('title', 'SICORE - Délégations de crédits')

@php
    $payload = $moduleData ?? [];
    $result = data_get($payload, 'data', []);

    $delegations = data_get($result, 'data', $result);
    $delegations = is_array($delegations) ? array_values($delegations) : [];

    $meta = data_get($payload, 'meta', data_get($result, 'meta', []));
    $stats = data_get($payload, 'stats', []);

    $optionData = data_get($options ?? [], 'data', $options ?? []);

    $annees = data_get($optionData, 'annees_academiques', []);

    $total = (int) data_get($stats, 'total', data_get($meta, 'total', count($delegations)));
    $montantTotal = (float) data_get($stats, 'montant_total', 0);
    $totalVentilations = (int) data_get($stats, 'ventilations', 0);
    $montantMoyen = $total > 0 ? $montantTotal / $total : 0;

    $updateRouteTemplate = route('paie.delegation-credits.update', ['delegation' => '__ID__']);
    $deleteRouteTemplate = route('paie.delegation-credits.destroy', ['delegation' => '__ID__']);

    $canWrite = \App\Support\SicoreAccess::can(
        request(),
        'paie.bulletins.write'
    );
@endphp

@section('content')
<main class="main-content dc-page">

    <x-topbar
        title="Délégations de crédits"
        subtitle="Gestion de la paie > Délégations de crédits"
        icon="fa-solid fa-money-bill-transfer"
        :search-id="null"
    />

    <section class="content-area">

        {{-- Statistiques --}}
        <div class="stats-grid four">

            <article class="stat-card">
                <div>
                    <p class="stat-label">Délégations</p>
                    <p class="stat-value">
                        {{ number_format($total, 0, ',', ' ') }}
                    </p>
                    <p class="stat-note">Délégations enregistrées</p>
                </div>
                <span class="stat-icon green">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Montant total</p>
                    <p class="stat-value">
                        {{ number_format($montantTotal, 0, ',', ' ') }}
                        <small>FCFA</small>
                    </p>
                    <p class="stat-note">Crédits délégués</p>
                </div>
                <span class="stat-icon blue">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Ventilations</p>
                    <p class="stat-value">
                        {{ number_format($totalVentilations, 0, ',', ' ') }}
                    </p>
                    <p class="stat-note">Répartitions enregistrées</p>
                </div>
                <span class="stat-icon purple">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
            </article>

            <article class="stat-card">
                <div>
                    <p class="stat-label">Montant moyen</p>
                    <p class="stat-value">
                        {{ number_format($montantMoyen, 0, ',', ' ') }}
                        <small>FCFA</small>
                    </p>
                    <p class="stat-note">Par délégation</p>
                </div>
                <span class="stat-icon yellow">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </article>

        </div>

        {{-- Actions --}}
        <div class="actions-row">
            <p class="breadcrumb">
                Gestion de la paie &gt; Délégations de crédits
            </p>

            @if($canWrite)
                <div class="actions-group">
                    <button
                        type="button"
                        class="btn-primary"
                        id="dc-add"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Ajouter une délégation
                    </button>
                </div>
            @endif
        </div>

        @if(session('success'))
            <p class="dc-alert dc-success">
                {{ session('success') }}
            </p>
        @endif

        @if(session('error'))
            <p class="dc-alert dc-error">
                {{ session('error') }}
            </p>
        @endif

        @if(!empty($apiError))
            <p class="dc-alert dc-error">
                Impossible de charger les délégations de crédits.
            </p>
        @endif

        @if($errors->any())
            <div class="dc-alert dc-error" role="alert">
                <strong>Veuillez corriger les erreurs :</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filtres --}}
        <form
            id="dc-filter-form"
            method="GET"
            action="{{ route('paie.delegation-credits') }}"
            class="filter-panel parametrage-filters"
        >

            <div class="form-group">
                <label for="dc-filter-reference">Référence lettre</label>
                <input
                    type="text"
                    class="form-control"
                    id="dc-filter-reference"
                    name="reference_lettre"
                    value="{{ request('reference_lettre') }}"
                    placeholder="Rechercher une référence"
                >
            </div>

            <div class="form-group">
                <label for="dc-filter-annee">Année académique</label>
                <select
                    class="form-control"
                    id="dc-filter-annee"
                    name="annee_academique_id"
                >
                    <option value="">Toutes les années</option>

                    @foreach($annees as $annee)
                        <option
                            value="{{ data_get($annee, 'id') }}"
                            @selected((string) request('annee_academique_id') === (string) data_get($annee, 'id'))
                        >
                            {{ data_get($annee, 'libelle', data_get($annee, 'annee', 'Année #'.data_get($annee, 'id'))) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="dc-filter-periode">Période de paie</label>
                <input type="text" class="form-control" id="dc-filter-periode"
                       name="periode_paie" maxlength="100"
                       value="{{ request('periode_paie') }}"
                       placeholder="Rechercher une période">
            </div>

            <div class="form-group dc-filter-buttons">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Rechercher
                </button>

                <a
                    href="{{ route('paie.delegation-credits') }}"
                    class="btn-secondary"
                >
                    Réinitialiser
                </a>
            </div>

        </form>

        {{-- Tableau --}}
        <section class="table-card">

            <div class="table-responsive">
                <table class="table" id="dc-table">

                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Objet</th>
                            <th>Année académique</th>
                            <th>Période</th>
                            <th>Date</th>
                            <th>Montant total</th>
                            <th>Ventilations</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($delegations as $delegation)
                            <tr>
                                <td>
                                    <strong>
                                        {{ data_get($delegation, 'reference_lettre', '—') }}
                                    </strong>
                                </td>

                                <td>{{ data_get($delegation, 'objet', '—') }}</td>

                                <td>
                                    {{ data_get($delegation, 'annee_academique.libelle', data_get($delegation, 'anneeAcademique.libelle', '—')) }}
                                </td>

                                <td>
                                    {{ data_get($delegation, 'periode_paie', '—') }}
                                </td>

                                <td>
                                    {{ data_get($delegation, 'date_delegation', '—') }}
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format((float) data_get($delegation, 'montant_total', 0), 0, ',', ' ') }}
                                        FCFA
                                    </strong>
                                </td>

                                <td>
                                    {{ count(data_get($delegation, 'ventilations', [])) }}
                                </td>

                                <td class="actions-cell">
                                <a
    href="{{ route('paie.delegation-credits.ventilations', ['delegation' => data_get($delegation, 'id')]) }}"
    class="btn-secondary dc-ventilation-link"
    title="Gérer les ventilations"
>
    <i class="fa-solid fa-layer-group"></i>
    Ventilations
</a>
                                    <button
                                        type="button"
                                        class="icon-action"
                                        data-dc-view="{{ data_get($delegation, 'id') }}"
                                        title="Consulter"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    @if($canWrite)
                                        <button
                                            type="button"
                                            class="icon-action"
                                            data-dc-edit="{{ data_get($delegation, 'id') }}"
                                            title="Modifier"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="icon-action icon-action-danger"
                                            data-dc-delete="{{ data_get($delegation, 'id') }}"
                                            title="Supprimer"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-message">
                                    <x-table-empty-state>
                                        Aucune délégation de crédits trouvée.
                                    </x-table-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="actions-row dc-pagination">
                <p>
                    {{ data_get($meta, 'total', count($delegations)) }}
                    résultat(s)
                </p>

                <div class="form-group">
                    <label for="dc-per-page">Résultats par page</label>
                    <select
                        class="form-control"
                        id="dc-per-page"
                        name="per_page"
                        form="dc-filter-form"
                    >
                        @foreach([10, 25, 50, 100] as $limit)
                            <option
                                value="{{ $limit }}"
                                @selected((int) request('per_page', 10) === $limit)
                            >
                                {{ $limit }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!empty($meta))
                @include('components.pagination', ['pagination' => $meta])
            @endif

        </section>

    </section>
</main>

{{-- Formulaire modal --}}
<div
    class="modal-backdrop"
    id="dc-modal"
    data-modal
    hidden
>
    <div
        class="modal-dialog dc-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="dc-modal-title"
    >
        <div class="modal-header">
            <h2 id="dc-modal-title">Ajouter une délégation</h2>

            <button
                type="button"
                class="modal-close"
                data-dc-close
                aria-label="Fermer"
            >
                &times;
            </button>
        </div>

        <form
            id="dc-form"
            action="{{ route('paie.delegation-credits.store') }}"
            method="POST"
        >
            @csrf

            <div id="dc-method"></div>

            <div class="dc-form-grid">

                <div class="form-field">
                    <label for="dc-annee">Année académique *</label>
                    <select
                        id="dc-annee"
                        name="annee_academique_id"
                        required
                    >
                        <option value="">Sélectionner</option>

                        @foreach($annees as $annee)
                            <option value="{{ data_get($annee, 'id') }}">
                                {{ data_get($annee, 'libelle', data_get($annee, 'annee', 'Année #'.data_get($annee, 'id'))) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="dc-reference">Référence lettre *</label>
                    <input
                        type="text"
                        id="dc-reference"
                        name="reference_lettre"
                        maxlength="191"
                        required
                    >
                </div>

                <div class="form-field dc-full">
                    <label for="dc-objet">Objet *</label>
                    <input
                        type="text"
                        id="dc-objet"
                        name="objet"
                        maxlength="1000"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="dc-periode">Période de paie *</label>
                    <input type="text" id="dc-periode" name="periode_paie"
                           maxlength="100" placeholder="Ex. : Août 2026" required>
                </div>

                <div class="form-field">
                    <label for="dc-date">Date de délégation *</label>
                    <input
                        type="date"
                        id="dc-date"
                        name="date_delegation"
                        value="{{ now()->format('Y-m-d') }}"
                        required
                    >
                </div>

            </div>

            <div class="form-actions">
                <button
                    type="button"
                    class="btn-secondary"
                    data-dc-close
                >
                    Annuler
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                    id="dc-submit"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    Enregistrer
                </button>
            </div>

        </form>
    </div>
</div>

@if($canWrite)
<form
    id="dc-delete-form"
    method="POST"
    hidden
>
    @csrf
    @method('DELETE')
</form>
@endif

@endsection

@push('styles')
<style>
.dc-page .dc-alert {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: 9px;
}

.dc-page .dc-success {
    background: #ecfdf5;
    color: #166534;
}

.dc-page .dc-error {
    background: #fef2f2;
    color: #b91c1c;
}

.dc-page .dc-filter-buttons {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.dc-page .dc-pagination {
    padding: 18px 24px;
    flex-wrap: wrap;
}

.dc-page .dc-pagination p {
    margin: 0;
}

.dc-modal-dialog {
    width: 100%;
    max-width: 900px;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    padding: 30px;
    background: #fff;
    border-radius: 16px;
}

.dc-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.dc-form-grid .dc-full {
    grid-column: 1 / -1;
}

.dc-modal-dialog .form-field {
    display: grid;
    gap: 8px;
}

.dc-modal-dialog .form-field label {
    font-size: 13px;
    font-weight: 700;
    color: #334155;
}

.dc-modal-dialog .form-field input,
.dc-modal-dialog .form-field select {
    width: 100%;
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    box-sizing: border-box;
}

 .dc-ventilation-link { margin-right: 8px; white-space: nowrap; }
 .dc-ventilation-link:disabled { opacity: .55; cursor: not-allowed; }

.dc-modal-dialog .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.dc-modal-dialog [hidden],
.modal-backdrop[hidden] {
    display: none !important;
}

@media(max-width: 760px) {
    .dc-form-grid {
        grid-template-columns: 1fr;
    }

    .dc-form-grid .dc-full {
        grid-column: auto;
    }

    .dc-modal-dialog {
        padding: 20px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const records = {{ \Illuminate\Support\Js::from($delegations) }};
    const canWrite = {{ \Illuminate\Support\Js::from($canWrite) }};
    const storeUrl = {{ \Illuminate\Support\Js::from(route('paie.delegation-credits.store')) }};
    const updateUrlTemplate = {{ \Illuminate\Support\Js::from($updateRouteTemplate) }};
    const deleteUrlTemplate = {{ \Illuminate\Support\Js::from($deleteRouteTemplate) }};

    const modal = document.getElementById('dc-modal');
    const form = document.getElementById('dc-form');
    const method = document.getElementById('dc-method');
    const submit = document.getElementById('dc-submit');
    const title = document.getElementById('dc-modal-title');

    function openModal() { modal.hidden = false; }
    function closeModal() { modal.hidden = true; }
    function findRecord(id) {
        return records.find(item => String(item.id) === String(id));
    }
    function resetForm() {
        form.reset();
        form.action = storeUrl;
        method.innerHTML = '';
        title.textContent = 'Ajouter une délégation';
        submit.hidden = false;
        form.querySelectorAll('input, select').forEach(input => {
            input.disabled = false;
        });
    }
    function populateForm(record, readonly = false) {
        resetForm();
        document.getElementById('dc-annee').value = record.annee_academique_id || '';
        document.getElementById('dc-reference').value = record.reference_lettre || '';
        document.getElementById('dc-objet').value = record.objet || '';
        document.getElementById('dc-periode').value = record.periode_paie || '';
        document.getElementById('dc-date').value = String(record.date_delegation || '').substring(0, 10);

        if (readonly) {
            title.textContent = 'Consulter la délégation';
            form.querySelectorAll('input, select').forEach(input => {
                input.disabled = true;
            });
            submit.hidden = true;
        } else {
            title.textContent = 'Modifier la délégation';
            form.action = updateUrlTemplate.replace('__ID__', record.id);
            method.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        }
        openModal();
    }

    document.getElementById('dc-add')?.addEventListener('click', () => {
        resetForm();
        openModal();
    });
    document.querySelectorAll('[data-dc-view]').forEach(button => {
        button.addEventListener('click', () => {
            const record = findRecord(button.dataset.dcView);
            if (record) populateForm(record, true);
        });
    });
    if (canWrite) {
        document.querySelectorAll('[data-dc-edit]').forEach(button => {
            button.addEventListener('click', () => {
                const record = findRecord(button.dataset.dcEdit);
                if (record) populateForm(record);
            });
        });
        document.querySelectorAll('[data-dc-delete]').forEach(button => {
            button.addEventListener('click', () => {
                const record = findRecord(button.dataset.dcDelete);
                if (!record) return;
                if ((record.ventilations || []).length > 0) {
                    alert('Cette délégation possède des ventilations. Supprimez-les avant la délégation.');
                    return;
                }
                if (!confirm('Supprimer définitivement la délégation « '
                    + (record.reference_lettre || record.id) + ' » ?')) return;
                const deleteForm = document.getElementById('dc-delete-form');
                deleteForm.action = deleteUrlTemplate.replace('__ID__', record.id);
                deleteForm.requestSubmit();
            });
        });
    }
    document.querySelectorAll('[data-dc-close]').forEach(button => {
        button.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });
    document.getElementById('dc-per-page')?.addEventListener('change', () => {
        document.getElementById('dc-filter-form').requestSubmit();
    });
    form.addEventListener('submit', event => {
        if (!canWrite || submit.hidden) event.preventDefault();
    });

    @if($errors->any())
        resetForm();
        const oldValues = {{ \Illuminate\Support\Js::from(old()) }};
        document.getElementById('dc-annee').value = oldValues.annee_academique_id || '';
        document.getElementById('dc-reference').value = oldValues.reference_lettre || '';
        document.getElementById('dc-objet').value = oldValues.objet || '';
        document.getElementById('dc-periode').value = oldValues.periode_paie || '';
        document.getElementById('dc-date').value = oldValues.date_delegation || '';
        openModal();
    @endif
});
</script>
@endpush
