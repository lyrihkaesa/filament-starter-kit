<?php

declare(strict_types=1);

use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

$applicationName = (string) env('APP_NAME', 'filament-starter-kit');

if ($applicationName === '') {
    $applicationName = 'filament-starter-kit';
}

$destinationDisks = array_values(array_filter(array_map(
    static fn (string $disk): string => mb_trim($disk),
    explode(',', (string) env('BACKUP_DESTINATION_DISKS', 'backups,s3')),
), static fn (string $disk): bool => $disk !== ''));

$notificationRecipients = array_values(array_filter(array_map(
    static fn (string $recipient): string => mb_trim($recipient),
    explode(',', (string) env('BACKUP_NOTIFICATION_EMAIL', (string) env('MAIL_FROM_ADDRESS', 'hello@example.com'))),
), static fn (string $recipient): bool => $recipient !== ''));

return [
    'backup' => [
        'name' => $applicationName,

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],

                'exclude' => [
                    base_path('node_modules'),
                    base_path('vendor'),
                    storage_path('app/backups'),
                    storage_path('framework'),
                ],

                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => base_path(),
            ],

            'databases' => [
                (string) env('BACKUP_DATABASE_CONNECTION', (string) env('DB_CONNECTION', 'sqlite')),
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_timestamp_format' => null,
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => '',
            'disks' => $destinationDisks,
            'continue_on_failure' => true,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => env('BACKUP_ENCRYPTION', 'aes256'),
        'verify_backup' => (bool) env('BACKUP_VERIFY', false),
        'tries' => 1,
        'retry_delay' => 0,
    ],

    'notifications' => [
        'notifications' => [
            BackupHasFailedNotification::class => ['mail'],
            UnhealthyBackupWasFoundNotification::class => ['mail'],
            CleanupHasFailedNotification::class => ['mail'],
            BackupWasSuccessfulNotification::class => ['mail'],
            HealthyBackupWasFoundNotification::class => ['mail'],
            CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        'notifiable' => Notifiable::class,

        'mail' => [
            'to' => count($notificationRecipients) === 1 ? $notificationRecipients[0] : $notificationRecipients,

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],

        'discord' => [
            'webhook_url' => '',
            'username' => '',
            'avatar_url' => '',
        ],

        'webhook' => [
            'url' => '',
        ],
    ],

    'log_channel' => null,

    'monitor_backups' => [
        [
            'name' => $applicationName,
            'disks' => $destinationDisks,
            'health_checks' => [
                MaximumAgeInDays::class => (int) env('BACKUP_MONITOR_MAX_AGE_DAYS', 2),
                MaximumStorageInMegabytes::class => (int) env('BACKUP_MAX_STORAGE_MB', 10240),
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => (int) env('BACKUP_KEEP_ALL_DAYS', 30),
            'keep_daily_backups_for_days' => (int) env('BACKUP_KEEP_DAILY_DAYS', 0),
            'keep_weekly_backups_for_weeks' => (int) env('BACKUP_KEEP_WEEKLY_WEEKS', 0),
            'keep_monthly_backups_for_months' => (int) env('BACKUP_KEEP_MONTHLY_MONTHS', 0),
            'keep_yearly_backups_for_years' => (int) env('BACKUP_KEEP_YEARLY_YEARS', 0),
            'delete_oldest_backups_when_using_more_megabytes_than' => (int) env('BACKUP_MAX_STORAGE_MB', 10240),
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

    'schedule' => [
        'frequency' => (string) env('BACKUP_SCHEDULE_FREQUENCY', 'daily'),
        'time' => (string) env('BACKUP_SCHEDULE_TIME', '01:00'),
        'weekly_day' => (int) env('BACKUP_SCHEDULE_WEEKLY_DAY', 1),
        'monthly_day' => (int) env('BACKUP_SCHEDULE_MONTHLY_DAY', 1),
        'cleanup_time' => (string) env('BACKUP_CLEANUP_TIME', '01:30'),
        'monitor_time' => (string) env('BACKUP_MONITOR_TIME', '06:00'),
    ],
];
