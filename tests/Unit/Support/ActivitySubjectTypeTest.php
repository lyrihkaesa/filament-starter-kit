<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Support\Activity\ActivitySubjectType;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('returns labels and morph map', function (): void {
    expect(ActivitySubjectType::labels())->toBe([
        ActivitySubjectType::USER => 'User',
        ActivitySubjectType::POST => 'Post',
    ])->and(ActivitySubjectType::morphMap())->toBe([
        ActivitySubjectType::USER => User::class,
        ActivitySubjectType::POST => Post::class,
    ]);
});

it('returns expected database filter values', function (): void {
    expect(ActivitySubjectType::databaseValuesForFilter(null))->toBeEmpty()
        ->and(ActivitySubjectType::databaseValuesForFilter(''))->toBeEmpty()
        ->and(ActivitySubjectType::databaseValuesForFilter(ActivitySubjectType::USER))->toBe([
            ActivitySubjectType::USER,
            User::class,
        ])
        ->and(ActivitySubjectType::databaseValuesForFilter('unknown_type'))->toBe(['unknown_type']);
});

it('returns expected label from database values', function (): void {
    expect(ActivitySubjectType::labelFromDatabase(null))->toBe('Unknown')
        ->and(ActivitySubjectType::labelFromDatabase(''))->toBe('Unknown')
        ->and(ActivitySubjectType::labelFromDatabase(ActivitySubjectType::USER))->toBe('User')
        ->and(ActivitySubjectType::labelFromDatabase(User::class))->toBe('User')
        ->and(ActivitySubjectType::labelFromDatabase(ActivitySubjectType::POST))->toBe('Post')
        ->and(ActivitySubjectType::labelFromDatabase('App\\Models\\CustomModel'))->toBe('Custom Model');
});
