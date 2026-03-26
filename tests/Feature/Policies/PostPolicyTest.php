<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('can delete post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->delete($user, $post))->toBeTrue();
});

it('can restore post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

    expect($this->policy->restore($user, $post))->toBeTrue();
});

it('can force delete post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $post = Post::factory()->create();

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

    expect($this->policy->replicate($user, $post))->toBeTrue();
});

it('can reorder post with permission', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->reorder($user))->toBeTrue();
});
