<?php

namespace App\Http\Controllers;

use App\Services\Api\ApiClient;
use App\Services\Organisation\OrganisationContext;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected OrganisationContext $organisation,
    ) {}

    public function index(): View
    {
        $metrics = [];
        try {
            $response = $this->api->get('dashboard');
            if ($response->successful()) $metrics = $response->json('data', []);
        } catch (ConnectionException) {
            // Le tableau reste disponible avec des valeurs neutres.
        }

        return view('pages.dashboard.index', [
            'metrics' => is_array($metrics) ? $metrics : [],
            'scopeLabel' => $this->organisation->label(),
            'isScoped' => $this->organisation->isScoped(),
        ]);
    }
}