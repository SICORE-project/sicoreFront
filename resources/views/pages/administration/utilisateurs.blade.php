@extends('layouts.app')

@section('title', 'SICORE - Utilisateurs')

@section('content')
<main class="main-content">
    <x-topbar title="Utilisateurs" subtitle="Gestion utilisateur > Accès organisationnels" icon="fa-solid fa-user-shield" />
    <section class="content-area">
        @if ($apiError)<div class="alert alert-danger">{{ $apiError }}</div>@endif

        <section class="objective-card">
            <h2>Accès organisationnels</h2>
            <ul class="objective-list">
                <li>Limiter chaque utilisateur au périmètre national, IA, IEF ou lieu de service autorisé.</li>
                <li>Le choix d’un niveau précis inclut automatiquement ses niveaux parents.</li>
                <li>Les permissions fonctionnelles restent déterminées par le rôle.</li>
            </ul>
        </section>

        @php
            $accesses = collect($users)->pluck('acces_organisationnel.niveau');
            $centralCount = collect($users)->filter(fn ($user) => data_get($user, 'acces_organisationnel.ia.type_perimetre') === 'central')->count();
            $regionalCount = collect($users)->filter(fn ($user) => data_get($user, 'acces_organisationnel.ia.type_perimetre') === 'regional')->count();
            $stats = [
                ['National', $accesses->filter(fn ($value) => $value === 'national')->count(), 'Sans restriction locale', 'red', 'fa-solid fa-earth-africa'],
                ['Central (IA)', $centralCount, 'IA de Dakar', 'blue', 'fa-solid fa-building'],
                ['Régional (IA)', $regionalCount, 'Kaolack, Ziguinchor…', 'green', 'fa-solid fa-map-location-dot'],
                ['IEF / Lieux', $accesses->filter(fn ($value) => in_array($value, ['ief', 'lieu_service'], true))->count(), 'Périmètres locaux', 'yellow', 'fa-solid fa-location-dot'],
            ];
        @endphp
        <div class="stats-grid four">
            @foreach ($stats as [$label, $value, $note, $color, $icon])
                <article class="stat-card"><div><p class="stat-label">{{ $label }}</p><p class="stat-value">{{ $value }}</p><p class="stat-note">{{ $note }}</p></div><span class="stat-icon {{ $color }}"><i class="{{ $icon }}"></i></span></article>
            @endforeach
        </div>

        <div class="actions-row">
            <p class="breadcrumb">Gestion utilisateur &gt; Utilisateurs</p>
            <button type="button" class="btn-primary" id="new-user-button">Nouvel utilisateur</button>
        </div>

        <section class="filter-panel">
            <div class="form-group"><label for="profile-filter">Profil</label><select id="profile-filter" class="form-control"><option value="">Tous</option>@foreach($roles as $role)<option value="{{ Str::lower($role['nom']) }}">{{ $role['nom'] }}</option>@endforeach</select></div>
            <div class="form-group"><label for="status-filter">Statut</label><select id="status-filter" class="form-control"><option value="">Tous</option><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
            <div class="form-group"><label for="service-filter">Service</label><select id="service-filter" class="form-control"><option value="">Tous</option>@foreach(collect($users)->map(fn ($user) => data_get($user, 'acces_organisationnel.lieu_service.libelle') ?? data_get($user, 'acces_organisationnel.ief.libelle') ?? data_get($user, 'acces_organisationnel.ia.libelle') ?? data_get($user, 'acces_organisationnel.structure.libelle'))->filter()->unique()->sort() as $service)<option value="{{ Str::lower($service) }}">{{ $service }}</option>@endforeach</select></div>
            <div class="actions-group"><button type="button" class="btn-secondary" id="filter-users">Filtrer</button></div>
        </section>

        <section class="table-card">
            <div class="table-responsive"><table class="table" id="users-table">
                <thead><tr><th>Nom</th><th>E-mail</th><th>Profil</th><th>Service</th><th>Statut</th><th class="actions-cell">Actions</th></tr></thead>
                <tbody>
                @forelse ($users as $user)
                    @php
                        $access = $user['acces_organisationnel'] ?? [];
                        $level = $access['niveau'] ?? 'national';
                        $structure = data_get($access, 'lieu_service.libelle') ?? data_get($access, 'ief.libelle') ?? data_get($access, 'ia.libelle') ?? data_get($access, 'structure.libelle') ?? (($access['est_affecte'] ?? false) ? 'Toutes les structures' : 'Structure à affecter');
                        $profile = data_get($user, 'role.nom', 'Aucun rôle');
                        $status = Str::lower($user['statut'] ?? 'inactif');
                    @endphp
                    <tr data-user-row data-profile="{{ Str::lower($profile) }}" data-service="{{ Str::lower($structure) }}" data-status="{{ $status }}">
                        <td><strong>{{ $user['nom_complet'] ?? '-' }}</strong></td><td>{{ $user['email'] ?? '-' }}</td><td>{{ $profile }}</td>
                        <td>{{ $structure }}</td>
                        <td><span class="badge {{ $status === 'actif' ? 'badge-active' : 'badge-inactive' }}">{{ $status === 'actif' ? 'Actif' : 'Inactif' }}</span></td>
                        <td class="actions-cell"><div class="user-actions">
                            <button type="button" class="table-action js-view" data-user='@json($user)'>Voir</button>
                            <button type="button" class="table-action edit js-edit" data-user='@json($user)'>Modifier</button>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Aucun utilisateur trouvé.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            <p id="users-empty" class="empty-message" hidden>Aucun utilisateur ne correspond aux filtres.</p>
            <div class="users-pagination" id="users-pagination" aria-label="Pagination des utilisateurs">
                <p id="pagination-summary"></p>
                <div class="pagination-controls">
                    <button type="button" class="page-btn page-arrow" id="previous-page" aria-label="Page précédente">←</button>
                    <div id="page-numbers" class="page-numbers"></div>
                    <button type="button" class="page-btn page-arrow" id="next-page" aria-label="Page suivante">→</button>
                </div>
            </div>
        </section>
    </section>
