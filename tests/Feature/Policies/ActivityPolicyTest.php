<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Policies\ActivityPolicy;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(ShieldSeeder::class);
    $this->policy = new ActivityPolicy();
});

it('allows all activity abilities for super admin', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user))->toBeTrue()
        ->and($this->policy->delete($user))->toBeTrue()
        ->and($this->policy->restore($user))->toBeTrue()
        ->and($this->policy->forceDelete($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->replicate($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});

it('denies all activity abilities without permissions', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user))->toBeFalse()
        ->and($this->policy->delete($user))->toBeFalse()
        ->and($this->policy->restore($user))->toBeFalse()
        ->and($this->policy->forceDelete($user))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});
