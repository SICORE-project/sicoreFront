@extends('layouts.app')

@section('title', 'SICORE - Détails du rôle')

@section('content')
<main class="main-content" style="margin-left: 280px; padding: 1.5rem;">
    <x-topbar title="Détails du rôle" subtitle="Gestion Utilisateur > Profils / Rôles > Détails" icon="fa-solid fa-users-cog" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            @if(session('success'))
                <div style="background:#dcfce7; border:1px solid #16a34a; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Informations du rôle -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Nom du profil</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px; font-weight: 500;">
                        {{ $role['nom'] ?? '-' }}
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Slug</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <code>{{ $role['slug'] ?? '-' }}</code>
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Niveau d'accès</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <span class="badge 
                            @if($role['niveau'] == 'systeme') badge-danger
                            @elseif($role['niveau'] == 'admin_metier') badge-purple
                            @elseif($role['niveau'] == 'gestionnaire') badge-info
                            @else badge-secondary @endif">
                            {{ ucfirst($role['niveau'] ?? '') }}
                        </span>
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Statut</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        <span class="badge {{ ($role['est_actif'] ?? false) ? 'badge-success' : 'badge-danger' }}">
                            {{ ($role['est_actif'] ?? false) ? 'Actif' : 'Inactif' }}
                        </span>
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-weight: 600; color: #374151; font-size: 14px;">Description</label>
                <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px; min-height: 40px;">
                    {{ $role['description'] ?? 'Aucune description' }}
                </p>
            </div>

            <!-- Permissions associées -->
            <div style="margin-bottom: 24px;">
                <label style="font-weight: 600; color: #374151; font-size: 14px;">Permissions associées</label>
                <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
                    @if(isset($role['permissions']) && count($role['permissions']) > 0)
                        @foreach($role['permissions'] as $permission)
                            <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 12px; font-size: 13px; display: inline-block;">
                                {{ $permission['nom'] ?? $permission['name'] ?? 'Permission' }}
                            </span>
                        @endforeach
                    @else
                        <p style="color: #6b7280; font-size: 14px;">Aucune permission associée</p>
                    @endif
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Créé le</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        {{ isset($role['created_at']) ? date('d/m/Y à H:i', strtotime($role['created_at'])) : '-' }}
                    </p>
                </div>
                <div>
                    <label style="font-weight: 600; color: #374151; font-size: 14px;">Dernière modification</label>
                    <p style="padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 4px;">
                        {{ isset($role['updated_at']) ? date('d/m/Y à H:i', strtotime($role['updated_at'])) : '-' }}
                    </p>
                </div>
            </div>

            <!-- Boutons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                <a href="{{ route('admin.roles.index') }}" class="btn-secondary" style="padding: 10px 20px; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; background: #f9fafb; text-decoration: none; transition: all 0.2s;">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="{{ route('admin.roles.edit', $role['id']) }}" class="btn-warning" style="padding: 10px 20px; background: #f59e0b; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            </div>

        </section>
    </section>
</main>
@endsection