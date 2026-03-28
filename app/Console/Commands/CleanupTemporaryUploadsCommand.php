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
        $expiredUploads = TemporaryUpload::where('status', '!=', 'finalized')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($expiredUploads as $upload) {
            Storage::disk($upload->disk)->delete($upload->path);
            $upload->delete();
        }

        $this->info("Cleaned up {$expiredUploads->count()} expired temporary uploads.");

        return self::SUCCESS;
    }
}
