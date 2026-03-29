<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Actions\Profile\DeleteUserAccountAction;
use App\Actions\Profile\RestoreUserAccountAction;
use App\Models\User;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('avatarMedia'))
            ->columns([
                CuratorColumn::make('avatarMedia')
                    ->label(__('Avatar'))
                    ->circular()
                    ->defaultImageUrl(asset('images/thumbnails/images-dark-500x500.jpg')),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(__('Email verified at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->state(fn (User $record): string => match (true) {
                        $record->isAnonymous() => __('Permanently Deleted'),
                        $record->trashed() => __('Deleted'),
                        default => __('Active'),
                    })
                    ->color(fn (User $record): string => match (true) {
                        $record->isAnonymous() => 'info',
                        $record->trashed() => 'danger',
                        default => 'success',
                    }),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => __('Active'),
                        'deleted' => __('Deleted'),
                        'anonymized' => __('Permanently Deleted'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        /** @var Builder<User> $query */
                        if (empty($data['value'])) {
                            return $query->whereNull('deleted_at');
                        }

                        return match ($data['value']) {
                            'active' => $query->whereNull('deleted_at')->whereNull('anonymized_at'),
                            'deleted' => $query->onlyTrashed()->whereNull('anonymized_at'),
                            'anonymized' => $query->withTrashed()->whereNotNull('anonymized_at'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Impersonate::make()
                    ->link()
                    ->color('warning')
                    ->iconSize(IconSize::Small),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->using(fn (User $record, DeleteUserAccountAction $deleteAction) => $deleteAction->handle($record, Auth::user())),
                RestoreAction::make()
                    ->using(fn (User $record, RestoreUserAccountAction $restoreAction) => $restoreAction->handle($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
