<?php

namespace App\Filament\Resources\Users\Pages;

use Filament\Actions\EditAction;
use Filament\Support\Enums\IconSize;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Users\UserResource;
use STS\FilamentImpersonate\Actions\Impersonate;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()
                ->color('warning')
                ->iconSize(IconSize::Small),
            EditAction::make(),
        ];
    }
}
