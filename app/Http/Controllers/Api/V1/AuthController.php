<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutCurrentTokenAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

final class AuthController
{
    public function store(RegisterRequest $request, RegisterUserAction $registerUserAction, LoginUserAction $loginUserAction): JsonResponse
    {
        $validated = $request->validated();
        $deviceName = isset($validated['device_name']) && is_scalar($validated['device_name']) ? (string) $validated['device_name'] : 'flutter-mobile';
        unset($validated['device_name'], $validated['password_confirmation']);

        /** @var array{name: string, email: string, password: string} $payload */
        $payload = $validated;

        /** @var User $user */
        $user = $registerUserAction->handle($payload);
        $abilities = $this->resolveAbilities($user);
        $passwordInput = $request->input('password', '');
        $token = $loginUserAction->handle($user, is_scalar($passwordInput) ? (string) $passwordInput : '', $deviceName, $abilities);

        return JsonResource::make([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
        ])->additional([
            'message' => 'User registered successfully.',
        ])->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function create(LoginRequest $request, LoginUserAction $loginUserAction): JsonResponse
    {
        $validated = $request->validated();
        $deviceName = isset($validated['device_name']) && is_scalar($validated['device_name']) ? (string) $validated['device_name'] : 'flutter-mobile';

        $user = User::query()->where('email', isset($validated['email']) && is_scalar($validated['email']) ? (string) $validated['email'] : '')->first();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $abilities = $this->resolveAbilities($user);
        $token = $loginUserAction->handle($user, isset($validated['password']) && is_scalar($validated['password']) ? (string) $validated['password'] : '', $deviceName, $abilities);

        if ($token === null) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return JsonResource::make([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
        ])->additional([
            'message' => 'Login successful.',
        ])->response();
    }

    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();

        throw_unless($user->tokenCan('profile:read'), AuthorizationException::class, 'Missing required token ability.');

        return (new UserResource($user))
            ->additional([
                'message' => 'Authenticated user retrieved successfully.',
            ])
            ->response();
    }

    public function destroy(LogoutCurrentTokenAction $logoutCurrentTokenAction): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();
        $logoutCurrentTokenAction->handle($user);

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function resolveAbilities(User $user): array
    {
        $abilities = ['profile:read'];

        if ($user->can('ViewAny:User') || $user->can('View:User')) {
            $abilities[] = 'users:read';
        }

        if ($user->can('Create:User')) {
            $abilities[] = 'users:create';
        }

        if ($user->can('Update:User')) {
            $abilities[] = 'users:update';
        }

        if ($user->can('Delete:User')) {
            $abilities[] = 'users:delete';
        }

        if ($user->can('ViewAny:Post') || $user->can('View:Post')) {
            $abilities[] = 'posts:read';
        }

        if ($user->can('Create:Post')) {
            $abilities[] = 'posts:create';
        }

        if ($user->can('Update:Post')) {
            $abilities[] = 'posts:update';
        }

        if ($user->can('Delete:Post')) {
            $abilities[] = 'posts:delete';
        }

        return array_values(array_unique($abilities));
    }
}
