
@extends('layouts.app')

@section('title', 'SICORE - Detail de la convocation')
@section('content')
<main class="main-content">
  <x-topbar
    title="Detail de la convocation"
    subtitle="Indemnites > Convocations > Detail"
    icon="fa-solid fa-envelope-open-text"
  />

  <section class="content-area">
    <div class="actions-row">
      <p class="breadcrumb">Indemnites &gt; Convocations &gt; #{{ $id }}</p>
      <div class="actions-group">
        <a class="btn-secondary" href="{{ route('indemnites.convocations') }}">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          Retour a la liste
        </a>
        <a class="btn-secondary" href="{{ route('indemnites.convocations.edit', $id) }}">
          <i class="fa-solid fa-pen" aria-hidden="true"></i>
          Modifier
        </a>
        <a class="btn-secondary" href="{{ route('indemnites.convocations.suivi', $id) }}">
          <i class="fa-solid fa-list-check" aria-hidden="true"></i>
          Suivi des envois
        </a>
      </div>
    </div>

    {{-- Informations generales --}}
    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>{{ $convocation['objet'] ?? 'Convocation' }}</h2>
          <p class="breadcrumb">Reference #{{ $id }}</p>
        </div>
        <x-convocation-statut-badge :statut="$convocation['statut'] ?? null" />
      </div>

      <div class="form-section">
        <div class="form-grid">
          <div class="form-group">
            <label>Date d'emission</label>
            <p>{{ isset($convocation['date_emission']) ? \Illuminate\Support\Carbon::parse($convocation['date_emission'])->format('d/m/Y') : '—' }}</p>
          </div>
          <div class="form-group">
            <label>Centre d'examen</label>
            <p>{{ $convocation['lieu_examen'] ?? '—' }}</p>
          </div>
          <div class="form-group">
            <label>Lieu d'affectation</label>
            <p>{{ $convocation['lieu_affectation'] ?? '—' }}</p>
          </div>
          <div class="form-group">
            <label>Ordre de mission</label>
            <p>{{ ($convocation['ordre_de_mission'] ?? false) ? 'Oui' : 'Non' }}</p>
          </div>
        </div>
      </div>
    </section>

    {{-- Beneficiaires --}}
    <section class="table-card">
      <div class="panel-header">
        <div>
          <h2>Membres du jury convoques</h2>
          <p>{{ count($beneficiaires) }} beneficiaire(s) rattache(s) a cette convocation</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Prenom</th>
              <th>Fonction</th>
              <th>Telephone</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($beneficiaires as $beneficiaire)
              <tr>
                <td>{{ $beneficiaire['nom'] ?? $beneficiaire['enseignant']['nom'] ?? '—' }}</td>
                <td>{{ $beneficiaire['prenom'] ?? $beneficiaire['enseignant']['prenom'] ?? '—' }}</td>
                <td>{{ $beneficiaire['fonction'] ?? '—' }}</td>
                <td>{{ $beneficiaire['telephone'] ?? $beneficiaire['enseignant']['telephone'] ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if (empty($beneficiaires))
        <p class="empty-message">Aucun beneficiaire ajoute pour le moment.</p>
      @endif

      <div class="form-section">
        <h3>Ajouter un membre du jury</h3>
        <form
          method="POST"
          action="{{ route('indemnites.convocations.beneficiaires.store', $id) }}"
          class="form-grid"
          data-convocation-form
          data-search-url="{{ route('indemnites.convocations.enseignants.rechercher') }}"
        >
          @csrf
          <div class="form-group">
            <label for="enseignant_search">Enseignant <span class="required">*</span></label>
            <div class="enseignant-search" data-enseignant-search>
              <input
                class="form-control"
                id="enseignant_search"
                type="text"
                placeholder="Rechercher un enseignant (nom, prenom, matricule)…"
                data-enseignant-search-input
                autocomplete="off"
              >
              <input type="hidden" name="enseignant_id" data-enseignant-id-input required>
              <ul class="enseignant-suggestions" data-enseignant-suggestions hidden></ul>
            </div>
          </div>
          <div class="form-group">
            <label for="fonction">Fonction</label>
            <select class="form-control" id="fonction" name="fonction" data-fonction-select>
              <option value="">Selectionner une fonction</option>
              <option value="President de jury">President de jury</option>
              <option value="Membre du jury">Membre du jury</option>
              <option value="Surveillant/correcteur">Surveillant/correcteur</option>
              <option value="Chef de centre">Chef de centre</option>
            </select>
          </div>
          <div class="form-group" style="align-self: flex-end;">
            <button class="btn-primary" type="submit">
              <i class="fa-solid fa-plus" aria-hidden="true"></i>
              Ajouter
            </button>
          </div>
        </form>
      </div>
    </section>

    {{-- Document scanne --}}
    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>Document scanne</h2>
          <p class="breadcrumb">Deposer une version signee ou scannee de la convocation</p>
        </div>
      </div>
      <form
        class="form-section"
        method="POST"
        action="{{ route('indemnites.convocations.fichier.store', $id) }}"
        enctype="multipart/form-data"
      >
        @csrf
        <div class="form-grid">
          <div class="form-group full">
            <label for="fichier">Fichier (PDF, JPG ou PNG, 5 Mo max) <span class="required">*</span></label>
            <input class="form-control" id="fichier" name="fichier" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn-secondary" type="submit">
            <i class="fa-solid fa-upload" aria-hidden="true"></i>
            Deposer le fichier
          </button>
        </div>
      </form>
    </section>

    {{-- Generation et telechargement du PDF --}}
    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>Document PDF</h2>
          <p class="breadcrumb">Generer puis telecharger la convocation au format PDF</p>
        </div>
      </div>
      <div class="form-actions" style="justify-content: flex-start;">
        <form method="POST" action="{{ route('indemnites.convocations.pdf.generer', $id) }}">
          @csrf
          <button class="btn-secondary" type="submit">
            <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
            Generer le PDF
          </button>
        </form>
        <a class="btn-secondary" href="{{ route('indemnites.convocations.pdf.telecharger', $id) }}">
          <i class="fa-solid fa-download" aria-hidden="true"></i>
          Telecharger le PDF
        </a>
      </div>
    </section>

    {{-- Envoi et relance --}}
    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>Envoi aux beneficiaires</h2>
          <p class="breadcrumb">Notifier les membres du jury par email, SMS ou courrier</p>
        </div>
      </div>
      <form class="form-section" method="POST" action="{{ route('indemnites.convocations.envoyer', $id) }}">
        @csrf
        <div class="form-grid">
          <div class="form-group">
            <label for="canal">Canal</label>
            <select class="form-control" id="canal" name="canal">
              <option value="email">Email</option>
              <option value="sms">SMS</option>
              <option value="courrier">Courrier</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="message">Message (optionnel)</label>
            <textarea class="form-control" id="message" name="message" rows="3" maxlength="2000" placeholder="Message accompagnant l'envoi…"></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn-primary" type="submit">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            Envoyer la convocation
          </button>
        </div>
      </form>

      <form class="form-section" method="POST" action="{{ route('indemnites.convocations.relancer', $id) }}">
        @csrf
        <div class="form-grid">
          <div class="form-group full">
            <label for="relance_message">Message de relance (optionnel)</label>
            <textarea class="form-control" id="relance_message" name="message" rows="3" maxlength="2000" placeholder="Message de relance…"></textarea>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn-secondary" type="submit">
            <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
            Relancer les beneficiaires non repondus
          </button>
        </div>
      </form>
    </section>

    {{-- Suppression --}}
    <section class="form-card">
      <div class="form-card-header">
        <div>
          <h2>Zone sensible</h2>
          <p class="breadcrumb">La suppression est definitive</p>
        </div>
      </div>
      <form
        method="POST"
        action="{{ route('indemnites.convocations.destroy', $id) }}"
        onsubmit="return confirm('Voulez-vous vraiment supprimer cette convocation ? Cette action est irreversible.');"
      >
        @csrf
        @method('DELETE')
        <div class="form-actions" style="justify-content: flex-start;">
          <button class="btn-danger-soft" type="submit">
            <i class="fa-solid fa-trash" aria-hidden="true"></i>
            Supprimer la convocation
          </button>
        </div>
      </form>
    </section>
  </section>
</main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/indemnites/convocation-form.js') }}" defer></script>
@endpush
