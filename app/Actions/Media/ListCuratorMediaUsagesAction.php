<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMedia;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Throwable;

final readonly class ListCuratorMediaUsagesAction
{
    /**
     * @return Collection<int, array{
     *     model_type: string,
     *     model_label: string,
     *     model_id: string,
     *     field_name: string,
     *     record_url: string|null
     * }>
     */
    public function handle(CuratorMedia $media): Collection
    {
        return $media->usages()
            ->select(['model_type', 'model_id', 'field_name'])
            ->latest()
            ->get()
            ->map(function ($usage): array {
                $modelType = (string) $usage->model_type;
                $modelId = (string) $usage->model_id;

                return [
                    'model_type' => $modelType,
                    'model_label' => $this->resolveModelLabel($modelType),
                    'model_id' => $modelId,
                    'field_name' => (string) $usage->field_name,
                    'record_url' => $this->resolveRecordUrl($modelType, $modelId),
                ];
            })
            ->values();
    }

    private function resolveModelLabel(string $modelType): string
    {
        return match ($modelType) {
            User::class => 'User',
            Post::class => 'Post',
            default => class_basename($modelType),
        };
    }

    private function resolveRecordUrl(string $modelType, string $modelId): ?string
    {
        try {
            return match ($modelType) {
                User::class => route('filament.app.resources.users.edit', ['record' => $modelId]),
                Post::class => route('filament.app.resources.posts.edit', ['record' => $modelId]),
                default => null,
            };
        } catch (Throwable) {
            return null;
        }
    }
}
