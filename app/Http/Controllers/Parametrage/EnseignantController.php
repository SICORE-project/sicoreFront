<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\EnseignantService;
use App\Services\Parametrage\InspectionAcademieService;
use App\Services\Parametrage\IefService;
use App\Services\Parametrage\CorpsService;
use App\Services\Parametrage\CategorieService;
use App\Services\Parametrage\DiplomeService;
use App\Services\Parametrage\SpecialiteService;
use App\Services\Parametrage\LieuServiceService;
use App\Services\Parametrage\InstitutionFinanciereService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class EnseignantController extends Controller
{
    public function index(
        Request $request,
        EnseignantService $service,
        InspectionAcademieService $academies,
        CorpsService $corps,
        IefService $iefs,
        CategorieService $categories,
        DiplomeService $diplomes,
        SpecialiteService $disciplines,
        InstitutionFinanciereService $institutions,
    ): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'prenom' => ['nullable', 'string', 'max:50'],
            'nom' => ['nullable', 'string', 'max:50'],
            'corps_id' => ['nullable', 'integer', 'min:1'],
            'diplome_id' => ['nullable', 'integer', 'min:1'],
            'ia_id' => ['nullable', 'integer', 'min:1'],
            'ief_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $result = $service->getAll(array_merge($filters, [
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => 20,
        ]));
        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            return redirect()->route('login')->with('warning', $result['error']);
        }
        $iaResult = $academies->getAll(1, 100);
        $result['academies'] = $iaResult['items'];
        $result['filterIefs'] = $request->filled('ia_id') ? $academies->getIefs($request->integer('ia_id'))['items'] : [];
        $result['regionOptions'] = $academies->regions();
        $result['iefOptions'] = $iefs->getAll(['per_page' => 100])['items'];
        $result['corpsOptions'] = $corps->getAll(['per_page' => 100])['items'];
        $result['categorieOptions'] = $categories->getAll(['per_page' => 100])['items'];
        $result['diplomeOptions'] = $diplomes->options();
        $result['disciplineOptions'] = $disciplines->getActiveForSelection();
        $result['institutionOptions'] = $institutions->getAll(1, 100, ['statut' => 'actif'])['items'];
        return view('pages.enseignants.index', $result);
    }

    public function ieFs(Request $request, InspectionAcademieService $academies): \Illuminate\Http\JsonResponse
    {
        $result = $academies->getIefs($request->integer('ia_id'));
        return response()->json(['items' => $result['items'], 'error' => $result['error']]);
    }

    public function etablissements(Request $request, LieuServiceService $lieux): JsonResponse
    {
        $filters = $request->validate(['ief_id' => ['required', 'integer', 'min:1']]);
        $items = [];
        $page = 1;
        do {
            $result = $lieux->getAll($page, 100, $filters);
            if ($result['error']) {
                return response()->json(['items' => [], 'error' => $result['error']], $result['unauthorized'] ? 401 : 502);
            }
            $items = array_merge($items, $result['items']);
            $page++;
        } while ($page <= $result['pagination']['last_page']);

        return response()->json(['items' => $items, 'error' => null]);
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

    public function storeReferentiel(
        Request $request,
        CorpsService $corps,
        InspectionAcademieService $academies,
        IefService $iefs,
        CategorieService $categories,
        SpecialiteService $disciplines,
        DiplomeService $diplomes,
        LieuServiceService $lieux,
        InstitutionFinanciereService $institutions,
    ): JsonResponse {
        $type = $request->string('type')->toString();
        $rules = config('teacher_referentiels')[$type] ?? null;
        abort_if($rules === null, 422, 'Type de référentiel inconnu.');
        $data = $request->validate($rules);
        $result = match ($type) {
            'corps' => $corps->create($data),
            'categorie' => $categories->create($data),
            'discipline' => $disciplines->create($data),
            'lieu_service' => $lieux->create($data),
            'banque' => $institutions->create($data),
            'diplome' => $diplomes->create($data),
            'ia' => $academies->create($data),
            'ief' => $iefs->create($data),
        };

        if (! ($result['success'] ?? false)) {
            return response()->json(['message' => $result['message'] ?? 'Création impossible.', 'errors' => $result['errors'] ?? []], 422);
        }

        return response()->json(['message' => $result['message'] ?? 'Référentiel créé.', 'data' => $result['data'] ?? null]);
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
        $married = $request->boolean('est_en_couple');
        $request->merge([
            'nombre_enfants' => $married ? ($request->input('nombre_enfants') ?? 0) : 0,
            'nombre_femmes' => $married ? ($request->input('nombre_femmes') ?? 0) : 0,
            'conjoint_travaille' => $married ? ($request->input('conjoint_travaille') ?? false) : false,
        ]);
        return $request->validate([
            'matricule' => ['required', 'string', 'max:9', 'regex:/\A[A-Za-z0-9]+\z/'],
            'nom' => ['required', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'date_naissance' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->format('Y-m-d')],
            'telephone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'lieu_naissance' => ['nullable', 'string', 'max:100'],
            'cni' => ['nullable', 'string', 'regex:/\A[0-9]{13,15}\z/'],
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
            'conjoint_travaille' => ['nullable', 'boolean'],
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
            'date_naissance.before_or_equal' => 'L’enseignant doit avoir au moins 18 ans.',
            'date_naissance.date' => 'Veuillez saisir une date de naissance valide.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'matricule.max' => 'Le matricule ne doit pas dépasser 9 caractères.',
            'matricule.regex' => 'Le matricule doit contenir uniquement des lettres et des chiffres.',
            'cni.regex' => 'Le numéro de carte d’identité doit contenir entre 13 et 15 chiffres.',
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
