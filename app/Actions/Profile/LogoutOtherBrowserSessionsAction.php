<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class LogoutOtherBrowserSessionsAction
{
    public function handle(User $user, string $password): void
    {
        Auth::guard()->logoutOtherDevices($password);

        if (config('session.driver') === 'database') {
            $currentSessionIdRaw = null;

            try {
                $currentSessionIdRaw = session()->getId();
            } catch (Throwable) {
                // No session
            }

            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '<>', $currentSessionIdRaw ?? 'none')
                ->delete();
        }
    }
}
