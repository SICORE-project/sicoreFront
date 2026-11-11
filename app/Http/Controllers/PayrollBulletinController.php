<?php

namespace App\Http\Controllers;

use App\Contracts\SicoreApiClientInterface;
use App\Exceptions\SicoreApiException;
use App\Services\Organisation\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Read-only bulletin consultation using the existing salary statement API. */
class PayrollBulletinController extends Controller
{
    public function __construct(private readonly SicoreApiClientInterface $api, private readonly OrganisationContext $organisation) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'period_id' => ['nullable', 'integer', 'min:1'],
            'academic_year_id' => ['nullable', 'integer', 'min:1'],
            'statut_enseignant_id' => ['nullable', 'integer', 'min:1'],
            'ia_id' => ['nullable', 'integer', 'min:1'],
            'ief_id' => ['nullable', 'integer', 'min:1'],
            'payment_place_id' => ['nullable', 'integer', 'min:1'],
            'matricule' => ['nullable', 'string', 'max:50'],
            'group' => ['nullable', 'boolean'],
            'consult' => ['nullable', 'boolean'],
            'suggest' => ['nullable', 'boolean'],
        ]);
        $filters = array_replace($filters, $this->organisation->query());
        $query = array_diff_key($filters, array_flip(['group', 'consult', 'suggest']));
        $token = (string) $request->session()->get('access_token');
        try {
            $moduleData = $this->api->payrollPage($token, 'paie-etat-salaires', array_filter($query));
            $records = collect(data_get($moduleData, 'salary_statement.rows', []));
            if ($request->boolean('suggest')) {
                return response()->json(['teachers' => $records->take(15)->map(fn ($row) => [
                    'matricule' => $row['matricule'],
                    'name' => trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')),
                ])->values()])->header('Cache-Control', 'no-store, private');
            }
            if ($request->boolean('consult')) {
                if (empty($filters['period_id']) || empty($filters['academic_year_id']) || empty($filters['statut_enseignant_id'])) {
                    throw ValidationException::withMessages(['period_id' => 'Choisissez le corps, l’année académique et la période de paie.']);
                }
                $matricule = trim((string) ($filters['matricule'] ?? ''));
                if ($matricule === '' && ! $request->boolean('group')) {
                    throw ValidationException::withMessages(['matricule' => 'Sélectionnez un matricule ou cochez « Édition bulletin avec le groupe ».']);
                }
                if ($matricule !== '' && $request->boolean('group')) {
                    throw ValidationException::withMessages(['matricule' => 'Effacez le matricule pour consulter tout le groupe.']);
                }
                if ($matricule !== '') {
                    $records = $records->filter(fn ($row) => mb_strtoupper(trim($row['matricule'])) === mb_strtoupper($matricule));
                }
                if ($records->isEmpty()) {
                    throw ValidationException::withMessages(['period_id' => 'Aucun bulletin disponible dans l’état des salaires pour ces critères.']);
                }
                // Reuse existing authorized IDs; never generate or alter a payslip.
                $catalogue = $this->api->payrollPage($token, 'paie-bulletins', ['period_id' => $moduleData['period']['id']]);
                $ids = collect($catalogue['rows'] ?? [])->mapWithKeys(function ($row) {
                    $action = collect(data_get($row, '8.actions', []))->firstWhere('code', 'view-payslip');
                    return $action ? [$row[0] => $action['payload']['payroll_payslip_id']] : [];
                });
                $bulletins = $records->map(function ($row) use ($ids, $token) {
                    abort_unless($ids->has($row['reference']), 409, 'Le bulletin a changé. Relancez la consultation.');
                    return $this->api->payslip($token, (int) $ids[$row['reference']]);
                })->values()->all();
                return response()->view('pages.paie.bulletins-document', compact('bulletins', 'filters'))
                    ->header('Cache-Control', 'no-store, private');
            }
            return view('pages.paie.bulletins', compact('moduleData', 'filters'));
        } catch (SicoreApiException $exception) {
            if ($exception->status === 401) {
                $request->session()->forget('access_token');
                return redirect('/login');
            }
            if ($request->boolean('suggest')) {
                return response()->json(['message' => 'Suggestions indisponibles.'], $exception->status);
            }
            return response()->view('pages.paie.bulletins', [
                'moduleData' => [], 'filters' => $filters,
                'apiError' => 'Consultation indisponible. Vérifiez la connexion et vos droits de lecture de l’état des salaires.',
            ], $exception->status);
        }
    }
}
