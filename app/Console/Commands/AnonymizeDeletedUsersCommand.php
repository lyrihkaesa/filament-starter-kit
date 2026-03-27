<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Profile\AnonymizeUserAction;
use App\Models\User;
use Illuminate\Console\Command;

final class AnonymizeDeletedUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:anonymize-deleted-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymize users who have been soft-deleted for more than 30 days.';

    /**
     * Execute the console command.
     */
    public function handle(AnonymizeUserAction $anonymizeAction): void
    {
        $users = User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->whereNull('anonymized_at')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users to anonymize.');

            return;
        }

        $this->info("Anonymizing {$users->count()} users...");

        foreach ($users as $user) {
            $anonymizeAction->handle($user);
            $this->line("Anonymized user: {$user->id}");
        }

        $this->info('Anonymization complete.');
    }
}
