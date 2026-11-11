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
        $headers = ['matricule', 'nom', 'prenom', 'date_naissance', 'cni', 'telephone',
            'statut_enseignant', 'corps', 'categorie', 'indice', 'ief',
            'situation_matrimoniale', 'nombre_enfants', 'code_banque', 'code_guichet', 'numero_compte'];
        $path = app(\App\Services\Exports\TeacherExcelExport::class)->create(
            array_fill(0, 500, array_fill(0, count($headers), '')), $headers, true
        );
        return response()->download($path, 'modele-enseignants.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function store(Request $request, ApiClient $api)
    {
        $this->authorizeImport($request);
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048']]);
        $file = $request->file('csv');
        $isExcel = strtolower($file->getClientOriginalExtension()) === 'xlsx';
        $handle = $isExcel
            ? app(\App\Services\Imports\TeacherExcelImport::class)->csv($file->getRealPath())
            : fopen($file->getRealPath(), 'r');
        try {
            $response = $api->postMultipart($request->session()->get('sicore_user.role_slug') === 'gestionnaire_ia'
                ? 'ia/teachers/import' : 'admin/personnel/enseignants/import', [], [
                ['name' => 'csv', 'contents' => $handle, 'filename' => $isExcel ? 'enseignants.csv' : $file->getClientOriginalName()],
            ], 120);
        } catch (ConnectionException) {
            return back()->withErrors(['csv' => 'Le service backend est inaccessible.']);
        } finally {
            fclose($handle);
        }
        if ($response->serverError()) {
            return back()->withErrors(['csv' => 'Le serveur a interrompu le traitement du fichier et aucun bilan complet n’a pu être reçu. Vérifiez la liste des enseignants ou les soumissions avant de relancer l’import. Si le problème persiste, contactez l’administrateur.']);
        }
        return $response->successful()
            ? back()->with('success', $response->json('message'))->with('teacher_import_report', $response->json())
            : back()->withErrors($response->json('errors', ['csv' => $response->json('message', 'Import impossible.')]));
    }
}