</main>

<div class="modal-overlay" id="new-user-modal" hidden>
    <div class="modal-container modal-container-large" role="dialog" aria-modal="true" aria-labelledby="new-user-title">
        <div class="modal-header"><div><h3 id="new-user-title">Nouvel utilisateur</h3><p>Créer un compte et lui attribuer un rôle.</p></div><button type="button" class="modal-close" data-close-modal>&times;</button></div>
        <form method="POST" action="{{ route('utilisateurs.store') }}">@csrf
            <div class="modal-body form-grid">
                <div class="form-group"><label for="new-prenom">Prénom</label><input class="form-control" id="new-prenom" name="prenom" value="{{ old('prenom') }}" required></div>
                <div class="form-group"><label for="new-nom">Nom</label><input class="form-control" id="new-nom" name="nom" value="{{ old('nom') }}" required></div>
                <div class="form-group full"><label for="new-email">Email</label><input class="form-control" id="new-email" type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="form-group full"><label for="new-password">Mot de passe provisoire</label><input class="form-control" id="new-password" type="password" name="password" minlength="8" required></div>
                <div class="form-group"><label for="new-role">Rôle</label><select class="form-control" id="new-role" name="role_id" required><option value="">Sélectionner</option>@foreach($roles as $role)<option value="{{ $role['id'] }}" data-allowed-structures='@json(config("organisation-access.role_levels.".($role["niveau"] ?? ""), []))'>{{ $role['nom'] }}</option>@endforeach</select><small class="role-structure-help" id="new-role-help"></small></div>
                <div class="form-group"><label for="new-status">Statut</label><select class="form-control" id="new-status" name="statut"><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
                <div class="form-group full organisation-heading"><strong>Lieu de service</strong><span>Code et libellé selon le périmètre choisi.</span></div>
                <div class="form-group"><label for="new-perimetre">Périmètre</label><select class="form-control" id="new-perimetre" name="perimetre" required><option value="national">National</option><option value="regional">Régional</option></select></div>
                <div class="form-group" id="new-national-group"><label for="new-national">Structure nationale</label><select class="form-control" id="new-national" name="structure_organisationnelle_id"><option value="">Sélectionner DRH, DAGE ou DECPC</option>@foreach($structuresNationales as $structure)<option value="{{ $structure['id'] }}">{{ $structure['code'] }} — {{ $structure['libelle'] }}</option>@endforeach</select></div>
                <div class="form-group" id="new-ia-group" hidden><label for="new-ia">IA</label><select class="form-control" id="new-ia" name="ia_id"><option value="">Sélectionner une IA</option></select></div>
                <div class="form-group" id="new-ief-group" hidden><label for="new-ief">IEF</label><select class="form-control" id="new-ief" name="ief_id" disabled><option value="">Toutes les IEF de l'IA</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-secondary" data-close-modal>Annuler</button><button type="submit" class="btn-primary">Créer</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="view-user-modal" hidden>
    <div class="modal-container" role="dialog" aria-modal="true" aria-labelledby="view-user-title">
        <div class="modal-header"><div><h3 id="view-user-title">Détails de l’utilisateur</h3><p id="view-user-subtitle"></p></div><button type="button" class="modal-close" data-close-modal>&times;</button></div>
        <div class="modal-body details-grid">
            <div><span>Nom complet</span><strong id="view-name"></strong></div><div><span>Email</span><strong id="view-email"></strong></div>
            <div><span>Rôle</span><strong id="view-role"></strong></div><div><span>Statut</span><strong id="view-status"></strong></div>
            <div class="full"><span>Périmètre organisationnel</span><strong id="view-access"></strong></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-primary" data-close-modal>Fermer</button></div>
    </div>
