@php($searchAccess = app(\App\Services\Organisation\InterfaceAccess::class))
@if($searchAccess->isDrh() && $searchAccess->allows('enseignants.read'))
<section class="panel recruitment-panel" id="teacher-results">
    <h2>{{ ['total_agents' => 'Tous les enseignants', 'prise_service_enregistree' => 'Enseignants ayant pris service', 'enseignants_abandon' => 'Enseignants en abandon'][request('situation')] ?? 'Rechercher un enseignant' }}</h2>
    <p>Recherchez dans tous les enseignants de votre périmètre, tous lots confondus.</p>
    <form method="GET" action="{{ route('recruitment.index') }}">
        <input type="hidden" name="situation" value="{{ request('situation') }}"><label for="teacher-search">Nom, prénom ou matricule</label>
        <input class="form-control" type="search" id="teacher-search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Ex. Awa Diop ou matricule">
        <button class="btn-primary" type="submit">Rechercher</button>
        @if(request()->filled('search'))<a href="{{ route('recruitment.index') }}">Effacer la recherche</a>@endif
    </form>
    @if($searchError)<p class="recruitment-errors" role="alert">{{ $searchError }}</p>@endif
    @if($searchResults !== null)
        <p>{{ $searchResults['total'] }} enseignant(s) trouvé(s)</p>
        <div class="table-responsive"><table class="table"><thead><tr><th>Matricule</th><th>Prénom et nom</th><th>Situation</th><th>Dossier</th></tr></thead><tbody>
        @forelse($searchResults['data'] as $teacher)
            <tr><td>{{ $teacher['matricule'] }}</td><td>{{ $teacher['prenom'] }} {{ $teacher['nom'] }}</td>
            <td>{{ $teacher['statut'] === 'abandon' ? 'Abandon déclaré' : ($teacher['date_prise_service'] ? 'Prise de service enregistrée le '.$teacher['date_prise_service'] : 'Prise de service non enregistrée') }}</td>
            <td>@if($teacher['batch_id'])<a href="{{ route('recruitment.show',$teacher['batch_id']) }}">Voir le lot et l’historique</a>@else Enseignant déjà enregistré, sans lot de recrutement @endif</td></tr>
        @empty<tr><td colspan="4">Aucun enseignant ne correspond à votre recherche.</td></tr>@endforelse
        </tbody></table></div>
        <nav aria-label="Pages des résultats">
        @if($searchResults['current_page'] > 1)<a href="{{ route('recruitment.index',['search'=>request('search'),'situation'=>request('situation'),'page'=>$searchResults['current_page']-1]) }}">Précédent</a>@endif
        Page {{ $searchResults['current_page'] }} / {{ $searchResults['last_page'] }}
        @if($searchResults['current_page'] < $searchResults['last_page'])<a href="{{ route('recruitment.index',['search'=>request('search'),'situation'=>request('situation'),'page'=>$searchResults['current_page']+1]) }}">Suivant</a>@endif
        </nav>
    @endif
</section>
@endif
