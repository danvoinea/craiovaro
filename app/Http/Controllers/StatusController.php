<?php

namespace App\Http\Controllers;

use App\Actions\GetSystemStatus;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function __construct(public GetSystemStatus $getSystemStatus) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $this->getSystemStatus->execute(),
        ]);
    }
}
