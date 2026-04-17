<?php

declare(strict_types=1);

use App\Actions\Media\CheckMediaUsageAction;
use App\Actions\Media\DeleteCuratorMediaAction;
use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Reset permissions cache before testing
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->memberRole = Role::query()->firstOrCreate(['name' => 'member']);
    $this->memberRole->syncPermissions([
        Permission::query()->firstOrCreate(['name' => 'ViewOwn:Post']),
        Permission::query()->firstOrCreate(['name' => 'Create:Post']),
        Permission::query()->firstOrCreate(['name' => 'UpdateOwn:Post']),
        Permission::query()->firstOrCreate(['name' => 'DeleteOwn:Post']),
    ]);

    $this->adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
    $this->adminRole->syncPermissions([
        Permission::query()->firstOrCreate(['name' => 'View:Post']),
        Permission::query()->firstOrCreate(['name' => 'Create:Post']),
        Permission::query()->firstOrCreate(['name' => 'Update:Post']),
        Permission::query()->firstOrCreate(['name' => 'Delete:Post']),
        Permission::query()->firstOrCreate(['name' => 'Delete:CuratorMedia']),
        // Intentionally omitting DeleteUsed:CuratorMedia for admin testing
    ]);
});

it('allows member to manage only their own posts', function (): void {
    $member1 = User::factory()->create();
    $member1->assignRole($this->memberRole);

    $member2 = User::factory()->create();
    $member2->assignRole($this->memberRole);

    actingAs($member1);

    $post1 = Post::factory()->create(['author_id' => $member1->id]);
    $post2 = Post::factory()->create(['author_id' => $member2->id]);

    expect($member1->can('update', $post1))->toBeTrue()
        ->and($member1->can('update', $post2))->toBeFalse()
        ->and($member1->can('delete', $post1))->toBeTrue()
        ->and($member1->can('delete', $post2))->toBeFalse();
});

it('syncs media usage automatically when post is saved with a thumbnail', function (): void {
    $member = User::factory()->create();
    $member->assignRole($this->memberRole);
    actingAs($member);

    // Assuming CuratorMediaFactory exists
    $media = CuratorMedia::query()->forceCreate([
        'disk' => 'public',
        'directory' => 'media',
        'visibility' => 'public',
        'name' => 'test-image',
        'path' => 'media/test-image.jpg',
        'width' => 100,
        'height' => 100,
        'size' => 1000,
        'type' => 'image/jpeg',
        'ext' => 'jpg',
        'alt' => 'test',
        'title' => 'test',
        'description' => 'test',
        'caption' => 'test',
        'exif' => [],
        'curations' => [],
    ]);

    $post = Post::factory()->create(['author_id' => $member->id]);
    $post->update(['thumbnail_curator_id' => $media->id]);

    $isInUse = resolve(CheckMediaUsageAction::class)->handle((string) $media->id);
    expect($isInUse)->toBeTrue();
});

it('prevents user from deleting media if it is in use and they lack bypass permission', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole($this->adminRole);
    actingAs($admin);

    $media = CuratorMedia::query()->forceCreate([
        'disk' => 'public',
        'directory' => 'media',
        'visibility' => 'public',
        'name' => 'test-image',
        'path' => 'media/test-image.jpg',
        'width' => 100,
        'height' => 100,
        'size' => 1000,
        'type' => 'image/jpeg',
        'ext' => 'jpg',
        'alt' => 'test',
        'title' => 'test',
        'description' => 'test',
        'caption' => 'test',
        'exif' => [],
        'curations' => [],
    ]);

    $post = Post::factory()->create();
    $post->update(['thumbnail_curator_id' => $media->id]);

    $action = resolve(DeleteCuratorMediaAction::class);

    // Attempt delete without explicit bypass allowed
    $result = $action->handle($media, (string) $admin->id, false);

    expect($result)->toBeFalse()
        ->and($media->refresh()->exists)->toBeTrue();
});

it('allows deletion of media in use if explicitly bypassed via permission', function (): void {
    $admin = User::factory()->create();
    $this->adminRole->givePermissionTo(Permission::query()->firstOrCreate(['name' => 'DeleteUsed:CuratorMedia']));
    $admin->assignRole($this->adminRole);
    actingAs($admin);

    $media = CuratorMedia::query()->forceCreate([
        'disk' => 'public',
        'directory' => 'media',
        'visibility' => 'public',
        'name' => 'test-image',
        'path' => 'media/test-image.jpg',
        'width' => 100,
        'height' => 100,
        'size' => 1000,
        'type' => 'image/jpeg',
        'ext' => 'jpg',
        'alt' => 'test',
        'title' => 'test',
        'description' => 'test',
        'caption' => 'test',
        'exif' => [],
        'curations' => [],
    ]);

    $post = Post::factory()->create();
    $post->update(['thumbnail_curator_id' => $media->id]);

    $action = resolve(DeleteCuratorMediaAction::class);

    // Pass true because the action class gets 'true' from the Filament Action if user has permission
    $result = $action->handle($media, (string) $admin->id, true);

    expect($result)->toBeTrue();
});
