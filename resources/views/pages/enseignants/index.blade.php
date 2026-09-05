@extends('layouts.app')

@section('title', 'SICORE - Dashboard Enseignant')
@section('content')
<main class="main-content">
    <x-topbar
      title="Dashboard Enseignant"
      subtitle="Paramétrage > Enseignants"
      icon="fa-solid fa-chalkboard-user"
      search-id="teacherSearch"
      search-placeholder="Rechercher un enseignant…"
      filter-target="#teacherTable"
    />

    <section class="content-area">
      <div class="actions-row">
        <p class="breadcrumb">Paramétrage &gt; Enseignants</p>
        <div class="actions-group">
          <button class="btn-primary" type="button" data-modal-open="teacher-create-modal">
            <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
            Ajouter un enseignant
          </button>
        </div>
      </div>

      <div class="stats-grid four">
        <article class="stat-card">
          <div><p class="stat-label">Total enseignants</p><p class="stat-value">{{ $pagination['total'] }}</p><p class="stat-note">Dossiers enregistrés</p></div>
          <span class="stat-icon blue">EN</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Affichés</p><p class="stat-value">{{ count($items) }}</p><p class="stat-note neutral">Page courante</p></div>
          <span class="stat-icon green">OK</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Actifs</p><p class="stat-value">{{ collect($items)->where('est_actif', true)->count() }}</p><p class="stat-note neutral">Sur cette page</p></div>
          <span class="stat-icon yellow">AT</span>
        </article>
        <article class="stat-card">
          <div><p class="stat-label">Page</p><p class="stat-value">{{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}</p><p class="stat-note neutral">Pagination</p></div>
          <span class="stat-icon purple">IA</span>
        </article>
      </div>

      <section class="table-card">
        <div class="panel-header">
          <div>
            <h2>Derniers enseignants ajoutes</h2>
            <p>Liste des enseignants enregistrés</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table" id="teacherTable">
            <thead>
              <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>IA</th>
                <th>Corps</th>
                <th>Statut</th>
                <th class="actions-cell">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($items as $teacher)
              <tr>
                <td>{{ data_get($teacher, 'matricule', '—') }}</td>
                <td>{{ data_get($teacher, 'nom', '—') }}</td>
                <td>{{ data_get($teacher, 'prenom', '—') }}</td>
                <td>{{ data_get($teacher, 'ia.libelle', data_get($teacher, 'ia.nom', '—')) }}</td>
                <td>{{ data_get($teacher, 'corps.libelle', '—') }}</td>
                <td><span class="badge {{ data_get($teacher, 'est_actif') ? 'badge-active' : 'badge-suspended' }}">{{ data_get($teacher, 'statut', '—') }}</span></td>
                <td class="actions-cell"><button class="icon-action" type="button" title="Voir les détails" aria-label="Voir les détails de {{ data_get($teacher, 'prenom') }} {{ data_get($teacher, 'nom') }}" data-modal-open="teacher-view-modal" data-view-teacher='@json($teacher)'><i class="fa-solid fa-eye" aria-hidden="true"></i></button><button class="icon-action" type="button" title="Modifier" data-edit-teacher='@json($teacher)'><i class="fa-solid fa-pen" aria-hidden="true"></i></button><form method="POST" action="{{ route('enseignants.destroy', data_get($teacher, 'id')) }}" class="inline-form" onsubmit="return confirm('Voulez-vous supprimer cet enseignant ?');">@csrf @method('DELETE')<button class="icon-action icon-action-danger" type="submit" title="Supprimer"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></form></td>
              </tr>
              @empty
              <tr><td colspan="7" class="empty-message">Aucun enseignant trouvé.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($pagination['last_page'] > 1)
          <nav class="pagination" aria-label="Pagination des enseignants">
            @if ($pagination['current_page'] > 1)<a class="page-btn" href="{{ route('enseignants.index', ['page' => $pagination['current_page'] - 1]) }}">&#8592;</a>@endif
            <span class="page-btn active">{{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}</span>
            @if ($pagination['current_page'] < $pagination['last_page'])<a class="page-btn" href="{{ route('enseignants.index', ['page' => $pagination['current_page'] + 1]) }}">&#8594;</a>@endif
          </nav>
        @endif
      </section>
    </section>
  </main>

<x-module-indemnite type="modal" id="teacher-create-modal" title="Ajouter un enseignant" :open="$errors->any()">
  <form class="teacher-form" method="POST" action="{{ route('enseignants.store') }}">
    @csrf
    <div class="wizard-progress" aria-label="Progression de la création">
      <button class="wizard-step active" type="button" data-create-step="1"><span class="wizard-step-number">1</span><i class="fa-solid fa-address-card wizard-step-icon" aria-hidden="true"></i><span>Identité</span></button>
      <button class="wizard-step" type="button" data-create-step="2"><span class="wizard-step-number">2</span><i class="fa-solid fa-briefcase wizard-step-icon" aria-hidden="true"></i><span>Carrière</span></button>
      <button class="wizard-step" type="button" data-create-step="3"><span class="wizard-step-number">3</span><i class="fa-solid fa-people-roof wizard-step-icon" aria-hidden="true"></i><span>Situation familiale</span></button>
      <button class="wizard-step" type="button" data-create-step="4"><span class="wizard-step-number">4</span><i class="fa-solid fa-building-columns wizard-step-icon" aria-hidden="true"></i><span>Coordonnées &amp; banque</span></button>
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label for="teacher-matricule">Matricule <span class="required">*</span></label>
        <input class="form-control" id="teacher-matricule" name="matricule" value="{{ old('matricule') }}" maxlength="30" required>
      </div>
      <div class="form-group">
        <label for="teacher-nom">Nom <span class="required">*</span></label>
        <input class="form-control" id="teacher-nom" name="nom" value="{{ old('nom') }}" maxlength="50" required>
      </div>
      <div class="form-group">
        <label for="teacher-prenom">Prénom <span class="required">*</span></label>
        <input class="form-control" id="teacher-prenom" name="prenom" value="{{ old('prenom') }}" maxlength="50" required>
      </div>
      <div class="form-group">
        <label for="teacher-email">E-mail</label>
        <input class="form-control" id="teacher-email" name="email" type="email" value="{{ old('email') }}" maxlength="100">
      </div>
      <div class="form-group">
        <label for="teacher-date-naissance">Date de naissance</label>
        <input class="form-control" id="teacher-date-naissance" name="date_naissance" type="date" value="{{ old('date_naissance') }}" max="{{ now()->subDay()->format('Y-m-d') }}">
      </div>
      <div class="form-group">
        <label for="teacher-date-recrutement">Date de recrutement</label>
        <input class="form-control" id="teacher-date-recrutement" name="date_recrutement" type="date" value="{{ old('date_recrutement') }}" max="{{ now()->format('Y-m-d') }}">
      </div>
      <div class="form-group">
        <label for="teacher-telephone">Téléphone</label>
        <input class="form-control" id="teacher-telephone" name="telephone" value="{{ old('telephone') }}" maxlength="20">
      </div>
      <div class="form-group">
        <label for="teacher-ia">Inspection académique (IA) <span class="required">*</span></label>
        <select class="form-control" id="teacher-ia" name="ia_id" required data-teacher-ia data-iefs-url="{{ route('enseignants.iefs') }}">
          <option value="">Sélectionner une IA</option>
          @foreach ($academies as $academy)
            <option value="{{ data_get($academy, 'id') }}">{{ data_get($academy, 'libelle', data_get($academy, 'nom', 'IA')) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="teacher-ief">Inspection de l’Éducation et de la Formation (IEF) <span class="required">*</span></label>
        <select class="form-control" id="teacher-ief" name="ief_id" required disabled data-teacher-ief>
          <option value="">Sélectionner d’abord une IA</option>
        </select>
      </div>
      <div class="form-group">
        <label for="teacher-corps">Corps <span class="required">*</span></label>
        <select class="form-control" id="teacher-corps" name="corps_id" required>
          <option value="">Sélectionner un corps</option>
          @foreach ($corpsOptions as $corps)
            <option value="{{ data_get($corps, 'id') }}" data-corps-code="{{ data_get($corps, 'code') }}" @selected((string) old('corps_id') === (string) data_get($corps, 'id'))>{{ data_get($corps, 'libelle', 'Corps') }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group"><label for="teacher-diplome">Diplôme</label><select class="form-control" id="teacher-diplome" name="diplome_id"><option value="">Sélectionner un diplôme</option>@foreach($diplomeOptions as $diplome)<option value="{{ data_get($diplome, 'id') }}" data-salaire-brut="{{ data_get($diplome, 'salaire_brut') }}" data-categorie-id="{{ data_get($diplome, 'categorie_id', data_get($diplome, 'categorie.id')) }}" @selected((string) old('diplome_id') === (string) data_get($diplome, 'id'))>{{ data_get($diplome, 'libelle') }}</option>@endforeach</select></div>
      <div class="form-group" data-create-categorie-field hidden>
        <label for="teacher-categorie">Catégorie <span class="required">*</span></label>
        <select class="form-control" id="teacher-categorie" name="categorie_id" disabled>
          <option value="">Sélectionner une catégorie</option>
          @foreach ($categorieOptions as $categorie)
            <option value="{{ data_get($categorie, 'id') }}" data-corps-id="{{ data_get($categorie, 'corps_id', data_get($categorie, 'corps.id')) }}" @selected((string) old('categorie_id') === (string) data_get($categorie, 'id'))>{{ data_get($categorie, 'libelle', 'Catégorie') }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="teacher-statut">Statut <span class="required">*</span></label>
        <select class="form-control" id="teacher-statut" name="statut" required>
          <option value="en_activite" @selected(old('statut', 'en_activite') === 'en_activite')>En activité</option>
          <option value="retraite" @selected(old('statut') === 'retraite')>Retraité</option>
          <option value="suspension_provisoire" @selected(old('statut') === 'suspension_provisoire')>Suspension provisoire</option>
          <option value="cessation_paiement" @selected(old('statut') === 'cessation_paiement')>Cessation de paiement</option>
        </select>
      </div>
      <div class="form-group full">
        <label for="teacher-adresse">Adresse</label>
        <textarea class="form-control" id="teacher-adresse" name="adresse" maxlength="255">{{ old('adresse') }}</textarea>
      </div>
      <div class="form-group"><label for="teacher-lieu-naissance">Lieu de naissance</label><input class="form-control" id="teacher-lieu-naissance" name="lieu_naissance" value="{{ old('lieu_naissance') }}" maxlength="100"></div>
      <div class="form-group"><label for="teacher-cni">N° carte d’identité</label><input class="form-control" id="teacher-cni" name="cni" value="{{ old('cni') }}" maxlength="50"></div>
      <div class="form-group"><label for="teacher-genre">Genre</label><select class="form-control" id="teacher-genre" name="genre"><option value="">Sélectionner</option><option value="M" @selected(old('genre') === 'M')>Masculin</option><option value="F" @selected(old('genre') === 'F')>Féminin</option></select></div>
      <div class="form-group"><label for="teacher-discipline">Discipline</label><select class="form-control" id="teacher-discipline" name="discipline_id"><option value="">Sélectionner une discipline</option>@foreach($disciplineOptions as $discipline)<option value="{{ data_get($discipline, 'id') }}">{{ data_get($discipline, 'libelle', data_get($discipline, 'nom')) }}</option>@endforeach</select></div>
      <div class="form-group"><label for="teacher-lieu-service">Lieu de service</label><select class="form-control" id="teacher-lieu-service" name="lieu_service_id"><option value="">Sélectionner un lieu</option>@foreach($lieuServiceOptions as $lieu)<option value="{{ data_get($lieu, 'id') }}">{{ data_get($lieu, 'libelle', data_get($lieu, 'nom')) }}</option>@endforeach</select></div>
      <div class="form-group"><label for="teacher-salaire">Salaire brut</label><input class="form-control" id="teacher-salaire" name="salaire_brut" type="number" min="0" step="1" value="{{ old('salaire_brut') }}" readonly><small>Renseigné automatiquement selon le diplôme et la catégorie.</small></div>
      <div class="form-group"><label for="teacher-generation">Génération</label><input class="form-control" id="teacher-generation" name="generation" maxlength="20" value="{{ old('generation') }}"></div>
      <div class="form-group" data-create-contract-field hidden><label for="teacher-date-fin-contrat">Fin du contrat <span class="required">*</span></label><input class="form-control" id="teacher-date-fin-contrat" name="date_fin_contrat" type="date" value="{{ old('date_fin_contrat') }}"></div>
      <div class="form-group"><label for="teacher-couple">Situation familiale</label><select class="form-control" id="teacher-couple" name="est_en_couple" required><option value="0">Célibataire</option><option value="1" @selected(old('est_en_couple') == 1)>Marié(e)</option></select></div>
      <div class="form-group"><label for="teacher-enfants">Nombre d’enfants <span class="optional-label">(facultatif)</span></label><input class="form-control" id="teacher-enfants" name="nombre_enfants" type="number" min="0" value="{{ old('nombre_enfants') }}"></div>
      <div class="form-group"><label for="teacher-femmes">Nombre de femme(s) <span class="optional-label">(facultatif)</span></label><input class="form-control" id="teacher-femmes" name="nombre_femmes" type="number" min="0" value="{{ old('nombre_femmes') }}"></div>
      <div class="form-group"><label for="teacher-parts">Nombre de parts</label><input class="form-control" id="teacher-parts" name="nombre_parts_fiscales" type="number" step="0.5" min="1" max="5" value="{{ old('nombre_parts_fiscales', 1) }}" readonly><small>Le total des parts est plafonné à 5.</small></div>
      <div class="form-group"><label for="teacher-conjoint-travaille">Le conjoint travaille</label><select class="form-control" id="teacher-conjoint-travaille" name="conjoint_travaille" required><option value="0">Non</option><option value="1" @selected(old('conjoint_travaille') == 1)>Oui</option></select></div>
      <div class="form-group full"><label for="teacher-observations">Observations</label><textarea class="form-control" id="teacher-observations" name="observations">{{ old('observations') }}</textarea></div>
      <div class="form-group"><label for="teacher-banque">Banque</label><select class="form-control" id="teacher-banque" name="compte_bancaire[institut_financier_id]"><option value="">Sélectionner une banque</option>@foreach($institutionOptions as $institution)<option value="{{ data_get($institution, 'id') }}">{{ data_get($institution, 'libelle', data_get($institution, 'nom')) }}</option>@endforeach</select></div>
      <div class="form-group"><label for="teacher-code-banque">Code banque</label><input class="form-control" id="teacher-code-banque" name="compte_bancaire[code_banque]" maxlength="5"></div>
      <div class="form-group"><label for="teacher-code-guichet">Code guichet</label><input class="form-control" id="teacher-code-guichet" name="compte_bancaire[code_guichet]" maxlength="5"></div>
      <div class="form-group"><label for="teacher-numero-compte">Numéro de compte</label><input class="form-control" id="teacher-numero-compte" name="compte_bancaire[numero_compte]" maxlength="11"></div>
      <div class="form-group"><label for="teacher-cle-rib">Clé RIB</label><input class="form-control" id="teacher-cle-rib" name="compte_bancaire[cle_rib]" maxlength="2"></div>
      <div class="form-group"><label for="teacher-type-virement">Type de virement</label><select class="form-control" id="teacher-type-virement" name="compte_bancaire[type_virement]"><option value="unitaire">Unitaire</option><option value="masse">Masse</option></select></div>
    </div>
    <input type="hidden" name="est_actif" value="1">
    @if ($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <div class="form-actions">
      <button class="btn-secondary" type="button" data-modal-close>Annuler</button>
      <button class="btn-secondary" type="button" data-create-prev hidden>Précédent</button>
      <button class="btn-primary" type="button" data-create-next>Suivant</button>
      <button class="btn-primary" type="submit" data-create-submit hidden><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer</button>
    </div>
  </form>
</x-module-indemnite>

<x-module-indemnite type="modal" id="teacher-view-modal" title="Détails de l’enseignant">
  <div class="teacher-profile">
    <header class="teacher-profile-hero">
      <div class="teacher-profile-avatar" data-view-initials>EN</div>
      <div class="teacher-profile-heading">
        <p class="teacher-profile-eyebrow">Fiche enseignant</p>
        <h3 data-view-full-name>—</h3>
        <div class="teacher-profile-meta"><span data-view-matricule>—</span><span class="teacher-profile-status" data-view-status>—</span></div>
      </div>
    </header>

    <div class="teacher-profile-grid">
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Identité</h4></div><dl class="teacher-detail-list">
        <div><dt>Nom</dt><dd data-teacher-detail="nom" data-format="text">Non renseigné</dd></div>
        <div><dt>Prénom</dt><dd data-teacher-detail="prenom" data-format="text">Non renseigné</dd></div>
        <div><dt>Carte d’identité</dt><dd data-teacher-detail="cni" data-format="text">Non renseigné</dd></div>
        <div><dt>Genre</dt><dd data-teacher-detail="genre" data-format="text">Non renseigné</dd></div>
        <div><dt>Date de naissance</dt><dd data-teacher-detail="date_naissance" data-format="date">Non renseigné</dd></div>
        <div><dt>Lieu de naissance</dt><dd data-teacher-detail="lieu_naissance" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Coordonnées</h4></div><dl class="teacher-detail-list">
        <div><dt>E-mail</dt><dd data-teacher-detail="email" data-format="text">Non renseigné</dd></div>
        <div><dt>Téléphone</dt><dd data-teacher-detail="telephone" data-format="text">Non renseigné</dd></div>
        <div><dt>Adresse</dt><dd data-teacher-detail="adresse" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Situation familiale</h4></div><dl class="teacher-detail-list">
        <div><dt>En couple</dt><dd data-teacher-detail="est_en_couple" data-format="bool">Non renseigné</dd></div>
        <div><dt>Nombre d’enfants</dt><dd data-teacher-detail="nombre_enfants" data-format="text">Non renseigné</dd></div>
        <div><dt>Nombre de femmes</dt><dd data-teacher-detail="nombre_femmes" data-format="text">Non renseigné</dd></div>
        <div><dt>Conjoint en activité</dt><dd data-teacher-detail="conjoint_travaille" data-format="bool">Non renseigné</dd></div>
        <div><dt>Parts fiscales</dt><dd data-teacher-detail="nombre_parts_fiscales" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Affectation et qualification</h4></div><dl class="teacher-detail-list">
        <div><dt>Inspection académique</dt><dd data-teacher-detail="ia.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>IEF</dt><dd data-teacher-detail="ief.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Lieu de service</dt><dd data-teacher-detail="lieu_service.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Corps</dt><dd data-teacher-detail="corps.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Grade</dt><dd data-teacher-detail="grade.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Diplôme</dt><dd data-teacher-detail="diplome.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Catégorie</dt><dd data-teacher-detail="categorie.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Discipline</dt><dd data-teacher-detail="discipline.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Catégorie de personnel</dt><dd data-teacher-detail="categorie_personnel" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Contrat et rémunération</h4></div><dl class="teacher-detail-list">
        <div><dt>Salaire brut</dt><dd data-teacher-detail="salaire_brut" data-format="money">Non renseigné</dd></div>
        <div><dt>Recrutement</dt><dd data-teacher-detail="date_recrutement" data-format="date">Non renseigné</dd></div>
        <div><dt>Prise de service</dt><dd data-teacher-detail="date_prise_service" data-format="date">Non renseigné</dd></div>
        <div><dt>Fin du contrat</dt><dd data-teacher-detail="date_fin_contrat" data-format="date">Non renseigné</dd></div>
        <div><dt>Année de recrutement</dt><dd data-teacher-detail="annee_recrutement" data-format="text">Non renseigné</dd></div>
        <div><dt>Génération</dt><dd data-teacher-detail="generation" data-format="text">Non renseigné</dd></div>
        <div><dt>Statut</dt><dd data-teacher-detail="statut_libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Actif</dt><dd data-teacher-detail="est_actif" data-format="bool">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Compte bancaire</h4></div><dl class="teacher-detail-list">
        <div><dt>Banque</dt><dd data-teacher-detail="compte_bancaire.institut_financier.libelle" data-format="text">Non renseigné</dd></div>
        <div><dt>Titulaire</dt><dd data-teacher-detail="compte_bancaire.titulaire_compte" data-format="text">Non renseigné</dd></div>
        <div><dt>Numéro de compte</dt><dd data-teacher-detail="compte_bancaire.numero_compte" data-format="text">Non renseigné</dd></div>
        <div><dt>Code banque</dt><dd data-teacher-detail="compte_bancaire.code_banque" data-format="text">Non renseigné</dd></div>
        <div><dt>Code guichet</dt><dd data-teacher-detail="compte_bancaire.code_guichet" data-format="text">Non renseigné</dd></div>
        <div><dt>Clé RIB</dt><dd data-teacher-detail="compte_bancaire.cle_rib" data-format="text">Non renseigné</dd></div>
        <div><dt>IBAN</dt><dd data-teacher-detail="compte_bancaire.iban" data-format="text">Non renseigné</dd></div>
        <div><dt>BIC</dt><dd data-teacher-detail="compte_bancaire.bic" data-format="text">Non renseigné</dd></div>
        <div><dt>Type de virement</dt><dd data-teacher-detail="compte_bancaire.type_virement" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Syndicat</h4></div><dl class="teacher-detail-list">
        <div><dt>Nom</dt><dd data-teacher-detail="syndicat.nom" data-format="text">Non renseigné</dd></div>
        <div><dt>Sigle</dt><dd data-teacher-detail="syndicat.sigle" data-format="text">Non renseigné</dd></div>
        <div><dt>Affiliation</dt><dd data-teacher-detail="syndicat.numero_affiliation" data-format="text">Non renseigné</dd></div>
        <div><dt>Taux personnalisé</dt><dd data-teacher-detail="syndicat.taux_personnalise" data-format="text">Non renseigné</dd></div>
        <div><dt>Adhésion</dt><dd data-teacher-detail="syndicat.date_adhesion" data-format="text">Non renseigné</dd></div>
        <div><dt>Résiliation</dt><dd data-teacher-detail="syndicat.date_resiliation" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Mutuelle</h4></div><dl class="teacher-detail-list">
        <div><dt>Nom</dt><dd data-teacher-detail="mutuelle.nom" data-format="text">Non renseigné</dd></div>
        <div><dt>Sigle</dt><dd data-teacher-detail="mutuelle.sigle" data-format="text">Non renseigné</dd></div>
        <div><dt>Affiliation</dt><dd data-teacher-detail="mutuelle.numero_affiliation" data-format="text">Non renseigné</dd></div>
        <div><dt>Adhésion</dt><dd data-teacher-detail="mutuelle.date_adhesion" data-format="text">Non renseigné</dd></div>
        <div><dt>Résiliation</dt><dd data-teacher-detail="mutuelle.date_resiliation" data-format="text">Non renseigné</dd></div>
      </dl></section>
      <section class="teacher-detail-card"><div class="teacher-detail-title"><h4>Informations complémentaires</h4></div><dl class="teacher-detail-list">
        <div><dt>Observations</dt><dd data-teacher-detail="observations" data-format="text">Non renseigné</dd></div>
        <div><dt>Création du dossier</dt><dd data-teacher-detail="created_at" data-format="date">Non renseigné</dd></div>
        <div><dt>Dernière modification</dt><dd data-teacher-detail="updated_at" data-format="date">Non renseigné</dd></div>
      </dl></section>
    </div>
  </div>
  <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Fermer</button></div>
</x-module-indemnite>

<x-module-indemnite type="modal" id="teacher-edit-modal" title="Modifier un enseignant">
  <form class="teacher-form" method="POST" id="teacher-edit-form" data-action-template="{{ route('enseignants.update', ['enseignant' => '__id__']) }}">
    @csrf
    @method('PUT')
    <div class="wizard-progress" aria-label="Progression de la modification">
      <button class="wizard-step active" type="button" data-edit-step="1"><span class="wizard-step-number">1</span><i class="fa-solid fa-address-card wizard-step-icon" aria-hidden="true"></i><span>Identité</span></button>
      <button class="wizard-step" type="button" data-edit-step="2"><span class="wizard-step-number">2</span><i class="fa-solid fa-briefcase wizard-step-icon" aria-hidden="true"></i><span>Carrière</span></button>
      <button class="wizard-step" type="button" data-edit-step="3"><span class="wizard-step-number">3</span><i class="fa-solid fa-people-roof wizard-step-icon" aria-hidden="true"></i><span>Situation familiale</span></button>
      <button class="wizard-step" type="button" data-edit-step="4"><span class="wizard-step-number">4</span><i class="fa-solid fa-building-columns wizard-step-icon" aria-hidden="true"></i><span>Coordonnées &amp; banque</span></button>
    </div>
    <section class="wizard-panel" data-edit-panel="1">
      <div class="form-section"><h3>Informations de l’enseignant</h3><div class="form-grid">
        <div class="form-group"><label for="edit-teacher-matricule">Matricule <span class="required">*</span></label><input class="form-control" id="edit-teacher-matricule" name="matricule" maxlength="30" required></div>
        <div class="form-group"><label for="edit-teacher-nom">Nom <span class="required">*</span></label><input class="form-control" id="edit-teacher-nom" name="nom" maxlength="50" required></div>
        <div class="form-group"><label for="edit-teacher-prenom">Prénom <span class="required">*</span></label><input class="form-control" id="edit-teacher-prenom" name="prenom" maxlength="50" required></div>
        <div class="form-group"><label for="edit-teacher-lieu-naissance">Lieu de naissance</label><input class="form-control" id="edit-teacher-lieu-naissance" name="lieu_naissance" maxlength="100"></div>
        <div class="form-group"><label for="edit-teacher-cni">N° carte d’identité</label><input class="form-control" id="edit-teacher-cni" name="cni" maxlength="50"></div>
        <div class="form-group"><label for="edit-teacher-genre">Genre</label><select class="form-control" id="edit-teacher-genre" name="genre"><option value="">Sélectionner</option><option value="M">Masculin</option><option value="F">Féminin</option></select></div>
        <div class="form-group"><label for="edit-teacher-ia">Inspection académique (IA) <span class="required">*</span></label><select class="form-control" id="edit-teacher-ia" name="ia_id" required data-edit-teacher-ia data-iefs-url="{{ route('enseignants.iefs') }}"><option value="">Sélectionner une IA</option>@foreach ($academies as $academy)<option value="{{ data_get($academy, 'id') }}">{{ data_get($academy, 'libelle', data_get($academy, 'nom', 'IA')) }}</option>@endforeach</select></div>
        <div class="form-group"><label for="edit-teacher-ief">Inspection de l’Éducation et de la Formation (IEF) <span class="required">*</span></label><select class="form-control" id="edit-teacher-ief" name="ief_id" required disabled data-edit-teacher-ief><option value="">Sélectionner d’abord une IA</option></select></div>
        <div class="form-group"><label for="edit-teacher-date-recrutement">Date de recrutement</label><input class="form-control" id="edit-teacher-date-recrutement" name="date_recrutement" type="date"></div>
        <div class="form-group"><label for="edit-teacher-statut">Statut <span class="required">*</span></label><select class="form-control" id="edit-teacher-statut" name="statut" required><option value="en_activite">En activité</option><option value="retraite">Retraité</option><option value="suspension_provisoire">Suspension provisoire</option><option value="cessation_paiement">Cessation de paiement</option></select></div>
        <div class="form-group"><label for="edit-teacher-corps">Corps <span class="required">*</span></label><select class="form-control" id="edit-teacher-corps" name="corps_id" required><option value="">Sélectionner un corps</option>@foreach ($corpsOptions as $corps)<option value="{{ data_get($corps, 'id') }}" data-corps-code="{{ data_get($corps, 'code') }}">{{ data_get($corps, 'libelle', 'Corps') }}</option>@endforeach</select></div>
      <div class="form-group"><label for="edit-teacher-diplome">Diplôme</label><select class="form-control" id="edit-teacher-diplome" name="diplome_id"><option value="">Sélectionner un diplôme</option>@foreach($diplomeOptions as $diplome)<option value="{{ data_get($diplome, 'id') }}" data-salaire-brut="{{ data_get($diplome, 'salaire_brut') }}" data-categorie-id="{{ data_get($diplome, 'categorie_id', data_get($diplome, 'categorie.id')) }}">{{ data_get($diplome, 'libelle') }}</option>@endforeach</select></div>
        <div class="form-group" data-edit-categorie-field hidden><label for="edit-teacher-categorie">Catégorie <span class="required">*</span></label><select class="form-control" id="edit-teacher-categorie" name="categorie_id" disabled><option value="">Sélectionner une catégorie</option>@foreach ($categorieOptions as $categorie)<option value="{{ data_get($categorie, 'id') }}" data-corps-id="{{ data_get($categorie, 'corps_id', data_get($categorie, 'corps.id')) }}">{{ data_get($categorie, 'libelle', 'Catégorie') }}</option>@endforeach</select></div>
      </div></div>
    </section>
    <section class="wizard-panel" data-edit-panel="2" hidden><div class="form-section"><h3>Informations professionnelles</h3><div class="form-grid">
      <div class="form-group"><label for="edit-teacher-discipline">Discipline</label><select class="form-control" id="edit-teacher-discipline" name="discipline_id"><option value="">Sélectionner une discipline</option>@foreach($disciplineOptions as $discipline)<option value="{{ data_get($discipline, 'id') }}">{{ data_get($discipline, 'libelle', data_get($discipline, 'nom')) }}</option>@endforeach</select></div>
      <div class="form-group"><label for="edit-teacher-lieu-service">Lieu de service</label><select class="form-control" id="edit-teacher-lieu-service" name="lieu_service_id"><option value="">Sélectionner un lieu</option>@foreach($lieuServiceOptions as $lieu)<option value="{{ data_get($lieu, 'id') }}">{{ data_get($lieu, 'libelle', data_get($lieu, 'nom')) }}</option>@endforeach</select></div>
      <div class="form-group"><label for="edit-teacher-salaire">Salaire brut</label><input class="form-control" id="edit-teacher-salaire" name="salaire_brut" type="number" min="0" readonly><small>Renseigné automatiquement selon le diplôme et la catégorie.</small></div>
      <div class="form-group"><label for="edit-teacher-generation">Génération</label><input class="form-control" id="edit-teacher-generation" name="generation" maxlength="20"></div>
      <div class="form-group" data-edit-contract-field hidden><label for="edit-teacher-date-fin-contrat">Fin du contrat <span class="required">*</span></label><input class="form-control" id="edit-teacher-date-fin-contrat" name="date_fin_contrat" type="date"></div>
      <div class="form-group full"><label for="edit-teacher-observations">Observations</label><textarea class="form-control" id="edit-teacher-observations" name="observations"></textarea></div>
    </div></div></section>
    <section class="wizard-panel" data-edit-panel="3" hidden><div class="form-section"><h3>Situation familiale et parts</h3><div class="form-grid">
      <div class="form-group"><label for="edit-teacher-couple">Situation familiale</label><select class="form-control" id="edit-teacher-couple" name="est_en_couple" required><option value="0">Célibataire</option><option value="1">Marié(e)</option></select></div>
      <div class="form-group"><label for="edit-teacher-enfants">Nombre d’enfants <span class="optional-label">(facultatif)</span></label><input class="form-control" id="edit-teacher-enfants" name="nombre_enfants" type="number" min="0"></div>
      <div class="form-group"><label for="edit-teacher-femmes">Nombre de femme(s) <span class="optional-label">(facultatif)</span></label><input class="form-control" id="edit-teacher-femmes" name="nombre_femmes" type="number" min="0"></div>
      <div class="form-group"><label for="edit-teacher-parts">Nombre de parts</label><input class="form-control" id="edit-teacher-parts" name="nombre_parts_fiscales" type="number" step="0.5" min="1" max="5" readonly><small>Le total des parts est plafonné à 5.</small></div>
      <div class="form-group"><label for="edit-teacher-conjoint-travaille">Le conjoint travaille</label><select class="form-control" id="edit-teacher-conjoint-travaille" name="conjoint_travaille" required><option value="0">Non</option><option value="1">Oui</option></select></div>
    </div></div></section>
    <section class="wizard-panel" data-edit-panel="4" hidden>
      <div class="form-section"><h3>Contact</h3><div class="form-grid">
        <div class="form-group"><label for="edit-teacher-email">E-mail</label><input class="form-control" id="edit-teacher-email" name="email" type="email" maxlength="100"></div>
        <div class="form-group"><label for="edit-teacher-telephone">Téléphone</label><input class="form-control" id="edit-teacher-telephone" name="telephone" maxlength="20"></div>
        <div class="form-group"><label for="edit-teacher-date-naissance">Date de naissance</label><input class="form-control" id="edit-teacher-date-naissance" name="date_naissance" type="date"></div>
        <div class="form-group full"><label for="edit-teacher-adresse">Adresse</label><textarea class="form-control" id="edit-teacher-adresse" name="adresse" maxlength="255"></textarea></div>
        <div class="form-group"><label for="edit-teacher-banque">Banque</label><select class="form-control" id="edit-teacher-banque" name="compte_bancaire[institut_financier_id]"><option value="">Sélectionner une banque</option>@foreach($institutionOptions as $institution)<option value="{{ data_get($institution, 'id') }}">{{ data_get($institution, 'libelle', data_get($institution, 'nom')) }}</option>@endforeach</select></div>
        <div class="form-group"><label for="edit-teacher-code-banque">Code banque</label><input class="form-control" id="edit-teacher-code-banque" name="compte_bancaire[code_banque]" maxlength="5"></div>
        <div class="form-group"><label for="edit-teacher-code-guichet">Code guichet</label><input class="form-control" id="edit-teacher-code-guichet" name="compte_bancaire[code_guichet]" maxlength="5"></div>
        <div class="form-group"><label for="edit-teacher-numero-compte">Numéro de compte</label><input class="form-control" id="edit-teacher-numero-compte" name="compte_bancaire[numero_compte]" maxlength="11"></div>
        <div class="form-group"><label for="edit-teacher-cle-rib">Clé RIB</label><input class="form-control" id="edit-teacher-cle-rib" name="compte_bancaire[cle_rib]" maxlength="2"></div>
        <div class="form-group"><label for="edit-teacher-type-virement">Type de virement</label><select class="form-control" id="edit-teacher-type-virement" name="compte_bancaire[type_virement]"><option value="unitaire">Unitaire</option><option value="masse">Masse</option></select></div>
      </div></div>
    </section>
    <input type="hidden" name="est_actif" value="1">
    <div class="form-actions"><button class="btn-secondary" type="button" data-modal-close>Annuler</button><button class="btn-secondary" type="button" data-edit-prev hidden>Précédent</button><button class="btn-primary" type="button" data-edit-next>Suivant</button><button class="btn-primary" type="submit" data-edit-submit hidden><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer les modifications</button></div>
  </form>
</x-module-indemnite>
@endsection

@push('styles')
<style>
  #teacher-view-modal .modal-dialog { width: min(920px, calc(100% - 32px)); }
  #teacher-create-modal .wizard-progress,
  #teacher-edit-modal .wizard-progress {
    display: flex;
    justify-content: flex-start;
    gap: 6px;
    max-width: 100%;
    padding: 10px 14px 0;
    overflow-x: auto;
  }
  #teacher-create-modal .wizard-step,
  #teacher-edit-modal .wizard-step {
    width: max-content;
    min-width: max-content;
    min-height: 34px;
    flex: 0 0 max-content;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 9px;
    font-size: 11px;
    white-space: nowrap;
  }
  #teacher-create-modal .wizard-step-number,
  #teacher-edit-modal .wizard-step-number {
    width: 21px;
    height: 21px;
    flex-basis: 21px;
    font-size: 10px;
  }
  .teacher-form .optional-label { color: #64748b; font-size: 11px; font-weight: 500; }
  #teacher-create-modal .wizard-step-icon,
  #teacher-edit-modal .wizard-step-icon {
    width: 14px;
    color: currentColor;
    font-size: 12px;
    text-align: center;
  }
  .teacher-profile { padding: 4px; }
  .teacher-profile-hero { display: flex; align-items: center; gap: 18px; padding: 22px; margin-bottom: 18px; border-radius: 18px; color: #fff; background: linear-gradient(135deg, var(--primary), #0f766e); box-shadow: 0 16px 35px rgba(15, 118, 110, .18); }
  .teacher-profile-avatar { display: grid; place-items: center; width: 72px; height: 72px; flex: 0 0 72px; border: 3px solid rgba(255,255,255,.55); border-radius: 22px; color: var(--primary); background: #fff; font-size: 23px; font-weight: 900; letter-spacing: .04em; }
  .teacher-profile-heading { min-width: 0; }
  .teacher-profile-eyebrow { margin: 0 0 3px; color: rgba(255,255,255,.78); font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
  .teacher-profile-heading h3 { margin: 0; color: #fff; font-size: clamp(20px, 3vw, 28px); }
  .teacher-profile-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 9px; margin-top: 8px; font-size: 13px; }
  .teacher-profile-meta > span:first-child { font-weight: 700; }
  .teacher-profile-status { padding: 4px 9px; border: 1px solid rgba(255,255,255,.35); border-radius: 999px; background: rgba(255,255,255,.16); font-size: 11px; font-weight: 800; }
  .teacher-profile-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
  .teacher-detail-card { padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 7px 22px rgba(15, 23, 42, .05); }
  .teacher-detail-card-wide { grid-column: 1 / -1; }
  .teacher-detail-title { display: flex; align-items: center; gap: 9px; padding-bottom: 12px; border-bottom: 1px solid #eef2f7; }
  .teacher-detail-title i { display: grid; place-items: center; width: 31px; height: 31px; border-radius: 9px; color: var(--primary); background: #ecfdf5; }
  .teacher-detail-title h4 { margin: 0; color: #0f172a; font-size: 14px; }
  .teacher-detail-list { display: grid; gap: 0; margin: 8px 0 0; }
  .teacher-detail-list > div { display: grid; grid-template-columns: minmax(120px, .8fr) minmax(0, 1.2fr); gap: 10px; padding: 6px 0; border-bottom: 1px dashed #e2e8f0; }
  .teacher-detail-list > div:last-child { border-bottom: 0; }
  .teacher-detail-list dt { color: #64748b; font-size: 12px; font-weight: 700; }
  .teacher-detail-list dd { margin: 0; overflow-wrap: anywhere; color: #0f172a; font-size: 12px; font-weight: 500; text-align: left; white-space: pre-wrap; }
  .teacher-detail-list a { color: var(--primary); text-decoration: none; }
  .teacher-detail-contact { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
  .teacher-detail-contact > div { display: block; padding: 8px 0; border: 0; }
  .teacher-detail-contact dd { margin-top: 5px; text-align: left; }
  @media (max-width: 680px) {
    .teacher-profile-hero { align-items: flex-start; padding: 17px; }
    .teacher-profile-avatar { width: 58px; height: 58px; flex-basis: 58px; border-radius: 17px; font-size: 18px; }
    .teacher-profile-grid { grid-template-columns: 1fr; }
    .teacher-detail-card-wide { grid-column: auto; }
    .teacher-detail-contact { grid-template-columns: 1fr; gap: 0; }
  }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/charts.js') }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  function teacherValue(teacher, path, fallback) {
    var value = path.split('.').reduce(function (current, key) { return current && current[key] !== undefined ? current[key] : null; }, teacher);
    return value === null || value === undefined || value === '' ? (fallback || '—') : value;
  }

  function teacherDate(value) {
    if (!value) { return '—'; }
    var parts = String(value).slice(0, 10).split('-');
    return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : value;
  }

  document.querySelectorAll('[data-view-teacher]').forEach(function (button) {
    button.addEventListener('click', function () {
      var teacher = JSON.parse(button.dataset.viewTeacher);
      var modal = document.getElementById('teacher-view-modal');
      var firstName = teacherValue(teacher, 'prenom', '');
      var lastName = teacherValue(teacher, 'nom', '');
      var fullName = (firstName + ' ' + lastName).trim() || 'Enseignant';
      var initials = ((firstName.charAt(0) || '') + (lastName.charAt(0) || '')).toUpperCase() || 'EN';
      var statuses = { en_activite: 'En activité', retraite: 'Retraité', suspension_provisoire: 'Suspension provisoire', abandon: 'Abandon', decede: 'Décédé', integre: 'Intégré', radie: 'Radié', cessation_paiement: 'Cessation de paiement' };
      modal.querySelector('[data-view-initials]').textContent = initials;
      modal.querySelector('[data-view-full-name]').textContent = fullName;
      modal.querySelector('[data-view-matricule]').textContent = 'Matricule : ' + teacherValue(teacher, 'matricule');
      modal.querySelector('[data-view-status]').textContent = statuses[teacher.statut] || teacherValue(teacher, 'statut');
      modal.querySelectorAll('[data-teacher-detail]').forEach(function (field) {
        var value = teacherValue(teacher, field.dataset.teacherDetail, 'Non renseigné');
        if (value !== 'Non renseigné') {
          if (field.dataset.format === 'date') { value = teacherDate(value); }
          if (field.dataset.format === 'bool') { value = value === true || value === 1 || value === '1' ? 'Oui' : 'Non'; }
          if (field.dataset.format === 'money') { value = Number(value).toLocaleString('fr-FR') + ' FCFA'; }
        }
        field.textContent = value;
      });
    });
  });

  var ia = document.querySelector('[data-teacher-ia]');
  var ief = document.querySelector('[data-teacher-ief]');

  if (ia && ief) {
    ia.addEventListener('change', function () {
      ief.innerHTML = '<option value="">Chargement des IEF...</option>';
      ief.disabled = true;
      if (!ia.value) { ief.innerHTML = '<option value="">Sélectionner d’abord une IA</option>'; return; }
      fetch(ia.dataset.iefsUrl + '?ia_id=' + encodeURIComponent(ia.value), { headers: { 'Accept': 'application/json' } })
        .then(function (response) { return response.json(); })
        .then(function (payload) {
          ief.innerHTML = '<option value="">Sélectionner une IEF</option>';
          (payload.items || []).forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.libelle || item.nom || 'IEF';
            ief.appendChild(option);
          });
          ief.disabled = false;
        })
        .catch(function () { ief.innerHTML = '<option value="">Impossible de charger les IEF</option>'; });
    });
  }

  var createForm = document.querySelector('#teacher-create-modal form');
  var createCorps = document.getElementById('teacher-corps');
  var createCategorie = document.getElementById('teacher-categorie');
  var createCategorieField = document.querySelector('[data-create-categorie-field]');
  var createStep = 1;
  var createGroups = [
    ['teacher-matricule', 'teacher-cni', 'teacher-nom', 'teacher-prenom', 'teacher-date-naissance', 'teacher-lieu-naissance', 'teacher-genre'],
    ['teacher-ia', 'teacher-ief', 'teacher-corps', 'teacher-diplome', 'teacher-categorie', 'teacher-salaire', 'teacher-discipline', 'teacher-lieu-service', 'teacher-date-recrutement', 'teacher-date-fin-contrat', 'teacher-generation', 'teacher-statut', 'teacher-observations'],
    ['teacher-couple', 'teacher-conjoint-travaille', 'teacher-enfants', 'teacher-femmes', 'teacher-parts'],
    ['teacher-email', 'teacher-telephone', 'teacher-adresse', 'teacher-banque', 'teacher-type-virement', 'teacher-code-banque', 'teacher-code-guichet', 'teacher-numero-compte', 'teacher-cle-rib']
  ];

  function reorderFormGroups(container, ids) {
    if (!container) { return; }
    ids.forEach(function (id) {
      var field = document.getElementById(id);
      var group = field ? field.closest('.form-group') : null;
      if (group) { container.appendChild(group); }
    });
  }

  var createGrid = createForm ? createForm.querySelector('.form-grid') : null;
  createGroups.forEach(function (ids) { reorderFormGroups(createGrid, ids); });

  var editFieldGroups = {
    1: ['edit-teacher-matricule', 'edit-teacher-cni', 'edit-teacher-nom', 'edit-teacher-prenom', 'edit-teacher-date-naissance', 'edit-teacher-lieu-naissance', 'edit-teacher-genre'],
    2: ['edit-teacher-ia', 'edit-teacher-ief', 'edit-teacher-corps', 'edit-teacher-diplome', 'edit-teacher-categorie', 'edit-teacher-salaire', 'edit-teacher-discipline', 'edit-teacher-lieu-service', 'edit-teacher-date-recrutement', 'edit-teacher-date-fin-contrat', 'edit-teacher-generation', 'edit-teacher-statut', 'edit-teacher-observations'],
    3: ['edit-teacher-couple', 'edit-teacher-conjoint-travaille', 'edit-teacher-enfants', 'edit-teacher-femmes', 'edit-teacher-parts'],
    4: ['edit-teacher-email', 'edit-teacher-telephone', 'edit-teacher-adresse', 'edit-teacher-banque', 'edit-teacher-type-virement', 'edit-teacher-code-banque', 'edit-teacher-code-guichet', 'edit-teacher-numero-compte', 'edit-teacher-cle-rib']
  };
  Object.keys(editFieldGroups).forEach(function (step) {
    var panel = document.querySelector('[data-edit-panel="' + step + '"]');
    reorderFormGroups(panel ? panel.querySelector('.form-grid') : null, editFieldGroups[step]);
  });

  function isContractuelCorps(select) {
    var option = select && select.options[select.selectedIndex];
    if (!option || !option.value) { return false; }
    return String(option.dataset.corpsCode || option.textContent).trim().toLowerCase() === 'contractuel';
  }

  function isVacataireCorps(select) {
    var option = select && select.options[select.selectedIndex];
    if (!option || !option.value) { return false; }
    var code = String(option.dataset.corpsCode || '').trim().toLowerCase();
    var label = String(option.textContent || '').trim().toLowerCase();
    return code === 'vac' || code.includes('vacat') || label.includes('vacat');
  }

  function diplomaLabel(option) {
    return option.textContent.normalize('NFC').trim().replace(/\s+/g, ' ').toUpperCase();
  }

  function hideDuplicateDiplomas(select, selectedId) {
    if (!select.diplomaOptions) {
      select.diplomaOptions = Array.from(select.options).map(function (option) { return option.cloneNode(true); });
    }
    var value = selectedId === undefined ? select.value : String(selectedId || '');
    var selected = select.diplomaOptions.find(function (option) { return option.value === value; });
    var representatives = new Map();
    select.diplomaOptions.forEach(function (option) {
      var label = diplomaLabel(option);
      if (!representatives.has(label) || option === selected) { representatives.set(label, option); }
    });
    select.replaceChildren();
    representatives.forEach(function (option) {
      var uniqueOption = option.cloneNode(true);
      uniqueOption.hidden = false;
      if (uniqueOption.value) { uniqueOption.textContent = diplomaLabel(option); }
      select.appendChild(uniqueOption);
    });
    select.value = value;
  }
  ['teacher-diplome', 'edit-teacher-diplome'].forEach(function (id) {
    hideDuplicateDiplomas(document.getElementById(id));
  });

  function applyDiplomeSalary(diplomeSelect, corpsSelect, categorieSelect, salaryInput) {
    var option = diplomeSelect && diplomeSelect.selectedOptions[0];
    diplomeSelect.setCustomValidity('');
    hideDuplicateDiplomas(diplomeSelect);
    if (isVacataireCorps(corpsSelect)) {
      salaryInput.value = '150000';
      return;
    }
    var categoryId = categorieSelect && !categorieSelect.disabled ? categorieSelect.value : '';
    var match = option && option.value ? option : null;
    if (match && categoryId) {
      match = diplomeSelect.diplomaOptions.find(function (candidate) {
        return candidate.value && diplomaLabel(candidate) === diplomaLabel(option)
          && String(candidate.dataset.categorieId || '') === String(categoryId);
      });
    } else if (isContractuelCorps(corpsSelect)) {
      match = null;
    }
    hideDuplicateDiplomas(diplomeSelect, match ? match.value : diplomeSelect.value);
    salaryInput.value = match ? (match.dataset.salaireBrut || '0') : '';
    diplomeSelect.setCustomValidity(option && option.value && categoryId && !match
      ? 'Aucun salaire brut paramétré pour ce diplôme et cette catégorie.' : '');
  }

  function filterCategories(select, corpsId, diplomeSelect) {
    var diploma = diplomeSelect.selectedOptions[0];
    var categoryIds = new Set();
    if (diploma && diploma.value) {
      diplomeSelect.diplomaOptions.forEach(function (option) {
        if (option.value && diplomaLabel(option) === diplomaLabel(diploma)) {
          categoryIds.add(String(option.dataset.categorieId || ''));
        }
      });
    }
    var available = 0;
    Array.from(select.options).forEach(function (option, index) {
      if (index === 0) { return; }
      var matches = categoryIds.has(String(option.value))
        && String(option.dataset.corpsId || '') === String(corpsId || '');
      option.hidden = !matches;
      option.disabled = !matches;
      if (matches) { available++; }
    });
    select.options[0].textContent = !diploma || !diploma.value
      ? 'Sélectionner d’abord un diplôme'
      : (available ? 'Sélectionner une catégorie' : 'Aucune catégorie pour ce diplôme');
  }

  function updateCreateCategorieVisibility() {
    var visible = createStep === 2 && isContractuelCorps(createCorps);
    createCategorieField.hidden = !visible;
    createCategorieField.style.display = visible ? '' : 'none';
    createCategorie.disabled = !visible;
    createCategorie.required = visible;
    filterCategories(createCategorie, createCorps.value, document.getElementById('teacher-diplome'));
    var selected = createCategorie.selectedOptions[0];
    if (!isContractuelCorps(createCorps) || (selected && selected.hidden)) { createCategorie.value = ''; createCategorie.classList.remove('is-invalid'); }
    var contractField = document.querySelector('[data-create-contract-field]');
    var contractInput = document.getElementById('teacher-date-fin-contrat');
    var contractVisible = createStep === 2 && isContractuelCorps(createCorps);
    contractField.hidden = !contractVisible;
    contractField.style.display = contractVisible ? '' : 'none';
    contractInput.required = contractVisible;
    contractInput.disabled = !contractVisible;
  }

  function validateCreateStep(step) {
    var valid = true;
    createGroups[step - 1].forEach(function (id) {
      var field = document.getElementById(id);
      if (!field || (!field.required && !field.value)) { return; }
      var value = field.value ? field.value.trim() : '';
      var invalid = !value || !field.validity.valid;
      if (!invalid && field.type === 'email') { invalid = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value); }
      if (!invalid && field.type === 'number') { invalid = Number(value) <= 0; }
      if (!invalid && field.type === 'date' && field.id === 'teacher-date-naissance') { invalid = value >= '{{ now()->format('Y-m-d') }}'; }
      field.classList.toggle('is-invalid', invalid);
      field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
      if (invalid) { valid = false; }
    });
    if (!valid) {
      var firstInvalid = createGroups[step - 1].map(function (id) { return document.getElementById(id); }).find(function (field) { return field && field.classList.contains('is-invalid'); });
      if (firstInvalid) { firstInvalid.focus(); }
    }
    return valid;
  }

  function showCreateStep(step) {
    createStep = Math.max(1, Math.min(4, step));
    createGroups.forEach(function (group, index) {
      var visible = index + 1 === createStep;
      group.forEach(function (id) {
        var field = document.getElementById(id);
        var wrapper = field ? field.closest('.form-group') : null;
        if (wrapper) { wrapper.hidden = !visible; wrapper.style.display = visible ? '' : 'none'; }
        if (field && field !== document.getElementById('teacher-ief')) { field.disabled = !visible; }
      });
    });
    document.querySelectorAll('[data-create-step]').forEach(function (button) {
      var buttonStep = Number(button.dataset.createStep);
      button.classList.toggle('active', buttonStep === createStep);
      button.classList.toggle('done', buttonStep < createStep);
      var marker = button.querySelector('.wizard-step-number');
      if (marker) { marker.textContent = buttonStep < createStep ? '✓' : String(buttonStep); }
    });
    createForm.querySelector('[data-create-prev]').hidden = createStep === 1;
    createForm.querySelector('[data-create-next]').hidden = createStep === 4;
    createForm.querySelector('[data-create-submit]').hidden = createStep !== 4;
    updateCreateCategorieVisibility();
  }

  if (createForm) {
    function calculateCreateParts() {
      var married = document.getElementById('teacher-couple').value === '1';
      var spouseWorks = married && document.getElementById('teacher-conjoint-travaille').value === '1';
      if (!married) { document.getElementById('teacher-conjoint-travaille').value = '0'; }
      document.getElementById('teacher-parts').value = Math.min(5, Math.max(1, 1 + (married ? 1 : 0) + Math.max(0, Number(document.getElementById('teacher-enfants').value) || 0) * .5 - (spouseWorks ? .5 : 0)));
    }
    document.getElementById('teacher-couple').addEventListener('change', calculateCreateParts);
    document.getElementById('teacher-enfants').addEventListener('input', calculateCreateParts);
    document.getElementById('teacher-conjoint-travaille').addEventListener('change', calculateCreateParts);
    calculateCreateParts();
    document.querySelectorAll('[data-create-step]').forEach(function (button) {
      button.addEventListener('click', function () {
        var target = Number(button.dataset.createStep);
        if (target <= createStep) { showCreateStep(target); return; }
        if (target === createStep + 1 && validateCreateStep(createStep)) { showCreateStep(target); }
      });
    });
    createForm.querySelector('[data-create-prev]').addEventListener('click', function () { showCreateStep(createStep - 1); });
    createForm.querySelector('[data-create-next]').addEventListener('click', function () { if (validateCreateStep(createStep)) { showCreateStep(createStep + 1); } });
    createForm.addEventListener('submit', function (event) {
      for (var step = 1; step <= 4; step += 1) {
        if (!validateCreateStep(step)) { event.preventDefault(); showCreateStep(step); return; }
      }
      createGroups.forEach(function (group) {
        group.forEach(function (id) {
          var field = document.getElementById(id);
          if (!field) { return; }
          field.disabled = false;
        });
      });
    });
    document.querySelectorAll('[data-modal-open="teacher-create-modal"]').forEach(function (button) { button.addEventListener('click', function () { showCreateStep(1); }); });
    createCorps.addEventListener('change', function () {
      updateCreateCategorieVisibility();
      applyDiplomeSalary(document.getElementById('teacher-diplome'), createCorps, createCategorie, document.getElementById('teacher-salaire'));
    });
    createCategorie.addEventListener('change', function () {
      applyDiplomeSalary(document.getElementById('teacher-diplome'), createCorps, createCategorie, document.getElementById('teacher-salaire'));
    });
    document.getElementById('teacher-diplome').addEventListener('change', function () {
      updateCreateCategorieVisibility();
      applyDiplomeSalary(this, createCorps, createCategorie, document.getElementById('teacher-salaire'));
    });
    showCreateStep(1);
  }

  var editModal = document.getElementById('teacher-edit-modal');
  var editForm = document.getElementById('teacher-edit-form');
  var editIa = document.getElementById('edit-teacher-ia');
  var editIef = document.getElementById('edit-teacher-ief');
  var editCorps = document.getElementById('edit-teacher-corps');
  var editCategorie = document.getElementById('edit-teacher-categorie');
  var editCategorieField = document.querySelector('[data-edit-categorie-field]');
  var editStep = 1;

  function updateEditCategorieVisibility() {
    var visible = isContractuelCorps(editCorps);
    editCategorieField.hidden = !visible;
    editCategorieField.style.display = visible ? '' : 'none';
    editCategorie.disabled = !visible;
    editCategorie.required = visible;
    filterCategories(editCategorie, editCorps.value, document.getElementById('edit-teacher-diplome'));
    var selected = editCategorie.selectedOptions[0];
    if (!visible || (selected && selected.hidden)) { editCategorie.value = ''; editCategorie.classList.remove('is-invalid'); }
    var contractField = document.querySelector('[data-edit-contract-field]');
    var contractInput = document.getElementById('edit-teacher-date-fin-contrat');
    contractField.hidden = !visible;
    contractField.style.display = visible ? '' : 'none';
    contractInput.required = visible;
  }

  function loadEditIefs(iaId, selectedId) {
    editIef.innerHTML = '<option value="">Chargement des IEF...</option>';
    editIef.disabled = true;
    if (!iaId) { editIef.innerHTML = '<option value="">Sélectionner d’abord une IA</option>'; return; }
    fetch(editIa.dataset.iefsUrl + '?ia_id=' + encodeURIComponent(iaId), { headers: { 'Accept': 'application/json' } })
      .then(function (response) { return response.json(); })
      .then(function (payload) {
        editIef.innerHTML = '<option value="">Sélectionner une IEF</option>';
        (payload.items || []).forEach(function (item) {
          var option = document.createElement('option'); option.value = item.id; option.textContent = item.libelle || item.nom || 'IEF'; editIef.appendChild(option);
        });
        if (selectedId) { editIef.value = selectedId; }
        editIef.disabled = false;
      });
  }

  function showEditStep(step) {
    editStep = Math.max(1, Math.min(4, step));
    editForm.querySelectorAll('[data-edit-panel]').forEach(function (panel) { panel.hidden = Number(panel.dataset.editPanel) !== editStep; });
    editForm.querySelectorAll('[data-edit-step]').forEach(function (button) {
      var buttonStep = Number(button.dataset.editStep);
      button.classList.toggle('active', buttonStep === editStep);
      button.classList.toggle('done', buttonStep < editStep);
      var marker = button.querySelector('.wizard-step-number');
      if (marker) { marker.textContent = buttonStep < editStep ? '✓' : String(buttonStep); }
    });
    editForm.querySelector('[data-edit-prev]').hidden = editStep === 1;
    editForm.querySelector('[data-edit-next]').hidden = editStep === 4;
    editForm.querySelector('[data-edit-submit]').hidden = editStep !== 4;
  }

  function validateEditStep() {
    var panel = editForm.querySelector('[data-edit-panel="' + editStep + '"]');
    var valid = true;
    panel.querySelectorAll('[required]:not(:disabled)').forEach(function (field) {
      var value = field.value ? field.value.trim() : '';
      var invalid = !value || !field.validity.valid;
      if (!invalid && field.type === 'email') { invalid = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value); }
      if (!invalid && field.type === 'number') { invalid = Number(value) <= 0; }
      field.classList.toggle('is-invalid', invalid);
      if (invalid) { valid = false; }
    });
    if (!valid) { panel.querySelector('.is-invalid').focus(); }
    return valid;
  }

  function validateAllEditSteps() {
    var firstInvalidStep = null;
    for (var step = 1; step <= 4; step += 1) {
      editStep = step;
      if (!validateEditStep() && firstInvalidStep === null) { firstInvalidStep = step; }
    }
    if (firstInvalidStep !== null) { showEditStep(firstInvalidStep); return false; }
    return true;
  }

  document.querySelectorAll('[data-edit-teacher]').forEach(function (button) {
    button.addEventListener('click', function () {
      var teacher = JSON.parse(button.dataset.editTeacher);
      editForm.action = editForm.dataset.actionTemplate.replace('__id__', teacher.id);
      document.getElementById('edit-teacher-matricule').value = teacher.matricule || '';
      document.getElementById('edit-teacher-nom').value = teacher.nom || '';
      document.getElementById('edit-teacher-prenom').value = teacher.prenom || '';
      document.getElementById('edit-teacher-lieu-naissance').value = teacher.lieu_naissance || '';
      document.getElementById('edit-teacher-cni').value = teacher.cni || '';
      document.getElementById('edit-teacher-genre').value = teacher.genre || '';
      document.getElementById('edit-teacher-date-naissance').value = teacher.date_naissance || '';
      document.getElementById('edit-teacher-adresse').value = teacher.adresse || '';
      document.getElementById('edit-teacher-email').value = teacher.email || '';
      document.getElementById('edit-teacher-telephone').value = teacher.telephone || '';
      editIa.value = teacher.ia ? teacher.ia.id : '';
      editCorps.value = teacher.corps ? teacher.corps.id : '';
      hideDuplicateDiplomas(document.getElementById('edit-teacher-diplome'), teacher.diplome ? teacher.diplome.id : '');
      editCategorie.value = teacher.categorie ? teacher.categorie.id : '';
      updateEditCategorieVisibility();
      document.getElementById('edit-teacher-date-recrutement').value = teacher.date_recrutement || '';
      document.getElementById('edit-teacher-statut').value = teacher.statut || 'en_activite';
      document.getElementById('edit-teacher-discipline').value = teacher.discipline ? teacher.discipline.id : '';
      document.getElementById('edit-teacher-lieu-service').value = teacher.lieu_service ? teacher.lieu_service.id : '';
      document.getElementById('edit-teacher-salaire').value = teacher.salaire_brut || '';
      document.getElementById('edit-teacher-generation').value = teacher.generation || '';
      document.getElementById('edit-teacher-date-fin-contrat').value = teacher.date_fin_contrat || '';
      document.getElementById('edit-teacher-observations').value = teacher.observations || '';
      document.getElementById('edit-teacher-couple').value = teacher.est_en_couple ? '1' : '0';
      document.getElementById('edit-teacher-enfants').value = teacher.nombre_enfants || '';
      document.getElementById('edit-teacher-femmes').value = teacher.nombre_femmes === null || teacher.nombre_femmes === undefined ? '' : teacher.nombre_femmes;
      document.getElementById('edit-teacher-conjoint-travaille').value = teacher.conjoint_travaille ? '1' : '0';
      var account = teacher.compte_bancaire || {};
      document.getElementById('edit-teacher-banque').value = account.institut_financier ? account.institut_financier.id : '';
      document.getElementById('edit-teacher-code-banque').value = account.code_banque || '';
      document.getElementById('edit-teacher-code-guichet').value = account.code_guichet || '';
      document.getElementById('edit-teacher-numero-compte').value = account.numero_compte || '';
      document.getElementById('edit-teacher-cle-rib').value = account.cle_rib || '';
      document.getElementById('edit-teacher-type-virement').value = account.type_virement || 'unitaire';
      applyDiplomeSalary(document.getElementById('edit-teacher-diplome'), editCorps, editCategorie, document.getElementById('edit-teacher-salaire'));
      calculateEditParts();
      loadEditIefs(editIa.value, teacher.ief ? teacher.ief.id : '');
      showEditStep(1);
      editModal.hidden = false;
    });
  });
  editIa.addEventListener('change', function () { loadEditIefs(editIa.value, ''); });
  editCorps.addEventListener('change', function () {
    updateEditCategorieVisibility();
    applyDiplomeSalary(document.getElementById('edit-teacher-diplome'), editCorps, editCategorie, document.getElementById('edit-teacher-salaire'));
  });
  editCategorie.addEventListener('change', function () {
    applyDiplomeSalary(document.getElementById('edit-teacher-diplome'), editCorps, editCategorie, document.getElementById('edit-teacher-salaire'));
  });
  document.getElementById('edit-teacher-diplome').addEventListener('change', function () {
    updateEditCategorieVisibility();
    applyDiplomeSalary(this, editCorps, editCategorie, document.getElementById('edit-teacher-salaire'));
  });
  function calculateEditParts() {
    var married = document.getElementById('edit-teacher-couple').value === '1';
    var spouseWorks = married && document.getElementById('edit-teacher-conjoint-travaille').value === '1';
    if (!married) { document.getElementById('edit-teacher-conjoint-travaille').value = '0'; }
    document.getElementById('edit-teacher-parts').value = Math.min(5, Math.max(1, 1 + (married ? 1 : 0) + Math.max(0, Number(document.getElementById('edit-teacher-enfants').value) || 0) * .5 - (spouseWorks ? .5 : 0)));
  }
  document.getElementById('edit-teacher-couple').addEventListener('change', calculateEditParts);
  document.getElementById('edit-teacher-enfants').addEventListener('input', calculateEditParts);
  document.getElementById('edit-teacher-conjoint-travaille').addEventListener('change', calculateEditParts);
  editForm.querySelectorAll('[data-edit-step]').forEach(function (button) {
    button.addEventListener('click', function () {
      var targetStep = Number(button.dataset.editStep);
      if (targetStep <= editStep) { showEditStep(targetStep); return; }
      if (targetStep === editStep + 1 && validateEditStep()) { showEditStep(targetStep); }
    });
  });
  editForm.querySelector('[data-edit-prev]').addEventListener('click', function () { showEditStep(editStep - 1); });
  editForm.querySelector('[data-edit-next]').addEventListener('click', function () { if (validateEditStep()) showEditStep(editStep + 1); });
  editForm.addEventListener('submit', function (event) {
    if (!validateAllEditSteps()) { event.preventDefault(); }
  });
});
</script>
@endpush
