<?php

declare(strict_types=1);

use App\Actions\Profile\RevokeOtherDevicesAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['queue.default' => 'sync']);
    app()->setLocale('en');
});

it('clears other database sessions when revoking other devices', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $currentSessionId = 'current_session_id';
    Session::shouldReceive('getId')->andReturn($currentSessionId);

    $otherUser = User::factory()->create();

    DB::table('sessions')->insert([
        ['id' => $currentSessionId, 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
        ['id' => 'user_other_session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
        ['id' => 'other_user_session', 'user_id' => $otherUser->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
    ]);

    Auth::shouldReceive('guard->logoutOtherDevices')
        ->once()
        ->with('password123');

    resolve(RevokeOtherDevicesAction::class)->handle($user, 'password123');

    $this->assertDatabaseHas('sessions', ['id' => $currentSessionId]);
    $this->assertDatabaseMissing('sessions', ['id' => 'user_other_session']);
    $this->assertDatabaseHas('sessions', ['id' => 'other_user_session']);

    $data = json_decode((string) DB::table('notifications')->where('notifiable_id', $user->id)->value('data'), true);
    expect($data['title'])->toBe('Other Devices Logged Out');
});

it('also revokes all sanctum tokens when revoking other devices', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create(['password' => bcrypt('password123')]);

    Session::shouldReceive('getId')->andReturn('current_session');
    Auth::shouldReceive('guard->logoutOtherDevices')->once()->with('password123');

    $user->createToken('mobile:Android:Pixel 8');
    $user->createToken('mobile:iOS:iPhone 15');

    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(2);

    resolve(RevokeOtherDevicesAction::class)->handle($user, 'password123');

    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
});

it('still revokes tokens even when session driver is not database', function (): void {
    config(['session.driver' => 'file']);

    $user = User::factory()->create(['password' => bcrypt('password123')]);

    Auth::shouldReceive('guard->logoutOtherDevices')->once()->with('password123');

    $user->createToken('mobile:Android:Pixel 8');

    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(1);

    resolve(RevokeOtherDevicesAction::class)->handle($user, 'password123');

    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(0);
});

it('handles session exception when getting id', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create(['password' => bcrypt('password123')]);

    Session::shouldReceive('getId')->andThrow(new Exception('No session'));
    Auth::shouldReceive('guard->logoutOtherDevices')->once();

    resolve(RevokeOtherDevicesAction::class)->handle($user, 'password123');

    expect(true)->toBeTrue();
});
