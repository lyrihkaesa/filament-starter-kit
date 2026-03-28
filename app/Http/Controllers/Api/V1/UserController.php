<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Http\Requests\Users\IndexUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserCollection;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function index(IndexUserRequest $request): JsonResponse
    {
        $this->ensureAbility($request, 'users:read');

        $validated = $request->validated();
        $perPage = isset($validated['per_page']) && is_numeric($validated['per_page']) ? (int) ($validated['per_page']) : 15;

        $query = User::query()->latest()
            ->orderByDesc('id');

        $cursor = isset($validated['cursor']) && is_scalar($validated['cursor']) ? (string) $validated['cursor'] : null;

        $users = isset($validated['pagination']) && $validated['pagination'] === 'cursor'
            ? $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor)->withQueryString()
            : $query->paginate($perPage)->withQueryString();

        $items = $this->collectionItems($users);
        $itemCapabilities = $this->capabilitiesForUsers($request, $items);
        $collectionCapabilities = [
            'create' => $this->canPerform($request, 'users:create', 'create'),
        ];

        return (new UserCollection($users))
            ->withCapabilities($itemCapabilities, $collectionCapabilities)
            ->additional([
                'message' => 'Users retrieved successfully.',
            ])
            ->response();
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUserAction): JsonResponse
    {
        $this->ensureAbility($request, 'users:create');

        /** @var array{name: string, email: string, password: string, email_verified_at?: string|null, roles?: array<int, string>} $payload */
        $payload = $request->validated();
        $user = $createUserAction->handle($payload);

        return $this->userResponse($request, $user, 'User created successfully.', Response::HTTP_CREATED);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorizeAction($request, 'users:read', 'view', $user);

        return $this->userResponse($request, $user, 'User retrieved successfully.');
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $updateUserAction): JsonResponse
    {
        $this->ensureAbility($request, 'users:update');

        /** @var array{name?: string, email?: string, password?: string, email_verified_at?: string|null, roles?: array<int, string>} $payload */
        $payload = $request->validated();
        $updatedUser = $updateUserAction->handle($user, $payload);

        return $this->userResponse($request, $updatedUser, 'User updated successfully.');
    }

    public function destroy(Request $request, User $user, DeleteUserAction $deleteUserAction): JsonResponse
    {
        $this->authorizeAction($request, 'users:delete', 'delete', $user);
        $deleteUserAction->handle($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    private function userResponse(Request $request, User $user, string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new UserResource($user))
            ->withCapabilities($this->capabilitiesForUser($request, $user))
            ->additional([
                'message' => $message,
            ])
            ->response()
            ->setStatusCode($status);
    }

    private function authorizeAction(Request $request, string $ability, string $policyAbility, User $user): void
    {
        $this->ensureAbility($request, $ability);
        Gate::authorize($policyAbility, $user);
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
    private function capabilitiesForUser(Request $request, User $user): array
    {
        return [
            'view' => $this->canPerform($request, 'users:read', 'view', $user),
            'update' => $this->canPerform($request, 'users:update', 'update', $user),
            'delete' => $this->canPerform($request, 'users:delete', 'delete', $user),
        ];
    }

    /**
     * @param  iterable<int, User>  $users
     * @return array<string, array<string, bool>>
     */
    private function capabilitiesForUsers(Request $request, iterable $users): array
    {
        $capabilities = [];

        foreach ($users as $user) {
            $routeKey = $user->getRouteKey();
            $capabilities[is_scalar($routeKey) ? (string) $routeKey : ''] = $this->capabilitiesForUser($request, $user);
        }

        return $capabilities;
    }

    private function canPerform(Request $request, string $tokenAbility, string $policyAbility, ?User $subject = null): bool
    {
        /** @var User|null $authUser */
        $authUser = $request->user();

        if (! $authUser instanceof User || ! $authUser->tokenCan($tokenAbility)) {
            return false;
        }

        $gate = Gate::forUser($authUser);

        return $subject instanceof User
            ? $gate->allows($policyAbility, $subject)
            : $gate->allows($policyAbility, User::class);
    }

    /**
     * @param  LengthAwarePaginator<int, User>|CursorPaginator<int, User>  $paginator
     * @return Collection<int, User>
     */
    private function collectionItems(LengthAwarePaginator|CursorPaginator $paginator): Collection
    {
        if (method_exists($paginator, 'getCollection')) {
            /** @var Collection<int, User> $items */
            $items = $paginator->getCollection();

            return $items;
        }

        /** @var Collection<int, User> $items */
        $items = collect([]);

        return $items;
    }
}
