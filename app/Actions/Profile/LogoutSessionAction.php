<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

final readonly class LogoutSessionAction
{
    public function handle(User $user, string $sessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $session = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->first();

        if ($session === null) {
            return;
        }

        $userAgent = is_string($session->user_agent) ? $session->user_agent : '';
        $agent = $this->parseUserAgent($userAgent);
        $device = sprintf('%s on %s', $agent['browser'], $agent['platform']);

        /** @var scalar $activity */
        $activity = $session->last_activity ?? 0;
        $lastActive = Date::createFromTimestamp((int) $activity)->diffForHumans();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->delete();

        Notification::make()
            ->title(__('Log Out Successful'))
            ->body(__('You have been logged out from :device (:ip). Last active: :last_active', [
                'device' => $device,
                'ip' => is_scalar($session->ip_address) ? (string) $session->ip_address : '',
                'last_active' => is_scalar($lastActive) ? (string) $lastActive : '',
            ]))
            ->warning()
            ->sendToDatabase($user);
    }

    /**
     * @return array{is_desktop: bool, browser: string, platform: string}
     */
    private function parseUserAgent(string $userAgent): array
    {
        $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|Phone/i', $userAgent);

        $browser = 'Unknown Browser';
        if (preg_match('/Edge|Edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        }

        $platform = 'Unknown OS';
        if (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        return [
            'is_desktop' => ! $isMobile,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
