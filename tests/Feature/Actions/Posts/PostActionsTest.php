<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Posts;

use App\Actions\Posts\CreatePostAction;
use App\Actions\Posts\DeletePostAction;
use App\Actions\Posts\UpdatePostAction;
use App\Models\CuratorMedia;
use App\Models\CuratorMediaUsage;
use App\Models\Post;
use App\Models\User;

it('creates post and syncs thumbnail media usage via action', function (): void {
    $author = User::factory()->create();
    $media = CuratorMedia::factory()->create();

    $post = resolve(CreatePostAction::class)->handle([
        'title' => 'Action Created Post',
        'slug' => 'action-created-post',
        'content' => '<p>Content</p>',
        'author_id' => $author->id,
        'thumbnail_curator_id' => $media->id,
    ]);

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->thumbnail_curator_id)->toBe($media->id);

    $this->assertDatabaseHas('curator_media_usages', [
        'curator_media_id' => $media->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});

it('updates post and syncs thumbnail media usage via action', function (): void {
    $author = User::factory()->create();
    $oldMedia = CuratorMedia::factory()->create();
    $newMedia = CuratorMedia::factory()->create();

    $post = Post::factory()->create([
        'author_id' => $author->id,
        'thumbnail_curator_id' => $oldMedia->id,
    ]);

    resolve(UpdatePostAction::class)->handle($post, [
        'thumbnail_curator_id' => $newMedia->id,
    ]);

    expect($post->fresh()?->thumbnail_curator_id)->toBe($newMedia->id);

    $this->assertDatabaseHas('curator_media_usages', [
        'curator_media_id' => $newMedia->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});

it('deletes post and removes usages via action', function (): void {
    $post = Post::factory()->create();
    $media = CuratorMedia::factory()->create();

    CuratorMediaUsage::query()->create([
        'curator_media_id' => $media->id,
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    expect(resolve(DeletePostAction::class)->handle($post))->toBeTrue();

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);

    $this->assertDatabaseMissing('curator_media_usages', [
        'model_id' => $post->id,
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});
