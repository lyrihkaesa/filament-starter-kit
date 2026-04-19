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
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class PostController
{
    public function index(IndexPostRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $query = Post::query()
            ->with(['author', 'thumbnailCurator'])
            ->latest()
            ->orderByDesc('id');

        $cursor = $request->string('cursor')->toString() ?: null;

        $posts = $request->string('pagination')->toString() === 'cursor'
            ? $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor)->withQueryString()
            : $query->paginate($perPage)->withQueryString();

        $items = $this->collectionItems($posts);
        $itemCapabilities = $this->capabilitiesForPosts($user, $items);
        $collectionCapabilities = [
            'create' => $this->canPerform($user, 'posts:create', 'create'),
        ];

        return (new PostCollection($posts))
            ->withCapabilities($itemCapabilities, $collectionCapabilities)
            ->additional([
                'message' => 'Posts retrieved successfully.',
            ])
            ->response();
    }

    public function store(StorePostRequest $request, CreatePostAction $createPostAction, #[CurrentUser] User $user): JsonResponse
    {
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

        return $this->postResponse($user, $post, 'Post created successfully.', Response::HTTP_CREATED);
    }

    public function show(Request $request, Post $post, #[CurrentUser] User $authUser): JsonResponse
    {
        Gate::forUser($authUser)->authorize('view', $post);
        throw_unless($authUser->tokenCan('posts:read'), AuthorizationException::class, 'Missing required token ability.');

        return $this->postResponse($authUser, $post->loadMissing(['author', 'thumbnailCurator']), 'Post retrieved successfully.');
    }

    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $updatePostAction, #[CurrentUser] User $authUser): JsonResponse
    {
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

        return $this->postResponse($authUser, $updatedPost->loadMissing(['author', 'thumbnailCurator']), 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post, DeletePostAction $deletePostAction, #[CurrentUser] User $authUser): JsonResponse
    {
        Gate::forUser($authUser)->authorize('delete', $post);
        throw_unless($authUser->tokenCan('posts:delete'), AuthorizationException::class, 'Missing required token ability.');

        $deletePostAction->handle($post);

        return response()->json([
            'message' => 'Post deleted successfully.',
        ]);
    }

    private function postResponse(User $authUser, Post $post, string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new PostResource($post))
            ->withCapabilities($this->capabilitiesForPost($authUser, $post))
            ->additional([
                'message' => $message,
            ])
            ->response()
            ->setStatusCode($status);
    }

    /**
     * @return array<string, bool>
     */
    private function capabilitiesForPost(User $authUser, Post $post): array
    {
        return [
            'view' => $this->canPerform($authUser, 'posts:read', 'view', $post),
            'update' => $this->canPerform($authUser, 'posts:update', 'update', $post),
            'delete' => $this->canPerform($authUser, 'posts:delete', 'delete', $post),
        ];
    }

    /**
     * @param  iterable<int, Post>  $posts
     * @return array<string, array<string, bool>>
     */
    private function capabilitiesForPosts(User $authUser, iterable $posts): array
    {
        $capabilities = [];

        foreach ($posts as $post) {
            $routeKey = $post->getRouteKey();
            $capabilities[is_scalar($routeKey) ? (string) $routeKey : ''] = $this->capabilitiesForPost($authUser, $post);
        }

        return $capabilities;
    }

    private function canPerform(User $authUser, string $tokenAbility, string $policyAbility, ?Post $subject = null): bool
    {
        if (! $authUser->tokenCan($tokenAbility)) {
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

