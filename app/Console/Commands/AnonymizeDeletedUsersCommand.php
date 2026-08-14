<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Profile\AnonymizeUserAction;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Description('Anonymize users who have been soft-deleted for more than 30 days.')]
#[Signature('app:anonymize-deleted-users')]
final class AnonymizeDeletedUsersCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnonymizeUserAction $anonymizeAction): void
    {
        $users = User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->where(function (Builder $query): void {
                $query->whereColumn('deleted_by', 'id')
                    ->orWhereNull('deleted_by');
            })
            ->whereNull('anonymized_at')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users to anonymize.');

            return;
        }

        $this->info(sprintf('Anonymizing %d users...', $users->count()));

        /** @var User $user */
        foreach ($users as $user) {
            $anonymizeAction->handle($user);
            $this->line('Anonymized user: '.$user->id);
        }

        $this->info('Anonymization complete.');
    }
}
