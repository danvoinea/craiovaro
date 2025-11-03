<?php

use App\Http\Controllers\Admin\NewsSourceController as AdminNewsSourceController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::get('/status', [StatusController::class, 'show'])
    ->name('status.show');

Route::prefix('admin')->group(function (): void {
    Route::apiResource('news-sources', AdminNewsSourceController::class)
        ->parameters(['news-sources' => 'newsSource'])
        ->names([
            'index' => 'admin.newsSources.index',
            'store' => 'admin.newsSources.store',
            'show' => 'admin.newsSources.show',
            'update' => 'admin.newsSources.update',
            'destroy' => 'admin.newsSources.destroy',
        ]);
});
