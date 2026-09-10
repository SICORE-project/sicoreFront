<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\EnseignantService;
use App\Services\Parametrage\InspectionAcademieService;
use App\Services\Parametrage\CorpsService;
use App\Services\Parametrage\CategorieService;
use App\Services\Parametrage\DiplomeService;
use App\Services\Parametrage\DisciplineService;
use App\Services\Parametrage\LieuServiceService;
use App\Services\Parametrage\InstitutionFinanciereService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnseignantController extends Controller
{
    public function index(
        Request $request,
        EnseignantService $service,
        InspectionAcademieService $academies,
        CorpsService $corps,
        CategorieService $categories,
        DiplomeService $diplomes,
        DisciplineService $disciplines,
        LieuServiceService $lieuxService,
        InstitutionFinanciereService $institutions,
    ): View|RedirectResponse
    {
        $result = $service->getAll([
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => 20,
        ]);
        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            return redirect()->route('login')->with('warning', $result['error']);
        }
        $iaResult = $academies->getAll(1, 100);
        $result['academies'] = $iaResult['items'];
        $result['corpsOptions'] = $corps->getAll(['per_page' => 100])['items'];
        $result['categorieOptions'] = $categories->getAll(['per_page' => 100])['items'];
        $result['diplomeOptions'] = $diplomes->options();
        $result['disciplineOptions'] = $disciplines->getActiveForSelection();
        $result['lieuServiceOptions'] = $lieuxService->getAll(1, 100, ['statut' => 'actif'])['items'];
        $result['institutionOptions'] = $institutions->getAll(1, 100, ['statut' => 'actif'])['items'];
        return view('pages.enseignants.index', $result);
    }

    public function ieFs(Request $request, InspectionAcademieService $academies): \Illuminate\Http\JsonResponse
    {
        $result = $academies->getIefs($request->integer('ia_id'));
        return response()->json(['items' => $result['items'], 'error' => $result['error']]);
    }

    public function create(): View
    {
        return view('pages.enseignants.create', ['teacher' => null, 'editing' => false]);
    }

    public function store(Request $request, EnseignantService $service): RedirectResponse
    {
        $result = $service->create($this->validated($request));
        return $this->redirect($result);
    }

    public function edit(int $enseignant, EnseignantService $service): View|RedirectResponse
    {
        $result = $service->find($enseignant);
        if (! $result['success']) {
            return redirect()->route('enseignants.index')->with('error', $result['message']);
        }
        return view('pages.enseignants.create', ['teacher' => $result['data'], 'editing' => true]);
    }

    public function update(Request $request, int $enseignant, EnseignantService $service): RedirectResponse
    {
        return $this->redirect($service->update($enseignant, $this->validated($request)));
    }

    public function destroy(int $enseignant, EnseignantService $service): RedirectResponse
    {
        return $this->redirect($service->delete($enseignant));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'matricule' => ['required', 'string', 'max:30'],
            'nom' => ['required', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'lieu_naissance' => ['nullable', 'string', 'max:100'],
            'cni' => ['nullable', 'string', 'max:50'],
            'genre' => ['nullable', 'in:M,F'],
            'diplome_id' => ['nullable', 'integer', 'min:1'],
            'discipline_id' => ['nullable', 'integer', 'min:1'],
            'lieu_service_id' => ['nullable', 'integer', 'min:1'],
            'salaire_brut' => ['nullable', 'numeric', 'min:0'],
            'generation' => ['nullable', 'string', 'max:20'],
            'date_fin_contrat' => ['nullable', 'date', 'after_or_equal:date_recrutement'],
            'est_en_couple' => ['required', 'boolean'],
            'nombre_enfants' => ['nullable', 'integer', 'min:0'],
            'nombre_femmes' => ['nullable', 'integer', 'min:0'],
            'nombre_parts_fiscales' => ['required', 'numeric', 'min:1', 'max:5'],
            'conjoint_travaille' => ['required', 'boolean'],
            'observations' => ['nullable', 'string'],
            'compte_bancaire' => ['nullable', 'array'],
            'compte_bancaire.institut_financier_id' => ['nullable', 'integer', 'min:1'],
            'compte_bancaire.code_banque' => ['nullable', 'string', 'max:5'],
            'compte_bancaire.code_guichet' => ['nullable', 'string', 'max:5'],
            'compte_bancaire.numero_compte' => ['nullable', 'string', 'max:11'],
            'compte_bancaire.cle_rib' => ['nullable', 'string', 'max:2'],
            'compte_bancaire.iban' => ['nullable', 'string', 'max:34'],
            'compte_bancaire.bic' => ['nullable', 'string', 'max:11'],
            'compte_bancaire.titulaire_compte' => ['nullable', 'string', 'max:100'],
            'compte_bancaire.type_virement' => ['nullable', 'in:unitaire,masse'],
            'date_recrutement' => ['nullable', 'date'],
            'date_prise_service' => ['nullable', 'date'],
            'ia_id' => ['required', 'integer'],
            'ief_id' => ['required', 'integer'],
            'corps_id' => ['required', 'integer', 'min:1'],
            'categorie_id' => ['nullable', 'integer', 'min:1'],
            'statut' => ['required', 'in:en_activite,retraite,suspension_provisoire,abandon,decede,integre,radie,cessation_paiement'],
            'est_actif' => ['required', 'boolean'],
        ], [
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'date_naissance.date' => 'Veuillez saisir une date de naissance valide.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.unique' => 'Ce matricule existe déjà.',
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'corps_id.required' => 'Le corps est obligatoire.',
        ]);
    }

    private function redirect(array $result): RedirectResponse
    {
        $redirect = redirect()->route('enseignants.index');
        return $result['success']
            ? $redirect->with('success', $result['message'])
            : back()->withInput()->withErrors($result['errors'] ?: ['api' => $result['message']]);
    }
}
