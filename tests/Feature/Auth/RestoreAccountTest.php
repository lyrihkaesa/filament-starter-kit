<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use App\Notifications\Auth\RestoreAccountNotification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use function Pest\Livewire\livewire;

it('shows restore hint when attempting to login with soft-deleted account', function () {
    $user = User::factory()->create();
    $user->delete();

    livewire(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['data.email'])
        ->assertSee(__('auth.deleted'))
        ->assertSet('showRestoreAccountHint', true);
});

it('can request account restoration', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->delete();

    livewire(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'password')
        ->call('authenticate') // Trigger hint
        ->call('requestRestoreAccount')
        ->assertNotified(__('auth.restore_requested'))
        ->assertSet('showRestoreAccountHint', false);

    Notification::assertSentTo($user, RestoreAccountNotification::class);
});

it('can restore account via signed url', function () {
    $user = User::factory()->create();
    $user->delete();

    $url = URL::temporarySignedRoute(
        'restore-account',
        now()->addMinutes(60),
        ['id' => $user->id]
    );

    $this->get($url)
        ->assertRedirect(Filament::getLoginUrl());

    expect($user->fresh()->deleted_at)->toBeNull();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});

it('cannot restore account with invalid signature', function () {
    $user = User::factory()->create();
    $user->delete();

    $url = URL::route('restore-account', ['id' => $user->id]);

    $this->get($url)
        ->assertForbidden();

    expect($user->fresh()->trashed())->toBeTrue();
});
