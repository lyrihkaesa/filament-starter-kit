<?php

declare(strict_types=1);

use App\Filament\Resources\Posts\Pages\ViewPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('can view post page', function (): void {
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);
    $post = Post::factory()->create();

    $this->actingAs($user);

    livewire(ViewPost::class, [
        'record' => $post->id,
    ])
        ->assertSuccessful();
});
