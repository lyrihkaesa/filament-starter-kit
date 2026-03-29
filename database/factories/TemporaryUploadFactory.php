<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemporaryUpload>
 */
final class TemporaryUploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => $this->faker->uuid(),
            'disk' => 'uploads_tmp',
            'path' => 'tmp/uploads/profile_avatar/'.$this->faker->uuid().'.jpg',
            'file_name' => 'avatar.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'purpose' => 'profile_avatar',
            'status' => 'prepared',
            'final_visibility' => 'public',
        ];
    }
}
