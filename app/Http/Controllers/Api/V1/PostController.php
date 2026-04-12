<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Posts\CreatePostAction;
use App\Actions\Posts\DeletePostAction;
use App\Actions\Posts\UpdatePostAction;
use App\Http\Requests\Posts\IndexPostRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostCollection;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class PostController
{
    public function index(IndexPostRequest $request): JsonResponse
    {
        $this->ensureAbility($request, 'posts:read');

        $validated = $request->validated();
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) ($validated['per_page']) : 15;

        $query = Post::query()
            ->with(['author', 'thumbnailCurator'])
            ->latest()
            ->orderByDesc('id');

        $cursor = isset($validated['cursor']) && is_scalar($validated['cursor']) ? (string) $validated['cursor'] : null;

        $posts = isset($validated['pagination']) && $validated['pagination'] === 'cursor'
            ? $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor)->withQueryString()
            : $query->paginate($perPage)->withQueryString();

        $items = $this->collectionItems($posts);
        $itemCapabilities = $this->capabilitiesForPosts($request, $items);
        $collectionCapabilities = [
            'create' => $this->canPerform($request, 'posts:create', 'create'),
        ];

        return (new PostCollection($posts))
            ->withCapabilities($itemCapabilities, $collectionCapabilities)
            ->additional([
                'message' => 'Posts retrieved successfully.',
            ])
            ->response();
    }

    public function store(StorePostRequest $request, CreatePostAction $createPostAction): JsonResponse
    {
        $this->ensureAbility($request, 'posts:create');

        /** @var array{
         *     title: string,
         *     slug: string,
         *     content: string,
         *     author_id: string,
         *     thumbnail_curator_id?: string|null,
         *     published_at?: string|null,
         * } $payload
         */
        $payload = $request->validated();
        $post = $createPostAction->handle($payload);

        return $this->postResponse($request, $post, 'Post created successfully.', Response::HTTP_CREATED);
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        $this->authorizeAction($request, 'posts:read', 'view', $post);

        return $this->postResponse($request, $post->loadMissing(['author', 'thumbnailCurator']), 'Post retrieved successfully.');
    }

    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $updatePostAction): JsonResponse
    {
        $this->ensureAbility($request, 'posts:update');

        /** @var array{
         *     title?: string,
         *     slug?: string,
         *     content?: string,
         *     author_id?: string,
         *     thumbnail_curator_id?: string|null,
         *     published_at?: string|null,
         * } $payload
         */
        $payload = $request->validated();
        $updatedPost = $updatePostAction->handle($post, $payload);

        return $this->postResponse($request, $updatedPost->loadMissing(['author', 'thumbnailCurator']), 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post, DeletePostAction $deletePostAction): JsonResponse
    {
        $this->authorizeAction($request, 'posts:delete', 'delete', $post);
        $deletePostAction->handle($post);

        return response()->json([
            'message' => 'Post deleted successfully.',
        ]);
    }

    private function postResponse(Request $request, Post $post, string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new PostResource($post))
            ->withCapabilities($this->capabilitiesForPost($request, $post))
            ->additional([
                'message' => $message,
            ])
            ->response()
            ->setStatusCode($status);
    }

    private function authorizeAction(Request $request, string $ability, string $policyAbility, Post $post): void
    {
        $this->ensureAbility($request, $ability);
        Gate::authorize($policyAbility, $post);
    }

    private function ensureAbility(Request $request, string $ability): void
    {
        /** @var User $authUser */
        $authUser = $request->user();

        throw_unless($authUser->tokenCan($ability), AuthorizationException::class, 'Missing required token ability.');
    }

    /**
     * @return array<string, bool>
     */
    private function capabilitiesForPost(Request $request, Post $post): array
    {
        return [
            'view' => $this->canPerform($request, 'posts:read', 'view', $post),
            'update' => $this->canPerform($request, 'posts:update', 'update', $post),
            'delete' => $this->canPerform($request, 'posts:delete', 'delete', $post),
        ];
    }

    /**
     * @param  iterable<int, Post>  $posts
     * @return array<string, array<string, bool>>
     */
    private function capabilitiesForPosts(Request $request, iterable $posts): array
    {
        $capabilities = [];

        foreach ($posts as $post) {
            $routeKey = $post->getRouteKey();
            $capabilities[is_scalar($routeKey) ? (string) $routeKey : ''] = $this->capabilitiesForPost($request, $post);
        }

        return $capabilities;
    }

    private function canPerform(Request $request, string $tokenAbility, string $policyAbility, ?Post $subject = null): bool
    {
        /** @var User|null $authUser */
        $authUser = $request->user();

        if (! $authUser instanceof User || ! $authUser->tokenCan($tokenAbility)) {
            return false;
        }

        $gate = Gate::forUser($authUser);

        return $subject instanceof Post
            ? $gate->allows($policyAbility, $subject)
            : $gate->allows($policyAbility, Post::class);
    }

    /**
     * @param  LengthAwarePaginator<int, Post>|CursorPaginator<int, Post>  $paginator
     * @return Collection<int, Post>
     */
    private function collectionItems(LengthAwarePaginator|CursorPaginator $paginator): Collection
    {
        if (method_exists($paginator, 'getCollection')) {
            /** @var Collection<int, Post> $items */
            $items = $paginator->getCollection();

            return $items;
        }

        /** @var Collection<int, Post> $items */
        $items = collect([]);

        return $items;
    }
}
