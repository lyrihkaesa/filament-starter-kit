<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\CuratorMedia;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);
    $this->actingAs($user);
});

it('can list users', function (): void {
    User::factory()->count(10)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords(User::query()->limit(10)->get());
});

it('can create users using action class', function (): void {
    Livewire::test(CreateUser::class)
        ->set('data.name', 'New User')
        ->set('data.email', 'new@example.com')
        ->set('data.password', 'password')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'new@example.com',
    ]);
});

it('can create users with direct avatar upload that becomes curator media', function (): void {
    $avatarDisk = config()->string('curator.default_disk');

    Storage::fake($avatarDisk);
    $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);

    Livewire::test(CreateUser::class)
        ->set('data.name', 'Curator User')
        ->set('data.email', 'curator@example.com')
        ->set('data.password', 'password')
        ->set('data.avatar_upload', $avatar)
        ->call('create')
        ->assertHasNoFormErrors();

    $createdUser = User::query()->where('email', 'curator@example.com')->firstOrFail();
    $media = CuratorMedia::query()->findOrFail($createdUser->avatar_curator_id);

    $this->assertDatabaseHas('users', [
        'email' => 'curator@example.com',
        'avatar_curator_id' => $media->getKey(),
    ]);
    Storage::disk($avatarDisk)->assertExists($media->path);
});

it('can update users using action class', function (): void {
    $user = User::factory()->create();
    $updatedName = 'Updated Name';

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->set('data.name', $updatedName)
        ->set('data.password', 'password')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe($updatedName);
});

it('can update users with direct avatar upload that becomes curator media', function (): void {
    $avatarDisk = config()->string('curator.default_disk');

    Storage::fake($avatarDisk);
    $user = User::factory()->create();
    $avatar = UploadedFile::fake()->image('updated-avatar.jpg', 500, 500);

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->set('data.avatar_upload', $avatar)
        ->set('data.password', 'password')
        ->call('save')
        ->assertHasNoFormErrors();

    $updatedUser = $user->refresh();
    $media = CuratorMedia::query()->findOrFail($updatedUser->avatar_curator_id);

    expect($updatedUser->avatar_curator_id)->toBe($media->getKey());
    Storage::disk($avatarDisk)->assertExists($media->path);
});

it('can delete users using action class from table', function (): void {
    $user = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->callTableAction(DeleteAction::class, $user);

    $this->assertSoftDeleted($user);
});

it('can delete users using action class from edit page', function (): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted($user);
});
