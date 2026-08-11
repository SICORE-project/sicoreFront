<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

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
            'per_page' => 15,
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
            $meta['per_page'] ?? 15,
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
            'stats' => $stats,
            'statutFiltre' => $request->query('statut', ''),
        ]);
    }

    public function create(): View
    {
        return view('pages.indemnites.convocations.create', [
            'utilisateur' => session('sicore_user'),
        ]);
    }

    // Cree la convocation, puis (si fournis) ses centres d'examen et ses
    // beneficiaires. Le depot du fichier scanne se fait separement, depuis
    // la fiche de la convocation (voir storeFichier plus bas).
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date_emission' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
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

        return view('pages.indemnites.convocations.edit', [
            'convocation' => $this->formatConvocationForView($resultat['data']),
            'id' => $id,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'date_emission' => ['sometimes', 'date'],
            'objet' => ['sometimes', 'string', 'max:255'],
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

    // Ajoute un ou plusieurs beneficiaires a une convocation existante
    public function storeBeneficiaires(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'enseignant_id' => ['required', 'integer'],
            'fonction' => ['nullable', 'string', 'max:100'],
        ]);

        $resultat = $this->convocations->ajouterBeneficiairesAvecFonction($id, [
            ['enseignant_id' => $data['enseignant_id'], 'fonction' => $data['fonction'] ?? null],
        ]);

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Beneficiaire ajoute.');
    }

    public function storeFichier(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'fichier' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $resultat = $this->convocations->deposerFichier($id, $request->file('fichier'));

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Fichier depose.');
    }

    public function genererPdf(int|string $id): RedirectResponse
    {
        $resultat = $this->convocations->genererPdf($id);

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'PDF genere.');
    }

    // Recupere le PDF depuis le backend (avec le jeton) et le retransmet au navigateur
    public function telechargerPdf(int|string $id)
    {
        $response = $this->convocations->telechargerPdf($id);

        if (! $response->successful()) {
            return redirect()
                ->route('indemnites.convocations.show', $id)
                ->with('error', 'Impossible de telecharger le PDF de cette convocation.');
        }

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type') ?? 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"convocation-{$id}.pdf\"",
        ]);
    }

    public function envoyer(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'canal' => ['nullable', 'in:email,sms,courrier'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $resultat = $this->convocations->envoyer($id, $data);

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Envoi effectue.');
    }

    public function relancer(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $resultat = $this->convocations->relancer($id, $data);

        return redirect()
            ->route('indemnites.convocations.show', $id)
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Relance effectuee.');
    }

    public function suivi(int|string $id): View
    {
        $resultat = $this->convocations->suivi($id);
        $envois = $resultat['success'] ? ($resultat['data']['data'] ?? []) : [];

        $stats = ['total' => count($envois), 'envoye' => 0, 'echec' => 0];
        foreach ($envois as $envoi) {
            $statut = $envoi['statut'] ?? null;
            if ($statut && array_key_exists($statut, $stats)) {
                $stats[$statut]++;
            }
        }

        return view('pages.indemnites.convocations.suivi', [
            'envois' => $envois,
            'stats' => $stats,
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