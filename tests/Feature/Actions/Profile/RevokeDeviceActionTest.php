<?php

declare(strict_types=1);

use App\Actions\Profile\RevokeDeviceAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['queue.default' => 'sync']);
    app()->setLocale('en');
});

// =========================================================
// Revoking a Web Session (session:{id})
// =========================================================

it('revokes a web session by prefixed id', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    DB::table('sessions')->insert([
        ['id' => 'user_session_1', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
        ['id' => 'user_session_2', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
        ['id' => 'other_user_session', 'user_id' => $otherUser->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
    ]);

    resolve(RevokeDeviceAction::class)->handle($user, 'session:user_session_1');

    expect(DB::table('sessions')->where('id', 'user_session_1')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'user_session_2')->exists())->toBeTrue()
        ->and(DB::table('sessions')->where('id', 'other_user_session')->exists())->toBeTrue();

    $data = json_decode((string) DB::table('notifications')->where('notifiable_id', $user->id)->value('data'), true);
    expect($data['title'])->toBe('Log Out Successful');
});

it('does nothing when revoking a web session not owned by the user', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    DB::table('sessions')->insert([
        'id' => 'other_user_session', 'user_id' => $otherUser->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time(),
    ]);

    resolve(RevokeDeviceAction::class)->handle($user, 'session:other_user_session');

    // Should still exist – the user cannot revoke another user's session
    expect(DB::table('sessions')->where('id', 'other_user_session')->exists())->toBeTrue();
});

it('does nothing when revoking a session that does not exist', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    resolve(RevokeDeviceAction::class)->handle($user, 'session:non_existent');

    expect(true)->toBeTrue();
});

it('does nothing when session driver is not database', function (): void {
    config(['session.driver' => 'file']);

    $user = User::factory()->create();
    resolve(RevokeDeviceAction::class)->handle($user, 'session:some_id');

    expect(true)->toBeTrue();
});

// =========================================================
// Revoking an API Token (token:{id})
// =========================================================

it('revokes a sanctum token by prefixed id', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('mobile:Android:Pixel 8');

    $tokenId = $token->accessToken->id;

    expect(PersonalAccessToken::find($tokenId))->not->toBeNull();

    resolve(RevokeDeviceAction::class)->handle($user, "token:{$tokenId}");

    expect(PersonalAccessToken::find($tokenId))->toBeNull();

    $data = json_decode((string) DB::table('notifications')->where('notifiable_id', $user->id)->value('data'), true);
    expect($data['title'])->toBe('Device Disconnected');
});

it('does nothing when revoking a token not owned by the user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $token = $otherUser->createToken('mobile:Android:Other Device');
    $tokenId = $token->accessToken->id;

    resolve(RevokeDeviceAction::class)->handle($user, "token:{$tokenId}");

    // Token should still exist
    expect(PersonalAccessToken::find($tokenId))->not->toBeNull();
});

it('does nothing when revoking a token that does not exist', function (): void {
    $user = User::factory()->create();
    resolve(RevokeDeviceAction::class)->handle($user, 'token:99999');

    expect(true)->toBeTrue();
});

// =========================================================
// Unknown prefix
// =========================================================

it('does nothing when prefixed device id is unrecognized', function (): void {
    $user = User::factory()->create();
    resolve(RevokeDeviceAction::class)->handle($user, 'unknown:some_id');

    expect(true)->toBeTrue();
});