</div>

<div class="modal-overlay" id="edit-user-modal" hidden>
    <div class="modal-container" role="dialog" aria-modal="true" aria-labelledby="edit-user-title">
        <div class="modal-header"><div><h3 id="edit-user-title">Modifier l’utilisateur</h3><p id="edit-user-subtitle"></p></div><button type="button" class="modal-close" data-close-modal>&times;</button></div>
        <form method="POST" id="edit-user-form" data-action-template="{{ route('utilisateurs.update', '__ID__') }}">@csrf @method('PUT')
            <div class="modal-body form-grid">
                <div class="form-group"><label for="edit-prenom">Prénom</label><input class="form-control" id="edit-prenom" name="prenom" required></div>
                <div class="form-group"><label for="edit-nom">Nom</label><input class="form-control" id="edit-nom" name="nom" required></div>
                <div class="form-group full"><label for="edit-email">Email</label><input class="form-control" id="edit-email" type="email" name="email" required></div>
                <div class="form-group"><label for="edit-role">Rôle</label><select class="form-control" id="edit-role" name="role_id" required>@foreach($roles as $role)<option value="{{ $role['id'] }}" data-allowed-structures='@json(config("organisation-access.role_levels.".($role["niveau"] ?? ""), []))'>{{ $role['nom'] }}</option>@endforeach</select><small class="role-structure-help" id="edit-role-help"></small></div>
                <div class="form-group"><label for="edit-status">Statut</label><select class="form-control" id="edit-status" name="statut"><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-secondary" data-close-modal>Annuler</button><button type="submit" class="btn-primary">Enregistrer</button></div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px}.modal-overlay[hidden]{display:none}.modal-container{width:min(620px,100%);background:#fff;border-radius:12px;box-shadow:0 24px 60px rgba(0,0,0,.2)}.modal-header,.modal-footer{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #e5e7eb}.modal-footer{justify-content:flex-end;gap:8px;border-top:1px solid #e5e7eb;border-bottom:0}.modal-header h3{margin:0}.modal-header p{margin:4px 0 0;color:#6b7280}.modal-close{border:0;background:none;font-size:28px;cursor:pointer}.modal-body{padding:22px}.modal-body .form-group{margin-bottom:16px}.access-help{padding:10px 12px;background:#eff6ff;color:#1e40af;border-radius:7px;font-size:14px}.user-actions{display:flex;justify-content:flex-end;gap:7px;flex-wrap:wrap}.table-action{min-height:36px;padding:7px 11px;border:1px solid #dbe3ee;border-radius:9px;background:#fff;color:#2563eb;cursor:pointer;font-size:13px;font-weight:600;white-space:nowrap}.table-action.edit{color:#b45309}.table-action.organisation{color:#334e68}.table-action:hover{background:#f1f5f9}.form-grid,.details-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}.form-grid .full,.details-grid .full{grid-column:1/-1}.details-grid{gap:20px}.details-grid div{display:flex;flex-direction:column;gap:5px;padding:12px;border:1px solid #e5e7eb;border-radius:8px}.details-grid span{color:#6b7280;font-size:13px}.actions-row{margin-top:20px}.users-pagination{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 20px;border-top:1px solid #e5e7eb}.users-pagination p{margin:0;color:#64748b;font-size:14px}.pagination-controls,.page-numbers{display:flex;align-items:center;gap:9px}.page-btn{min-width:44px;height:44px;padding:0 12px;border:1px solid #dbe3ee;border-radius:11px;background:#fff;color:#1e293b;cursor:pointer;font-size:18px;font-weight:700}.page-btn.active{background:#08753f;border-color:#08753f;color:#fff}.page-btn:disabled{opacity:.4;cursor:not-allowed}.page-arrow{font-size:20px;font-weight:400}@media(max-width:640px){.form-grid,.details-grid{grid-template-columns:1fr}.form-grid .full,.details-grid .full{grid-column:auto}.users-pagination{align-items:flex-start;flex-direction:column}}
