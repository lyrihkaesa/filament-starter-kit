<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\Privacy;

it('returns correct labels for Privacy enum', function (): void {
    expect(Privacy::PRIVATE->getLabel())->toBe('Private');
    expect(Privacy::MEMBER->getLabel())->toBe('Member');
    expect(Privacy::PUBLIC->getLabel())->toBe('Public');
});
