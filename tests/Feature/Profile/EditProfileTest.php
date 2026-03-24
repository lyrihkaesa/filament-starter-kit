<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can see the consolidated profile page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(EditProfile::getUrl())
        ->assertStatus(200)
        ->assertSee(__('Profile Information'))
        ->assertSee(__('Update Password'))
        ->assertSee(__('Browser Sessions'));
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

it('can logout a single browser session', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Mock another session in the database
    config(['session.driver' => 'database']);
    DB::table('sessions')->insert([
        'id' => 'other_session_id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'last_activity' => now()->timestamp,
        'payload' => 'payload',
    ]);

    expect(DB::table('sessions')->where('id', 'other_session_id')->exists())->toBeTrue();

    Livewire::test(EditProfile::class)
        ->call('logoutSession', 'other_session_id')
        ->assertHasNoErrors()
        ->assertNotified();

    expect(DB::table('sessions')->where('id', 'other_session_id')->exists())->toBeFalse();
});
