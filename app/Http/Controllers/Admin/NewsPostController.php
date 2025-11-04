<?php

namespace App\Http\Controllers\Admin;

use App\Actions\News\CreateNewsPost;
use App\Actions\News\DeleteNewsPost;
use App\Actions\News\ListNewsPosts;
use App\Actions\News\UpdateNewsPost as UpdateNewsPostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsPostRequest;
use App\Http\Requests\UpdateNewsPostRequest;
use App\Http\Resources\NewsPostResource;
use App\Models\NewsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NewsPostController extends Controller
{
    public function __construct(
        protected ListNewsPosts $listNewsPosts,
        protected CreateNewsPost $createNewsPost,
        protected UpdateNewsPostAction $updateNewsPost,
        protected DeleteNewsPost $deleteNewsPost
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['category', 'is_published', 'is_highlighted', 'per_page', 'page']);
        $paginator = $this->listNewsPosts->execute($filters);
        $paginator->appends($request->query());

        return NewsPostResource::collection($paginator);
    }

    public function store(StoreNewsPostRequest $request): JsonResponse
    {
        $post = $this->createNewsPost->execute($request->validated());

        return NewsPostResource::make($post)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NewsPost $newsPost): NewsPostResource
    {
        return NewsPostResource::make($newsPost);
    }

    public function update(UpdateNewsPostRequest $request, NewsPost $newsPost): NewsPostResource
    {
        $post = $this->updateNewsPost->execute($newsPost, $request->validated());

        return NewsPostResource::make($post);
    }

    public function destroy(NewsPost $newsPost): Response
    {
        $this->deleteNewsPost->execute($newsPost);

        return response()->noContent();
    }
}
