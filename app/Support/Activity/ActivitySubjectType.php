<?php

declare(strict_types=1);

namespace App\Support\Activity;

use App\Models\Post;
use App\Models\User;

final readonly class ActivitySubjectType
{
    public const string USER = 'user';

    public const string POST = 'post';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::USER => __('User'),
            self::POST => __('Post'),
        ];
    }

    /**
     * @return array<string, class-string>
     */
    public static function morphMap(): array
    {
        return [
            self::USER => User::class,
            self::POST => Post::class,
        ];
    }

    /**
     * @return list<string>
     */
    public static function databaseValuesForFilter(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $modelClass = self::modelClass($value);

        if ($modelClass === null) {
            return [$value];
        }

        // Keep backward compatibility with historical activity rows
        // that still store FQCN values before morph map aliases were introduced.
        return [$value, $modelClass];
    }

    public static function labelFromDatabase(?string $databaseValue): string
    {
        if ($databaseValue === null || $databaseValue === '') {
            return __('Unknown');
        }

        foreach (self::morphMap() as $alias => $modelClass) {
            if (in_array($databaseValue, [$alias, $modelClass], true)) {
                return self::labels()[$alias];
            }
        }

        return str($databaseValue)->afterLast('\\')->headline()->toString();
    }

    private static function modelClass(string $alias): ?string
    {
        return self::morphMap()[$alias] ?? null;
    }
}
