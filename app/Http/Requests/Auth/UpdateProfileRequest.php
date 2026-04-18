<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'avatar_upload_id' => ['nullable', 'string', 'uuid'],
            'avatar_media_id' => ['nullable', 'string', 'uuid', 'exists:curator,id'],
            'locale' => ['sometimes', 'string', 'max:20'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'theme' => ['sometimes', 'string', 'in:light,dark,system'],
        ];
    }
}
