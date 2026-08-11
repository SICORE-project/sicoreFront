{{--
    Convocation générique (formation, mission, réunion) : pas de tableau
    centre/jury/métier, juste le texte de l'objet + les infos pratiques et
    la liste des bénéficiaires convoqués.
--}}
<p class="objet">{{ $convocation->objet }}</p>

<table class="info-generique">
    @if ($convocation->lieu_examen)
        <tr>
            <td class="label">Lieu</td>
            <td>{{ $convocation->lieu_examen }}</td>
        </tr>
    @endif

    @if ($convocation->ordre_de_mission)
        <tr>
            <td class="label">Ordre de mission</td>
            <td>Oui{{ $convocation->lieu_affectation ? ' — Affectation : ' . $convocation->lieu_affectation : '' }}</td>
        </tr>
    @endif
</table>

<table class="jury">
    <thead>
        <tr>
            <th class="col-prenoms">PRENOMS</th>
            <th class="col-nom">NOM</th>
            <th class="col-fonction">FONCTION</th>
            <th class="col-provenance">PROVENANCE</th>
            <th class="col-telephone">TELEPHONE</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($convocation->enseignants as $enseignant)
            <tr>
                <td class="col-prenoms">{{ $enseignant->prenom }}</td>
                <td class="col-nom">{{ $enseignant->nom }}</td>
                <td class="col-fonction">{{ $enseignant->pivot->fonction }}</td>
                <td class="col-provenance">{{ optional($enseignant->lieuService)->code }}</td>
                <td class="col-telephone">{{ $enseignant->telephone }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5"><em>Aucun bénéficiaire affecté à cette convocation.</em></td>
            </tr>
        @endforelse
    </tbody>
</table>
