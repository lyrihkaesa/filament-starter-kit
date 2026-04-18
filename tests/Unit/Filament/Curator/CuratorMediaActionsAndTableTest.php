<?php

declare(strict_types=1);

use App\Actions\Media\DeleteCuratorMediaAction as DeleteCuratorMediaRecordAction;
use App\Filament\Curator\Actions\CuratorMediaDeleteAction;
use App\Filament\Curator\Actions\CuratorMediaDeleteBulkAction;
use App\Filament\Curator\MediaTable;
use App\Filament\Pages\Media\EditMedia;
use App\Models\CuratorMedia;
use App\Models\Permission;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Exceptions\Cancel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::findOrCreate('Delete:CuratorMedia');
    Permission::findOrCreate('DeleteOwn:CuratorMedia');
    Permission::findOrCreate('DeleteUsed:CuratorMedia');
});

function markMediaAsInUse(CuratorMedia $media, User $user): void
{
    $media->usages()->create([
        'curator_media_id' => (string) $media->id,
        'model_id' => (string) $user->id,
        'model_type' => $user->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);
}

function getBulkDeleteBeforeHook(DeleteBulkAction $action): Closure
{
    $reflection = new ReflectionObject($action);
    $property = $reflection->getProperty('before');

    /** @var Closure $before */
    $before = $property->getValue($action);

    return $before;
}

it('builds curator media delete action', function (): void {
    expect(CuratorMediaDeleteAction::make())->toBeInstanceOf(DeleteAction::class);
});

it('evaluates curator media delete authorization rules', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('DeleteOwn:CuratorMedia');

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');

    $other = User::factory()->create();
    $other->givePermissionTo('DeleteOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);

    auth()->logout();
    expect(CuratorMediaDeleteAction::make()->record($media)->isAuthorized())->toBeFalse();

    $this->actingAs($admin);
    expect(CuratorMediaDeleteAction::make()->record($media)->isAuthorized())->toBeTrue();

    $this->actingAs($owner);
    expect(CuratorMediaDeleteAction::make()->record($media)->isAuthorized())->toBeTrue();

    $this->actingAs($other);
    expect(CuratorMediaDeleteAction::make()->record($media)->isAuthorized())->toBeFalse();
});

it('derives disabled state tooltip and modal description from usage state', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('DeleteOwn:CuratorMedia');
    $this->actingAs($owner);

    $unused = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);
    $used = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);
    $user = User::factory()->create();

    markMediaAsInUse($used, $user);

    $usedAction = CuratorMediaDeleteAction::make()->record($used);
    $unusedAction = CuratorMediaDeleteAction::make()->record($unused);

    expect($usedAction->isDisabled())->toBeTrue()
        ->and($usedAction->getTooltip())->toBe($used->getDeletionBlockedMessage())
        ->and($usedAction->getModalDescription())->toBe($used->getDeletionBlockedMessage());

    expect($unusedAction->isDisabled())->toBeFalse()
        ->and($unusedAction->getTooltip())->toBeNull()
        ->and($unusedAction->getModalDescription())->toBe('Are you sure you want to delete this media?');
});

it('keeps used media delete action enabled for admin users', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');
    $admin->givePermissionTo('DeleteUsed:CuratorMedia');
    $this->actingAs($admin);

    $used = CuratorMedia::factory()->create();
    $user = User::factory()->create();
    markMediaAsInUse($used, $user);

    $action = CuratorMediaDeleteAction::make()->record($used);

    expect($action->isDisabled())->toBeFalse()
        ->and($action->getTooltip())->toBeNull();
});

it('uses custom delete callback to delete media records', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');
    $this->actingAs($admin);

    $media = CuratorMedia::factory()->create();
    $action = CuratorMediaDeleteAction::make()->record($media);

    $result = $action->process(null, [
        'record' => $media,
        'deleteMediaAction' => new DeleteCuratorMediaRecordAction(),
    ]);

    expect($result)->toBeTrue()
        ->and($media->fresh()->trashed())->toBeTrue();
});

