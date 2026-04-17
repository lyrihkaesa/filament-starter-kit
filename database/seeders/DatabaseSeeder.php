<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('testing')) {
            $this->call([
                TestingShieldSeeder::class,
                PostSeeder::class,
            ]);

            return;
        }

        $this->call([
            ShieldSeeder::class,
            PostSeeder::class,
        ]);
    }
}
