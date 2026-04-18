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
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function index(IndexUserRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $query = User::query()->latest()
            ->orderByDesc('id');

        $cursor = $request->string('cursor')->toString() ?: null;

        $users = $request->string('pagination')->toString() === 'cursor'
            ? $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor)->withQueryString()
            : $query->paginate($perPage)->withQueryString();

        $items = $this->collectionItems($users);
        $itemCapabilities = $this->capabilitiesForUsers($user, $items);
        $collectionCapabilities = [
            'create' => $this->canPerform($user, 'users:create', 'create'),
        ];

        return (new UserCollection($users))
            ->withCapabilities($itemCapabilities, $collectionCapabilities)
            ->additional([
                'message' => 'Users retrieved successfully.',
            ])
            ->response();
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUserAction, #[CurrentUser] User $user): JsonResponse
    {
        /** @var array{name: string, email: string, password: string, email_verified_at?: string|null, roles?: array<int, string>} $payload */
        $payload = $request->validated();
        $newUser = $createUserAction->handle($payload);

        return $this->userResponse($user, $newUser, 'User created successfully.', Response::HTTP_CREATED);
    }

    public function show(Request $request, User $user, #[CurrentUser] User $authUser): JsonResponse
    {
        Gate::forUser($authUser)->authorize('view', $user);
        throw_unless($authUser->tokenCan('users:read'), AuthorizationException::class, 'Missing required token ability.');

        return $this->userResponse($authUser, $user, 'User retrieved successfully.');
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $updateUserAction, #[CurrentUser] User $authUser): JsonResponse
    {
        /** @var array{name?: string, email?: string, password?: string, email_verified_at?: string|null, roles?: array<int, string>} $payload */
        $payload = $request->validated();
        $updatedUser = $updateUserAction->handle($user, $payload);

        return $this->userResponse($authUser, $updatedUser, 'User updated successfully.');
    }

    public function destroy(Request $request, User $user, DeleteUserAction $deleteUserAction, #[CurrentUser] User $authUser): JsonResponse
    {
        Gate::forUser($authUser)->authorize('delete', $user);
        throw_unless($authUser->tokenCan('users:delete'), AuthorizationException::class, 'Missing required token ability.');

        $deleteUserAction->handle($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    private function userResponse(User $authUser, User $subjectUser, string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new UserResource($subjectUser))
            ->withCapabilities($this->capabilitiesForUser($authUser, $subjectUser))
            ->additional([
                'message' => $message,
            ])
            ->response()
            ->setStatusCode($status);
    }

    /**
     * @return array<string, bool>
     */
    private function capabilitiesForUser(User $authUser, User $targetUser): array
    {
        return [
            'view' => $this->canPerform($authUser, 'users:read', 'view', $targetUser),
            'update' => $this->canPerform($authUser, 'users:update', 'update', $targetUser),
            'delete' => $this->canPerform($authUser, 'users:delete', 'delete', $targetUser),
        ];
    }

    /**
     * @param  iterable<int, User>  $users
     * @return array<string, array<string, bool>>
     */
    private function capabilitiesForUsers(User $authUser, iterable $users): array
    {
        $capabilities = [];

        foreach ($users as $user) {
            $routeKey = $user->getRouteKey();
            $capabilities[is_scalar($routeKey) ? (string) $routeKey : ''] = $this->capabilitiesForUser($authUser, $user);
        }

        return $capabilities;
    }

    private function canPerform(User $authUser, string $tokenAbility, string $policyAbility, ?User $subject = null): bool
    {
        if (! $authUser->tokenCan($tokenAbility)) {
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
