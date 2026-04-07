<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\Media\EditMedia;
use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use Awcodes\Curator\Resources\Media\Pages\ListMedia;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $user = User::factory()->create();

    Permission::findOrCreate('ViewAny:CuratorMedia');
    Permission::findOrCreate('View:CuratorMedia');
    Permission::findOrCreate('Update:CuratorMedia');
    Permission::findOrCreate('Delete:CuratorMedia');

    $user->givePermissionTo(['ViewAny:CuratorMedia', 'View:CuratorMedia', 'Update:CuratorMedia', 'Delete:CuratorMedia']);

    $this->actingAs($user);
});

it('disables deleting used curator media from the table', function (): void {
    $media = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->getKey(),
    ]);

    $media->usages()->create([
        'model_id' => $post->getKey(),
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    Livewire::test(ListMedia::class)
        ->assertTableActionDisabled(DeleteAction::class, $media);
});

it('disables deleting used curator media from the edit page', function (): void {
    $media = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->getKey(),
    ]);

    $media->usages()->create([
        'model_id' => $post->getKey(),
        'model_type' => $post->getMorphClass(),
        'field_name' => 'thumbnail_curator_id',
    ]);

    $page = app(EditMedia::class);
    $page->record = $media;

    expect($page->getSubheading())->toBe($media->getDeletionBlockedMessage());
});
