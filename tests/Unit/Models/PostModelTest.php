<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('assigns authenticated user as author when creating without author id', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $post = Post::query()->create([
        'title' => 'Post Author Autofill',
        'slug' => 'post-author-autofill',
        'content' => 'Post content',
    ]);

    expect($post->author_id)->toBe($user->id);
});
