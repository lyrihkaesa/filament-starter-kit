<?php

declare(strict_types=1);

use Tests\TestCase;

pest()->extend(TestCase::class);

it('stores backups on dedicated local and s3 disks by default', function (): void {
    expect(config('filesystems.disks.backups.root'))->toBe(storage_path('app/backups'))
        ->and(config('backup.backup.destination.disks'))->toBe(['backups', 's3']);
});

it('backs up the active database connection and emails the configured recipient', function (): void {
    expect(config('backup.backup.source.databases'))->toBe([(string) env('DB_CONNECTION', 'sqlite')])
        ->and(config('backup.notifications.mail.to'))->toBe((string) env('MAIL_FROM_ADDRESS', 'hello@example.com'));
});

it('keeps backups for thirty days by default', function (): void {
    expect(config('backup.cleanup.default_strategy.keep_all_backups_for_days'))->toBe(30)
        ->and(config('backup.cleanup.default_strategy.keep_daily_backups_for_days'))->toBe(0)
        ->and(config('backup.cleanup.default_strategy.keep_weekly_backups_for_weeks'))->toBe(0)
        ->and(config('backup.cleanup.default_strategy.keep_monthly_backups_for_months'))->toBe(0)
        ->and(config('backup.cleanup.default_strategy.keep_yearly_backups_for_years'))->toBe(0)
        ->and(config('backup.backup.encryption'))->toBe('aes256');
});

it('uses daily backup scheduling by default', function (): void {
    expect(config('backup.schedule.frequency'))->toBe('daily')
        ->and(config('backup.schedule.time'))->toBe('01:00')
        ->and(config('backup.schedule.cleanup_time'))->toBe('01:30')
        ->and(config('backup.schedule.monitor_time'))->toBe('06:00');
});
