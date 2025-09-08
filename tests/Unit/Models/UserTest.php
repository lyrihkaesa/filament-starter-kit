<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reports non-anonymous by default', function () {
    $user = User::factory()->create();

    expect($user->isAnonymous())->toBeFalse();
});

it('reports anonymous when anonymized_at set', function () {
    $user = User::factory()->create([
        'anonymized_at' => now(),
    ]);

    expect($user->isAnonymous())->toBeTrue();
});
