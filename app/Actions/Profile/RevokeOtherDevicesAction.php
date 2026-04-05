<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;

/**
 * Revokes all other active devices for a user.
 *
 * "Other" means: all devices that are NOT the current web session.
 * This includes:
 *   - Other web sessions in the `sessions` table
 *   - All API tokens in the `personal_access_tokens` table
 *     (API tokens are stateless; there is no concept of "current token" in a web context)
 */
final readonly class RevokeOtherDevicesAction
{
    public function handle(User $user, string $password): void
    {
        Auth::guard()->logoutOtherDevices($password);

        $this->revokeOtherSessions($user);
        $this->revokeAllTokens($user);

        Notification::make()
            ->title(__('Other Devices Logged Out'))
            ->body(__('All other sessions and connected devices have been signed out successfully.'))
            ->warning()
            ->sendToDatabase($user);
    }

    private function revokeOtherSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $currentSessionId = null;

        try {
            $currentSessionId = session()->getId();
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            // No session available (e.g. in test context)
        }

        // @codeCoverageIgnoreEnd

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '<>', $currentSessionId ?? 'none')
            ->delete();
    }

    private function revokeAllTokens(User $user): void
    {
        PersonalAccessToken::query()
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', $user->getMorphClass())
            ->delete();
    }
}
