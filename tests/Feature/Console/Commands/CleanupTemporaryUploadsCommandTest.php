<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('cleans up expired temporary uploads', function (): void {
    Storage::fake('uploads_tmp');

    $user = User::factory()->create();

    $expiredUpload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'session_id' => '1234',
        'disk' => 'uploads_tmp',
        'path' => 'expired.txt',
        'file_name' => 'expired.txt',
        'mime_type' => 'text/plain',
        'size' => 10,
        'purpose' => 'test',
        'status' => 'prepared',
        'final_visibility' => 'public',
    ]);
    $expiredUpload->created_at = now()->subHours(25);
    $expiredUpload->saveQuietly();

    Storage::disk('uploads_tmp')->put('expired.txt', '123');

    $recentUpload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'session_id' => '5678',
        'disk' => 'uploads_tmp',
        'path' => 'recent.txt',
        'file_name' => 'recent.txt',
        'mime_type' => 'text/plain',
        'size' => 10,
        'purpose' => 'test',
        'status' => 'prepared',
        'final_visibility' => 'public',
        'created_at' => now()->subHours(2),
    ]);

    Storage::disk('uploads_tmp')->put('recent.txt', '123');

    $this->artisan('uploads:cleanup')
        ->expectsOutput('Cleaned up 1 expired temporary uploads.')
        ->assertExitCode(0);

    expect(TemporaryUpload::query()->find($expiredUpload->id))->toBeNull();
    Storage::disk('uploads_tmp')->assertMissing('expired.txt');

    expect(TemporaryUpload::query()->find($recentUpload->id))->not->toBeNull();
    Storage::disk('uploads_tmp')->assertExists('recent.txt');
});
