<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class IndexUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('viewAny', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pagination' => ['required', 'string', 'in:page,cursor'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'cursor' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'pagination' => $this->input('pagination', 'page'),
        ]);
    }
}
