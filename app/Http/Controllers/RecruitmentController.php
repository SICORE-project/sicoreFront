<?php

namespace App\Http\Controllers;

use App\Services\Recruitment\RecruitmentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecruitmentController extends Controller
{
    public function __construct(private RecruitmentService $api) {}

    public function index(Request $request)
    {
        $batchFilters = $request->validate(['year'=>['nullable','integer','between:1900,2100'], 'reference'=>['nullable','string','max:100']]);
        $batches = $notices = [];
        $error = null;
        try {
            $response = $this->api->get('batches', $batchFilters);
            if ($response->successful()) $batches = $response->json('data', []);
            else $error = $this->message($response);
            if (! $error) {
                $response = $this->api->get('notices');
                if ($response->successful()) $notices = $response->json('data', []);
            }
        } catch (ConnectionException) {
            $error = 'Le service des recrutements est momentanément inaccessible.';
        }
        $searchResults = null;
        $searchError = null;
        $filters = $request->validate(['search'=>['nullable','string','max:100'], 'situation'=>['nullable','in:total_agents,prise_service_enregistree,enseignants_abandon'], 'page'=>['nullable','integer','min:1']]);
        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        if (($request->filled('search') || $request->filled('situation')) && $access->isDrh() && $access->allows('enseignants.read')) {
            try {
                $response = $this->api->get('search',$filters);
                if ($response->successful()) $searchResults = $response->json();
                else $searchError = $this->message($response);
            } catch (ConnectionException) {
                $searchError = 'La recherche est momentanément indisponible.';
            }
        }
        return view('pages.recruitment.index', compact('batches', 'notices', 'error', 'searchResults', 'searchError'));
    }

    public function create()
    {
        return view('pages.recruitment.import');
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
            'recruited_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);
        unset($data['file']);
        try {
            $response = $this->api->post('batches', $data + ['preview' => '1'], $request->file('file'), 'file');
        } catch (ConnectionException) {
            return back()->withInput($data)->withErrors(['file' => 'La vérification est indisponible. Réessayez.']);
        }
        if (! $response->successful()) return $this->failure($response, $data);
        $path = $request->file('file')->store('recruitment-previews', 'local');
        if (! $path) return back()->withInput($data)->withErrors(['file' => 'Impossible de conserver le fichier pour sa confirmation. Réessayez.']);
        $this->clearPending($request);
        $pending = $data + [
            'path' => $path,
            'token' => (string) Str::uuid(), 'expires_at' => now()->addMinutes(30)->timestamp,
            'user_id' => session('sicore_user.id'),
        ];
        $request->session()->put('recruitment_import', $pending);
        return view('pages.recruitment.preview', [
            'pending' => $pending, 'rows' => array_slice($response->json('data.rows', []), 0, 100),
            'total' => $response->json('data.total', 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['token' => ['required', 'uuid']]);
        $pending = $request->session()->get('recruitment_import');
        if (! is_array($pending) || ! hash_equals($pending['token'], $request->string('token')->toString())
            || $pending['expires_at'] < now()->timestamp || $pending['user_id'] !== session('sicore_user.id')
            || ! Storage::disk('local')->exists($pending['path'])) {
            return redirect()->route('recruitment.create')->withErrors(['file' => 'Cet aperçu a expiré. Vérifiez à nouveau le fichier.']);
        }
        $file = new UploadedFile(Storage::disk('local')->path($pending['path']), 'recrutement.csv', 'text/csv', null, true);
        try {
            $response = $this->api->post('batches', [
                'reference' => $pending['reference'], 'recruited_at' => $pending['recruited_at'], 'preview' => '0',
            ], $file, 'file');
        } catch (ConnectionException) {
            return redirect()->route('recruitment.index')->with('warning', 'La confirmation du serveur n’a pas été reçue. Vérifiez la liste des lots avant de réessayer.');
        }
        if (! $response->successful()) {
            return redirect()->route('recruitment.create')->withErrors($response->json('errors') ?: ['file' => $this->message($response)]);
        }
        $this->clearPending($request);
        return redirect()->route('recruitment.show', $response->json('data.id'))
            ->with('success', 'Lot importé. Les recrutés sont inactifs. Joignez maintenant leur ordre de service.');
    }

    public function cancel(Request $request)
    {
        $this->clearPending($request);
        return redirect()->route('recruitment.create');
    }

    private function clearPending(Request $request): void
    {
        $pending = $request->session()->pull('recruitment_import');
        if (is_array($pending) && isset($pending['path'])) Storage::disk('local')->delete($pending['path']);
    }

    public function show(int $batch)
    {
        $data = $this->batch($batch);
        $data['members'] = array_map(function ($member) {
            $member['due_at'] = ! empty($member['service_date']) && $member['engagement'] === 'vacataire'
                ? CarbonImmutable::parse($member['service_date'])->addYearsNoOverflow(2)->toDateString() : null;
            $member['due'] = $member['due_at'] && $member['due_at'] <= now()->toDateString();
            return $member;
        }, $data['members']);
        return view('pages.recruitment.show', $data);
    }

    public function os(Request $request, int $batch)
    {
        $request->validate(['document' => ['required', 'file', 'mimes:pdf', 'max:10240']]);
        return $this->mutation($request, "batches/$batch/os", [], $batch);
    }

    public function transmit(Request $request, int $batch)
    {
        return $this->mutation($request, "batches/$batch/transmit", [], $batch);
    }

    public function serviceForm(int $batch, int $member)
    {
        $data = $this->batch($batch);
        $teacher = collect($data['members'])->firstWhere('id', $member);
        abort_unless($teacher, 404);
        $options = [];
        $error = null;
        try {
            $response = $this->api->get('establishments');
            if ($response->successful()) $options = $response->json('data', []);
            else $error = $this->message($response);
        } catch (ConnectionException) {
            $error = 'Les établissements sont momentanément indisponibles.';
        }
        return view('pages.recruitment.service', ['batch' => $data['batch'], 'teacher' => $teacher, 'options' => $options, 'error' => $error]);
    }

    public function service(Request $request, int $batch, int $member)
    {
        $data = $request->validate([
            'service_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'lieu_service_id' => ['required', 'integer', 'min:1'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);
        unset($data['document']);
        abort_unless(collect($this->batch($batch)['members'])->contains('id', $member), 404);
        return $this->mutation($request, "members/$member/service", $data, $batch);
    }

    public function transition(Request $request, int $batch, int $member)
    {
        $data = $request->validate([
            'effective_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);
        unset($data['document']);
        abort_unless(collect($this->batch($batch)['members'])->contains('id', $member), 404);
        return $this->mutation($request, "members/$member/transition", $data + ['engagement' => 'contractuel'], $batch);
    }

    public function document(int $event)
    {
        return $this->download("documents/$event", 'justificatif.pdf', 'application/pdf');
    }

    public function template()
    {
        return $this->download('template', 'modele-recrutement.csv', 'text/csv; charset=UTF-8');
    }

    private function download(string $path, string $name, string $type)
    {
        try { $response = $this->api->get($path); }
        catch (ConnectionException) { abort(503, 'Le document est momentanément indisponible.'); }
        abort_unless($response->successful(), $response->status(), 'Document indisponible.');
        return response($response->body(), 200, ['Content-Type' => $type,
            'Content-Disposition' => 'attachment; filename="'.$name.'"', 'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff']);
    }

    private function batch(int $id): array
    {
        try { $response = $this->api->get("batches/$id"); }
        catch (ConnectionException) { abort(503, 'Le lot est momentanément indisponible.'); }
        abort_unless($response->successful(), $response->status(), 'Ce lot est indisponible ou hors de votre périmètre.');
        return $response->json('data');
    }

    private function mutation(Request $request, string $path, array $data, int $batch)
    {
        try { $response = $this->api->post($path, $data, $request->file('document')); }
        catch (ConnectionException) {
            return back()->withInput($data)->withErrors(['document' => 'La confirmation du serveur n’a pas été reçue. Consultez l’historique avant de réessayer.']);
        }
        if (! $response->successful()) return $this->failure($response, $data);
        return redirect()->route('recruitment.show', $batch)->with('success', $response->json('message', 'Opération enregistrée.'));
    }

    private function failure(Response $response, array $data)
    {
        return back()->withInput($data)->withErrors($response->json('errors') ?: ['operation' => $this->message($response)]);
    }

    private function message(Response $response): string
    {
        return $response->json('message') ?: 'Impossible de charger les recrutements. Vérifiez vos droits ou réessayez plus tard.';
    }
}
