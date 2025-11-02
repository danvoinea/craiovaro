<?php

use App\Actions\GetSystemStatus;
use Illuminate\Support\Facades\Route;

Route::get('/status', function (GetSystemStatus $action) {
    return response()->json([
        'ok' => true,
        'data' => $action->execute(),
    ]);
});
