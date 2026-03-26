<?php

declare(strict_types=1);

use App\Filament\Resources\Posts\Schemas\PostInfolist;
use Filament\Schemas\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('configures PostInfolist schema', function (): void {
    $schema = mock(Schema::class);
    $schema->shouldReceive('components')->once()->andReturnSelf();

    expect(PostInfolist::configure($schema))->toBe($schema);
});
