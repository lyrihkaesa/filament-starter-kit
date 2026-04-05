<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use DeviceDetector\DeviceDetector;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Revokes a single active device for a user.
 *
 * Accepts a prefixed device ID to determine which backing store to target:
 *   - "session:{id}"  → deletes from the `sessions` table
 *   - "token:{id}"    → deletes from the `personal_access_tokens` table
 */
final readonly class RevokeDeviceAction
{
    public function handle(User $user, string $prefixedDeviceId): void
    {
        if (str_starts_with($prefixedDeviceId, 'session:')) {
            $this->revokeSession($user, mb_substr($prefixedDeviceId, 8));

            return;
        }

        if (str_starts_with($prefixedDeviceId, 'token:')) {
            $this->revokeToken($user, (int) mb_substr($prefixedDeviceId, 6));

            return;
        }
    }

    private function revokeSession(User $user, string $sessionId): void
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
        $label = $this->buildDeviceLabel($userAgent);

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
                'device' => $label,
                'ip' => is_scalar($session->ip_address) ? (string) $session->ip_address : '',
                'last_active' => is_scalar($lastActive) ? (string) $lastActive : '',
            ]))
            ->warning()
            ->sendToDatabase($user);
    }

    private function revokeToken(User $user, int $tokenId): void
    {
        $token = PersonalAccessToken::query()
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', $user->getMorphClass())
            ->find($tokenId);

        if (! ($token instanceof PersonalAccessToken)) {
            return;
        }

        // @codeCoverageIgnoreStart
        $lastActive = $token->last_used_at
            ? $token->last_used_at->diffForHumans()
            : $token->created_at?->diffForHumans() ?? '-';
        // @codeCoverageIgnoreEnd

        $token->delete();

        Notification::make()
            ->title(__('Device Disconnected'))
            ->body(__('The device ":name" has been disconnected. Last active: :last_active', [
                'name' => $token->name,
                'last_active' => $lastActive,
            ]))
            ->warning()
            ->sendToDatabase($user);
    }

    private function buildDeviceLabel(string $userAgent): string
    {
        // @codeCoverageIgnoreStart
        if ($userAgent === '') {
            return 'Unknown Device';
        }

        // @codeCoverageIgnoreEnd

        $detector = new DeviceDetector($userAgent);
        $detector->parse();

        $client = $detector->getClient();
        $os = $detector->getOs();

        $browserName = is_array($client) && is_string($client['name'] ?? null) ? $client['name'] : 'Unknown Browser';
        $osName = is_array($os) && is_string($os['name'] ?? null) ? $os['name'] : 'Unknown OS';

        return sprintf('%s on %s', $browserName, $osName);
    }
}
