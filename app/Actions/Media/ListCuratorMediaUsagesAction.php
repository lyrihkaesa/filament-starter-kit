<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMedia;
use App\Models\CuratorMediaUsage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Throwable;

// @codeCoverageIgnoreStart
final readonly class ListCuratorMediaUsagesAction
{
    /**
     * @return Collection<int, array{
     *     model_type: string,
     *     model_label: string,
     *     model_id: string,
     *     field_name: string,
     *     actions: array<int, array{label: string, url: string, icon: string}>
     * }>
     */
    public function handle(CuratorMedia $media): Collection
    {
        return $media->usages()
            ->select(['model_type', 'model_id', 'field_name'])
            ->latest()
            ->get()
            ->map(function (CuratorMediaUsage $usage): array {
                $modelType = (string) $usage->model_type;
                $modelId = (string) $usage->model_id;

                /** @var class-string<Model> $modelClass */
                $modelClass = Relation::getMorphedModel($modelType) ?? $modelType;

                return [
                    'model_type' => $modelType,
                    'model_label' => $this->resolveModelLabel($modelClass),
                    'model_id' => $modelId,
                    'field_name' => (string) $usage->field_name,
                    'actions' => $this->resolveActions($modelClass, $modelId),
                ];
            })
            ->values();
    }

    /**
     * @param  class-string  $modelClass
     */
    private function resolveModelLabel(string $modelClass): string
    {
        return match ($modelClass) {
            User::class => 'User',
            Post::class => 'Post',
            default => class_basename($modelClass),
        };
    }

    /**
     * @param  class-string  $modelClass
     * @return array<int, array{label: string, url: string, icon: string}>
     */
    private function resolveActions(string $modelClass, string $modelId): array
    {
        $actions = [];
        $authUser = auth()->user();

        try {
            if ($modelClass === User::class) {
                $userRecord = User::query()->find($modelId);

                // 1. Opsi Edit User (Jika punya role/permission via Policy)
                if ($userRecord && $authUser?->can('update', $userRecord)) {
                    $actions[] = [
                        'label' => __('Edit User'),
                        'url' => route('filament.app.resources.users.edit', ['record' => $modelId]),
                        'icon' => 'heroicon-o-pencil-square',
                    ];
                }

                // 2. Opsi Edit Profile (Jika itu record dirinya sendiri)
                if ($authUser?->id === $modelId) {
                    $actions[] = [
                        'label' => __('Edit Profile'),
                        'url' => route('filament.app.auth.profile'),
                        'icon' => 'heroicon-o-user',
                    ];
                }

                return $actions;
            }

            if ($modelClass === Post::class) {
                $postRecord = Post::query()->find($modelId);

                if ($postRecord && $authUser?->can('update', $postRecord)) {
                    $actions[] = [
                        'label' => __('Edit Post'),
                        'url' => route('filament.app.resources.posts.edit', ['record' => $modelId]),
                        'icon' => 'heroicon-o-pencil-square',
                    ];
                }

                return $actions;
            }
        } catch (Throwable) {
            // Skip action if route or permission check fails
        }

        return $actions;
    }
}

// @codeCoverageIgnoreEnd
