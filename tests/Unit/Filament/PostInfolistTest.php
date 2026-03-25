<?php

declare(strict_types=1);

use App\Filament\Resources\Posts\Schemas\PostInfolist;
use Filament\Schemas\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('configures PostInfolist schema', function (): void {
    $schema = $this->getMockBuilder(Schema::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['components'])
        ->getMock();
    $schema->method('components')->willReturnSelf();

    expect(PostInfolist::configure($schema))->toBe($schema);
});