.modal-container-large{width:min(760px,100%);max-height:calc(100vh - 40px);overflow:auto}.organisation-heading{display:flex;flex-direction:column;gap:4px;margin-top:8px;padding-top:16px;border-top:1px solid #e5e7eb}.organisation-heading span{color:#64748b;font-size:13px}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const hierarchy = @json($organisation);
    const newPerimetre = document.getElementById('new-perimetre'); const newNational = document.getElementById('new-national'); const newIa = document.getElementById('new-ia'); const newIef = document.getElementById('new-ief'); const newRole = document.getElementById('new-role');
    const makeOption = (value, label) => new Option(label, value); const label = item => [item.code, item.libelle].filter(Boolean).join(' - ');
    hierarchy.filter(ia => ia.type_perimetre === 'regional').forEach(ia => newIa.add(makeOption(ia.id, label(ia))));
    const fillNewIefs = () => { newIef.replaceChildren(makeOption('', "Toutes les IEF de l'IA")); const ia = hierarchy.find(item => String(item.id) === newIa.value); (ia?.iefs ?? []).forEach(ief => newIef.add(makeOption(ief.id, label(ief)))); newIef.disabled = !newIa.value; };
    const selectedStructureType = () => newPerimetre.value === 'national' ? 'national' : (newIef.value ? 'ief' : 'ia');
    const validateRoleStructure = () => { const option = newRole.selectedOptions[0]; const allowed = option?.dataset.allowedStructures ? JSON.parse(option.dataset.allowedStructures) : []; const valid = !newRole.value || allowed.includes(selectedStructureType()); newRole.setCustomValidity(valid ? '' : "Ce rôle n'est pas autorisé pour ce type de structure."); document.getElementById('new-role-help').textContent = valid ? '' : "Choisissez un rôle compatible avec la structure."; };
    const toggleNewPerimetre = () => { const regional = newPerimetre.value === 'regional'; document.getElementById('new-national-group').hidden = regional; document.getElementById('new-ia-group').hidden = !regional; document.getElementById('new-ief-group').hidden = !regional; newNational.disabled = regional; newNational.required = !regional; newIa.disabled = !regional; newIa.required = regional; newIef.disabled = !regional || !newIa.value; if (regional) newNational.value = ''; else { newIa.value = ''; fillNewIefs(); } validateRoleStructure(); };
    newPerimetre.addEventListener('change', toggleNewPerimetre); newIa.addEventListener('change', () => { fillNewIefs(); validateRoleStructure(); }); newIef.addEventListener('change', validateRoleStructure); newRole.addEventListener('change', validateRoleStructure); toggleNewPerimetre();
    const openModal = target => { target.hidden = false; document.body.style.overflow = 'hidden'; };
    const closeModal = target => { target.hidden = true; if (![...document.querySelectorAll('.modal-overlay')].some(item => !item.hidden)) document.body.style.overflow = ''; };
    document.getElementById('new-user-button').addEventListener('click', () => openModal(document.getElementById('new-user-modal')));
    document.querySelectorAll('.js-view').forEach(button => button.addEventListener('click', () => { const user = JSON.parse(button.dataset.user); const access = user.acces_organisationnel ?? {}; const structure = access.lieu_service?.libelle ?? access.ief?.libelle ?? access.ia?.libelle ?? access.structure?.libelle ?? (access.est_affecte ? 'Toutes les structures' : 'Structure à affecter'); const levelName = access.niveau === 'ia' ? (access.ia?.type_perimetre === 'central' ? 'Central (IA)' : (access.ia?.type_perimetre === 'regional' ? 'Régional (IA)' : 'IA')) : ({national: 'National', ief: 'IEF', lieu_service: 'Lieu de service'}[access.niveau] ?? 'Non défini'); document.getElementById('view-user-subtitle').textContent = user.email; document.getElementById('view-name').textContent = user.nom_complet ?? '-'; document.getElementById('view-email').textContent = user.email ?? '-'; document.getElementById('view-role').textContent = user.role?.nom ?? 'Aucun rôle'; document.getElementById('view-status').textContent = user.statut ?? '-'; document.getElementById('view-access').textContent = `${levelName} — ${structure}`; openModal(document.getElementById('view-user-modal')); }));
    document.querySelectorAll('.js-edit').forEach(button => button.addEventListener('click', () => { const user = JSON.parse(button.dataset.user); const editForm = document.getElementById('edit-user-form'); const access = user.acces_organisationnel ?? {}; const structureType = access.niveau === 'national' || access.structure ? 'national' : (access.niveau === 'ief' || access.ief ? 'ief' : 'ia'); const editRole = document.getElementById('edit-role'); [...editRole.options].forEach(option => { const allowed = option.dataset.allowedStructures ? JSON.parse(option.dataset.allowedStructures) : []; option.disabled = !allowed.includes(structureType) && String(option.value) !== String(user.role?.id ?? ''); }); editForm.action = editForm.dataset.actionTemplate.replace('__ID__', user.id); document.getElementById('edit-user-subtitle').textContent = user.nom_complet ?? ''; document.getElementById('edit-prenom').value = user.prenom ?? ''; document.getElementById('edit-nom').value = user.nom ?? ''; document.getElementById('edit-email').value = user.email ?? ''; editRole.value = user.role?.id ?? ''; document.getElementById('edit-role-help').textContent = 'Seuls les rôles compatibles avec la structure actuelle sont disponibles.'; document.getElementById('edit-status').value = user.statut ?? 'actif'; openModal(document.getElementById('edit-user-modal')); }));
    document.querySelectorAll('[data-close], [data-close-modal]').forEach(button => button.addEventListener('click', () => closeModal(button.closest('.modal-overlay')))); document.querySelectorAll('.modal-overlay').forEach(item => item.addEventListener('click', event => { if (event.target === item) closeModal(item); })); document.addEventListener('keydown', event => { if (event.key === 'Escape') document.querySelectorAll('.modal-overlay:not([hidden])').forEach(closeModal); });
    const profileFilter = document.getElementById('profile-filter'); const statusFilter = document.getElementById('status-filter'); const serviceFilter = document.getElementById('service-filter'); const rows = [...document.querySelectorAll('[data-user-row]')];
    const perPage = 10; let currentPage = 1; let filteredRows = rows;
    const renderPagination = () => { const total = filteredRows.length; const pages = Math.max(1, Math.ceil(total / perPage)); currentPage = Math.min(currentPage, pages); const start = (currentPage - 1) * perPage; const end = Math.min(start + perPage, total); rows.forEach(row => row.hidden = true); filteredRows.slice(start, end).forEach(row => row.hidden = false); document.getElementById('users-empty').hidden = total > 0; document.getElementById('users-pagination').hidden = total === 0; document.getElementById('pagination-summary').textContent = total ? `Affichage de ${start + 1} à ${end} sur ${total} utilisateur${total > 1 ? 's' : ''}` : ''; document.getElementById('previous-page').disabled = currentPage === 1; document.getElementById('next-page').disabled = currentPage === pages; const numbers = document.getElementById('page-numbers'); numbers.replaceChildren(); for (let page = 1; page <= pages; page++) { const button = document.createElement('button'); button.type = 'button'; button.className = `page-btn${page === currentPage ? ' active' : ''}`; button.textContent = page; button.setAttribute('aria-label', `Page ${page}`); button.addEventListener('click', () => { currentPage = page; renderPagination(); }); numbers.appendChild(button); } };
    const filter = () => { filteredRows = rows.filter(row => (!profileFilter.value || row.dataset.profile === profileFilter.value) && (!statusFilter.value || row.dataset.status === statusFilter.value) && (!serviceFilter.value || row.dataset.service === serviceFilter.value)); currentPage = 1; renderPagination(); };
    document.getElementById('previous-page').addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderPagination(); } }); document.getElementById('next-page').addEventListener('click', () => { if (currentPage < Math.ceil(filteredRows.length / perPage)) { currentPage++; renderPagination(); } }); document.getElementById('filter-users').addEventListener('click', filter); renderPagination();
})();
</script>
@endpush
