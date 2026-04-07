<?php

declare(strict_types=1);

use App\Models\Post;

it('can render posts index page with published posts', function (): void {
    $publishedPost = Post::factory()->create([
        'title' => 'Published Post',
        'slug' => 'published-post',
        'is_published' => true,
    ]);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee($publishedPost->title);
});

it('does not show unpublished posts on index page', function (): void {
    Post::factory()->create([
        'title' => 'Published Post',
        'slug' => 'published-post-index',
        'is_published' => true,
    ]);

    Post::factory()->create([
        'title' => 'Unpublished Post',
        'slug' => 'unpublished-post-index',
        'is_published' => false,
    ]);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('Published Post')
        ->assertDontSee('Unpublished Post');
});

it('can render published post detail page by slug', function (): void {
    $publishedPost = Post::factory()->create([
        'title' => 'Published Post Detail',
        'slug' => 'published-post-detail',
        'is_published' => true,
    ]);

    $this->get(route('posts.show', ['post' => $publishedPost->slug]))
        ->assertSuccessful()
        ->assertSee($publishedPost->title);
});

it('returns 404 for unpublished post detail page', function (): void {
    $unpublishedPost = Post::factory()->create([
        'slug' => 'unpublished-post-detail',
        'is_published' => false,
    ]);

    $this->get(route('posts.show', ['post' => $unpublishedPost->slug]))
        ->assertNotFound();
});
