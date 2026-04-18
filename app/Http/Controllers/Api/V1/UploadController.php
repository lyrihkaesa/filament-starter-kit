<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\PrepareUploadRequest;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class UploadController
{
    public function store(PrepareUploadRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $purpose = $request->string('purpose')->toString();

        $config = config('api-uploads.purposes.'.$purpose);
        assert(is_array($config));

        $maxSizeConfig = $config['max_size'] ?? 0;
        assert(is_numeric($maxSizeConfig));
        $maxSize = (int) $maxSizeConfig;

        $diskName = config('filesystems.default') === 's3' ? 's3' : 'uploads_tmp';
        /**
         * @var Filesystem $disk
         */
        $disk = Storage::disk($diskName);

        $sessionId = Str::uuid()->toString();
        $fileNameInput = $request->string('file_name')->toString();
        $extension = pathinfo($fileNameInput, PATHINFO_EXTENSION);

        $fileName = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
        $path = sprintf('tmp/uploads/%s/%s', $purpose, $fileName);

        $upload = TemporaryUpload::query()->create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'disk' => $diskName,
            'path' => $path,
            'file_name' => $fileNameInput,
            'mime_type' => $request->string('content_type')->toString(),
            'size' => $request->integer('file_size'),
            'purpose' => $purpose,
            'status' => 'prepared',
            'final_visibility' => $request->getFinalVisibility(),
        ]);

        $uploadUrl = '';
        $uploadType = 'put';
        $method = 'PUT';

        if ($diskName === 's3' && method_exists($disk, 'temporaryUploadUrl')) {
            // @codeCoverageIgnoreStart
            $uploadUrl = $disk->temporaryUploadUrl($path, now()->addMinutes(30));
            // @codeCoverageIgnoreEnd
        } else {
            $uploadUrl = URL::temporarySignedRoute('v1.uploads.file', now()->addMinutes(30), ['upload' => $upload->id]);
        }

        return response()->json([
            'message' => 'Upload prepared successfully.',
            'data' => [
                'upload_id' => $upload->id,
                'purpose' => $purpose,
                'status' => $upload->status,
                'disk' => $diskName,
                'temporary_path' => $path,
                'final_visibility' => $upload->final_visibility,
                'max_size' => $maxSize * 1024,
                'accepted_file_types' => $config['allowed_mimes'],
                'upload_type' => $uploadType,
                'upload_url' => $uploadUrl,
                'method' => $method,
                'headers' => [
                    'Content-Type' => $request->string('content_type')->toString(),
                ],
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ],
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, TemporaryUpload $upload): JsonResponse
    {
        abort_if($upload->status !== 'prepared', Response::HTTP_FORBIDDEN, 'Invalid upload session.');

        $content = (string) $request->getContent();
        abort_if($content === '', Response::HTTP_BAD_REQUEST, 'Empty file content.');

        Storage::disk((string) $upload->disk)->put((string) $upload->path, $content);

        return response()->json([
            'message' => 'File uploaded successfully.',
        ]);
    }

    public function edit(Request $request, TemporaryUpload $upload, #[CurrentUser] User $user): JsonResponse
    {
        abort_if($upload->user_id !== $user->id || $upload->status !== 'prepared', Response::HTTP_FORBIDDEN, 'Invalid upload session.');

        $disk = Storage::disk((string) $upload->disk);
        abort_unless($disk->exists((string) $upload->path), Response::HTTP_BAD_REQUEST, 'File not found in storage. Please upload the file first.');

        $actualSize = $disk->size((string) $upload->path);
        abort_if($actualSize === 0, Response::HTTP_BAD_REQUEST, 'Uploaded file is empty.');

        $upload->update(['status' => 'uploaded']);

        return response()->json([
            'message' => 'Upload marked as complete.',
            'data' => $upload,
        ]);
    }

    public function show(Request $request, TemporaryUpload $upload, #[CurrentUser] User $user): JsonResponse
    {
        abort_if($upload->user_id !== $user->id, Response::HTTP_FORBIDDEN);

        return response()->json([
            'data' => $upload,
        ]);
    }

    public function destroy(Request $request, TemporaryUpload $upload, #[CurrentUser] User $user): JsonResponse
    {
        abort_if($upload->user_id !== $user->id, Response::HTTP_FORBIDDEN);

        if ($upload->status !== 'finalized') {
            Storage::disk((string) $upload->disk)->delete((string) $upload->path);
        }

        $upload->delete();

        return response()->json([
            'message' => 'Upload session deleted.',
        ]);
    }
}
