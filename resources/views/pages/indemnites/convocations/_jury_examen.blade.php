{{--
    Tableau centre / jury / métier, reproduisant la structure de formconf.jpeg.
    Attend $convocation avec la relation `centres` chargée, chaque
    ConvocationCentre ayant lui-même `enseignants` (pivot: fonction) et
    `chefCentre` chargés (cf. ConvocationPdfController::generer(), eager
    loading à mettre à jour — voir note de livraison).

    Structure attendue en base pour reproduire fidèlement le papier :
    un bloc "centre" (ex: "LTP FXN/THIES JURY 1") peut être représenté par
    PLUSIEURS lignes convocation_centres partageant le même `centre`/`jury`
    mais un `metier` différent (ex: une ligne "metier = null" pour le
    président de jury, une ligne "Technicien en Maintenance Véhicules
    Moteurs (MVM)", une ligne "Technicien en Froid Climatisation (FC)").
    Le blade regroupe visuellement les lignes consécutives qui partagent
    le même centre/jury, et n'affiche l'en-tête "CENTRE" qu'une fois.
--}}
<p class="objet">{{ $convocation->objet }}</p>

<table class="jury">
    <thead>
        <tr>
            <th class="col-prenoms">PRENOMS</th>
            <th class="col-nom">NOM</th>
            <th class="col-fonction">FONCTION</th>
            <th class="col-provenance">PROVENANCE</th>
            <th class="col-telephone">TELEPHONE</th>
            <th class="col-chef">CHEF DE CENTRE</th>
        </tr>
    </thead>
    <tbody>
        @php $centrePrecedent = null; @endphp

        @forelse ($convocation->centres as $centre)
            @php
                $memeBlocQueLePrecedent = $centrePrecedent
                    && $centrePrecedent->centre === $centre->centre
                    && $centrePrecedent->jury === $centre->jury;
                $nombreMembres = $centre->enseignants->count();
            @endphp

            @unless ($memeBlocQueLePrecedent)
                <tr class="centre-header">
                    <td colspan="6">
                        CENTRE : {{ $centre->centre }}
                        @if ($centre->jury)
                            &nbsp;— JURY {{ $centre->jury }}
                        @endif
                    </td>
                </tr>
            @endunless

            @if ($centre->metier)
                <tr class="metier-header">
                    <td colspan="6">{{ $centre->metier }}</td>
                </tr>
            @endif

            @forelse ($centre->enseignants as $index => $enseignant)
                <tr>
                    <td class="col-prenoms">{{ $enseignant->prenom }}</td>
                    <td class="col-nom">{{ $enseignant->nom }}</td>
                    <td class="col-fonction">{{ $enseignant->pivot->fonction }}</td>
                    <td class="col-provenance">{{ optional($enseignant->lieuService)->code }}</td>
                    <td class="col-telephone">{{ $enseignant->telephone }}</td>

                    @if ($index === 0)
                        <td class="col-chef" rowspan="{{ $nombreMembres }}">
                            @if ($centre->chefCentre)
                                <strong>{{ $centre->chefCentre->nom_complet_inverse }}</strong><br>
                                {{ $centre->chef_centre_telephone ?: $centre->chefCentre->telephone }}
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5"><em>Aucun membre affecté à ce centre.</em></td>
                    <td class="col-chef">
                        @if ($centre->chefCentre)
                            <strong>{{ $centre->chefCentre->nom_complet_inverse }}</strong><br>
                            {{ $centre->chef_centre_telephone ?: $centre->chefCentre->telephone }}
                        @endif
                    </td>
                </tr>
            @endforelse

            @php $centrePrecedent = $centre; @endphp
        @empty
            <tr>
                <td colspan="6"><em>Aucun centre défini pour cette convocation.</em></td>
            </tr>
        @endforelse
    </tbody>
</table>
