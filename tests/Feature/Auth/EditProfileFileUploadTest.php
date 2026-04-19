<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('can update profile information and upload avatar to selected storage disk', function (string $disk): void {
    Storage::fake($disk);

    // Override the config disk specifically for this test run
    config(['curator.default_disk' => $disk]);

    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->set('data.name', 'Feature Name Pest')
        ->set('data.email', 'feature-email@example.com')
        ->set('data.locale', 'en')
        ->set('data.avatar_curator_id', $file)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $user->refresh();

    // Verify DB
    expect($user->name)->toBe('Feature Name Pest')
        ->and($user->email)->toBe('feature-email@example.com');

    // Verify file actually stored
    $media = $user->avatarMedia;
    expect($media)->not->toBeNull()
        ->and($media->disk)->toBe($disk);

    Storage::disk($disk)->assertExists($media->path);

})->with(['public', 's3']);
