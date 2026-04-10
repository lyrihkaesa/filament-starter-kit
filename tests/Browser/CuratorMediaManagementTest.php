<?php

declare(strict_types=1);

use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use App\Enums\Privacy;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ([
        'ViewAny:CuratorMedia',
        'View:CuratorMedia',
        'ViewOwn:CuratorMedia',
        'Create:CuratorMedia',
        'Update:CuratorMedia',
        'DeleteOwn:CuratorMedia',
    ] as $permission) {
        Permission::findOrCreate($permission);
    }
});

it('shows curator upload form via browser', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'ViewAny:CuratorMedia',
        'View:CuratorMedia',
        'Create:CuratorMedia',
        'Update:CuratorMedia',
    ]);

    $this->actingAs($user);

    $page = $this->visit('/app/media/create');
    $page->assertPathIs('/app/media/create')
        ->assertPresent('input[type="file"]');
});

it('disables delete action in browser when media is in use for non admin users', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo([
        'ViewAny:CuratorMedia',
        'ViewOwn:CuratorMedia',
        'Update:CuratorMedia',
        'DeleteOwn:CuratorMedia',
    ]);

    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
        'disk' => 'public',
        'visibility' => 'public',
        'privacy' => Privacy::PUBLIC,
        'path' => 'uploads/browser-media-used.jpg',
    ]);

    Storage::disk('public')->put($media->path, 'browser-test');

    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->id,
    ]);

    $media->usages()->create([
        'curator_media_id' => $media->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    $this->actingAs($owner);

    $page = $this->visit("/app/media/{$media->getKey()}/edit");
    $page->assertPathIs("/app/media/{$media->getKey()}/edit");

    $isDeleteDisabled = $page->script(<<<'JS'
(() => {
  const deleteButton = Array.from(document.querySelectorAll('button'))
    .find((button) => button.textContent?.trim().includes('Delete'));

  if (!deleteButton) {
    return null;
  }

  return deleteButton.matches(':disabled') || deleteButton.getAttribute('aria-disabled') === 'true';
})()
JS
    );

    expect($isDeleteDisabled)->toBeTrue();
});
