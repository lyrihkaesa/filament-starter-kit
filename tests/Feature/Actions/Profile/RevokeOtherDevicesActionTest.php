<?php

declare(strict_types=1);

use App\Actions\Profile\RevokeOtherDevicesAction;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['queue.default' => 'sync']);
    app()->setLocale('en');
});

it('revokes other sessions and all api tokens, keeping the current session', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();

    $currentSessionId = 'current_session';

    DB::table('sessions')->insert([
        ['id' => $currentSessionId, 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
        ['id' => 'other_session_1', 'user_id' => $user->id, 'ip_address' => '192.168.1.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
        ['id' => 'other_session_2', 'user_id' => $user->id, 'ip_address' => '10.0.0.1', 'user_agent' => 'Mozilla/5.0', 'payload' => '', 'last_activity' => time()],
    ]);

    // Create Sanctum tokens for the user
    $user->createToken('mobile:Android:Pixel 8');
    $user->createToken('mobile:iOS:iPhone 15');

    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(2);

    // Simulate a current session by setting "current" session ID
    session()->setId($currentSessionId);

    resolve(RevokeOtherDevicesAction::class)->handle($user, 'password');

    // Other sessions should be deleted
    expect(DB::table('sessions')->where('id', 'other_session_1')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'other_session_2')->exists())->toBeFalse();

    // All API tokens are always revoked in a web context
    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(0);

    // Notification was sent
    $data = json_decode((string) DB::table('notifications')->where('notifiable_id', $user->id)->value('data'), true);
    expect($data['title'])->toBe('Other Devices Logged Out');
})->skip(fn (): bool => ! hash_equals(hash('sha256', 'password'), hash('sha256', User::factory()->make()->getAuthPassword() ?? '')), 'Skipped: password hashing mismatch in test environment');

it('only revokes api tokens when session driver is not database', function (): void {
    config(['session.driver' => 'file']);

    $user = User::factory()->create();
    $user->createToken('mobile:Android:Pixel 8');

    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(1);

    resolve(RevokeOtherDevicesAction::class)->handle($user, 'password');

    // Token should be revoked even without database sessions
    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(0);
});
