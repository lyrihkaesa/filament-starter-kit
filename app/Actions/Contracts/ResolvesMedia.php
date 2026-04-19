<?php

declare(strict_types=1);

namespace App\Actions\Contracts;

use App\Models\CuratorMedia;

interface ResolvesMedia
{
    public function handle(?string $uploadId, ?string $curatorId, string $purpose): ?CuratorMedia;
}
