<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts;

use App\Actions\Media\DeleteAllMediaUsagesAction;
use App\Actions\Media\SyncMediaUsageAction;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Pages\ViewPost;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Schemas\PostInfolist;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view' => ViewPost::route('/{record}'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    /**
     * @param  array{thumbnail_curator_id?: string|null}  $data
     */
    private static function afterCreate(Model $record, array $data): void
    {
        if (! empty($data['thumbnail_curator_id'])) {
            resolve(SyncMediaUsageAction::class)->handle($record, 'thumbnail_curator_id', $data['thumbnail_curator_id']);
        }
    }

    /**
     * @param  array{thumbnail_curator_id?: string|null}  $data
     */
    private static function afterSave(Model $record, array $data): void
    {
        if (array_key_exists('thumbnail_curator_id', $data)) {
            resolve(SyncMediaUsageAction::class)->handle($record, 'thumbnail_curator_id', $data['thumbnail_curator_id'] ?: null);
        }
    }

    private static function afterDelete(Model $record): void
    {
        resolve(DeleteAllMediaUsagesAction::class)->handle($record);
    }
}
