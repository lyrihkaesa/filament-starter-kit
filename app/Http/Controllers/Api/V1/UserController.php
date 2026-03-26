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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function index(IndexUserRequest $request): JsonResponse
    {
        $this->ensureAbility($request, 'users:read');

        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);

        $query = User::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $users = $validated['pagination'] === 'cursor'
            ? $query->cursorPaginate($perPage, ['*'], 'cursor', $validated['cursor'] ?? null)->withQueryString()
            : $query->paginate($perPage)->withQueryString();

        return (new UserCollection($users))
            ->additional([
                'message' => 'Users retrieved successfully.',
            ])
            ->response();
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUserAction): JsonResponse
    {
        $this->ensureAbility($request, 'users:create');

        $user = $createUserAction->handle($request->validated());

        return (new UserResource($user))
            ->additional([
                'message' => 'User created successfully.',
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorizeAction($request, 'users:read', 'view', $user);

        return (new UserResource($user))
            ->additional([
                'message' => 'User retrieved successfully.',
            ])
            ->response();
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $updateUserAction): JsonResponse
    {
        $this->ensureAbility($request, 'users:update');

        $updatedUser = $updateUserAction->handle($user, $request->validated());

        return (new UserResource($updatedUser))
            ->additional([
                'message' => 'User updated successfully.',
            ])
            ->response();
    }

    public function destroy(Request $request, User $user, DeleteUserAction $deleteUserAction): JsonResponse
    {
        $this->authorizeAction($request, 'users:delete', 'delete', $user);
        $deleteUserAction->handle($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
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

        if (! $authUser->tokenCan($ability)) {
            throw new AuthorizationException('Missing required token ability.');
        }
    }
}
