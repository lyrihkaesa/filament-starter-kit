<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('user canImpersonate returns true by default', function (): void {
    $user = User::factory()->create();
    expect($user->canImpersonate())->toBeTrue();
});

it('user canBeImpersonated returns true by default', function (): void {
    $user = User::factory()->create();
    expect($user->canBeImpersonated())->toBeTrue();
});
