<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $responseRoles = Http::withToken(session('access_token'))  // <-- Changé
            ->get(config('services.backend.url') . '/admin/roles/all');

        $responsePermissions = Http::withToken(session('access_token'))  // <-- Changé
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $totalRoles = $responseRoles->successful() ? count($responseRoles->json()['data'] ?? []) : 0;
        $totalPermissions = $responsePermissions->successful() ? count($responsePermissions->json()['data'] ?? []) : 0;

        return view('pages.dashboard.index', compact('totalRoles', 'totalPermissions'));
    }
}
