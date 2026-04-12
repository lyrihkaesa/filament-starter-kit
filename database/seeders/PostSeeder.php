<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

final class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! App::environment('local')) {
            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory()->count(5)->create();
        }

        foreach ($users as $user) {
            Post::factory()->count(10)->create([
                'author_id' => $user->id,
            ])->each(function (Post $post) use ($user): void {
                $media = CuratorMedia::factory()->create([
                    'created_by' => $user->id,
                ]);

                $post->update([
                    'thumbnail_curator_id' => $media->id,
                ]);
            });
        }
    }
}
