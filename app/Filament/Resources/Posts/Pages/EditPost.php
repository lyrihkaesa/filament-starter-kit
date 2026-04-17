<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Actions\Posts\DeletePostAction;
use App\Actions\Posts\UpdatePostAction;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(fn (Post $record, DeletePostAction $deletePostAction): bool => $deletePostAction->handle($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Post $record */
        /** @var array{title?: string, slug?: string, content?: string, author_id?: string, thumbnail_curator_id?: string|null, published_at?: string|null} $data */
        return resolve(UpdatePostAction::class)->handle($record, $data);
    }
}
