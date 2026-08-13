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

    // Liste des convocations, avec filtres optionnels (statut, date, objet)
    public function index(Request $request): View
    {
        $filtres = array_filter([
            'statut' => $request->query('statut'),
            'date' => $request->query('date'),
            'objet' => $request->query('objet'),
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
            // Liste "a plat" pour le tableau DAGE (point 3 du cahier des
            // charges) : une ligne par (convocation x centre x agent),
            // avec les colonnes Agent/Type/Session/Centre/Role/... qui ne
            // sont pas toutes portees par le meme objet Eloquent.
            'lignes' => $this->construireLignes($items),
            'stats' => $stats,
            'statutFiltre' => $request->query('statut', ''),
            'importAvertissements' => session('import_avertissements', []),
        ]);
    }

    /**
     * Option A du workflow DAGE : import du fichier CSV remis par la
     * DECPC (voir GUIDE-IMPORT-CONVOCATIONS.md).
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'fichier' => ['required', 'file', 'mimes:csv,txt,docx', 'max:5120'],
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

    // Export CSV du tableau DAGE, en respectant les filtres actifs
    // (statut/date/objet) — mêmes lignes que construireLignes(), mais sur
    // la totalité des résultats filtrés plutôt que la seule page affichée.
    public function export(Request $request): StreamedResponse
    {
        $filtres = array_filter([
            'statut' => $request->query('statut'),
            'date' => $request->query('date'),
            'objet' => $request->query('objet'),
            'per_page' => 5000,
        ]);

        $resultat = $this->convocations->liste($filtres);
        $items = $resultat['success'] ? ($resultat['data']['data'] ?? []) : [];
        $lignes = $this->construireLignes($items);

        $nomFichier = 'convocations_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($lignes) {
            $sortie = fopen('php://output', 'w');

            // BOM UTF-8 : sans ca, Excel affiche les accents mal encodes.
            fwrite($sortie, "\xEF\xBB\xBF");

            fputcsv($sortie, [
                'Agent', 'Type', 'Objet', 'Session', 'Centre', 'Rôle',
                'Date début', 'Date fin', 'Lieu de service', "Lieu d'examen", 'Statut',
            ], ';');

            foreach ($lignes as $ligne) {
                fputcsv($sortie, [
                    $ligne['agent'] ?? '',
                    $ligne['type'] ?? '',
                    $ligne['objet'] ?? '',
                    $ligne['session'] ?? '',
                    $ligne['centre'] ?? '',
                    $ligne['role'] ?? '',
                    $ligne['date_debut'] ? Carbon::parse($ligne['date_debut'])->format('d/m/Y') : '',
                    $ligne['date_fin'] ? Carbon::parse($ligne['date_fin'])->format('d/m/Y') : '',
                    $ligne['lieu_service'] ?? '',
                    $ligne['lieu_examen'] ?? '',
                    $ligne['statut'] ?? '',
                ], ';');
            }

            fclose($sortie);
        }, $nomFichier, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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

            // Centres d'examen (etape 2 du wizard) : centre, jury, metier,
            // chef de centre. Un membre du jury (ci-dessous) reference son
            // centre par sa position dans ce tableau ("centre_index"),
            // puisqu'a ce stade les centres n'ont pas encore d'id reel.
            'centres' => ['nullable', 'array'],
            'centres.*.centre' => ['required_with:centres', 'string', 'max:255'],
            'centres.*.jury' => ['nullable', 'string', 'max:100'],
            'centres.*.metier' => ['nullable', 'string', 'max:255'],
            'centres.*.chef_centre_id' => ['nullable', 'integer'],
            'centres.*.chef_centre_telephone' => ['nullable', 'string', 'max:30'],

            // Membres du jury (etape 3 du wizard) : un enseignant, sa
            // fonction propre a cette convocation, et le centre auquel il
            // est affecte (index dans "centres" ci-dessus, ou vide).
            'beneficiaires' => ['nullable', 'array'],
            'beneficiaires.*.enseignant_id' => ['required_with:beneficiaires', 'integer'],
            'beneficiaires.*.fonction' => ['nullable', 'string', 'max:100'],
            'beneficiaires.*.provenance' => ['nullable', 'string', 'max:255'],
            'beneficiaires.*.categorie_personnel' => ['nullable', 'in:fonctionnaire,contractuel,vacataire'],
            'beneficiaires.*.centre_index' => ['nullable', 'integer'],
        ]);

        $data['utilisateur_id'] = session('sicore_user.id');
        $data['ordre_de_mission'] = $request->boolean('ordre_de_mission');

        $centres = $data['centres'] ?? [];
        $beneficiaires = $data['beneficiaires'] ?? [];
        unset($data['centres'], $data['beneficiaires']);

        $resultat = $this->convocations->creer($data);

        if (! $resultat['success']) {
            return back()->withInput()->withErrors(
                $resultat['errors'] ?? ['objet' => $resultat['message'] ?? 'Erreur lors de la creation.']
            );
        }

        $convocationId = $resultat['data']['id'];

        // Index (position dans "centres") -> id reel renvoye par l'API,
        // pour rattacher chaque beneficiaire a son centre.
        $centreIdParIndex = [];

        if (! empty($centres)) {
            $centresResultat = $this->convocations->creerCentres($convocationId, $centres);

            if ($centresResultat['success']) {
                foreach (($centresResultat['data'] ?? []) as $index => $centreCree) {
                    $centreIdParIndex[$index] = $centreCree['id'] ?? null;
                }
            }
        }

        if (! empty($beneficiaires)) {
            $beneficiaires = array_map(function (array $beneficiaire) use ($centreIdParIndex) {
                $centreIndex = $beneficiaire['centre_index'] ?? null;

                return [
                    'enseignant_id' => $beneficiaire['enseignant_id'],
                    'fonction' => $beneficiaire['fonction'] ?? null,
                    'provenance' => $beneficiaire['provenance'] ?? null,
                    'categorie_personnel' => $beneficiaire['categorie_personnel'] ?? null,
                    'centre_id' => $centreIndex !== null ? ($centreIdParIndex[$centreIndex] ?? null) : null,
                ];
            }, $beneficiaires);

            $this->convocations->ajouterBeneficiairesAvecFonction($convocationId, $beneficiaires);
        }

        return redirect()
            ->route('indemnites.convocations.show', $convocationId)
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
        $resultat = $this->convocations->trouver($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.convocations')
                ->with('error', $resultat['message'] ?? 'Convocation introuvable.');
        }

        $beneficiairesResultat = $this->convocations->beneficiaires($id);

        return view('pages.indemnites.convocations.show', [
            'convocation' => $this->formatConvocationForView($resultat['data']),
            'beneficiaires' => $beneficiairesResultat['success'] ? ($beneficiairesResultat['data']['data'] ?? []) : [],
            'id' => $id,
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
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

        return view('pages.indemnites.convocations.edit', [
            'convocation' => $this->formatConvocationForView($resultat['data']),
            'typesConvocation' => $typesResultat['success'] ? ($typesResultat['data'] ?? []) : [],
            'centres' => $centresResultat['success'] ? ($centresResultat['data'] ?? []) : [],
            'beneficiaires' => $beneficiairesResultat['success'] ? ($beneficiairesResultat['data']['data'] ?? []) : [],
            'id' => $id,
        ]);
    }

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
        ]);
        $data['ordre_de_mission'] = $request->boolean('ordre_de_mission');

        $resultat = $this->convocations->mettreAJour($id, $data);

        if (! $resultat['success']) {
            return back()->withInput()->withErrors(
                $resultat['errors'] ?? ['objet' => $resultat['message'] ?? 'Erreur lors de la mise a jour.']
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
        ]);

        $resultat = $this->convocations->ajouterBeneficiairesAvecFonction($id, [
            [
                'enseignant_id' => $data['enseignant_id'],
                'fonction' => $data['fonction'] ?? null,
                'provenance' => $data['provenance'] ?? null,
                'categorie_personnel' => $data['categorie_personnel'] ?? null,
                'centre_id' => $data['centre_id'] ?? null,
            ],
        ]);

        return redirect()
            ->route('indemnites.convocations.edit', $id)
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
        ]);

        $resultat = $this->convocations->mettreAJourBeneficiaire($id, $enseignantId, $data);

        return redirect()
            ->route('indemnites.convocations.edit', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Beneficiaire mis a jour.');
    }

    // Retire un beneficiaire de la convocation (l'enseignant lui-meme
    // n'est pas supprime, seul son rattachement a cette convocation l'est).
    public function destroyBeneficiaire(int|string $id, int|string $enseignantId): RedirectResponse
    {
        $resultat = $this->convocations->supprimerBeneficiaire($id, $enseignantId);

        return redirect()
            ->route('indemnites.convocations.edit', $id)
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
        ]);

        $resultat = $this->convocations->mettreAJourCentre($id, $centreId, $data);

        return redirect()
            ->route('indemnites.convocations.edit', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Centre mis a jour.');
    }

    // Supprime un centre d'examen (les membres qui y etaient rattaches ne
    // sont pas supprimes, seul leur rattachement au centre est retire).
    public function destroyCentre(int|string $id, int|string $centreId): RedirectResponse
    {
        $resultat = $this->convocations->supprimerCentre($id, $centreId);

        return redirect()
            ->route('indemnites.convocations.edit', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Centre supprime.');
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