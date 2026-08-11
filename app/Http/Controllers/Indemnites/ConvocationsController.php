<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConvocationsController extends Controller
{
    public function __construct(
        protected ConvocationService $convocations
    ) {}

    // Liste des convocations, avec filtre optionnel par statut
    public function index(Request $request): View
    {
        $filtres = array_filter([
            'statut' => $request->query('statut'),
            'per_page' => 15,
            'page' => $request->query('page', 1),
        ]);

        $resultat = $this->convocations->liste($filtres);

        $convocations = $resultat['success'] ? ($resultat['data']['data'] ?? []) : [];

        if (! $resultat['success']) {
            session()->flash('error', $resultat['message'] ?? "Impossible de charger les convocations.");
        }

        // Statistiques rapides pour les cartes en haut de la page,
        // calculees sur la page courante (l'API ne fournit pas d'agregat dedie).
        $stats = ['total' => count($convocations), 'brouillon' => 0, 'emise' => 0, 'envoyee' => 0, 'cloturee' => 0];
        foreach ($convocations as $convocation) {
            $statut = $convocation['statut'] ?? null;
            if ($statut && array_key_exists($statut, $stats)) {
                $stats[$statut]++;
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

    // Cree la convocation, puis (si fournis) attache les beneficiaires.
    // Le depot du fichier scanne se fait separement, depuis la fiche de
    // la convocation (voir storeFichier plus bas) — retire du formulaire
    // de creation pour l'instant.
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date_emission' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
            'lieu_examen' => ['nullable', 'string', 'max:255'],
            'lieu_affectation' => ['nullable', 'string', 'max:255'],
            'ordre_de_mission' => ['nullable', 'boolean'],
            'statut' => ['nullable', 'in:brouillon,emise,envoyee,cloturee'],
            // Beneficiaires : un enseignant_id par ligne du tableau, avec
            // sa fonction propre a cette convocation (voir create.blade.php).
            'beneficiaires' => ['nullable', 'array'],
            'beneficiaires.*.enseignant_id' => ['required_with:beneficiaires', 'integer'],
            'beneficiaires.*.fonction' => ['nullable', 'string', 'max:100'],
        ]);

        $data['utilisateur_id'] = session('sicore_user.id');
        $data['ordre_de_mission'] = $request->boolean('ordre_de_mission');
        $beneficiaires = $data['beneficiaires'] ?? [];
        unset($data['beneficiaires']);

        $resultat = $this->convocations->creer($data);

        if (! $resultat['success']) {
            return back()->withInput()->withErrors(
                $resultat['errors'] ?? ['objet' => $resultat['message'] ?? 'Erreur lors de la creation.']
            );
        }

        $convocationId = $resultat['data']['id'];

        if (! empty($beneficiaires)) {
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
            'convocation' => $resultat['data'],
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
            'convocation' => $resultat['data'],
            'id' => $id,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate([
            'date_emission' => ['sometimes', 'date'],
            'objet' => ['sometimes', 'string', 'max:255'],
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
}
