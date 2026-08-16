<?php


namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $response = $this->notificationService->list();

        if ($this->isUnauthorized($response, $request)) {
            return $this->sessionExpiredResponse($request);
        }

        return response()->json([
            'data' => $response->successful() ? $response->json('data') : [],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $response = $this->notificationService->unreadCount();

        if ($this->isUnauthorized($response, $request)) {
            return $this->sessionExpiredResponse($request);
        }

        return response()->json([
            'count' => $response->successful() ? $response->json('count') : 0,
        ]);
    }

    public function markAsRead(int $id, Request $request): JsonResponse
    {
        $response = $this->notificationService->markAsRead($id);

        if ($this->isUnauthorized($response, $request)) {
            return $this->sessionExpiredResponse($request);
        }

        return response()->json(['message' => 'ok'], $response->status());
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $response = $this->notificationService->markAllAsRead();

        if ($this->isUnauthorized($response, $request)) {
            return $this->sessionExpiredResponse($request);
        }

        return response()->json(['message' => 'ok'], $response->status());
    }

    protected function isUnauthorized(Response $response, Request $request): bool
    {
        return $response->status() === 401;
    }

    protected function sessionExpiredResponse(Request $request): JsonResponse
    {
        $request->session()->forget(['access_token', 'sicore_user']);

        return response()->json([
            'message' => 'Session expirée.',
            'redirect' => route('login'),
        ], 401);
    }
}