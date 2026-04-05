<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Curator;

use App\Filament\Curator\CustomMediaForm;
use Filament\Schemas\Components\Grid;
use Tests\TestCase;

uses(TestCase::class);

it('returns custom media form schema array', function (): void {
    $schema = CustomMediaForm::getSchema();

    expect($schema)->toBeArray();
    expect($schema[0])->toBeInstanceOf(Grid::class);
});
