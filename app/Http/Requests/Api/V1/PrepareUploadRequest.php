<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class PrepareUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->tokenCan('profile:read');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $purposes = config('api-uploads.purposes');
        assert(is_array($purposes));

        return [
            'file_name' => ['required', 'string'],
            'content_type' => ['required', 'string'],
            'file_size' => ['required', 'integer'],
            'purpose' => ['required', 'string', 'in:'.implode(',', array_keys($purposes))],
            'requested_visibility' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->has('purpose') && ! $validator->errors()->has('purpose')) {
                    $purposeInput = $this->input('purpose');
                    assert(is_string($purposeInput));
                    $purpose = $purposeInput;
                    $config = config('api-uploads.purposes.'.$purpose);

                    // @codeCoverageIgnoreStart
                    if (! is_array($config)) {
                        return;
                    }

                    // @codeCoverageIgnoreEnd

                    $fileSize = $this->input('file_size');
                    $maxSizeConfig = $config['max_size'] ?? 0;
                    if (is_numeric($fileSize) && is_numeric($maxSizeConfig) && (int) $fileSize > ((int) $maxSizeConfig * 1024)) {
                        $validator->errors()->add('file_size', 'The file size exceeds the maximum allowed size for this purpose.');
                    }

                    $contentType = $this->input('content_type');
                    if (is_string($contentType) && in_array($contentType, (array) $config['allowed_mimes'], true)) {
                        // Valid
                    } elseif (is_string($contentType)) {
                        $validator->errors()->add('content_type', 'The file type is not allowed for this purpose.');
                    }

                    $requestedVisibility = $this->input('requested_visibility');
                    if ($this->has('requested_visibility') && is_string($requestedVisibility) && ! in_array($requestedVisibility, (array) ($config['allowed_visibilities'] ?? []), true)) {
                        $validator->errors()->add('requested_visibility', 'The requested visibility is not allowed for this purpose.');
                    }
                }
            },
        ];
    }

    public function getFinalVisibility(): string
    {
        $purposeInput = $this->input('purpose');
        assert(is_string($purposeInput));
        $purpose = $purposeInput;
        $config = config('api-uploads.purposes.'.$purpose);

        // @codeCoverageIgnoreStart
        if (! is_array($config)) {
            return 'public';
        }

        // @codeCoverageIgnoreEnd

        $requestedVisibility = $this->input('requested_visibility');
        if (is_string($requestedVisibility) && in_array($requestedVisibility, (array) ($config['allowed_visibilities'] ?? []), true)) {
            return $requestedVisibility;
        }

        // @codeCoverageIgnoreStart
        $defaultVisibility = $config['default_visibility'] ?? 'public';
        assert(is_string($defaultVisibility));

        return $defaultVisibility;
        // @codeCoverageIgnoreEnd
    }
}
