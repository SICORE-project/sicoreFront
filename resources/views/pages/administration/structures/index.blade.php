@extends('layouts.app')

@section('title', 'SICORE - Structures organisationnelles')

@section('content')
<main class="main-content">
    <x-topbar title="Structures organisationnelles" subtitle="Gestion utilisateur > Structures organisationnelles" icon="fa-solid fa-sitemap" />
    <section class="content-area">
        <section class="objective-card"><h2>Gestion des structures</h2><p>Créez les structures nationales, IA et IEF utilisées pour le rattachement des utilisateurs.</p></section>
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
        <div class="actions-row"><a href="{{ route('utilisateurs.index') }}" class="btn-secondary">Utilisateurs</a><button class="btn-primary" type="button" data-structure-create><i class="fa-solid fa-plus"></i> Ajouter une structure</button></div>
        <section class="table-card"><div class="table-responsive"><table class="table"><thead><tr><th>Code</th><th>Libellé</th><th>Type</th><th>Périmètre</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
        @forelse($structures as $structure)
            <tr><td>{{ $structure['code'] }}</td><td>{{ $structure['libelle'] }}</td><td>{{ $structure['type'] }}</td><td>{{ ucfirst($structure['perimetre']) }}</td><td><span class="badge {{ ($structure['est_actif'] ?? true) ? 'badge-active' : 'badge-suspended' }}">{{ ($structure['est_actif'] ?? true) ? 'Actif' : 'Inactif' }}</span></td><td><div class="table-actions-inline"><button type="button" class="table-action" data-structure-edit data-structure='@json($structure)'>Modifier</button><form method="POST" action="{{ route('parametres.structures-organisationnelles.destroy', $structure['id']) }}" style="display:inline">@csrf @method('DELETE')<button class="table-action delete" onclick="return confirm('Supprimer cette structure ? Cette action est impossible si elle est liée à un utilisateur.')">Supprimer</button></form></div></td></tr>
        @empty <tr><td colspan="6" class="text-center">Aucune structure organisationnelle.</td></tr>@endforelse
        </tbody></table></div></section>
    </section>
    <x-module-indemnite type="modal" id="structure-modal" title="Structure organisationnelle" :open="$errors->any()">
        <form id="structure-form" method="POST">@csrf <input id="structure-method" name="_method" type="hidden">
            <div class="form-grid form-grid--balanced">
                <div class="form-group"><label for="structure-code">Code *</label><input id="structure-code" class="form-control" name="code" maxlength="20" required></div>
                <div class="form-group"><label for="structure-libelle">Libellé *</label><input id="structure-libelle" class="form-control" name="libelle" maxlength="100" required></div>
                <div class="form-group"><label for="structure-perimetre">Périmètre *</label><select id="structure-perimetre" class="form-control" name="perimetre" required><option value="national">National</option><option value="regional">Régional</option></select><small>Choisissez d’abord le périmètre.</small></div>
                <div class="form-group"><label for="structure-type">Type *</label><select id="structure-type" class="form-control" name="type" required></select></div>
                <div class="form-group" id="structure-ia-group"><label for="structure-ia-id">IA de rattachement *</label><select id="structure-ia-id" class="form-control" name="ia_id"><option value="">Sélectionner une IA</option></select></div>
                <div class="form-group" id="structure-ief-group"><label for="structure-ief-id">IEF de rattachement *</label><select id="structure-ief-id" class="form-control" name="ief_id"><option value="">Sélectionner une IEF</option></select></div>
                <div class="form-group"><label for="structure-status">Statut *</label><select id="structure-status" class="form-control" name="est_actif" required><option value="1">Actif</option><option value="0">Inactif</option></select></div>
            </div>
            <div class="form-actions"><button type="button" class="btn-secondary" data-modal-close>Annuler</button><button class="btn-primary">Enregistrer</button></div>
        </form>
    </x-module-indemnite>
</main>
@endsection

@push('styles')<style>#structure-modal .modal-dialog{max-width:700px}#structure-modal small{color:var(--text-muted)}</style>@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
 const structures=@json($structures), ias=@json($ias), modal=document.getElementById('structure-modal'), form=document.getElementById('structure-form'), base=@json(route('parametres.structures-organisationnelles.index'));
 const type=document.getElementById('structure-type'), perimetre=document.getElementById('structure-perimetre'), iaGroup=document.getElementById('structure-ia-group'), iefGroup=document.getElementById('structure-ief-group'), ia=document.getElementById('structure-ia-id'), ief=document.getElementById('structure-ief-id'), nationalTypes=['DRH','DAGE','DECPC'];
 const label=item=>[item.code,item.libelle].filter(Boolean).join(' — ');
 function fillIa(selected=''){ia.replaceChildren(new Option('Sélectionner une IA',''));ias.forEach(item=>ia.add(new Option(label(item),item.id)));ia.value=selected}
 function fillIef(selected=''){ief.replaceChildren(new Option('Sélectionner une IEF',''));structures.filter(item=>item.type==='IEF'&&String(item.ia_id)===ia.value&&item.ief_id).forEach(item=>ief.add(new Option(label(item),item.ief_id)));ief.value=selected}
 function fillTypes(selected=''){const types=perimetre.value==='national'?nationalTypes:['IA'];if(selected==='IEF')types.push('IEF');type.replaceChildren();types.forEach(value=>type.add(new Option(value,value)));type.value=types.includes(selected)?selected:types[0];applyType()}
 function applyType(){const national=nationalTypes.includes(type.value), isIef=type.value==='IEF';iaGroup.hidden=national;ia.required=!national;ia.disabled=national;iefGroup.hidden=!isIef;ief.required=isIef;ief.disabled=!isIef;if(national){ia.value='';ief.value=''}if(!isIef)ief.value=''}
 function open(s){form.action=s?`${base}/${s.id}`:base;document.getElementById('structure-method').value=s?'PUT':'';document.getElementById('structure-code').value=s?.code??'';document.getElementById('structure-libelle').value=s?.libelle??'';perimetre.value=s?.perimetre??'national';fillIa(s?.ia_id??'');fillIef(s?.ief_id??'');fillTypes(s?.type??'DRH');document.getElementById('structure-status').value=s?.est_actif===false?'0':'1';modal.hidden=false}
 perimetre.addEventListener('change',()=>fillTypes());type.addEventListener('change',applyType);ia.addEventListener('change',()=>fillIef());document.querySelector('[data-structure-create]').addEventListener('click',()=>open(null));document.querySelectorAll('[data-structure-edit]').forEach(button=>button.addEventListener('click',()=>open(JSON.parse(button.dataset.structure))));
});
</script>
@endpush
