<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Actions\Media\UpsertCuratorMediaFromPathAction;
use App\Models\CuratorMedia;
use Closure;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

final class CuratorFileUpload extends FileUpload
{
    private string|Closure|null $relationship = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (self $component, mixed $state): void {
            if (blank($state)) {
                $component->hydrateFromRelationship();

                return;
            }

            $component->hydrateFromCuratorState($state);
        });

        $this->dehydrateStateUsing(fn (): array|int|string|null => $this->dehydrateToCuratorState());
    }

    public static function make(?string $name = null): static
    {
        /** @var static $component */
        $component = parent::make($name);

        if ($name === null) {
            return $component;
        }

        return $component
            ->disk(config()->string('curator.default_disk'))
            ->visibility(config()->string('curator.default_visibility'));
    }

    public function relationship(string|Closure $relationshipName, string|Closure $titleColumnName, ?Closure $callback = null): static
    {
        $this->relationship = $relationshipName;

        return $this;
    }

    private function hydrateFromRelationship(): void
    {
        $relationship = $this->getRelationship();

        if (! ($relationship instanceof BelongsTo)) {
            return;
        }

        $relatedRecord = $relationship->getResults();

        if (! ($relatedRecord instanceof CuratorMedia)) {
            return;
        }

        $this->hydrateFromCuratorState($relatedRecord->getKey());
    }

    private function hydrateFromCuratorState(mixed $state): void
    {
        $ids = array_values(array_filter(Arr::wrap($state), filled(...)));

        if ($ids === []) {
            $this->state(null);
            $this->rawState([]);

            return;
        }

        $paths = CuratorMedia::query()
            ->whereKey($ids)
            ->pluck('path')
            ->filter(static fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();

        if ($paths === []) {
            $this->state(null);
            $this->rawState([]);

            return;
        }

        $this->rawState($paths);
        $this->state($this->isMultiple() ? $paths : $paths[0]);
    }

    /**
     * @return array<int, int|string>|int|string|null
     */
    private function dehydrateToCuratorState(): array|int|string|null
    {
        $paths = array_values(array_filter(
            Arr::wrap($this->getRawState()),
            static fn (mixed $path): bool => is_string($path) && filled($path),
        ));

        if ($paths === []) {
            return null;
        }

        /** @var array<int, int|string> $mediaIds */
        $mediaIds = [];

        foreach ($paths as $path) {
            $media = resolve(UpsertCuratorMediaFromPathAction::class)->handle(
                path: $path,
                originalFileName: basename($path),
                disk: $this->getDiskName(),
                visibility: $this->getVisibility(),
            );

            if (! ($media instanceof CuratorMedia)) {
                continue;
            }

            $key = $media->getKey();

            if (is_int($key) || is_string($key)) {
                $mediaIds[] = $key;
            }
        }

        if ($mediaIds === []) {
            return null;
        }

        if ($this->isMultiple()) {
            return $mediaIds;
        }

        return $mediaIds[0];
    }

    /**
     * @return BelongsTo<CuratorMedia, Model>|null
     */
    private function getRelationship(): ?BelongsTo
    {
        $relationshipName = $this->evaluate($this->relationship);

        if (! is_string($relationshipName) || $relationshipName === '') {
            return null;
        }

        $model = $this->getModelInstance();
        $relationship = $model->{$relationshipName}();

        return $relationship instanceof BelongsTo ? $relationship : null;
    }
}
