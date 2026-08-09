@extends('layouts.app')

@section('title', 'SICORE - Detail convocation')
@section('content')
<main class="main-content">
    <x-topbar
      title="Certification BT - Jury 1"
      subtitle="{{ 'Indemnites > Convocations > Convocation #'.$id }}"
      icon="fa-solid fa-calendar-check"
    />

    <section class="content-area">
      <div class="actions-row">
        <p class="breadcrumb">Centre : LTP FXN/THIES &nbsp;·&nbsp; Session du 04 au 09 aout 2025 &nbsp;·&nbsp; 8h00</p>
        <div class="actions-group">
          <a class="btn-secondary" href="{{ route('indemnites.convocations.edit', $id) }}">
            <i class="fa-solid fa-pen" aria-hidden="true"></i>
            Modifier
          </a>
          <a class="btn-secondary" href="{{ route('indemnites.convocations.suivi', $id) }}">
            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
            Suivi des envois
          </a>
          <button class="btn-primary" type="button" data-confirm="Generer le PDF de cette convocation ?" data-success-message="PDF genere avec succes.">
            <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
            Generer le PDF
          </button>
          <a class="btn-secondary" href="#" data-confirm="Telecharger le PDF de cette convocation ?" data-success-message="Telechargement demarre.">
            <i class="fa-solid fa-download" aria-hidden="true"></i>
            Telecharger
          </a>
        </div>
      </div>

      {{-- Informations generales --}}
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Informations generales</h2>
            <p>Details de la convocation</p>
          </div>
          <span class="badge badge-active">Envoyee</span>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label>Objet</label>
            <p>Certification en Brevet de Technicien (BT) - Jury 1</p>
          </div>
          <div class="form-group">
            <label>Date d'emission</label>
            <p>23/07/2025</p>
          </div>
          <div class="form-group">
            <label>Centre d'examen</label>
            <p>LTP FXN/THIES</p>
          </div>
          <div class="form-group">
            <label>Lieu d'affectation</label>
            <p>Thies</p>
          </div>
          <div class="form-group">
            <label>Ordre de mission</label>
            <p>Oui</p>
          </div>
          <div class="form-group">
            <label>Emise par</label>
            <p>Le Directeur - DECPC</p>
          </div>
          <div class="form-group">
            <label>Chef de centre</label>
            <p>Souleymane Toure &nbsp;·&nbsp; 33 911 07 17 / 77 579 97 93</p>
          </div>
        </div>
      </section>

      {{-- Beneficiaires --}}
      <section class="table-card">
        <div class="panel-header">
          <div>
            <h2>Beneficiaires</h2>
            <p>Membres de jury convoques a cette certification</p>
          </div>
          <button class="btn-secondary" type="button" data-toggle-target="#ajouter-beneficiaire">
            <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
            Ajouter un beneficiaire
          </button>
        </div>

        <div class="table-responsive">
          <table class="table" id="beneficiairesTable">
            <thead>
              <tr>
                <th>Prenoms</th>
                <th>Nom</th>
                <th>Fonction</th>
                <th>Provenance</th>
                <th>Telephone</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Adama</td>
                <td>Gueye</td>
                <td>President de jury</td>
                <td>I.S.I.L</td>
                <td>77 377 02 28</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button" data-confirm="Retirer ce beneficiaire de la convocation ?" data-success-message="Beneficiaire retire."><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
              <tr>
                <td>Nfaly</td>
                <td>Sarr</td>
                <td>Surveillant/correcteur</td>
                <td>LTP-FXN/THIES</td>
                <td>77 361 13 51</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button"><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
              <tr>
                <td>Papa Alioune Badara</td>
                <td>Sarr</td>
                <td>Surveillant/correcteur</td>
                <td>LTID</td>
                <td>77 168 38 99</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button"><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
              <tr>
                <td>Mamadou Moustapha</td>
                <td>Wone</td>
                <td>Surveillant/correcteur</td>
                <td>LTAP/SAINT-LOUIS</td>
                <td>77 613 89 24</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button"><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
              <tr>
                <td>Gueye</td>
                <td>Mbaye</td>
                <td>Surveillant/correcteur</td>
                <td>LTAB/DIOURBEL</td>
                <td>77 104 95 18</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button"><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
              <tr>
                <td>Assane</td>
                <td>Thiam</td>
                <td>Surveillant/correcteur</td>
                <td>LTP-FXN/THIES</td>
                <td>77 209 67 25</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button"><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
              <tr>
                <td>El Hadji</td>
                <td>Faye</td>
                <td>Surveillant/correcteur</td>
                <td>LTP-FXN/THIES</td>
                <td>77 209 67 26</td>
                <td class="actions-cell"><button class="icon-action" title="Retirer" type="button"><i class="fa-solid fa-user-minus" aria-hidden="true"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="empty-message">Aucun beneficiaire ajoute.</p>

        <div class="form-section" id="ajouter-beneficiaire" data-collapsible hidden>
          <h3>Ajouter un beneficiaire</h3>
          <form class="form-grid" data-validate-form data-success-message="Beneficiaire ajoute a la convocation.">
            <div class="form-group">
              <label for="nouvelEnseignant">Enseignant <span class="required">*</span></label>
              <select class="form-control" id="nouvelEnseignant" name="enseignant_id" required>
                <option value="">Selectionner un enseignant</option>
                <option value="108">DIOP Mamadou (ENS108)</option>
                <option value="109">FALL Aissatou (ENS109)</option>
                <option value="110">NDIAYE Cheikh (ENS110)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="nouvelleFonction">Fonction</label>
              <input class="form-control" id="nouvelleFonction" name="fonction" type="text" placeholder="Ex : Surveillant/correcteur">
            </div>
            <div class="form-group full form-actions" style="justify-content:flex-start;">
              <button class="btn-primary" type="submit">Ajouter a la convocation</button>
            </div>
          </form>
        </div>
      </section>

      {{-- Depot de fichier --}}
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Piece jointe</h2>
            <p>Deposer le document scanne de la convocation (PDF, JPG ou PNG - 5 Mo max)</p>
          </div>
        </div>
        <form class="form-grid" data-validate-form data-success-message="Fichier depose avec succes.">
          <div class="form-group full">
            <label for="fichier">Fichier <span class="required">*</span></label>
            <input class="form-control" id="fichier" name="fichier" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>
          <div class="form-group full form-actions" style="justify-content:flex-start;">
            <button class="btn-primary" type="submit">Deposer le fichier</button>
          </div>
        </form>
      </section>

      {{-- Envoi --}}
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Envoi de la convocation</h2>
            <p>Envoyer par email, SMS ou courrier a tout ou partie des beneficiaires</p>
          </div>
        </div>
        <form class="form-grid" data-validate-form data-success-message="Convocation envoyee aux beneficiaires.">
          <div class="form-group">
            <label for="canalEnvoi">Canal</label>
            <select class="form-control" id="canalEnvoi" name="canal">
              <option value="email" selected>Email</option>
              <option value="sms">SMS</option>
              <option value="courrier">Courrier</option>
            </select>
          </div>
          <div class="form-group">
            <label for="destinataires">Destinataires</label>
            <select class="form-control" id="destinataires" name="destinataires">
              <option value="tous" selected>Tous les beneficiaires (7)</option>
              <option value="selection">Beneficiaires selectionnes uniquement</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="messageEnvoi">Message (optionnel)</label>
            <textarea class="form-control" id="messageEnvoi" name="message" maxlength="2000" placeholder="Message accompagnant la convocation…"></textarea>
          </div>
          <div class="form-group full form-actions" style="justify-content:flex-start;">
            <button class="btn-primary" type="submit">
              <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
              Envoyer la convocation
            </button>
          </div>
        </form>
      </section>

      {{-- Relance --}}
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Relance</h2>
            <p>Relancer les beneficiaires qui n'ont pas encore accuse reception</p>
          </div>
        </div>
        <form class="form-grid" data-validate-form data-success-message="Relance envoyee aux beneficiaires concernes.">
          <div class="form-group full">
            <label for="messageRelance">Message de relance (optionnel)</label>
            <textarea class="form-control" id="messageRelance" name="message" maxlength="2000" placeholder="Merci de confirmer votre presence…"></textarea>
          </div>
          <div class="form-group full form-actions" style="justify-content:flex-start;">
            <button class="btn-secondary" type="submit">
              <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
              Relancer les non-repondants
            </button>
          </div>
        </form>
      </section>
    </section>
  </main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/convocation-show.js') }}" defer></script>
@endpush
