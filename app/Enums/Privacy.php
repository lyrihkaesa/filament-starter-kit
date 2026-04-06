<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum Privacy: string implements HasColor, HasIcon, HasLabel
{
    case PRIVATE = 'private';
    case MEMBER = 'member';
    case PUBLIC = 'public';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIVATE => __('Private'),
            self::MEMBER => __('Member'),
            self::PUBLIC => __('Public'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PRIVATE => 'danger',
            self::MEMBER => 'warning',
            self::PUBLIC => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PRIVATE => Heroicon::LockClosed,
            self::MEMBER => Heroicon::UserGroup,
            self::PUBLIC => Heroicon::GlobeAlt,
        };
    }
}
