<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PrepareUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purposes = array_keys(config('api-uploads.purposes', []));

        return [
            'purpose' => ['required', 'string', Rule::in($purposes)],
            'file_name' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1'],
            'requested_visibility' => ['nullable', 'string', Rule::in(['public', 'private'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('purpose') && ! $validator->errors()->has('purpose')) {
                $purpose = $this->input('purpose');
                $config = config("api-uploads.purposes.{$purpose}");

                if (! $config) {
                    return;
                }

                if (! in_array($this->input('content_type'), $config['allowed_mimes'], true)) {
                    $validator->errors()->add('content_type', 'The provided content type is not allowed for this purpose.');
                }

                // size is in bytes from client, max_size in config is in KB
                $maxSizeBytes = $config['max_size'] * 1024;
                if ($this->input('size') > $maxSizeBytes) {
                    $validator->errors()->add('size', 'The file size exceeds the maximum allowed size for this purpose.');
                }

                if ($this->has('requested_visibility')) {
                    if (! in_array($this->input('requested_visibility'), $config['allowed_visibilities'], true)) {
                        $validator->errors()->add('requested_visibility', 'The requested visibility is not allowed for this purpose.');
                    }
                }
            }
        });
    }

    public function getFinalVisibility(): string
    {
        $purpose = $this->input('purpose');
        $config = config("api-uploads.purposes.{$purpose}");

        if ($this->has('requested_visibility') && in_array($this->input('requested_visibility'), $config['allowed_visibilities'] ?? [], true)) {
            return $this->input('requested_visibility');
        }

        return $config['default_visibility'] ?? 'private';
    }
}
