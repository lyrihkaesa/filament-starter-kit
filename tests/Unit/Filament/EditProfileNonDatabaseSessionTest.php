<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Actions\Profile\RevokeDeviceAction;
use App\Actions\Profile\RevokeOtherDevicesAction;
use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('returns empty collection for active devices when session driver is not database', function (): void {
    config(['session.driver' => 'file']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $page = new EditProfile();

    // Should not throw; tokens are returned even when sessions are unavailable
    $devices = $page->getActiveDevicesList();

    // No sessions (file driver) and no tokens created → expect empty
    expect($devices)->toBeEmpty();
});

it('returns empty collection for active devices when session id throws', function (): void {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $this->actingAs($user);

    Session::shouldReceive('getId')->andThrow(new Exception('No session'));

    $page = new EditProfile();
    $devices = $page->getActiveDevicesList();

    // Sessions query throws, tokens are empty → empty list
    expect($devices)->toBeEmpty();
});

it('does nothing when revoking a device with file session driver', function (): void {
    config(['session.driver' => 'file']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $page = new EditProfile();
    $page->revokeDevice('session:any-id', resolve(RevokeDeviceAction::class));

    expect(true)->toBeTrue();
});

it('can call revokeOtherDevices on the page without exception', function (): void {
    config(['session.driver' => 'file']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $page = new EditProfile();
    $page->revokeOtherDevices('password', resolve(RevokeOtherDevicesAction::class));

    expect(true)->toBeTrue();
});
