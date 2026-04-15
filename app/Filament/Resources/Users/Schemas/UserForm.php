<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Forms\Components\CuratorFileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                CuratorFileUpload::make('avatar_curator_id')
                    ->label(__('Avatar'))
                    ->relationship('avatarMedia', 'id')
                    ->avatar()
                    ->imageEditor()
                    ->automaticallyOpenImageEditorForAspectRatio()
                    ->imageEditorViewportWidth(320)
                    ->imageEditorViewportHeight(320)
                    ->placeholder(__('Upload avatar'))
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->directory('avatars')
                    ->visibility('public'),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label(__('Email verified at')),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),
                Select::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->disabled(fn (): bool => ! auth()->user()?->can('Update:Role')),
            ]);
    }
}
