<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\Response;

class NotificationService
{
   protected string $endpoint = 'indemnites/notifications';

    public function __construct(
        protected ApiClient $api
    ) {}

    public function list(): Response
    {
        return $this->api->get($this->endpoint);
    }

    public function unreadCount(): Response
    {
        return $this->api->get("{$this->endpoint}/unread-count");
    }

    public function markAsRead(int $id): Response
    {
        return $this->api->patch("{$this->endpoint}/{$id}/read");
    }

    public function markAllAsRead(): Response
    {
        return $this->api->patch("{$this->endpoint}/read-all");
    }
}
