<?php
namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;

class TeacherPersonalController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function page(Request $request)
    {
        abort_unless(app(\App\Services\Organisation\InterfaceAccess::class)->isTeacher(), 403);
        $mode = $request->routeIs('teacher.payslips') ? 'bulletins' : ($request->routeIs('teacher.profile') ? 'informations' : 'dashboard');
        $data = []; $error = null;
        try {
            $response = $this->api->get($mode === 'bulletins' ? 'enseignant/bulletins' : 'enseignant/dossier', $request->only('annee', 'periode_id', 'page'));
            if ($response->successful()) $data = $response->json();
            elseif ($response->status() === 403) $error = 'Votre dossier enseignant doit être associé à votre compte. Contactez un administrateur.';
            else $error = 'Impossible de charger vos informations. Réessayez plus tard.';
        } catch (ConnectionException) {
            $error = 'Le service est momentanément indisponible.';
        }
        return response()->view('pages.teacher.workspace', compact('mode', 'data', 'error'))->header('Cache-Control', 'no-store, private');
    }

    public function pdf(Request $request, int $id)
    {
        abort_unless(app(\App\Services\Organisation\InterfaceAccess::class)->isTeacher(), 403);
        try {
            $result = $this->api->get("enseignant/bulletins/{$id}/pdf", ['download' => $request->boolean('download') ? 1 : 0]);
        } catch (ConnectionException) {
            abort(503, 'Le service est momentanément indisponible.');
        }
        abort_unless($result->successful(), in_array($result->status(), [401, 403, 404], true) ? $result->status() : 502);
        abort_unless(str_starts_with($result->body(), '%PDF-'), 502);
        return response($result->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="bulletin-'.$id.'.pdf"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}