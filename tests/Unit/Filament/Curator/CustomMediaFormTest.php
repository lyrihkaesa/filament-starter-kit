<?php

declare(strict_types=1);

use App\Filament\Curator\CustomMediaForm;
use Filament\Forms\Components\TextInput;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('returns custom media form schema array', function (): void {
    $schema = CustomMediaForm::getAdditionalInformationFormSchema();

    expect($schema)->toBeArray()
        ->and($schema[0])->toBeInstanceOf(TextInput::class);
});

it('slugifies media name in dehydration callback', function (): void {
    $schema = CustomMediaForm::getAdditionalInformationFormSchema();

    /** @var TextInput $nameInput */
    $nameInput = $schema[0];

    $reflection = new ReflectionObject($nameInput);
    $property = $reflection->getProperty('dehydrateStateUsing');

    $callback = $property->getValue($nameInput);

    $component = new class
    {
        public ?string $currentState = null;

        public function state(string $state): void
        {
            $this->currentState = $state;
        }
    };

    $result = $callback($component, 'Media Hero Banner');

    expect($result)->toBe('media-hero-banner')
        ->and($component->currentState)->toBe('media-hero-banner');
});
