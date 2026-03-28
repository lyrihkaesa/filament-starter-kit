<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\PrepareUploadRequest;
use App\Models\TemporaryUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class UploadController
{
    public function prepare(PrepareUploadRequest $request): JsonResponse
    {
        $purpose = $request->input('purpose');
        $config = config("api-uploads.purposes.{$purpose}");

        $diskName = config('filesystems.default') === 's3' ? 's3' : 'uploads_tmp';

        $sessionId = Str::uuid()->toString();
        $extension = pathinfo($request->input('file_name'), PATHINFO_EXTENSION);
        $fileName = Str::uuid()->toString().($extension ? ".{$extension}" : '');
        $path = "tmp/uploads/{$purpose}/{$fileName}";

        $upload = TemporaryUpload::create([
            'user_id' => $request->user()->id,
            'session_id' => $sessionId,
            'disk' => $diskName,
            'path' => $path,
            'file_name' => $request->input('file_name'),
            'mime_type' => $request->input('content_type'),
            'size' => $request->input('size'),
            'purpose' => $purpose,
            'status' => 'prepared',
            'final_visibility' => $request->getFinalVisibility(),
        ]);

        $disk = Storage::disk($diskName);

        $uploadUrl = '';
        $uploadType = 'put';
        $method = 'PUT';

        if ($diskName === 's3' && method_exists($disk, 'temporaryUploadUrl')) {
            $uploadUrl = $disk->temporaryUploadUrl($path, now()->addMinutes(30));
        } else {
            $uploadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('v1.uploads.file', now()->addMinutes(30), ['upload' => $upload->id]);
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
                'max_size' => $config['max_size'] * 1024,
                'accepted_file_types' => $config['allowed_mimes'],
                'upload_type' => $uploadType,
                'upload_url' => $uploadUrl,
                'method' => $method,
                'headers' => [
                    'Content-Type' => $request->input('content_type'),
                ],
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ],
        ], Response::HTTP_CREATED);
    }

    public function file(Request $request, TemporaryUpload $upload): JsonResponse
    {
        if ($upload->status !== 'prepared') {
            abort(Response::HTTP_FORBIDDEN, 'Invalid upload session.');
        }

        $content = $request->getContent();
        if (empty($content)) {
            abort(Response::HTTP_BAD_REQUEST, 'Empty file content.');
        }

        Storage::disk($upload->disk)->put($upload->path, $content);

        return response()->json(['message' => 'File uploaded to temporary storage.']);
    }

    public function markUploaded(Request $request, TemporaryUpload $upload): JsonResponse
    {
        if ($upload->user_id !== $request->user()->id || $upload->status !== 'prepared') {
            abort(Response::HTTP_FORBIDDEN, 'Invalid upload session.');
        }

        $disk = Storage::disk($upload->disk);
        if (! $disk->exists($upload->path)) {
            abort(Response::HTTP_BAD_REQUEST, 'File not found in storage. Please upload the file first.');
        }

        $actualSize = $disk->size($upload->path);
        if ($actualSize === 0) {
            abort(Response::HTTP_BAD_REQUEST, 'Uploaded file is empty.');
        }

        $upload->update(['status' => 'uploaded']);

        return response()->json([
            'message' => 'Upload marked as complete.',
            'data' => [
                'upload_id' => $upload->id,
                'status' => $upload->status,
            ],
        ]);
    }

    public function show(Request $request, TemporaryUpload $upload): JsonResponse
    {
        if ($upload->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'data' => $upload,
        ]);
    }

    public function destroy(Request $request, TemporaryUpload $upload): JsonResponse
    {
        if ($upload->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($upload->status !== 'finalized') {
            Storage::disk($upload->disk)->delete($upload->path);
        }

        $upload->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
