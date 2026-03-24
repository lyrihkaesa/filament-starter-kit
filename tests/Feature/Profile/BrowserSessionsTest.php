<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['session.driver' => 'database']);
});

it('can see the browser sessions on the consolidated profile page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(EditProfile::getUrl())
        ->assertStatus(200)
        ->assertSee(__('Browser Sessions'));
});

it('can logout other browser sessions from the consolidated profile page', function (): void {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->actingAs($user);

    // Simulate another session in database
    DB::table('sessions')->insert([
        'id' => 'another-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => 'payload',
        'last_activity' => now()->subMinutes(10)->timestamp,
    ]);

    Livewire::test(EditProfile::class)
        ->call('logoutOtherBrowserSessions', 'password')
        ->assertNotified();

    expect(DB::table('sessions')->where('user_id', $user->id)->where('id', 'another-session-id')->count())->toBe(0);
});
