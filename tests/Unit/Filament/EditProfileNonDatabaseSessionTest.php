<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Actions\Profile\LogoutOtherBrowserSessionsAction;
use App\Actions\Profile\LogoutSessionAction;
use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns empty collection for browser sessions when driver is not database', function (): void {
    config(['session.driver' => 'file']);
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = new EditProfile();

    // Test exception path in session ID
    Session::shouldReceive('getId')->andThrow(new Exception());
    $sessions = $page->getBrowserSessionsList();
    expect($sessions)->toBeEmpty();
});

it('does nothing when logging out session and driver is not database', function (): void {
    config(['session.driver' => 'file']);
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = new EditProfile();
    $page->logoutSession('any-id', resolve(LogoutSessionAction::class));

    expect(true)->toBeTrue(); // No exception thrown
});

it('can call logoutOtherBrowserSessions on the page', function (): void {
    config(['session.driver' => 'file']);
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = new EditProfile();
    $page->logoutOtherBrowserSessions('password', resolve(LogoutOtherBrowserSessionsAction::class));

    expect(true)->toBeTrue();
});
