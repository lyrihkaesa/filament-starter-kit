<?php

declare(strict_types=1);

use App\Models\Post;

it('renders default head meta tags in welcome view', function (): void {
    $rendered = view('welcome')->render();
    $appName = (string) config('app.name', 'Laravel');

    expect($rendered)
        ->toContain("<title>{$appName}</title>")
        ->toContain('name="description" content="Filament Starter Kit for Laravel with best practices"')
        ->toContain('property="og:type" content="website"')
        ->toContain('name="robots" content="all"');
});

it('renders head meta tags on posts index page', function (): void {
    $appName = (string) config('app.name', 'Laravel');

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSeeHtml("<title>Posts - {$appName}</title>")
        ->assertSeeHtml('name="description" content="Filament Starter Kit for Laravel with best practices"')
        ->assertSeeHtml('name="robots" content="all"');
});

it('renders post specific head meta tags on post detail page', function (): void {
    $post = Post::factory()->create([
        'title' => 'Building with Laravel Head',
        'slug' => 'building-with-laravel-head',
        'content' => 'Learn how to manage document head and SEO metadata with Laravel Head.',
        'published_at' => now(),
    ]);

    $appName = (string) config('app.name', 'Laravel');

    $this->get(route('posts.show', ['post' => $post->slug]))
        ->assertSuccessful()
        ->assertSeeHtml("<title>Building with Laravel Head - {$appName}</title>")
        ->assertSeeHtml('property="og:type" content="article"')
        ->assertSeeHtml('property="og:title" content="Building with Laravel Head"')
        ->assertSeeHtml('name="description" content="Learn how to manage document head and SEO metadata with Laravel Head."');
});
