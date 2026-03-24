<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class LogoutSessionAction
{
    public function handle(User $user, string $sessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->delete();
    }
}
