<?php

declare(strict_types=1);

use App\Enums\Privacy;
use Filament\Support\Icons\Heroicon;
use Tests\TestCase;

uses(TestCase::class);

it('returns correct labels for Privacy enum', function (): void {
    expect(Privacy::PRIVATE->getLabel())->toBe(__('Private'));
    expect(Privacy::MEMBER->getLabel())->toBe(__('Member'));
    expect(Privacy::PUBLIC->getLabel())->toBe(__('Public'));
});

it('returns correct colors for Privacy enum', function (): void {
    expect(Privacy::PRIVATE->getColor())->toBe('danger')
        ->and(Privacy::MEMBER->getColor())->toBe('warning')
        ->and(Privacy::PUBLIC->getColor())->toBe('success');
});

it('returns correct icons for Privacy enum', function (): void {
    expect(Privacy::PRIVATE->getIcon())->toBe(Heroicon::LockClosed)
        ->and(Privacy::MEMBER->getIcon())->toBe(Heroicon::UserGroup)
        ->and(Privacy::PUBLIC->getIcon())->toBe(Heroicon::GlobeAlt);
});
