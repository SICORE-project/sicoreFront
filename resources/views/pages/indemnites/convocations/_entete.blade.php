{{--
    Entête institutionnel commun (cf. formconf.jpeg).
    Attend une variable $convocation (App\Models\Convocations) dans le scope.
--}}
<table class="entete-table">
    <tr>
        <td class="entete-gauche">
            <strong>REPUBLIQUE DU SENEGAL</strong><br>
            <em>Un Peuple - Un But - Une Foi</em><br><br>
            <strong>MINISTERE DE LA FORMATION PROFESSIONNELLE ET TECHNIQUE</strong><br>
            <strong>DIRECTION DES EXAMENS, CONCOURS PROFESSIONNELS ET CERTIFICATIONS (DECPC)</strong>
        </td>
        <td class="entete-droite">
            Dakar, le {{ optional($convocation->date_emission)->translatedFormat('d F Y') }}
        </td>
    </tr>
</table>

<h1 class="titre-convocation">CONVOCATION</h1>
