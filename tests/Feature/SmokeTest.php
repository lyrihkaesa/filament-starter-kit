<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;

it('redirects home to login', function (): void {
    $this->get('/')
        ->assertRedirect('/login');
});

it('can visit public pages', function (string $url): void {
    $this->get($url)
        ->assertSuccessful();
})->with([
    'filament login' => '/app/login',
]);

it('can visit authenticated pages', function (string $url): void {
    $user = User::factory()->create();
    $user->assignRole(Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

    $this->actingAs($user)
        ->get($url)
        ->assertSuccessful();
})->with([
    'dashboard' => '/app',
    'users' => '/app/users',
    'posts' => '/app/posts',
    'profile' => '/app/profile',
]);
