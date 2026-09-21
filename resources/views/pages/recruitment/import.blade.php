@extends('layouts.app')
@section('title', 'SICORE - Ajouter des enseignants vacataires')
@section('content')
<main class="main-content recruitment-page">
    @include('pages.recruitment.header', ['heading' => 'Importer de nouveaux enseignants vacataires'])
    <section class="content-area">
        <a href="{{ route('recruitment.index') }}">← Retour aux lots</a>
        <section class="panel recruitment-panel">
            <h2>Importer votre fichier existant</h2>
            <form method="POST" action="{{ route('recruitment.preview') }}" enctype="multipart/form-data" class="recruitment-form">
                @csrf
                <div class="form-grid">
                    <div class="form-group"><label for="reference">Nom ou référence de la liste *</label><input class="form-control" id="reference" name="reference" required maxlength="100" value="{{ old('reference') }}" placeholder="Ex. Vacataires — rentrée 2026"></div>
                    <div class="form-group"><label for="recruited_at">Date de recrutement *</label><input class="form-control" type="date" id="recruited_at" name="recruited_at" required max="{{ now()->toDateString() }}" value="{{ old('recruited_at') }}"></div>
                    <div class="form-group"><label for="file">Votre liste des nouveaux vacataires *</label><input class="form-control" type="file" id="file" name="file" accept=".csv,text/csv" required></div>
                </div>
                <p>Format actuellement accepté : CSV UTF-8. Maximum : <strong>5 Mo et 5 000 enseignants</strong>. Les colonnes peuvent être dans un ordre différent ; prénom, nom et date de naissance doivent être présents.</p>
                <p>Vous pourrez vérifier les enseignants affichés avant de confirmer leur ajout. Vous joindrez ensuite l’ordre de service commun à cette liste.</p>
                <p>Chaque enseignant restera inactif jusqu’à l’enregistrement de sa prise de service par l’IA.</p>
                <button class="btn-primary" type="submit">Vérifier et afficher l’aperçu</button>
            </form>
        </section>
    </section>
</main>
@endsection
