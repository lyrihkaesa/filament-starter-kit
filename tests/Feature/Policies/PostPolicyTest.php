<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Database\Seeders\ShieldSeeder;

beforeEach(function (): void {
    $this->seed(ShieldSeeder::class);
    $this->policy = new PostPolicy();
});

it('can view any post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('can view post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->view($user, $post))->toBeTrue();
});

it('can view own post with own permission only', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('ViewOwn:Post');

    $post = Post::factory()->create([
        'author_id' => $user->id,
    ]);

    expect($this->policy->view($user, $post))->toBeTrue();
});

it('can create post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

it('can update post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->update($user, $post))->toBeTrue();
});

it('can update own post with own permission only', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('UpdateOwn:Post');

    $post = Post::factory()->create([
        'author_id' => $user->id,
    ]);

    expect($this->policy->update($user, $post))->toBeTrue();
});

it('can delete post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->delete($user, $post))->toBeTrue();
});

it('can delete own post with own permission only', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('DeleteOwn:Post');

    $post = Post::factory()->create([
        'author_id' => $user->id,
    ]);

    expect($this->policy->delete($user, $post))->toBeTrue();
});

it('can restore post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->restore($user, $post))->toBeTrue();
});

it('can restore own post with own permission only', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('RestoreOwn:Post');

    $post = Post::factory()->create([
        'author_id' => $user->id,
    ]);

    expect($this->policy->restore($user, $post))->toBeTrue();
});

it('can force delete post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->forceDelete($user, $post))->toBeTrue();
});

it('can force delete own post with own permission only', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('ForceDeleteOwn:Post');

    $post = Post::factory()->create([
        'author_id' => $user->id,
    ]);

    expect($this->policy->forceDelete($user, $post))->toBeTrue();
});

it('can force delete any post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

it('can restore any post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

it('can replicate post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->replicate($user))->toBeTrue();
});

it('can reorder post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->reorder($user))->toBeTrue();
});

it('denies when user has no post permissions', function (): void {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $post = Post::factory()->create([
        'author_id' => $anotherUser->id,
    ]);

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $post))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $post))->toBeFalse()
        ->and($this->policy->delete($user, $post))->toBeFalse()
        ->and($this->policy->restore($user, $post))->toBeFalse()
        ->and($this->policy->forceDelete($user, $post))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});
