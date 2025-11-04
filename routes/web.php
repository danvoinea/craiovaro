<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsPostController;
use App\Http\Controllers\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])
    ->name('home.show');

Route::get('/s/{code}', [ShortLinkController::class, 'redirect'])
    ->where('code', '[A-Za-z0-9]+')
    ->name('short-links.redirect');

Route::get('/{category}/{slug}', [NewsPostController::class, 'show'])
    ->where([
        'category' => '(?!api$|s$)[a-z0-9-]+',
        'slug' => '[a-z0-9-]+',
    ])
    ->name('news-posts.show');
