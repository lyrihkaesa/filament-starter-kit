<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuratorMedia>
 */
final class CuratorMediaFactory extends Factory
{
    protected $model = CuratorMedia::class;

    public function definition(): array
    {
        return [
            'disk' => 'local',
            'directory' => 'uploads',
            'visibility' => 'private',
            'name' => fake()->uuid(),
            'path' => 'uploads/'.fake()->uuid().'.jpg',
            'width' => 800,
            'height' => 600,
            'size' => 1024,
            'type' => 'image/jpeg',
            'ext' => 'jpg',
            'alt' => null,
            'title' => null,
            'description' => null,
            'caption' => null,
            'exif' => null,
            'curations' => null,
            'created_by' => User::factory(),
            'privacy' => Privacy::PRIVATE,
        ];
    }
}
