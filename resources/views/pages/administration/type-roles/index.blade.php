@extends('layouts.app')

@section('title', 'SICORE - Types de rôle')

@section('content')
<main class="main-content">
    <x-topbar title="Types de rôle" subtitle="Administration > Types de rôle" icon="fa-solid fa-tags" />
    <section class="content-area">
        @if(session('success')) <p class="alert alert-success">{{ session('success') }}</p> @endif
        @if(session('error')) <p class="alert alert-danger">{{ session('error') }}</p> @endif
        <div class="actions-row"><a href="{{ route('admin.type-roles.create') }}" class="btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nouveau type</a></div>
        <section class="table-card"><div class="table-responsive"><table class="table">
            <thead><tr><th>Code</th><th>Libellé</th><th>Description</th><th>Rôles</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>@forelse($typeRoles as $typeRole)
                <tr>
                    <td>{{ $typeRole['code'] }}</td>
                    <td>{{ $typeRole['libelle'] }}</td>
                    <td>{{ $typeRole['description'] ?? '-' }}</td>
                    <td>
                        @forelse(($typeRole['roles'] ?? []) as $role)
                            <span class="badge badge-active">{{ $role['libelle'] ?? $role['nom'] ?? '—' }}</span>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>{{ $typeRole['est_actif'] ? 'Actif' : 'Inactif' }}</td>
                    <td class="actions-cell">
                        <a href="{{ route('admin.type-roles.edit', $typeRole['id']) }}" class="icon-action" title="Modifier" aria-label="Modifier {{ $typeRole['libelle'] }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                        <form action="{{ route('admin.type-roles.destroy', $typeRole['id']) }}" method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce type de rôle ?')">
                            @csrf
                            @method('DELETE')
                            <button class="icon-action delete" type="submit" title="Supprimer" aria-label="Supprimer {{ $typeRole['libelle'] }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                        </form>
                    </td>
                </tr>
            @empty <tr><td colspan="6" class="text-center">Aucun type de rôle trouvé.</td></tr>@endforelse</tbody>
        </table></div></section>
    </section>
</main>
@endsection
