<?php

use App\Http\Controllers\Admin\NewsPostController as AdminNewsPostController;
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

    Route::apiResource('news-posts', AdminNewsPostController::class)
        ->parameters(['news-posts' => 'newsPost'])
        ->names([
            'index' => 'admin.newsPosts.index',
            'store' => 'admin.newsPosts.store',
            'show' => 'admin.newsPosts.show',
            'update' => 'admin.newsPosts.update',
            'destroy' => 'admin.newsPosts.destroy',
        ]);
});
