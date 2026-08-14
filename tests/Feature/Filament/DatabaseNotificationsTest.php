<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

beforeEach(function (): void {
    config(['queue.default' => 'sync']);
});

it('can send a database notification to a user', function (): void {
    $user = User::factory()->create();

    Notification::make()
        ->title('Test Notification')
        ->body('This is a test notification.')
        ->success()
        ->sendToDatabase($user);

    expect($user->notifications()->count())->toBe(1);

    $notification = $user->notifications()->first();
    expect($notification->data)->toMatchArray(['title' => 'Test Notification', 'body' => 'This is a test notification.', 'status' => 'success']);
});

it('stored notification has uuid for notifiable_id', function (): void {
    $user = User::factory()->create();

    Notification::make()
        ->title('UUID Test')
        ->sendToDatabase($user);

    $notification = $user->notifications()->first();

    expect($notification->notifiable_id)->toBe($user->id)
        ->and(Str::isUuid($notification->notifiable_id))->toBeTrue();
});
