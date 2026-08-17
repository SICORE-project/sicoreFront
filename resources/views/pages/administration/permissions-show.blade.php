@extends('layouts.app')

@section('title', 'SICORE - Détails de la permission')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <x-topbar title="Détails de la permission" subtitle="Gestion Utilisateur > Permissions > Détails" icon="fa-solid fa-lock" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            @if(session('success'))
                <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Informations de la permission -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Nom</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        {{ $permission['nom'] ?? '-' }}
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Slug</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <code>{{ $permission['slug'] ?? '-' }}</code>
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Groupe</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        {{ $permission['groupe'] ?? '-' }}
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Module</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        {{ $permission['module'] ?? '-' }}
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Action</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <span style="background: #dbeafe; color: #1e40af; padding: 2px 10px; border-radius: 12px; font-size: 13px;">
                            {{ $permission['action'] ?? '-' }}
                        </span>
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Statut</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <span style="background: {{ ($permission['est_actif'] ?? false) ? '#dcfce7' : '#fee2e2' }}; color: {{ ($permission['est_actif'] ?? false) ? '#166534' : '#991b1b' }}; padding: 2px 10px; border-radius: 12px; font-size: 13px;">
                            {{ ($permission['est_actif'] ?? false) ? 'Actif' : 'Inactif' }}
                        </span>
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-weight: 600; color: #374151; font-size: 14px;">Description</label>
                <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px; min-height: 40px;">
                    {{ $permission['description'] ?? 'Aucune description' }}
                </p>
            </div>

            <!-- Rôles associés -->
            <div style="margin-bottom: 24px;">
                <label style="font-weight: 600; color: #374151; font-size: 14px;">Rôles associés</label>
                <div style="margin-top: 8px;">
                    @if(isset($permission['roles']) && count($permission['roles']) > 0)
                        @foreach($permission['roles'] as $role)
                            <span style="background: #e5e7eb; color: #374151; padding: 4px 12px; border-radius: 12px; font-size: 13px; display: inline-block; margin: 2px;">
                                {{ $role['nom'] ?? $role['name'] ?? 'Rôle' }}
                            </span>
                        @endforeach
                    @else
                        <p style="color: #6b7280; font-size: 14px;">Aucun rôle associé</p>
                    @endif
                </div>
            </div>

            <!-- Boutons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                <a href="{{ route('admin.permissions.index') }}" class="btn-secondary" style="padding: 10px 20px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; background: #f9fafb; text-decoration: none; transition: all 0.2s;">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="{{ route('admin.permissions.edit', $permission['id']) }}" class="btn-warning" style="padding: 10px 20px; background: #f59e0b; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            </div>

        </section>
    </section>
</main>
@endsection