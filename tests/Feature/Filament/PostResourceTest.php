<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\CuratorMedia;
use App\Models\CuratorMediaUsage;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);
    $this->actingAs($user);
});

it('can list posts', function (): void {
    Post::factory()->count(10)->create();

    Livewire::test(ListPosts::class)
        ->assertCanSeeTableRecords(Post::query()->limit(10)->get());
});

it('can create posts', function (): void {
    $user = User::factory()->create();
    $publishedAt = now()->startOfMinute();

    Livewire::test(CreatePost::class)
        ->set('data.title', 'New Post')
        ->set('data.slug', 'new-post')
        ->set('data.content', 'Post content')
        ->set('data.published_at', $publishedAt->toDateTimeString())
        ->set('data.author_id', $user->id)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'title' => 'New Post',
        'slug' => 'new-post',
        'content' => '<p>Post content</p>',
        'published_at' => $publishedAt->toDateTimeString(),
        'author_id' => $user->id,
    ]);
});

it('can create posts with curator thumbnail', function (): void {
    $user = User::factory()->create();
    $publishedAt = now()->startOfMinute();
    $media = CuratorMedia::query()->create([
        'disk' => 'public',
        'directory' => 'posts/thumbnails',
        'visibility' => 'public',
        'name' => 'post-thumbnail',
        'path' => 'posts/thumbnails/post-thumbnail.jpg',
        'size' => 2048,
        'type' => 'image/jpeg',
        'ext' => 'jpg',
    ]);

    Livewire::test(CreatePost::class)
        ->set('data.title', 'Post with Curator Thumbnail')
        ->set('data.slug', 'post-with-curator-thumbnail')
        ->set('data.content', 'Post content')
        ->set('data.published_at', $publishedAt->toDateTimeString())
        ->set('data.author_id', $user->id)
        ->set('data.thumbnail_curator_id', [$media->fresh()->toArray()])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'slug' => 'post-with-curator-thumbnail',
        'thumbnail_curator_id' => $media->getKey(),
    ]);

    $this->assertDatabaseHas('curator_media_usages', [
        'curator_media_id' => $media->getKey(),
        'model_id' => Post::query()->where('slug', 'post-with-curator-thumbnail')->value('id'),
        'model_type' => (new Post())->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});

it('can update posts', function (): void {
    $post = Post::factory()->create();
    $updatedTitle = 'Updated Title';

    Livewire::test(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->set('data.title', $updatedTitle)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->title)->toBe($updatedTitle);
});

it('can update posts with curator thumbnail', function (): void {
    $post = Post::factory()->create();
    $media = CuratorMedia::query()->create([
        'disk' => 'public',
        'directory' => 'posts/thumbnails',
        'visibility' => 'public',
        'name' => 'updated-thumbnail',
        'path' => 'posts/thumbnails/updated-thumbnail.jpg',
        'size' => 2048,
        'type' => 'image/jpeg',
        'ext' => 'jpg',
    ]);

    Livewire::test(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->set('data.thumbnail_curator_id', [$media->fresh()->toArray()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->thumbnail_curator_id)->toBe($media->getKey());

    $usage = CuratorMediaUsage::query()
        ->where('model_id', $post->getKey())
        ->where('model_type', $post->getMorphClass())
        ->where('field_name', 'thumbnail_curator_id')
        ->first();

    expect($usage)->not->toBeNull()
        ->and($usage?->curator_media_id)->toBe($media->getKey());
});

it('can delete posts from table', function (): void {
    $media = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->getKey(),
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => $media->getKey(),
        'model_id' => $post->getKey(),
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    Livewire::test(ListPosts::class)
        ->callTableAction(DeleteAction::class, $post);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);

    $this->assertDatabaseMissing('curator_media_usages', [
        'model_id' => $post->getKey(),
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);
});

it('can delete posts from edit page', function (): void {
    $post = Post::factory()->create();

    Livewire::test(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});
