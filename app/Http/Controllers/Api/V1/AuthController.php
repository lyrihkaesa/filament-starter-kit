<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutCurrentTokenAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

final class AuthController
{
    public function store(RegisterRequest $request, RegisterUserAction $registerUserAction, LoginUserAction $loginUserAction): JsonResponse
    {
        $validated = $request->validated();
        $deviceName = $request->string('device_name', 'flutter-mobile')->toString();

        /** @var array{name: string, email: string, password: string} $payload */
        $payload = Arr::except($validated, ['device_name', 'password_confirmation']);

        $user = $registerUserAction->handle($payload);
        $abilities = $this->resolveAbilities($user);
        $password = $request->string('password')->toString();

        $token = $loginUserAction->handle($user, $password, $deviceName, $abilities);

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
        $email = $request->string('email')->toString();
        $password = $request->string('password')->toString();
        $deviceName = $request->string('device_name', 'flutter-mobile')->toString();

        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $abilities = $this->resolveAbilities($user);
        $token = $loginUserAction->handle($user, $password, $deviceName, $abilities);

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

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action, #[CurrentUser] User $user): JsonResponse
    {
        /** @var array{name?: string, avatar_upload_id?: string|null, avatar_media_id?: string|null, locale?: string, timezone?: string, theme?: string} $data */
        $data = $request->validated();

        $updatedUser = $action->handle($user, $data);

        return (new UserResource($updatedUser))
            ->additional([
                'message' => 'Profile updated successfully.',
            ])
            ->response();
    }

    public function show(#[CurrentUser] User $user): JsonResponse
    {
        throw_unless($user->tokenCan('profile:read'), AuthorizationException::class, 'Missing required token ability.');

        return (new UserResource($user))
            ->additional([
                'message' => 'Authenticated user retrieved successfully.',
            ])
            ->response();
    }

    public function destroy(LogoutCurrentTokenAction $logoutCurrentTokenAction, #[CurrentUser] User $user): JsonResponse
    {
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
