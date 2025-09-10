<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('avatar')
                    ->label(__('Avatar'))
                    ->circular(),
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('email')
                    ->label(__('Email')),
                TextEntry::make('email_verified_at')
                    ->label(__('Email verified at'))
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime(),
            ]);
    }
}
