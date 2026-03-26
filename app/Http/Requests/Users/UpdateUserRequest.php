<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authUser = $this->user();
        $targetUser = $this->route('user');

        if (! $targetUser instanceof User) {
            return false;
        }

        if ($authUser === null || ! $authUser->can('update', $targetUser)) {
            return false;
        }

        return ! ($this->has('roles') && ! $authUser->can('Update:Role'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'email_verified_at' => ['nullable', 'date'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
