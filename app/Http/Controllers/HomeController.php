<?php

namespace App\Http\Controllers;

use App\Services\News\HomeFeedBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected HomeFeedBuilder $feedBuilder,
    ) {}

    public function show(): View
    {
        $payload = Cache::remember('home:payload', now()->addSeconds(60), fn () => $this->feedBuilder->build());

        return view('home', $payload);
    }
}