it('builds curator media delete bulk action', function (): void {
    expect(CuratorMediaDeleteBulkAction::make())->toBeInstanceOf(DeleteBulkAction::class);
});

it('evaluates curator media bulk delete authorization rules', function (): void {
    auth()->logout();
    expect(CuratorMediaDeleteBulkAction::make()->isAuthorized())->toBeFalse();

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');
    $this->actingAs($admin);
    expect(CuratorMediaDeleteBulkAction::make()->isAuthorized())->toBeTrue();

    $owner = User::factory()->create();
    $owner->givePermissionTo('DeleteOwn:CuratorMedia');
    $this->actingAs($owner);
    expect(CuratorMediaDeleteBulkAction::make()->isAuthorized())->toBeTrue();
});

it('cancels bulk deletion when selected records are in use', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('DeleteOwn:CuratorMedia');
    $this->actingAs($owner);

    $action = CuratorMediaDeleteBulkAction::make();
    $before = getBulkDeleteBeforeHook($action);

    $usedMedia = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);
    markMediaAsInUse($usedMedia, $owner);

    expect(fn () => $before($action, collect([$usedMedia])))->toThrow(Cancel::class);
});

it('allows bulk deletion of used media for admin users', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');
    $admin->givePermissionTo('DeleteUsed:CuratorMedia');
    $this->actingAs($admin);

    $action = CuratorMediaDeleteBulkAction::make();
    $before = getBulkDeleteBeforeHook($action);

    $usedMedia = CuratorMedia::factory()->create();
    markMediaAsInUse($usedMedia, $admin);

    expect($before($action, collect([$usedMedia])))->toBeNull();
});

it('continues bulk deletion when selected records are unused', function (): void {
    $action = CuratorMediaDeleteBulkAction::make();
    $before = getBulkDeleteBeforeHook($action);

    $unusedMedia = CuratorMedia::factory()->create();

    expect($before($action, collect([$unusedMedia])))->toBeNull();
});

it('configures media table with custom action classes', function (): void {
    $livewire = mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->layoutView = 'table';

    $table = mock(Table::class);
    $table->shouldReceive('getLivewire')->once()->andReturn($livewire);
    $table->shouldReceive('columns')->andReturnSelf();
    $table->shouldReceive('searchable')->andReturnSelf();
    $table->shouldReceive('recordActions')->andReturnSelf();
    $table->shouldReceive('toolbarActions')->andReturnSelf();
    $table->shouldReceive('defaultSort')->andReturnSelf();
    $table->shouldReceive('contentGrid')->andReturnSelf();
    $table->shouldReceive('defaultPaginationPageOption')->andReturnSelf();
    $table->shouldReceive('paginationPageOptions')->andReturnSelf();
    $table->shouldReceive('recordUrl')->andReturnSelf();

    expect(MediaTable::configure($table))->toBe($table);
});

it('injects usage column in media table defaults', function (): void {
    $columns = MediaTable::getDefaultTableColumns();

    /** @var TextColumn $usageColumn */
    $usageColumn = $columns[4];

    expect($usageColumn)->toBeInstanceOf(TextColumn::class)
        ->and($usageColumn->getName())->toBe('usages_count')
        ->and($usageColumn->isBadge())->toBeTrue()
        ->and($usageColumn->getColor(2))->toBe('warning')
        ->and($usageColumn->getColor(0))->toBe('gray');
});

it('defines edit media header actions including preview and delete', function (): void {
    $media = CuratorMedia::factory()->create([
        'visibility' => 'public',
    ]);

    $page = resolve(EditMedia::class);
    $page->record = $media;

    $actions = $page->getHeaderActions();

    expect($actions)->toHaveCount(4)
        ->and($actions[0]->getName())->toBe('save')
        ->and($actions[1]->getName())->toBe('preview')
        ->and($actions[1]->getUrl())->toBe($media->url)
        ->and($actions[2]->getName())->toBe('viewUsages')
        ->and($actions[3]->getName())->toBe('delete')
        ->and($actions[3]->getModalHeading())->toBe('Delete Media');
});
