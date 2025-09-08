<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('soft delete should only fill deleted_at without anonymizing', function () {
    $user = User::factory()->create();

    $user->delete();

    expect($user->fresh()->deleted_at)->not()->toBeNull()
        ->and($user->fresh()->anonymized_at)->toBeNull()
        ->and($user->fresh()->name)->not->toStartWith('Anonymous');
});

it('force delete should anonymize user instead of removing record', function () {
    $user = User::factory()->create([
        'name' => 'Farhan',
        'email' => 'farhan@example.com',
    ]);

    $user->forceDelete();

    $fresh = $user->fresh();

    expect($fresh)->not()->toBeNull()
        ->and($fresh->anonymized_at)->not->toBeNull()
        ->and($fresh->name)->toStartWith('Anonymous')
        ->and($fresh->email)->toStartWith('anonymous');
});
