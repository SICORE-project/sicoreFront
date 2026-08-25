@extends('layouts.app')

@section('title', 'SICORE - Types de rôle')

@section('content')
<main class="main-content">
    <x-topbar title="Types de rôle" subtitle="Administration > Types de rôle" icon="fa-solid fa-tags" />
    <section class="content-area">
        @if(session('success')) <p class="alert alert-success">{{ session('success') }}</p> @endif
        @if(session('error')) <p class="alert alert-danger">{{ session('error') }}</p> @endif
        <div class="actions-row"><a href="{{ route('admin.type-roles.create') }}" class="btn-primary">Nouveau type</a></div>
        <section class="table-card"><div class="table-responsive"><table class="table">
            <thead><tr><th>Code</th><th>Libellé</th><th>Description</th><th>Rôles</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>@forelse($typeRoles as $typeRole)
                <tr><td>{{ $typeRole['code'] }}</td><td>{{ $typeRole['libelle'] }}</td><td>{{ $typeRole['description'] ?? '-' }}</td><td>{{ $typeRole['roles_count'] ?? 0 }}</td><td>{{ $typeRole['est_actif'] ? 'Actif' : 'Inactif' }}</td><td><a href="{{ route('admin.type-roles.edit', $typeRole['id']) }}" class="table-action">Modifier</a><form action="{{ route('admin.type-roles.destroy', $typeRole['id']) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="table-action delete" onclick="return confirm('Supprimer ce type de rôle ?')">Supprimer</button></form></td></tr>
            @empty <tr><td colspan="6" class="text-center">Aucun type de rôle trouvé.</td></tr>@endforelse</tbody>
        </table></div></section>
    </section>
</main>
@endsection
