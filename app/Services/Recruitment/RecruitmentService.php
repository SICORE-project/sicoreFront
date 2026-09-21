<?php

namespace App\Services\Recruitment;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;

class RecruitmentService
{
    public function __construct(private ApiClient $api) {}

    public function get(string $path): Response
    {
        return $this->api->get('recruitment/'.$path);
    }

    public function post(string $path, array $data = [], ?UploadedFile $file = null, string $field = 'document'): Response
    {
        if (! $file) return $this->api->post('recruitment/'.$path, $data);
        $stream = fopen($file->getRealPath(), 'rb');
        try {
            return $this->api->postMultipart('recruitment/'.$path, $data, [[
                'name' => $field, 'contents' => $stream, 'filename' => $file->getClientOriginalName(),
            ]]);
        } finally {
            if (is_resource($stream)) fclose($stream);
        }
    }
}
