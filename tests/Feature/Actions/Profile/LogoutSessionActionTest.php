<?php

declare(strict_types=1);

use App\Actions\Profile\LogoutSessionAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

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
});

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
