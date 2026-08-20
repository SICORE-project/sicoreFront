<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\InspectionAcademieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionAcademieController extends Controller
{
    public function index(Request $request, InspectionAcademieService $service): View|RedirectResponse
    {
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        return view('pages.parametres.ia-index', $result);
    }
}
