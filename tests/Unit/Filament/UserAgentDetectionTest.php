<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Pages\Auth\EditProfile;
use ReflectionClass;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('can detect various user agents using Matomo device-detector', function (
    string $userAgent,
    string $expectedType,
    string $expectedLabelContains,
): void {
    $reflection = new ReflectionClass(EditProfile::class);
    $method = $reflection->getMethod('parseUserAgent');

    $page = new EditProfile();
    $result = $method->invoke($page, $userAgent);

    expect($result['type'])->toBe($expectedType)
        ->and($result['label'])->toContain($expectedLabelContains);
})->with([
    'Chrome on Windows' => [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'web_session', 'Chrome',
    ],
    'Safari on macOS' => [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'web_session', 'Safari',
    ],
    'Firefox on Linux' => [
        'Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
        'web_session', 'Firefox',
    ],
    'Edge on Windows' => [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        'web_session', 'Edge',
    ],
    'Chrome on Android (mobile)' => [
        'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
        'mobile_app', 'Android',
    ],
    'Safari on iPhone (mobile)' => [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        'mobile_app', 'iOS',
    ],
    'Unknown/empty agent' => [
        '',
        'api_client', 'Unknown',
    ],
]);
