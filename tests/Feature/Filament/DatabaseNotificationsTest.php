<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can send a database notification to a user', function () {
    $user = User::factory()->create();

    Notification::make()
        ->title('Test Notification')
        ->body('This is a test notification.')
        ->success()
        ->sendToDatabase($user);

    expect($user->notifications()->count())->toBe(1);
    
    $notification = $user->notifications()->first();
    
    expect($notification->data['title'])->toBe('Test Notification');
    expect($notification->data['body'])->toBe('This is a test notification.');
    expect($notification->data['status'])->toBe('success');
});

it('stored notification has uuid for notifiable_id', function () {
    $user = User::factory()->create();

    Notification::make()
        ->title('UUID Test')
        ->sendToDatabase($user);

    $notification = $user->notifications()->first();
    
    expect($notification->notifiable_id)->toBe($user->id);
    expect(\Illuminate\Support\Str::isUuid($notification->notifiable_id))->toBeTrue();
});
