<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

final class StarterKitInfoWidget extends Widget
{
    // Tambahkan property jika ingin
    public string $laravelVersion;

    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.starter-kit-info-widget';

    public function mount(): void
    {
        $this->laravelVersion = app()->version();
    }
}
