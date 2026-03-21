<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can list posts', function () {
    Post::factory()->count(10)->create();

    Livewire::test(ListPosts::class)
        ->assertCanSeeTableRecords(Post::limit(10)->get());
});

it('can create posts', function () {
    $user = User::factory()->create();

    Livewire::test(CreatePost::class)
        ->set('data.title', 'New Post')
        ->set('data.slug', 'new-post')
        ->set('data.content', 'Post content')
        ->set('data.is_published', true)
        ->set('data.author_id', $user->id)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'title' => 'New Post',
        'slug' => 'new-post',
        'content' => 'Post content',
        'is_published' => true,
        'author_id' => $user->id,
    ]);
});

it('can update posts', function () {
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

it('can delete posts from table', function () {
    $post = Post::factory()->create();

    Livewire::test(ListPosts::class)
        ->callTableAction(DeleteAction::class, $post);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

it('can delete posts from edit page', function () {
    $post = Post::factory()->create();

    Livewire::test(EditPost::class, [
        'record' => $post->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});
