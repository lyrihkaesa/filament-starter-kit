<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TemporaryUpload;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Description('Cleanup expired temporary uploads')]
#[Signature('uploads:cleanup')]
final class CleanupTemporaryUploadsCommand extends Command
{
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
