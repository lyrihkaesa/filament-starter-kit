<?php

declare(strict_types=1);

use App\Support\Filament\FilamentNavigation;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('returns null when label is null', function (): void {
    expect(FilamentNavigation::sort(null))->toBeNull();
});

it('returns sort order for known labels and null for unknown label', function (): void {
    expect(FilamentNavigation::sort('Content Management'))->toBe(-99)
        ->and(FilamentNavigation::sort('System Management'))->toBe(-98)
        ->and(FilamentNavigation::sort('Post'))->toBe(-97)
        ->and(FilamentNavigation::sort('Not Existing'))->toBeNull();
});
