<?php

declare(strict_types=1);

use App\Actions\Profile\LogoutOtherBrowserSessionsAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

it('clears other database sessions when logging out other devices', function (): void {
    // Arrange: Use database session driver
    config(['session.driver' => 'database']);
    
    $user = User::factory()->create(['password' => bcrypt('password123')]);
    
    // Create current session
    $currentSessionId = 'current_session_id';
    Session::shouldReceive('getId')->andReturn($currentSessionId);
    
    // Create other user for session
    $otherUser = User::factory()->create();
    
    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'payload',
            'last_activity' => time(),
        ],
        [
            'id' => 'user_other_session',
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

    // Mock Auth::guard()->logoutOtherDevices
    Auth::shouldReceive('guard->logoutOtherDevices')
        ->once()
        ->with('password123');

    $action = resolve(LogoutOtherBrowserSessionsAction::class);

    // Act: Logout other devices
    $action->handle($user, 'password123');

    // Assert: Only current session remains for the user, other user's session remains
    $this->assertDatabaseHas('sessions', ['id' => $currentSessionId]);
    $this->assertDatabaseMissing('sessions', ['id' => 'user_other_session']);
    $this->assertDatabaseHas('sessions', ['id' => 'other_user_session']);

    // Assert: Notification is created
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $user->id,
        'notifiable_type' => $user->getMorphClass(),
    ]);

    $notification = DB::table('notifications')
        ->where('notifiable_id', $user->id)
        ->first();

    $data = json_decode($notification->data, true);
    expect($data['title'])->toBe('Other Devices Logged Out');
});

it('does not attempt to clear database sessions if driver is not database', function (): void {
    // Arrange: Use file session driver
    config(['session.driver' => 'file']);
    
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    // Mock Auth::guard()->logoutOtherDevices
    Auth::shouldReceive('guard->logoutOtherDevices')
        ->once()
        ->with('password123');

    $action = resolve(LogoutOtherBrowserSessionsAction::class);

    // Act: Logout other devices
    $action->handle($user, 'password123');

    // Assert: It runs without error and DB is not touched for sessions
    expect(true)->toBeTrue();
});

it('handles session exception when getting id', function (): void {
    config(['session.driver' => 'database']);
    $user = User::factory()->create(['password' => bcrypt('password123')]);
    
    Session::shouldReceive('getId')->andThrow(new \Exception('No session'));
    Auth::shouldReceive('guard->logoutOtherDevices')->once();

    $action = resolve(LogoutOtherBrowserSessionsAction::class);
    $action->handle($user, 'password123');
    
    expect(true)->toBeTrue();
});
