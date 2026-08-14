<?php

declare(strict_types=1);

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('fills created_by and privacy defaults during creating event', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $media = CuratorMedia::factory()->create([
        'created_by' => null,
        'privacy' => null,
    ]);

    expect($media->created_by)->toBe($user->id)
        ->and($media->privacy)->toBe(Privacy::PRIVATE);
});
