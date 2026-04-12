<?php

declare(strict_types=1);

use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function grantPostApiPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $user->givePermissionTo($permissions);
}

it('returns paginated posts with typed metadata and capabilities', function (): void {
    $admin = User::factory()->create();
    grantPostApiPermissions($admin, ['ViewAny:Post', 'View:Post', 'Create:Post']);
    Post::factory()->count(8)->create();

    Sanctum::actingAs($admin, ['posts:read', 'posts:create']);

    $response = $this->getJson('/api/v1/posts?per_page=3');

    $response->assertSuccessful();

    expect($response->json('meta.pagination_type'))->toBe('page')
        ->and($response->json('meta.per_page'))->toBeInt()
        ->and($response->json('meta.total'))->toBeInt()
        ->and($response->json('meta.can.create'))->toBeTrue()
        ->and($response->json('data.0.id'))->toBeString()
        ->and($response->json('data.0.can.view'))->toBeTrue()
        ->and($response->json('data.0.can.update'))->toBeFalse()
        ->and($response->json('data.0.can.delete'))->toBeFalse();
});

it('creates post via api and syncs curator media usage through actions', function (): void {
    $admin = User::factory()->create();
    $author = User::factory()->create();
    $media = CuratorMedia::factory()->create();

    grantPostApiPermissions($admin, ['Create:Post', 'View:Post']);
    Sanctum::actingAs($admin, ['posts:create', 'posts:read']);

    $response = $this->postJson('/api/v1/posts', [
        'title' => 'API Created Post',
        'slug' => 'api-created-post',
        'content' => '<p>Post API content</p>',
        'author_id' => $author->id,
        'thumbnail_curator_id' => $media->id,
    ]);

    $response->assertCreated();

    $postId = (string) $response->json('data.id');

    expect($response->json('message'))->toBe('Post created successfully.')
        ->and($response->json('data.thumbnail_curator_id'))->toBe($media->id);

    $this->assertDatabaseHas('curator_media_usages', [
        'curator_media_id' => $media->id,
        'model_id' => $postId,
        'model_type' => (new Post())->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});

it('updates post thumbnail via api and resyncs media usage', function (): void {
    $admin = User::factory()->create();
    $author = User::factory()->create();
    $oldMedia = CuratorMedia::factory()->create();
    $newMedia = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'author_id' => $author->id,
        'thumbnail_curator_id' => $oldMedia->id,
    ]);

    // Existing usage
    $post->thumbnailCurator?->usages()->create([
        'curator_media_id' => $oldMedia->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    grantPostApiPermissions($admin, ['Update:Post', 'View:Post']);
    Sanctum::actingAs($admin, ['posts:update', 'posts:read']);

    $response = $this->patchJson('/api/v1/posts/'.$post->id, [
        'thumbnail_curator_id' => $newMedia->id,
    ]);

    $response->assertSuccessful();

    expect($response->json('message'))->toBe('Post updated successfully.')
        ->and($response->json('data.thumbnail_curator_id'))->toBe($newMedia->id);

    $this->assertDatabaseHas('curator_media_usages', [
        'curator_media_id' => $newMedia->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});

it('deletes post via api and removes media usage records', function (): void {
    $admin = User::factory()->create();
    $media = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->id,
    ]);

    $media->usages()->create([
        'curator_media_id' => $media->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    grantPostApiPermissions($admin, ['Delete:Post']);
    Sanctum::actingAs($admin, ['posts:delete']);

    $response = $this->deleteJson('/api/v1/posts/'.$post->id);

    $response->assertSuccessful();

    expect($response->json())->toBe([
        'message' => 'Post deleted successfully.',
    ]);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);

    $this->assertDatabaseMissing('curator_media_usages', [
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});
