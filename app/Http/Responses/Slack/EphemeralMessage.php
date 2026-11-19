<?php

namespace App\Http\Responses\Slack;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

readonly class EphemeralMessage implements Responsable
{
    public function __construct(public string $text) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'response_type' => 'ephemeral',
            'text' => $this->text,
        ]);
    }
}
