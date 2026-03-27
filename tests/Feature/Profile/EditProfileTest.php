<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can see the consolidated profile page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(EditProfile::getUrl())
        ->assertStatus(200)
        ->assertSee(__('Profile Information'))
        ->assertSee(__('Update Password'))
        ->assertSee(__('Active Devices & Sessions'));
});

it('can update profile information', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('data.name', 'New Name')
        ->set('data.email', 'new@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh())
        ->name->toBe('New Name')
        ->email->toBe('new@example.com');
});

it('can revoke a single web session', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    config(['session.driver' => 'database']);
    DB::table('sessions')->insert([
        'id' => 'other_session_id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'last_activity' => now()->timestamp,
        'payload' => '',
    ]);

    expect(DB::table('sessions')->where('id', 'other_session_id')->exists())->toBeTrue();

    Livewire::test(EditProfile::class)
        ->call('revokeDevice', 'session:other_session_id')
        ->assertHasNoErrors()
        ->assertNotified();

    expect(DB::table('sessions')->where('id', 'other_session_id')->exists())->toBeFalse();
});

it('can revoke a sanctum api token', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $token = $user->createToken('mobile:Android:Pixel 8');
    $tokenId = $token->accessToken->id;

    expect(PersonalAccessToken::find($tokenId))->not->toBeNull();

    Livewire::test(EditProfile::class)
        ->call('revokeDevice', "token:{$tokenId}")
        ->assertHasNoErrors()
        ->assertNotified();

    expect(PersonalAccessToken::find($tokenId))->toBeNull();
});

it('formats sanctum token names for display', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $user->createToken('mobile:Android:Pixel 8');
    $user->createToken('desktop:Windows:PC');
    $user->createToken('pat:My-Token');
    $user->createToken('Simple Token');

    $this->get(EditProfile::getUrl())
        ->assertStatus(200)
        ->assertSee('Android Pixel 8')
        ->assertSee('Windows PC')
        ->assertSee('My-Token')
        ->assertSee('Simple Token')
        ->assertDontSee('mobile:Android:Pixel 8');
});
