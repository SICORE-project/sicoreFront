@extends('layouts.app')

@section('title', 'SICORE - Permissions du rôle')

@section('content')
<main class="main-content">
    <x-topbar title="Permissions du rôle" subtitle="{{ $role['nom'] ?? '' }}" icon="fa-solid fa-key" />

    <section class="content-area">
        <section class="table-card" style="padding: 24px;">

            <form action="{{ route('admin.roles.syncPermissions', $role['id']) }}" method="POST">
                @csrf
                @method('PUT')

                @foreach ($permissions as $module => $perms)
                    <div class="objective-card" style="margin-bottom: 16px;">
                        <h4 style="font-weight: 700; color: #2563eb; margin-bottom: 8px;">{{ ucfirst($module) }}</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
                            @foreach ($perms as $permission)
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission['id'] }}"
                                        @checked(in_array($permission['id'], $rolePermissions ?? [])) class="perm-checkbox">
                                    <span style="font-size: 14px;">{{ $permission['nom'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="actions-group" style="justify-content: flex-end; margin-top: 24px;">
                    <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>

        </section>
    </section>
</main>

@push('scripts')
<script>
    document.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const moduleDiv = this.closest('.objective-card');
            moduleDiv.querySelectorAll('.perm-checkbox').forEach(c => c.checked = this.checked);
        });
    });
</script>
@endpush
@endsection