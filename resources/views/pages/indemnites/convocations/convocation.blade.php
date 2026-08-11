<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Convocation #{{ $convocation->id }}</title>
    @include('pdf.convocation._styles')
</head>
<body>

    @include('pdf.convocation._entete', ['convocation' => $convocation])

    {{--
        Aiguillage par type_convocation. `jury_examen` affiche le tableau
        centre/jury/métier (formconf.jpeg) ; tous les autres types utilisent
        la mise en page générique. Si `type_convocation_id` est encore NULL
        (convocation créée avant la migration de typage), on retombe aussi
        sur le générique par prudence plutôt que de planter.
    --}}
    @if (optional($convocation->typeConvocation)->code === 'jury_examen')
        @include('pdf.convocation._jury_examen', ['convocation' => $convocation])
    @else
        @include('pdf.convocation._generique', ['convocation' => $convocation])
    @endif

    <div class="signature-bloc">
        <div class="signataire">
            Le Directeur
        </div>
    </div>

</body>
</html>
