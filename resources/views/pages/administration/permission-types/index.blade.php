@extends('layouts.app')

@php($isModule = $type === 'modules')
@section('title', 'SICORE - '.($isModule ? 'Types de module' : 'Types de groupe'))

@section('content')
<main class="main-content">
    <x-topbar :title="$isModule ? 'Types de module' : 'Types de groupe'" subtitle="Gestion Utilisateur > Permissions" :icon="$isModule ? 'fa-solid fa-cubes' : 'fa-solid fa-layer-group'" />
    <section class="content-area">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(!empty($typesError)) <div class="alert alert-danger">{{ $typesError }}</div> @endif
        <div class="actions-row">
            <a href="{{ route('admin.permissions.index') }}" class="btn-secondary">Retour aux permissions</a>
            <a href="{{ route('admin.permissions.types.create', $type) }}" class="btn-primary">
                <i class="fas fa-plus"></i> {{ $isModule ? 'Nouveau module' : 'Nouveau groupe' }}
            </a>
        </div>
        <section class="table-card"><div class="table-responsive"><table class="table">
            <thead><tr><th>Nom</th><th>Permissions</th><th>Permissions actives</th><th>Statut</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><strong>{{ \Illuminate\Support\Str::of($item['name'])->replace(['_', '-'], ' ')->title() }}</strong></td>
                        <td>{{ collect($item['permissions'])->join(', ') ?: '-' }}</td>
                        <td>{{ collect($item['active_permissions'])->join(', ') ?: '-' }}</td>
                        <td><span class="badge {{ ($item['usage_status'] ?? 'Utilisé') === 'Utilisé' ? 'badge-success' : 'badge-danger' }}">{{ $item['usage_status'] ?? 'Utilisé' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Aucun {{ $isModule ? 'module' : 'groupe' }} trouvé.</td></tr>
                @endforelse
            </tbody>
        </table></div></section>
    </section>
</main>
@endsection
