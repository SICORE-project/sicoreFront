@extends('layouts.app')

@section('title', 'SICORE - Liste des rôles')

@section('content')
    <main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
        <x-topbar title="Liste des rôles" icon="fa-solid fa-users-cog">
            <button type="button" class="btn-primary" data-role-modal="create">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter un rôle
            </button>
        </x-topbar>

        <section class="content-area">
            <!-- Tableau -->
            <section class="table-card">
                <div class="table-responsive">
                    <table class="table" id="moduleTable">
                        <thead>
                            <tr>
                                <th>Profil</th>
                                <th>Description</th>
                                <th>Type de rôle</th>
                                <th>Statut</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($roles['data'] ?? []) as $role)
                                <tr data-type-role="{{ data_get($role, 'type_role.id', '') }}" data-statut="{{ ($role['est_actif'] ?? false) ? '1' : '0' }}">
                                    <td><strong>{{ $role['nom'] ?? '-' }}</strong></td>
                                    <td>{{ $role['description'] ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ data_get($role, 'type_role.libelle', '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $role['est_actif'] ?? false ? 'badge-success' : 'badge-danger' }}">
                                            {{ $role['est_actif'] ?? false ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="table-actions-inline">
                                            <button type="button" class="table-action" data-role-modal="edit" data-role='@json($role)' title="Modifier le rôle" aria-label="Modifier le rôle"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                                            <form action="{{ route('admin.roles.destroy', $role['id']) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="table-action danger" title="Supprimer le rôle" aria-label="Supprimer le rôle"
                                                    onclick="return confirm('Supprimer ce rôle ?')"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-inbox"
                                            style="font-size: 2rem; color: #9ca3af; display: block; margin-bottom: 8px;"></i>
                                        Aucun rôle trouvé
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="empty-message">Aucune donnée trouvée.</p>
                <div class="pagination" aria-label="Pagination">
                    @if (!empty($roles['links']))
                        @foreach ($roles['links'] as $link)
                            @if ($link['url'])
                                <a href="{{ $link['url'] }}" class="page-btn {{ $link['active'] ? 'active' : '' }}">
                                    {{ $loop->first ? '←' : ($loop->last ? '→' : $link['label']) }}
                                </a>
                            @else
                                <span class="page-btn disabled">{{ $loop->first ? '←' : ($loop->last ? '→' : $link['label']) }}</span>
                            @endif
                        @endforeach
                    @else
                        <button class="page-btn" type="button">←</button>
                        <button class="page-btn active" type="button">1</button>
                        <button class="page-btn" type="button">2</button>
                        <button class="page-btn" type="button">→</button>
                    @endif
                </div>
            </section>
        </section>

        <div id="role-modal" class="role-modal" hidden aria-hidden="true">
            <div class="role-modal__backdrop" data-role-modal-close></div>
            <section class="role-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="role-modal-title">
                <div class="role-modal__header">
                    <h2 id="role-modal-title">Ajouter un rôle</h2>
                    <button type="button" class="role-modal__close" aria-label="Fermer" data-role-modal-close>&times;</button>
                </div>
                <form id="role-form" method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="role-form-method" value="">
                    <div class="form-group">
                        <label for="role-nom">Nom *</label>
                        <input id="role-nom" name="nom" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="role-description">Description</label>
                        <textarea id="role-description" name="description" class="form-control" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="role-type">Type de rôle *</label>
                        <select id="role-type" name="type_role_id" class="form-control" required>
                            <option value="">Sélectionnez un type</option>
                            @foreach($typeRoles as $typeRole)
                                <option value="{{ $typeRole['id'] }}">{{ $typeRole['libelle'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role-status">Statut *</label>
                        <select id="role-status" name="est_actif" class="form-control" required>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                    <div class="actions-group" style="justify-content:flex-end; margin-top:24px;">
                        <button type="button" class="btn-secondary" data-role-modal-close>Annuler</button>
                        <button type="submit" class="btn-primary" id="role-form-submit">Ajouter le rôle</button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    @push('styles')
    <style>
        .role-modal[hidden] { display: none; }
        .role-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 1rem; }
        .role-modal__backdrop { position: absolute; inset: 0; background: rgba(17, 24, 39, .55); }
        .role-modal__dialog { position: relative; width: min(900px, calc(100vw - 40px)); height: auto; max-width: 900px; max-height: calc(100vh - 40px); overflow: auto; background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 24px 50px rgba(0, 0, 0, .2); }
        .role-modal__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
        .role-modal__header h2 { margin: 0; font-size: 1.25rem; }
        .role-modal__close { border: 0; background: none; font-size: 2rem; line-height: 1; cursor: pointer; }
        .role-modal .form-group { margin-bottom: 1rem; }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('role-modal');
            const roleForm = document.getElementById('role-form');
            const methodField = document.getElementById('role-form-method');
            const nomField = document.getElementById('role-nom');
            const descriptionField = document.getElementById('role-description');
            const typeField = document.getElementById('role-type');
            const statusField = document.getElementById('role-status');
            const title = document.getElementById('role-modal-title');
            const submit = document.getElementById('role-form-submit');
            const storeUrl = @json(route('admin.roles.store'));
            const updateBaseUrl = @json(route('admin.roles.index'));

            function openRoleModal(role) {
                const editing = Boolean(role);
                title.textContent = editing ? 'Modifier le rôle' : 'Ajouter un rôle';
                submit.textContent = editing ? 'Enregistrer les modifications' : 'Ajouter le rôle';
                roleForm.action = editing ? `${updateBaseUrl}/${role.id}` : storeUrl;
                methodField.value = editing ? 'PUT' : '';
                nomField.value = role?.nom ?? '';
                descriptionField.value = role?.description ?? '';
                typeField.value = role?.type_role_id ?? '';
                statusField.value = role ? (role.est_actif ? '1' : '0') : '1';
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                nomField.focus();
            }

            function closeRoleModal() {
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                roleForm.reset();
            }

            document.querySelectorAll('[data-role-modal="create"]').forEach((button) => {
                button.addEventListener('click', () => openRoleModal(null));
            });
            document.querySelectorAll('[data-role-modal="edit"]').forEach((button) => {
                button.addEventListener('click', () => openRoleModal(JSON.parse(button.dataset.role)));
            });
            document.querySelectorAll('[data-role-modal-close]').forEach((button) => {
                button.addEventListener('click', closeRoleModal);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) closeRoleModal();
            });
        })();
    </script>
    @endpush
@endsection
