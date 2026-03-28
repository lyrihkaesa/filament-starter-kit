<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_upload')
                    ->label(__('Avatar'))
                    ->avatar()
                    ->imageEditor()
                    ->automaticallyOpenImageEditorForAspectRatio()
                    ->imageEditorViewportWidth(320)
                    ->imageEditorViewportHeight(320)
                    ->placeholder(__('Upload avatar'))
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->disk(config()->string('curator.default_disk'))
                    ->directory('avatars')
                    ->visibility('public')
                    ->storeFileNamesIn('avatar_upload_file_name'),
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
                    ->required(),
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
