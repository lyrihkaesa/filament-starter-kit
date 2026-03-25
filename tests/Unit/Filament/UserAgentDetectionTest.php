<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Pages\Auth\EditProfile;
use Tests\TestCase;

uses(TestCase::class);

it('can detect various user agents', function (string $userAgent, string $expectedBrowser, string $expectedPlatform, bool $expectedIsDesktop) {
    // We use reflection to test the protected createAgent method
    $reflection = new \ReflectionClass(EditProfile::class);
    $method = $reflection->getMethod('createAgent');
    $method->setAccessible(true);
    
    $page = new EditProfile();
    $result = $method->invoke($page, $userAgent);
    
    expect($result['browser'])->toBe($expectedBrowser)
        ->and($result['platform'])->toBe($expectedPlatform)
        ->and($result['is_desktop'])->toBe($expectedIsDesktop);
})->with([
    'Chrome on Windows' => [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Chrome', 'Windows', true
    ],
    'Safari on Mac' => [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Safari', 'macOS', true
    ],
    'Firefox on Linux' => [
        'Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0',
        'Firefox', 'Linux', true
    ],
    'Edge on Windows' => [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        'Edge', 'Windows', true
    ],
    'Chrome on Android' => [
        'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
        'Chrome', 'Android', false
    ],
    'Safari on iPhone' => [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        'Safari', 'iOS', false
    ],
    'Unknown agent' => [
        'Custom Bot 1.0',
        'Unknown Browser', 'Unknown OS', true
    ],
]);
