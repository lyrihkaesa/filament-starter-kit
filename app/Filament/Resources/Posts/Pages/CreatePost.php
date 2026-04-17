<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Actions\Posts\CreatePostAction;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var array{title: string, slug: string, content: string, author_id: string, thumbnail_curator_id?: string|null, published_at?: string|null} $data */
        return resolve(CreatePostAction::class)->handle($data);
    }
}
