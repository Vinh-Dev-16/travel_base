<?php

namespace Vinhdev\Travel\Contracts\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController
{
    public function responseJson(int $status, string $message): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'data'   => $message,
        ], $status);
    }

    public function responseJsonData(int $status, array $data): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'data'   => $data,
        ], $status);
    }
}