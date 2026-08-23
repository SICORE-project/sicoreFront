<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Administration\UserService;
use Illuminate\Http\Request;

class StructureOrganisationnelleController extends Controller
{
    public function __construct(private UserService $users) {}

    public function index()
    {
        return view('pages.administration.structures.index', [
            'structures' => $this->users->structures()['data'],
            'ias' => $this->users->ias(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->save($request, $id);
    }

    public function destroy(int $id)
    {
        $response = $this->users->deleteStructure($id);
        return redirect()->route('utilisateurs.structures.index')->with($response['success'] ? 'success' : 'error', $response['message'] ?? 'Suppression impossible.');
    }

    private function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'libelle' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:DRH,DAGE,DECPC,IA,IEF'],
            'perimetre' => ['required', 'in:national,regional'],
            'ia_id' => ['nullable', 'integer'],
            'ief_id' => ['nullable', 'integer'],
            'est_actif' => ['required', 'boolean'],
        ]);
        $response = $this->users->saveStructure($data, $id);

        return redirect()->route('utilisateurs.structures.index')->with($response['success'] ? 'success' : 'error', $response['message'] ?? 'Enregistrement impossible.');
    }
}
