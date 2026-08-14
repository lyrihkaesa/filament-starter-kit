<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(
        #[RouteParameter('user')] User $targetUser,
        #[CurrentUser] User $authUser
    ): bool {
        if ($authUser->currentAccessToken() && ! $authUser->tokenCan('users:update')) {
            return false;
        }

        if (! $authUser->can('update', $targetUser)) {
            return false;
        }

        return ! $this->has('roles') || $authUser->can('Update:Role');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(#[RouteParameter('user')] User $targetUser): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetUser->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'email_verified_at' => ['nullable', 'date'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
