<?php
namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;

class TeacherImportController extends Controller
{
    private function authorizeImport(Request $request): void
    {
        abort_unless(in_array($request->session()->get('sicore_user.role_slug'), ['admin', 'super_admin', 'gestionnaire_ia'], true), 403);
    }

    public function template(Request $request)
    {
        $this->authorizeImport($request);
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['matricule', 'nom', 'prenom', 'date_naissance', 'cni', 'telephone',
                'statut_enseignant_id', 'corps_id', 'indice', 'ia_id', 'ief_id', 'lieu_service_id',
                'situation_matrimoniale', 'nombre_enfants', 'nombre_femmes', 'conjoint_travaille',
                'date_recrutement', 'annee_recrutement', 'diplome_id', 'statut', 'observations',
                'code_banque', 'code_guichet', 'numero_compte', 'cle_rib'], ';', '"', '');
            fclose($output);
        }, 'modele-enseignants.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(Request $request, ApiClient $api)
    {
        $this->authorizeImport($request);
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $file = $request->file('csv');
        $handle = fopen($file->getRealPath(), 'r');
        try {
            $response = $api->postMultipart('admin/personnel/enseignants/import', [], [
                ['name' => 'csv', 'contents' => $handle, 'filename' => $file->getClientOriginalName()],
            ], 120);
        } catch (ConnectionException) {
            return back()->withErrors(['csv' => 'Le service backend est inaccessible.']);
        } finally {
            fclose($handle);
        }
        return $response->successful()
            ? back()->with('success', $response->json('message'))
            : back()->withErrors($response->json('errors', ['csv' => $response->json('message', 'Import impossible.')]));
    }
}
