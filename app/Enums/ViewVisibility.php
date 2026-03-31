<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ViewVisibility: string implements HasLabel
{
    case PRIVATE = 'private';
    case MEMBER = 'member';
    case PUBLIC = 'public';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::MEMBER => 'Member',
            self::PUBLIC => 'Public',
        };
    }
}
