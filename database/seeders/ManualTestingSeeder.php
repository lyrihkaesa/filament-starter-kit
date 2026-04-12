<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

final class ManualTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset Permissions Cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Call Shield Seeder to ensure roles and base users exist
        // Note: ShieldSeeder already creates superadmin@example.com, admin@example.com, and member@example.com
        $this->call(ShieldSeeder::class);

        // 3. Define the roles we want to test
        $roles = ['super_admin', 'admin', 'member'];

        foreach ($roles as $role) {
            // Find the user created by ShieldSeeder
            $email = match ($role) {
                'super_admin' => 'superadmin@example.com',
                'admin' => 'admin@example.com',
                'member' => 'member@example.com',
            };

            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->command->warn("User with email {$email} not found. Skipping for role {$role}.");
                continue;
            }

            // 4. Create Curator Media owned by this user
            // We use the factory which handles the physical and database record
            $media = CuratorMedia::factory()->create([
                'created_by' => $user->id,
                'name' => "Media for " . ucfirst($role),
                'alt' => "Alternative text for " . $role . " media",
            ]);

            // 5. Create Post authored by this user with the media as thumbnail
            // The Post observer will automatically create the record in curator_media_usages
            Post::factory()->create([
                'author_id' => $user->id,
                'title' => "Post created by " . ucfirst($role),
                'thumbnail_curator_id' => $media->id,
                'published_at' => now(),
            ]);

            $this->command->info("Seeded media and post for {$role} ({$email})");
        }

        $this->command->info('Manual Testing Seeder completed successfully!');
    }
}
