<?php

namespace App\Http\Controllers\Admin;

use App\Actions\News\CreateNewsSource;
use App\Actions\News\DeleteNewsSource;
use App\Actions\News\ListNewsSources;
use App\Actions\News\UpdateNewsSource as UpdateNewsSourceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsSourceRequest;
use App\Http\Requests\UpdateNewsSourceRequest;
use App\Http\Resources\NewsSourceResource;
use App\Models\NewsSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NewsSourceController extends Controller
{
    public function __construct(
        protected ListNewsSources $listNewsSources,
        protected CreateNewsSource $createNewsSource,
        protected UpdateNewsSourceAction $updateNewsSource,
        protected DeleteNewsSource $deleteNewsSource
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['is_active', 'source_type', 'per_page', 'page']);
        $paginator = $this->listNewsSources->execute($filters);
        $paginator->appends($request->query());

        return NewsSourceResource::collection($paginator);
    }

    public function store(StoreNewsSourceRequest $request): JsonResponse
    {
        $source = $this->createNewsSource->execute($request->validated());

        return NewsSourceResource::make($source->loadCount(['articles', 'logs']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NewsSource $newsSource): NewsSourceResource
    {
        return NewsSourceResource::make($newsSource->loadCount(['articles', 'logs']));
    }

    public function update(UpdateNewsSourceRequest $request, NewsSource $newsSource): NewsSourceResource
    {
        $updated = $this->updateNewsSource->execute($newsSource, $request->validated());

        return NewsSourceResource::make($updated->loadCount(['articles', 'logs']));
    }

    public function destroy(NewsSource $newsSource): Response
    {
        $this->deleteNewsSource->execute($newsSource);

        return response()->noContent();
    }
}
