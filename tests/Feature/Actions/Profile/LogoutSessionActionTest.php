<?php

declare(strict_types=1);

use App\Actions\Profile\LogoutSessionAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['queue.default' => 'sync']);
    app()->setLocale('en');
});

it('can logout a specific session', function (): void {
    // Arrange: Mock the sessions table in database
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    DB::table('sessions')->insert([
        [
            'id' => 'user_session_1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'payload',
            'last_activity' => time(),
        ],
        [
            'id' => 'user_session_2',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'payload',
            'last_activity' => time(),
        ],
        [
            'id' => 'other_user_session',
            'user_id' => $otherUser->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'payload',
            'last_activity' => time(),
        ],
    ]);

    $action = resolve(LogoutSessionAction::class);

    // Act: Logout a specific session of the user
    $action->handle($user, 'user_session_1');

    // Assert: The targeted session is deleted, others remain
    $this->assertDatabaseMissing('sessions', ['id' => 'user_session_1']);
    $this->assertDatabaseHas('sessions', ['id' => 'user_session_2']);
    $this->assertDatabaseHas('sessions', ['id' => 'other_user_session']);

    // Assert: Notification is created
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $user->id,
        'notifiable_type' => $user->getMorphClass(),
    ]);

    $notification = DB::table('notifications')
        ->where('notifiable_id', $user->id)
        ->first();

    $data = json_decode((string) $notification->data, true);
    expect($data['title'])->toBe('Log Out Successful')
        ->and($data['body'])->toContain('Unknown Browser on Unknown OS')
        ->and($data['body'])->toContain('127.0.0.1');
});

it('does nothing if session is not found', function (): void {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();
    $action = resolve(LogoutSessionAction::class);

    $action->handle($user, 'non_existent_session');

    expect(true)->toBeTrue();
});

it('correctly parses various user agents', function (string $userAgent, string $expectedDevice): void {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();

    DB::table('sessions')->insert([
        'id' => 'test_session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => $userAgent,
        'payload' => 'payload',
        'last_activity' => time(),
    ]);

    $action = resolve(LogoutSessionAction::class);
    $action->handle($user, 'test_session');

    $notification = DB::table('notifications')
        ->where('notifiable_id', $user->id)
        ->latest('created_at')
        ->first();

    $data = json_decode((string) $notification->data, true);
    expect($data['body'])->toContain($expectedDevice);
})->with([
    'Edge' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59', 'Edge on Windows'],
    'Chrome' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'Chrome on Windows'],
    'Safari' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15', 'Safari on macOS'],
    'Firefox' => ['Mozilla/5.0 (X11; Linux x86_64; rv:89.0) Gecko/20100101 Firefox/89.0', 'Firefox on Linux'],
    'iOS' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1', 'Safari on iOS'],
    'Android' => ['Mozilla/5.0 (Linux; Android 10; SM-G973F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36', 'Chrome on Android'],
]);

it('does nothing if session driver is not database', function (): void {
    // Arrange: Set session driver to file
    config(['session.driver' => 'file']);

    $user = User::factory()->create();
    $action = resolve(LogoutSessionAction::class);

    // Act & Assert: This should run without errors even if DB sessions table doesn't exist or is not used
    $action->handle($user, 'session_id');

    // Just a basic success assertion
    expect(true)->toBeTrue();
});
