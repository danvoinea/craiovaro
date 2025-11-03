<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\Links\ShortLinkService;
use Illuminate\Http\RedirectResponse;

class ShortLinkController extends Controller
{
    public function __construct(
        protected ShortLinkService $shortLinkService,
    ) {}

    public function redirect(string $code): RedirectResponse
    {
        /** @var ShortLink $shortLink */
        $shortLink = ShortLink::query()->where('code', $code)->firstOrFail();

        $this->shortLinkService->recordClick($shortLink);

        return redirect()->away($shortLink->target_url);
    }
}
