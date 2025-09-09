<?php

declare(strict_types=1);

use App\Models\User;

it('user canImpersonate returns true by default', function (): void {
    $user = new User();
    expect($user->canImpersonate())->toBeTrue();
});

it('user canBeImpersonated returns true by default', function (): void {
    $user = new User();
    expect($user->canBeImpersonated())->toBeTrue();
});

