<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostgresUuidTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_find_post_by_uuid(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);

        $found = Post::find($post->id);
        $this->assertNotNull($found);
        $this->assertEquals($post->id, $found->id);
    }
}
