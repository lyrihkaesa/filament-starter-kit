<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Auth\RestoreAccountNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('sends notification via mail channel', function (): void {
    $notification = new RestoreAccountNotification();

    expect($notification->via(new stdClass))->toBe(['mail']);
});

it('returns an empty mail message for non-user notifiable', function (): void {
    $notification = new RestoreAccountNotification();

    $mailMessage = $notification->toMail(new stdClass);

    expect($mailMessage)->toBeInstanceOf(MailMessage::class)
        ->and($mailMessage->subject)->toBeNull()
        ->and($mailMessage->actionUrl)->toBeNull();
});

it('builds the expected restoration mail message for users', function (): void {
    Date::setTestNow('2026-04-08 08:00:00');

    $user = User::factory()->create();
    $notification = new RestoreAccountNotification();

    $mailMessage = $notification->toMail($user);
    $expectedUrl = URL::temporarySignedRoute(
        'restore-account',
        now()->addDays(7),
        ['id' => $user->getKey()]
    );

    expect($mailMessage)->toBeInstanceOf(MailMessage::class)
        ->and($mailMessage->subject)->toBe('Permohonan Pemulihan Akun')
        ->and($mailMessage->actionText)->toBe('Pulihkan Akun')
        ->and($mailMessage->actionUrl)->toBe($expectedUrl)
        ->and($mailMessage->introLines)->toBe([
            'Anda menerima email ini karena kami menerima permintaan pemulihan akun untuk akun Anda.',
        ])
        ->and($mailMessage->outroLines)->toBe([
            'Tautan pemulihan ini akan kedaluwarsa dalam 60 menit.',
            'Jika Anda tidak merasa meminta pemulihan akun, abaikan email ini.',
        ]);

    Date::setTestNow();
});
