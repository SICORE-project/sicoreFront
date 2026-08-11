{{-- Feuille de style commune. dompdf ne supporte qu'un sous-ensemble de CSS 2.1/3 :
     pas de flexbox/grid, tables + floats uniquement. --}}
<style>
    @page {
        margin: 90px 40px 70px 40px;
    }

    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 11px;
        color: #1a1a1a;
    }

    .entete-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    .entete-gauche {
        width: 70%;
        text-align: center;
        line-height: 1.5;
        vertical-align: top;
    }

    .entete-droite {
        width: 30%;
        text-align: right;
        vertical-align: top;
        font-size: 11px;
    }

    .titre-convocation {
        text-align: center;
        text-decoration: underline;
        font-size: 20px;
        margin: 18px 0 20px 0;
    }

    .objet {
        text-align: justify;
        line-height: 1.5;
        margin-bottom: 18px;
    }

    table.jury {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
        font-size: 10px;
    }

    table.jury th,
    table.jury td {
        border: 1px solid #000;
        padding: 4px 6px;
        vertical-align: middle;
    }

    table.jury th {
        background-color: #eaeaea;
        text-align: center;
    }

    .centre-header td {
        background-color: #dcdcdc;
        font-weight: bold;
        text-align: center;
    }

    .metier-header td {
        background-color: #f2f2f2;
        font-style: italic;
        text-align: center;
    }

    .col-prenoms, .col-nom { width: 13%; }
    .col-fonction { width: 16%; }
    .col-provenance { width: 15%; }
    .col-telephone { width: 12%; }
    .col-chef { width: 18%; text-align: left; }

    .signature-bloc {
        margin-top: 40px;
        width: 100%;
    }

    .signature-bloc .signataire {
        float: right;
        text-align: center;
        width: 220px;
    }

    .info-generique {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
        font-size: 11px;
    }

    .info-generique td {
        padding: 4px 6px;
        vertical-align: top;
    }

    .info-generique td.label {
        font-weight: bold;
        width: 160px;
    }
</style>
