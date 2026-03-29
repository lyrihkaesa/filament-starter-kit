<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TemporaryUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class CleanupTemporaryUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired temporary uploads';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredUploads = TemporaryUpload::query()->where('status', '!=', 'finalized')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        /** @var TemporaryUpload $upload */
        foreach ($expiredUploads as $upload) {
            Storage::disk((string) $upload->disk)->delete((string) $upload->path);
            $upload->delete();
        }

        $this->info(sprintf('Cleaned up %d expired temporary uploads.', $expiredUploads->count()));

        return self::SUCCESS;
    }
}
