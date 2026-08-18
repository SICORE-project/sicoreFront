<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConvocationsController extends Controller
{
    public function __construct(
        protected ConvocationService $convocations
    ) {}

    // Liste des convocations, avec filtres optionnels (date, objet, metier, centre)
    public function index(Request $request): View
    {
         $filtres = array_filter([
        'date' => $request->query('date'),
        'objet' => $request->query('objet'),
        'metier' => $request->query('metier'),
        'centre' => $request->query('centre'),
        'per_page' => 10,
        'page' => $request->query('page', 1),
    ]);

        $resultat = $this->convocations->liste($filtres);

        $meta = $resultat['success'] ? ($resultat['data'] ?? []) : [];
        $items = $meta['data'] ?? [];

        if (! $resultat['success']) {
            session()->flash('error', $resultat['message'] ?? "Impossible de charger les convocations.");
        }

        // La vue (index.blade.php) attend un vrai paginator Laravel
        // (->total(), ->onFirstPage(), ->url(), ...). L'API renvoie une
        // pagination "a la Laravel" (data/current_page/per_page/total), on
        // la reconstruit ici cote frontend.
        $convocationsPage = array_map([$this, 'formatConvocationForView'], $items);

        $convocations = new LengthAwarePaginator(
            $convocationsPage,
            $meta['total'] ?? count($convocationsPage),
            $meta['per_page'] ?? 10,
            $meta['current_page'] ?? (int) $request->query('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Statistiques rapides pour les cartes en haut de page. L'API ne
        // fournit pas d'agregat dedie : "total" reprend le total reel de la
        // pagination, le reste est calcule sur la page courante uniquement.
        // NB: les cles doivent correspondre a index.blade.php
        // ($stats['envoyees'], $stats['brouillons'], $stats['cloturees']).
        $stats = [
            'total' => $convocations->total(),
            'brouillons' => 0,
            'emises' => 0,
            'envoyees' => 0,
            'cloturees' => 0,
        ];

        $statutVersCleStat = [
            'brouillon' => 'brouillons',
            'emise' => 'emises',
            'envoyee' => 'envoyees',
            'cloturee' => 'cloturees',
        ];

        foreach ($items as $convocation) {
            $statut = $convocation['statut'] ?? null;
            if ($statut && isset($statutVersCleStat[$statut])) {
                $stats[$statutVersCleStat[$statut]]++;
            }
        }

        return view('pages.indemnites.convocations.index', [
            'convocations' => $convocations,
            // Une ligne par (convocation x centre) : chaque centre porte
            // son propre metier (deux centres de meme nom mais de metier
            // different sont deux lignes distinctes en base), donc "Voir"/
            // "Modifier" doivent pointer vers CE centre precis plutot que
            // vers toute la convocation (cf. construireLignesCentres()).
            'centresLignes' => $this->construireLignesCentres($items),
            'stats' => $stats,
            'importAvertissements' => session('import_avertissements', []),
            // Selectionne dans le formulaire d'import (modal) : le type
            // n'est plus un champ du document Word, voir import() ci-dessous.
            'typesConvocation' => $this->typesConvocationPourImport(),
            // Menus deroulants des filtres (Date/Objet/Metier/Centre) :
            // valeurs distinctes de TOUTE la base, independantes de la page/
            // du filtre courant — pas seulement celles de $items (page de
            // 10) — sinon un filtre applique retirerait ses propres options
            // du menu. Voir ConvocationsController::optionsFiltres() cote back.
            'filtreOptions' => $this->optionsFiltresPourVue(),
        ]);
    }

    private function optionsFiltresPourVue(): array
    {
        $resultat = $this->convocations->optionsFiltres();

        $vide = ['objets' => [], 'centres' => [], 'metiers' => [], 'dates' => [], 'statuts' => []];

        return $resultat['success'] ? array_merge($vide, $resultat['data'] ?? []) : $vide;
    }

    private function typesConvocationPourImport(): array
    {
        $resultat = $this->convocations->typesConvocation();

        return $resultat['success'] ? ($resultat['data'] ?? []) : [];
    }

    /**
     * Aplati les convocations en lignes "une par centre" pour le tableau de
     * la liste : Objet / Centre / Date debut / Date fin / Action. Une
     * convocation sans centre produit quand meme une ligne (centre "—"),
     * pour rester visible tant qu'aucun centre n'a ete ajoute.
     *
     * NB : cette methode avait disparu du fichier (le "call to undefined
     * method" qui cassait la page liste) alors qu'elle est toujours
     * appelee depuis index() ci-dessus — restauree ici.
     */
    private function construireLignesCentres(array $items): array
    {
        $lignes = [];

        foreach ($items as $item) {
            $centres = $item['centres'] ?? [];

            $ligneCommune = [
                // "ne pas exposer mes donnees" : les liens/checkbox de la
                // liste utilisent desormais le slug opaque de la
                // convocation, jamais son id numerique sequentiel — cf.
                // App\Models\Concerns\HasOpaqueSlug cote back. Fallback sur
                // l'id si jamais le back n'a pas encore la colonne (avant
                // migration), pour ne pas casser la page entre-temps.
                'convocation_id' => $item['slug'] ?? $item['id'] ?? null,
                'objet' => $item['objet'] ?? null,
                'date_debut' => $item['date_debut'] ?? null,
                'date_fin' => $item['date_fin'] ?? null,
                'statut' => $item['statut'] ?? null,
            ];

            if (empty($centres)) {
                $lignes[] = array_merge($ligneCommune, [
                    'centre_id' => null,
                    'centre' => null,
                ]);

                continue;
            }

            foreach ($centres as $centre) {
                $lignes[] = array_merge($ligneCommune, [
                    'centre_id' => $centre['slug'] ?? $centre['id'] ?? null,
                    'centre' => $centre['centre'] ?? null,
                    // Supprimer le dernier centre d'une convocation supprime
                    // la convocation elle-meme (voir
                    // ConvocationCentreController::destroy() cote back) : le
                    // bouton "Supprimer" de la liste doit le dire a l'avance
                    // plutot que de le decouvrir apres coup.
                    'dernier_centre' => count($centres) === 1,
                ]);
            }
        }

        return $lignes;
    }

    /**
     * Import d'une convocation depuis le modèle Word rempli (voir
     * telechargerModeleWord() ci-dessous) : un document = une convocation
     * complète (infos + centres + membres du jury).
     */
    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fichier' => ['required', 'file', 'mimes:docx', 'max:5120'],
        ]);

        $utilisateurId = session('sicore_user.id');

        $resultat = $this->convocations->importer($request->file('fichier'), $utilisateurId);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.convocations')
                ->with('error', $resultat['message'] ?? "Échec de l'import du fichier.");
        }

        $donnees = $resultat['data'] ?? [];
        $importees = $donnees['importees'] ?? 0;
        $avertissements = array_merge($donnees['avertissements'] ?? [], $donnees['erreurs'] ?? []);

        $message = $importees > 0
            ? "{$importees} convocation(s) importée(s)."
            : "Aucune convocation n'a pu être importée.";

        if (! empty($avertissements)) {
            $message .= ' '.count($avertissements)." ligne(s) à vérifier — voir le détail ci-dessous.";
        }

        return redirect()
            ->route('indemnites.convocations')
            ->with($importees > 0 ? 'success' : 'error', $message)
            ->with('import_avertissements', $avertissements);
    }

    // Télécharge le modèle Word à remplir (mêmes champs que le formulaire
    // de nouvelle convocation) — relaie tel quel le corps binaire et les
    // headers renvoyés par le backend (voir ConvocationModeleWordController).
    public function telechargerModeleWord(): StreamedResponse
    {
        $reponse = $this->convocations->telechargerModeleWord();

        return response()->streamDownload(function () use ($reponse) {
            echo $reponse->body();
        }, 'modele-convocation.docx', [
            'Content-Type' => $reponse->header('Content-Type')
                ?: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Aplati les convocations (avec leurs centres et bénéficiaires
     * imbriqués) en lignes exploitables par le tableau DAGE. Une
     * convocation sans bénéficiaire produit quand même une ligne (agent
     * "—"), pour rester visible dans la liste tant qu'elle n'est pas
     * complétée (option B).
     */
    private function construireLignes(array $items): array
    {
        $lignes = [];

        foreach ($items as $item) {
            $centres = $item['centres'] ?? [];
            $enseignants = $item['enseignants'] ?? [];

            $ligneCommune = [
                'convocation_id' => $item['id'] ?? null,
                'objet' => $item['objet'] ?? null,
                // NB: les relations Eloquent sont serialisees en snake_case
                // par l'API (Model::$snakeAttributes = true, comportement
                // par defaut de Laravel) : la methode de relation
                // "typeConvocation()" devient la cle JSON "type_convocation",
                // PAS "typeConvocation". Meme chose pour chef_centre et
                // lieu_service plus bas — piege facile, cf. chef de centre
                // toujours "—" malgre chef_centre_id rempli en base.
                'type' => $item['type_convocation']['libelle'] ?? null,
                'session' => $item['session'] ?? null,
                'date_debut' => $item['date_debut'] ?? null,
                'date_fin' => $item['date_fin'] ?? null,
                'lieu_examen' => $item['lieu_examen'] ?? null,
                'statut' => $item['statut'] ?? null,
            ];

            if (empty($enseignants)) {
                $lignes[] = array_merge($ligneCommune, [
                    'agent' => null,
                    'role' => null,
                    'centre' => $centres[0]['centre'] ?? null,
                    'lieu_service' => null,
                ]);

                continue;
            }

            foreach ($enseignants as $enseignant) {
                $centreId = $enseignant['pivot']['centre_id'] ?? null;
                $centreNom = null;

                foreach ($centres as $centre) {
                    if ($centreId && (int) ($centre['id'] ?? null) === (int) $centreId) {
                        $centreNom = $centre['centre'] ?? null;
                        break;
                    }
                }

                // Convocation a un seul centre : pas d'ambiguite, on
                // l'affiche meme si le beneficiaire n'y est pas rattache
                // explicitement (ancien format d'ajout, sans centre_id).
                if (! $centreNom && count($centres) === 1) {
                    $centreNom = $centres[0]['centre'] ?? null;
                }

                $lignes[] = array_merge($ligneCommune, [
                    'agent' => trim(($enseignant['prenom'] ?? '').' '.($enseignant['nom'] ?? '')) ?: '—',
                    'role' => $enseignant['pivot']['fonction'] ?? null,
                    'centre' => $centreNom,
                    'lieu_service' => $enseignant['lieu_service']['libelle'] ?? null,
                ]);
            }
        }

        return $lignes;
    }

    public function create(): View
    {
        $typesResultat = $this->convocations->typesConvocation();

        return view('pages.indemnites.convocations.create', [
            'utilisateur' => session('sicore_user'),
            // ->get('data') renvoie déjà un tableau (json_decode assoc) ;
            // pas de succès -> select vide plutôt qu'une page cassée.
            'typesConvocation' => $typesResultat['success'] ? ($typesResultat['data'] ?? []) : [],
        ]);
    }

    // Cree la convocation, puis (si fournis) ses centres d'examen et ses
    // beneficiaires. L'import en masse (fichier DECPC, option A) se fait
    // depuis la liste (voir import() plus bas) — ce formulaire ne gere que
    // la saisie manuelle, sans depot de fichier.
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type_convocation_id' => ['nullable', 'integer'],
            'date_emission' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
            'session' => ['nullable', 'string', 'max:150'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'lieu_examen' => ['nullable', 'string', 'max:255'],
            'lieu_affectation' => ['nullable', 'string', 'max:255'],
            'ordre_de_mission' => ['nullable', 'boolean'],
            'statut' => ['nullable', 'in:brouillon,emise,envoyee,cloturee'],

            // Centres d'examen (etape 2 du wizard) : centre, jury, chef de
            // centre — UNE seule entree par centre physique (une convocation
            // doit toujours avoir au moins un centre). Chaque centre peut
            // couvrir PLUSIEURS metiers ("metiers", tableau — un par groupe
            // metier de la carte, cf. convocation-wizard.js::prepareFormData()),
            // chacun avec ses propres membres. Un membre du jury (ci-dessous)
            // reference son centre ET son metier par leur position dans ces
            // tableaux ("centre_index" / "metier_index"), puisqu'a ce stade
            // ils n'ont pas encore d'id reel.
            'centres' => ['required', 'array', 'min:1'],
            'centres.*.centre' => ['required_with:centres', 'string', 'max:255'],
            'centres.*.jury' => ['nullable', 'string', 'max:100'],
            'centres.*.metiers' => ['nullable', 'array'],
            'centres.*.metiers.*' => ['nullable', 'string', 'max:255'],
            'centres.*.chef_centre_id' => ['nullable', 'integer'],
            'centres.*.chef_centre_telephone' => ['nullable', 'string', 'max:30'],
            'centres.*.president_jury_id' => ['nullable', 'integer'],
            'centres.*.president_jury_telephone' => ['nullable', 'string', 'max:30'],

            // Membres du jury (etape 2 du wizard) : un enseignant, sa
            // fonction propre a cette convocation, et le centre/metier
            // auquel il est affecte (index dans "centres"/"centres.*.metiers"
            // ci-dessus, ou vide pour un groupe "general" sans metier).
            'beneficiaires' => ['nullable', 'array'],
            // "distinct" : "UN BENEFICIAIRE NE PEUT PAS ETRE CONVOQUE PLUS
            // DE UNE FOIS" - deja bloque cote wizard (convocation-wizard.js)
            // avant meme la soumission, mais garde-fou serveur au cas ou.
            'beneficiaires.*.enseignant_id' => ['required_with:beneficiaires', 'integer', 'distinct'],
            'beneficiaires.*.fonction' => ['nullable', 'string', 'max:100'],
            'beneficiaires.*.provenance' => ['nullable', 'string', 'max:255'],
            'beneficiaires.*.categorie_personnel' => ['nullable', 'in:fonctionnaire,contractuel,vacataire'],
            'beneficiaires.*.centre_index' => ['nullable', 'integer'],
            'beneficiaires.*.metier_index' => ['nullable', 'integer'],
        ], [
            'beneficiaires.*.enseignant_id.distinct' => "Un bénéficiaire ne peut pas être convoqué plus d'une fois sur la même convocation.",
        ]);

        $data['utilisateur_id'] = session('sicore_user.id');
        $data['ordre_de_mission'] = $request->boolean('ordre_de_mission');

        $centres = $data['centres'] ?? [];
        $beneficiaires = $data['beneficiaires'] ?? [];
        unset($data['centres'], $data['beneficiaires']);

        $resultat = $this->convocations->creer($data);

        if (! $resultat['success']) {
            return back()->withInput()->withErrors(
                $resultat['errors'] ?? ['erreur_generale' => $resultat['message'] ?? 'Erreur lors de la creation des informations générales de la convocation.']
            );
        }

        $convocationId = $resultat['data']['id'];
        // Slug opaque pour le lien de redirection ci-dessous ("ne pas
        // exposer mes donnees") : $convocationId (numerique) reste utilise
        // pour les appels internes suivants (creerCentres/ajouterBeneficiaires),
        // le back les accepte toujours aussi bien que le slug.
        $convocationSlug = $resultat['data']['slug'] ?? $convocationId;

        // Index (position dans "centres") -> id reel renvoye par l'API, pour
        // rattacher chaque beneficiaire a son centre ET a son metier precis
        // au sein de ce centre (position dans "centres.*.metiers" -> id reel
        // du metier cree, cf. ConvocationCentreController::store() qui
        // renvoie les metiers CREES dans le meme ordre que soumis, noms
        // vides exclus — meme convention que le compteur "metierPosition" du
        // wizard front, voir convocation-wizard.js::prepareFormData()).
        $centreIdParIndex = [];
        $metierIdParIndexParCentre = [];

        // La convocation existe deja a ce stade (creee ci-dessus) : un echec
        // sur les centres ou les beneficiaires ne doit plus etre ignore en
        // silence — avant ce correctif, la page redirigeait quand meme vers
        // "Convocation creee avec succes." meme si aucun centre/membre
        // n'avait ete enregistre, sans aucun moyen de savoir ce qui avait
        // vraiment ete sauvegarde. On redirige desormais vers la page
        // "Modifier" de la convocation deja creee (pas back(), qui ferait
        // perdre la saisie ET donnerait l'illusion que rien n'a ete
        // enregistre) avec un message d'erreur precis sur l'etape en cause.
        if (! empty($centres)) {
            $centresResultat = $this->convocations->creerCentres($convocationId, $centres);

            if (! $centresResultat['success']) {
                return redirect()
                    ->route('indemnites.convocations.edit', $convocationSlug)
                    ->with('error', "La convocation a été enregistrée, mais l'ajout des centres d'examen a échoué : ".($centresResultat['message'] ?? 'erreur inconnue').". Complétez l'étape « Centres, jurys et membres » ci-dessous pour réessayer.");
            }

            foreach (($centresResultat['data'] ?? []) as $index => $centreCree) {
                $centreIdParIndex[$index] = $centreCree['id'] ?? null;

                foreach (($centreCree['metiers'] ?? []) as $position => $metierCree) {
                    $metierIdParIndexParCentre[$index][$position] = $metierCree['id'] ?? null;
                }
            }
        }

        if (! empty($beneficiaires)) {
            $beneficiaires = array_map(function (array $beneficiaire) use ($centreIdParIndex, $metierIdParIndexParCentre) {
                $centreIndex = $beneficiaire['centre_index'] ?? null;
                $metierIndex = $beneficiaire['metier_index'] ?? null;

                $centreId = $centreIndex !== null ? ($centreIdParIndex[$centreIndex] ?? null) : null;
                $centreMetierId = ($centreIndex !== null && $metierIndex !== null)
                    ? ($metierIdParIndexParCentre[$centreIndex][$metierIndex] ?? null)
                    : null;

                return [
                    'enseignant_id' => $beneficiaire['enseignant_id'],
                    'fonction' => $beneficiaire['fonction'] ?? null,
                    'provenance' => $beneficiaire['provenance'] ?? null,
                    'categorie_personnel' => $beneficiaire['categorie_personnel'] ?? null,
                    'centre_id' => $centreId,
                    'centre_metier_id' => $centreMetierId,
                ];
            }, $beneficiaires);

            $beneficiairesResultat = $this->convocations->ajouterBeneficiairesAvecFonction($convocationId, $beneficiaires);

            if (! $beneficiairesResultat['success']) {
                return redirect()
                    ->route('indemnites.convocations.edit', $convocationSlug)
                    ->with('error', "La convocation et ses centres ont été enregistrés, mais l'ajout des membres du jury a échoué : ".($beneficiairesResultat['message'] ?? 'erreur inconnue').". Ajoutez-les depuis l'étape « Centres, jurys et membres » ci-dessous.");
            }
        }

        return redirect()
            ->route('indemnites.convocations.show', $convocationSlug)
            ->with('success', 'Convocation creee avec succes.');
    }

    // Recherche d'enseignants pour le tableau de beneficiaires (AJAX, JSON)
    public function rechercherEnseignants(Request $request)
    {
        $resultat = $this->convocations->rechercherEnseignants($request->query('search'));

        return response()->json([
            'success' => $resultat['success'],
            'data' => $resultat['success'] ? ($resultat['data']['data'] ?? []) : [],
        ]);
    }

    public function show(int|string $id): View|RedirectResponse
    {
        return $this->renderShow($id, null);
    }

    // Fiche detail d'UN centre precis d'une convocation (venant de la liste,
    // une ligne = un centre) : ne montre que ce centre et les membres qui y
    // sont explicitement rattaches (pivot.centre_id), pas toute la
    // convocation — cf. commentaire sur construireLignesCentres().
    public function showCentre(int|string $id, int|string $centreId): View|RedirectResponse
    {
        return $this->renderShow($id, $centreId);
    }

    private function renderShow(int|string $id, int|string|null $centreId): View|RedirectResponse
    {
        $resultat = $this->convocations->trouver($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.convocations')
                ->with('error', $resultat['message'] ?? 'Convocation introuvable.');
        }

        $convocation = $this->formatConvocationForView($resultat['data']);

        // Le back renvoie desormais la liste COMPLETE (plus de pagination,
        // cf. ConvocationBeneficiaireController::index()) : 'data' est
        // directement le tableau des beneficiaires, pas un objet paginateur
        // imbrique ('data'.'data').
        $beneficiairesResultat = $this->convocations->beneficiaires($id);
        $beneficiaires = $beneficiairesResultat['success'] ? ($beneficiairesResultat['data'] ?? []) : [];

        if ($centreId !== null) {
            // $centreId (parametre de route) est desormais le slug opaque du
            // centre ("ne pas exposer mes donnees") — on accepte aussi l'id
            // numerique en fallback (anciens liens/avant migration).
            $centre = $convocation->centres->first(
                fn ($c) => (string) ($c['slug'] ?? '') === (string) $centreId
                    || (string) ($c['id'] ?? '') === (string) $centreId
            );

            if (! $centre) {
                return redirect()
                    ->route('indemnites.convocations.show', $id)
                    ->with('error', 'Centre introuvable pour cette convocation.');
            }

            $convocation->centres = collect([$centre]);

            // Le pivot convocation_enseignant est toujours indexe par l'id
            // NUMERIQUE reel du centre (jamais expose) : on le retrouve ici
            // a partir du centre qu'on vient de matcher par slug.
            $centreIdReel = $centre['id'] ?? null;

            $beneficiaires = array_values(array_filter($beneficiaires, function ($beneficiaire) use ($centreIdReel) {
                return (int) ($beneficiaire['pivot']['centre_id'] ?? 0) === (int) $centreIdReel;
            }));
        }

        // Un bloc par centre — Centre / Jury / Chef de centre / Téléphone en
        // tete, puis les membres groupes par metier (un centre peut en
        // couvrir plusieurs, chacun avec ses propres membres) : meme
        // structure que le PDF (ConvocationPdfController), calquee sur le
        // modele papier "convocation jury" fourni par l'utilisatrice.
        $centresAvecMetiers = $convocation->centres
            ->map(function ($centre) use ($beneficiaires) {
                $beneficiairesDuCentre = array_values(array_filter($beneficiaires, function ($beneficiaire) use ($centre) {
                    return (int) ($beneficiaire['pivot']['centre_id'] ?? 0) === (int) ($centre['id'] ?? 0);
                }));

                return array_merge($centre, $this->grouperBeneficiairesParMetier($centre, $beneficiairesDuCentre));
            })
            ->all();

        // Membres ajoutes avant l'existence du rattachement a un centre
        // (pivot.centre_id vide, anciennes donnees) : ne doivent pas
        // disparaitre silencieusement de la fiche complete — regroupes dans
        // un bloc "Sans centre affecté" a part. Non applicable a la fiche
        // centree sur UN centre (deja filtree ci-dessus).
        if ($centreId === null) {
            $idsCentres = array_map('intval', $convocation->centres->pluck('id')->all());

            $beneficiairesSansCentre = array_values(array_filter($beneficiaires, function ($beneficiaire) use ($idsCentres) {
                $centreIdBeneficiaire = $beneficiaire['pivot']['centre_id'] ?? null;

                return empty($centreIdBeneficiaire) || ! in_array((int) $centreIdBeneficiaire, $idsCentres, true);
            }));

            if (! empty($beneficiairesSansCentre)) {
                $centresAvecMetiers[] = [
                    'id' => null,
                    'centre' => 'Sans centre affecté',
                    'jury' => null,
                    'president_jury' => null,
                    'president_jury_telephone' => null,
                    'chef_centre' => null,
                    'chef_centre_telephone' => null,
                    'metiers' => [
                        ['id' => null, 'metier' => 'Non classés', 'beneficiaires' => $beneficiairesSansCentre],
                    ],
                ];
            }
        }

        return view('pages.indemnites.convocations.show', [
            'convocation' => $convocation,
            'centresAvecMetiers' => $centresAvecMetiers,
            'id' => $id,
            'centreId' => $centreId,
        ]);
    }

    /**
     * Regroupe les membres (deja filtres pour CE centre) par metier de ce
     * centre (pivot.centre_metier_id), pour les fiches Voir/Modifier
     * centrees sur un centre (un centre peut couvrir plusieurs metiers,
     * chacun avec ses propres membres — cf. modele papier "convocation
     * jury BT"). Les membres sans metier (donnees anciennes, avant ce
     * regroupement) sont regroupes dans un pseudo-metier "id => null" en
     * fin de liste ("Non classés"), pour que les vues n'aient qu'UNE seule
     * boucle a ecrire plutot que de dupliquer l'affichage d'un tableau de
     * membres une deuxieme fois. Le select "Métier" du formulaire d'ajout
     * doit donc ignorer les entrees dont l'id est null.
     */
    private function grouperBeneficiairesParMetier(array $centre, array $beneficiaires): array
    {
        $metiers = collect($centre['metiers'] ?? [])->map(function (array $metier) use ($beneficiaires) {
            $metier['beneficiaires'] = array_values(array_filter($beneficiaires, function ($beneficiaire) use ($metier) {
                return (int) ($beneficiaire['pivot']['centre_metier_id'] ?? 0) === (int) $metier['id'];
            }));

            return $metier;
        })->all();

        $sansMetier = array_values(array_filter($beneficiaires, function ($beneficiaire) {
            return empty($beneficiaire['pivot']['centre_metier_id']);
        }));

        if (! empty($sansMetier)) {
            $metiers[] = [
                'id' => null,
                'metier' => 'Non classés',
                'beneficiaires' => $sansMetier,
            ];
        }

        return ['metiers' => $metiers];
    }

    // Telecharge le PDF de la convocation (le back le genere au vol s'il
    // n'existe pas encore). On relaie la reponse binaire de l'API telle
    // quelle plutot que de passer par ConvocationService::wrap() (concu
    // pour du JSON) — voir ConvocationService::telechargerPdf().
    public function downloadPdf(int|string $id): RedirectResponse|\Illuminate\Http\Response
    {
        $reponse = $this->convocations->telechargerPdf($id);

        if (! $reponse->successful()) {
            return redirect()
                ->route('indemnites.convocations.show', $id)
                ->with('error', $reponse->json('message') ?? "Impossible de générer le PDF de cette convocation.");
        }

        return response($reponse->body(), 200, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/pdf',
            'Content-Disposition' => $reponse->header('Content-Disposition')
                ?: 'attachment; filename="convocation-'.$id.'.pdf"',
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        return $this->renderEdit($id);
    }

    // Ancienne fiche de modification "centree sur un centre" (venant de la
    // liste, une ligne = un centre). Le wizard "Modifier" agit desormais
    // TOUJOURS sur la convocation entiere, exactement comme la creation
    // ("IL FAUT QUE EDIT SOIT EXACTEMENT COMME CREATE MAIS PREREMPLI") : le
    // scoper a un seul centre serait dangereux, puisque
    // ConvocationSyncController::sync() supprime tout centre non re-soumis -
    // une page limitee a un centre ferait donc disparaitre silencieusement
    // tous les AUTRES centres de la convocation au premier enregistrement.
    // On redirige simplement vers la fiche complete.
    public function editCentre(int|string $id, int|string $centreId): RedirectResponse
    {
        return redirect()->route('indemnites.convocations.edit', $id);
    }

    private function renderEdit(int|string $id): View|RedirectResponse
    {
        $resultat = $this->convocations->trouver($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.convocations')
                ->with('error', $resultat['message'] ?? 'Convocation introuvable.');
        }

        $typesResultat = $this->convocations->typesConvocation();
        $centresResultat = $this->convocations->centres($id);
        $beneficiairesResultat = $this->convocations->beneficiaires($id);

        $centres = $centresResultat['success'] ? ($centresResultat['data'] ?? []) : [];
        // Meme correctif que renderShow() : 'data' est directement le
        // tableau des beneficiaires depuis le retrait de la pagination.
        $beneficiaires = $beneficiairesResultat['success'] ? ($beneficiairesResultat['data'] ?? []) : [];

        return view('pages.indemnites.convocations.edit', [
            'convocation' => $this->formatConvocationForView($resultat['data']),
            'typesConvocation' => $typesResultat['success'] ? ($typesResultat['data'] ?? []) : [],
            'wizardData' => $this->construireWizardData($centres, $beneficiaires),
            'id' => $id,
        ]);
    }

    /**
     * Construit la structure de pre-remplissage attendue par
     * convocation-wizard.js::hydrateFromPrefill() (embarquee en JSON dans
     * edit.blade.php via window.__convocationWizardPrefill) : un tableau de
     * centres, chacun avec ses metiers, chacun avec ses membres - meme
     * imbrication que le wizard de creation, juste deja rempli.
     */
    private function construireWizardData(array $centres, array $beneficiaires): array
    {
        return array_values(array_map(function (array $centre) use ($beneficiaires) {
            $beneficiairesDuCentre = array_values(array_filter($beneficiaires, function ($beneficiaire) use ($centre) {
                return (int) ($beneficiaire['pivot']['centre_id'] ?? 0) === (int) ($centre['id'] ?? 0);
            }));

            $groupes = $this->grouperBeneficiairesParMetier($centre, $beneficiairesDuCentre);

            $metiers = array_map(function (array $metier) {
                return [
                    'id' => $metier['id'] ?? null,
                    // Le pseudo-metier "Non classés" (id => null, membres
                    // sans centre_metier_id) redevient un groupe "general"
                    // (nom vide) une fois renvoye au wizard - meme
                    // convention que la creation, cf. commentaire "groupe
                    // general" dans convocation-wizard.js::prepareFormData().
                    'metier' => $metier['id'] !== null ? ($metier['metier'] ?? '') : '',
                    'membres' => array_map(function ($beneficiaire) {
                        return [
                            'enseignant_id' => $beneficiaire['id'] ?? null,
                            'nom' => $beneficiaire['nom'] ?? null,
                            'prenom' => $beneficiaire['prenom'] ?? null,
                            'telephone' => $beneficiaire['telephone'] ?? null,
                            'fonction' => $beneficiaire['pivot']['fonction'] ?? null,
                            'provenance' => $beneficiaire['pivot']['provenance'] ?? null,
                            'categorie_personnel' => $beneficiaire['pivot']['categorie_personnel'] ?? null,
                        ];
                    }, $metier['beneficiaires'] ?? []),
                ];
            }, $groupes['metiers'] ?? []);

            return [
                'id' => $centre['id'] ?? null,
                'centre' => $centre['centre'] ?? null,
                'jury' => $centre['jury'] ?? null,
                'chef_centre_id' => $centre['chef_centre_id'] ?? null,
                'chef_centre_nom' => trim(($centre['chef_centre']['prenom'] ?? '').' '.($centre['chef_centre']['nom'] ?? '')) ?: null,
                'chef_centre_telephone' => $centre['chef_centre_telephone'] ?? null,
                'president_jury_id' => $centre['president_jury_id'] ?? null,
                'president_jury_nom' => trim(($centre['president_jury']['prenom'] ?? '').' '.($centre['president_jury']['nom'] ?? '')) ?: null,
                'president_jury_telephone' => $centre['president_jury_telephone'] ?? null,
                'metiers' => $metiers,
            ];
        }, $centres));
    }

    // Fiche "Modifier" alignee sur l'assistant de creation ("IL FAUT QUE
    // EDIT SOIT EXACTEMENT COMME CREATE MAIS PREREMPLI") : UN seul
    // enregistrement remplace toute la structure de la convocation (infos
    // generales + centres + leurs metiers + membres du jury) - cf. store()
    // ci-dessus pour la convention centre_index/metier_index, et
    // ConvocationSyncController::sync() cote back pour la logique de
    // remplacement complet.
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'type_convocation_id' => ['nullable', 'integer'],
            'date_emission' => ['sometimes', 'date'],
            'objet' => ['sometimes', 'string', 'max:255'],
            'session' => ['nullable', 'string', 'max:150'],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['sometimes', 'date', 'after_or_equal:date_debut'],
            'heure_debut' => ['sometimes', 'date_format:H:i'],
            'lieu_examen' => ['nullable', 'string', 'max:255'],
            'lieu_affectation' => ['nullable', 'string', 'max:255'],
            'ordre_de_mission' => ['nullable', 'boolean'],
            'statut' => ['nullable', 'in:brouillon,emise,envoyee,cloturee'],

            'centres' => ['required', 'array', 'min:1'],
            'centres.*.id' => ['nullable', 'integer'],
            'centres.*.centre' => ['required_with:centres', 'string', 'max:255'],
            'centres.*.jury' => ['nullable', 'string', 'max:100'],
            'centres.*.metiers' => ['nullable', 'array'],
            'centres.*.metiers.*.id' => ['nullable', 'integer'],
            'centres.*.metiers.*.metier' => ['nullable', 'string', 'max:255'],
            'centres.*.chef_centre_id' => ['nullable', 'integer'],
            'centres.*.chef_centre_telephone' => ['nullable', 'string', 'max:30'],
            'centres.*.president_jury_id' => ['nullable', 'integer'],
            'centres.*.president_jury_telephone' => ['nullable', 'string', 'max:30'],

            'beneficiaires' => ['nullable', 'array'],
            // "distinct" : "UN BENEFICIAIRE NE PEUT PAS ETRE CONVOQUE PLUS
            // DE UNE FOIS" - deja bloque cote wizard (convocation-wizard.js)
            // avant meme la soumission, mais garde-fou serveur au cas ou.
            'beneficiaires.*.enseignant_id' => ['required_with:beneficiaires', 'integer', 'distinct'],
            'beneficiaires.*.fonction' => ['nullable', 'string', 'max:100'],
            'beneficiaires.*.provenance' => ['nullable', 'string', 'max:255'],
            'beneficiaires.*.categorie_personnel' => ['nullable', 'in:fonctionnaire,contractuel,vacataire'],
            'beneficiaires.*.centre_index' => ['nullable', 'integer'],
            'beneficiaires.*.metier_index' => ['nullable', 'integer'],
        ], [
            'beneficiaires.*.enseignant_id.distinct' => "Un bénéficiaire ne peut pas être convoqué plus d'une fois sur la même convocation.",
        ]);

        $data['ordre_de_mission'] = $request->boolean('ordre_de_mission');

        $resultat = $this->convocations->mettreAJourStructure($id, $data);

        if (! $resultat['success']) {
            return back()->withInput()->withErrors(
                $resultat['errors'] ?? ['erreur_generale' => $resultat['message'] ?? 'Erreur lors de la mise a jour.']
            );
        }

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with('success', 'Convocation mise a jour avec succes.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $resultat = $this->convocations->supprimer($id);

        return redirect()
            ->route('indemnites.convocations')
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Convocation supprimee.');
    }

    // Suppression multiple depuis les cases a cocher de la liste (index).
    // Reutilise l'endpoint de suppression unitaire (pas de nouvel endpoint
    // API cote back) : une boucle simple suffit vu les volumes attendus.
    public function destroyMultiple(Request $request): RedirectResponse
    {
        // Une meme convocation peut apparaitre sur plusieurs lignes du
        // tableau DAGE (une par beneficiaire) : on deduplique les id
        // coches pour ne pas tenter de la supprimer deux fois.
        $ids = array_unique(array_filter((array) $request->input('ids', [])));

        if (empty($ids)) {
            return redirect()
                ->route('indemnites.convocations')
                ->with('error', 'Aucune convocation sélectionnée.');
        }

        $supprimees = 0;

        foreach ($ids as $id) {
            $resultat = $this->convocations->supprimer($id);

            if ($resultat['success']) {
                $supprimees++;
            }
        }

        $message = $supprimees > 0
            ? $supprimees.' convocation(s) supprimée(s).'
            : "Aucune des convocations sélectionnées n'a pu être supprimée.";

        return redirect()
            ->route('indemnites.convocations')
            ->with($supprimees > 0 ? 'success' : 'error', $message);
    }

    // Ajoute un ou plusieurs beneficiaires a une convocation existante
    // (utilise depuis la fiche "Modifier" — cf. section Membres du jury).
    public function storeBeneficiaires(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'enseignant_id' => ['required', 'integer'],
            'fonction' => ['nullable', 'string', 'max:100'],
            'provenance' => ['nullable', 'string', 'max:255'],
            'categorie_personnel' => ['nullable', 'in:fonctionnaire,contractuel,vacataire'],
            'centre_id' => ['nullable', 'integer'],
            'centre_metier_id' => ['nullable', 'integer'],
        ]);

        $resultat = $this->convocations->ajouterBeneficiairesAvecFonction($id, [
            [
                'enseignant_id' => $data['enseignant_id'],
                'fonction' => $data['fonction'] ?? null,
                'provenance' => $data['provenance'] ?? null,
                'categorie_personnel' => $data['categorie_personnel'] ?? null,
                'centre_id' => $data['centre_id'] ?? null,
                'centre_metier_id' => $data['centre_metier_id'] ?? null,
            ],
        ]);

        return redirect()
            ->to($this->redirectionEdition($id, $data['centre_id'] ?? null))
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Beneficiaire ajoute.');
    }

    // Modifie un beneficiaire deja rattache a la convocation (utilise
    // depuis la fiche "Modifier" — bouton "Modifier" de la section Membres
    // du jury, qui bascule le sous-formulaire d'ajout en mode edition).
    public function updateBeneficiaire(Request $request, int|string $id, int|string $enseignantId): RedirectResponse
    {
        $data = $request->validate([
            'fonction' => ['nullable', 'string', 'max:100'],
            'provenance' => ['nullable', 'string', 'max:255'],
            'categorie_personnel' => ['nullable', 'in:fonctionnaire,contractuel,vacataire'],
            'centre_id' => ['nullable', 'integer'],
            'centre_metier_id' => ['nullable', 'integer'],
        ]);

        $resultat = $this->convocations->mettreAJourBeneficiaire($id, $enseignantId, $data);

        return redirect()
            ->to($this->redirectionEdition($id, $data['centre_id'] ?? null))
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Beneficiaire mis a jour.');
    }

    // Apres ajout/modification/suppression d'un membre ou d'un metier
    // depuis une fiche centree sur un centre, on y reste (plutot que de
    // renvoyer vers la fiche complete de la convocation).
    private function redirectionEdition(int|string $id, int|string|null $centreId): string
    {
        return $centreId
            ? route('indemnites.convocations.centres.edit', [$id, $centreId])
            : route('indemnites.convocations.edit', $id);
    }

    // Retire un beneficiaire de la convocation (l'enseignant lui-meme
    // n'est pas supprime, seul son rattachement a cette convocation l'est).
    // ?centre_id=... (ajoute par la fiche centree sur un centre) permet d'y
    // rester apres la suppression plutot que de revenir a la fiche complete.
    public function destroyBeneficiaire(Request $request, int|string $id, int|string $enseignantId): RedirectResponse
    {
        $resultat = $this->convocations->supprimerBeneficiaire($id, $enseignantId);

        return redirect()
            ->to($this->redirectionEdition($id, $request->query('centre_id')))
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Beneficiaire retire.');
    }

    // Ajoute un centre d'examen a une convocation existante (utilise depuis
    // la fiche "Modifier" — cf. section Centres d'examen). Meme endpoint API
    // que la creation (ConvocationCentreController::store), qui accepte deja
    // un id de convocation existante.
    public function storeCentres(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'centre' => ['required', 'string', 'max:255'],
            'jury' => ['nullable', 'string', 'max:100'],
            'metier' => ['nullable', 'string', 'max:255'],
            'chef_centre_id' => ['nullable', 'integer'],
            'chef_centre_telephone' => ['nullable', 'string', 'max:30'],
            'president_jury_id' => ['nullable', 'integer'],
            'president_jury_telephone' => ['nullable', 'string', 'max:30'],
        ]);

        $resultat = $this->convocations->creerCentres($id, [$data]);

        return redirect()
            ->route('indemnites.convocations.edit', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Centre ajoute.');
    }

    // Modifie un centre d'examen deja rattache a la convocation (utilise
    // depuis la fiche "Modifier" — bouton "Modifier" de la section Centres
    // d'examen, qui bascule le sous-formulaire d'ajout en mode edition).
    public function updateCentre(Request $request, int|string $id, int|string $centreId): RedirectResponse
    {
        $data = $request->validate([
            'centre' => ['required', 'string', 'max:255'],
            'jury' => ['nullable', 'string', 'max:100'],
            'metier' => ['nullable', 'string', 'max:255'],
            'chef_centre_id' => ['nullable', 'integer'],
            'chef_centre_telephone' => ['nullable', 'string', 'max:30'],
            'president_jury_id' => ['nullable', 'integer'],
            'president_jury_telephone' => ['nullable', 'string', 'max:30'],
        ]);

        $resultat = $this->convocations->mettreAJourCentre($id, $centreId, $data);

        return redirect()
            ->to($this->redirectionEdition($id, $centreId))
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Centre mis a jour.');
    }

    // Supprime UN centre d'examen (les membres qui y etaient rattaches ne
    // sont pas supprimes, seul leur rattachement au centre est retire) sans
    // toucher aux autres centres ni supprimer la convocation elle-meme —
    // appele depuis le bouton "Supprimer" de la ligne (une ligne = un
    // centre) sur la liste des convocations, d'ou le retour a la liste.
    public function destroyCentre(int|string $id, int|string $centreId): RedirectResponse
    {
        $resultat = $this->convocations->supprimerCentre($id, $centreId);

        return redirect()
            ->route('indemnites.convocations')
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Centre supprime.');
    }

    // Ajoute un metier a un centre (utilise depuis la fiche centree sur ce
    // centre — cf. section Métiers).
    public function storeMetier(Request $request, int|string $id, int|string $centreId): RedirectResponse
    {
        $data = $request->validate([
            'metier' => ['required', 'string', 'max:255'],
        ]);

        $resultat = $this->convocations->ajouterMetier($id, $centreId, $data);

        return redirect()
            ->route('indemnites.convocations.centres.edit', [$id, $centreId])
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Métier ajouté.');
    }

    // Modifie un metier deja rattache au centre (bouton "Modifier" de la
    // section Métiers, meme bascule JS que pour un centre/beneficiaire).
    public function updateMetier(Request $request, int|string $id, int|string $centreId, int|string $metierId): RedirectResponse
    {
        $data = $request->validate([
            'metier' => ['required', 'string', 'max:255'],
        ]);

        $resultat = $this->convocations->mettreAJourMetier($id, $centreId, $metierId, $data);

        return redirect()
            ->route('indemnites.convocations.centres.edit', [$id, $centreId])
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Métier mis à jour.');
    }

    // Supprime un metier (les membres qui y etaient rattaches ne sont pas
    // supprimes, seul leur rattachement a ce metier est retire).
    public function destroyMetier(int|string $id, int|string $centreId, int|string $metierId): RedirectResponse
    {
        $resultat = $this->convocations->supprimerMetier($id, $centreId, $metierId);

        return redirect()
            ->route('indemnites.convocations.centres.edit', [$id, $centreId])
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Métier supprimé.');
    }

    // Envoie la convocation par e-mail a ses beneficiaires (tous par
    // defaut, ou seulement ceux coches dans le formulaire si un tableau
    // enseignant_ids est soumis). Message personnalise optionnel.
    public function envoyer(Request $request, int|string $id): RedirectResponse
    {
        $donnees = array_filter([
            'enseignant_ids' => $request->input('enseignant_ids'),
            'message' => $request->input('message'),
        ], fn ($valeur) => $valeur !== null && $valeur !== '');

        $resultat = $this->convocations->envoyer($id, $donnees);

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? "Échec de l'envoi de la convocation.");
    }

    // Relance les beneficiaires dont le dernier envoi est en echec (ou
    // seulement enseignant_ids si soumis).
    public function relancer(Request $request, int|string $id): RedirectResponse
    {
        $donnees = array_filter([
            'enseignant_ids' => $request->input('enseignant_ids'),
            'message' => $request->input('message'),
        ], fn ($valeur) => $valeur !== null && $valeur !== '');

        $resultat = $this->convocations->relancer($id, $donnees);

        return redirect()
            ->route('indemnites.convocations.suivi', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? "Échec de la relance.");
    }

    // Historique des envois + stats rapides de la convocation.
    public function suivi(int|string $id): View|RedirectResponse
    {
        $resultat = $this->convocations->suivi($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.convocations.show', $id)
                ->with('error', $resultat['message'] ?? 'Convocation introuvable.');
        }

        $donnees = $resultat['data'] ?? [];
        $envois = $donnees['envois'] ?? [];

        // Meme convention de formatage de date que le reste du module
        // (Carbon::parse protege par try/catch, cf. formatConvocationForView).
        $envois = array_map(function (array $envoi) {
            if (! empty($envoi['date_envoi'])) {
                try {
                    $envoi['date_envoi'] = Carbon::parse($envoi['date_envoi'])->format('d/m/Y H:i');
                } catch (\Throwable) {
                    // Date illisible : on l'affiche telle quelle plutot que de la faire disparaitre.
                }
            }

            return $envoi;
        }, $envois);

        return view('pages.indemnites.convocations.suivi', [
            'stats' => $donnees['stats'] ?? ['total' => 0, 'envoye' => 0, 'echec' => 0],
            'envois' => $envois,
            'id' => $id,
        ]);
    }

    /**
     * Convertit un item JSON de l'API (tableau associatif) en objet
     * exploitable par les vues Blade, qui accedent aux champs en notation
     * objet ($convocation->objet) et appellent des methodes de collection
     * sur les relations ($convocation->centres->pluck('nom')) ainsi que
     * ->format() sur les dates (optional($convocation->date_debut)).
     */
    private function formatConvocationForView(array $item): object
    {
        $convocation = (object) $item;

        foreach (['date_debut', 'date_fin', 'date_emission'] as $champDate) {
            if (! empty($item[$champDate])) {
                try {
                    $convocation->{$champDate} = Carbon::parse($item[$champDate]);
                } catch (\Throwable) {
                    $convocation->{$champDate} = null;
                }
            }
        }

        $convocation->centres = collect($item['centres'] ?? []);
        $convocation->enseignant = isset($item['enseignant']) ? (object) $item['enseignant'] : null;

        return $convocation;
    }
}