<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Support\Activity\ActivitySubjectType;
use App\Support\Filament\FilamentNavigation;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use stdClass;
use UnitEnum;

final class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static UnitEnum|string|null $navigationGroup = 'System Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    public static function getNavigationGroup(): ?string
    {
        return __('System Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Activity');
    }

    public static function getModelLabel(): string
    {
        return __('Activity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activity');
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentNavigation::sort(self::getNavigationLabel());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var \App\Models\User $user */
                $user = auth()->user();

                if ($user->hasAnyRole(['super_admin', 'admin'])) {
                    return $query;
                }

                return $query->where('causer_id', $user->id);
            })
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Log Time'))
                    ->dateTime('d M Y H:i:s')
                    ->sinceTooltip()
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label(__('Causer'))
                    ->placeholder(__('System'))
                    ->description(fn (Activity $record): ?string => $record->causer_id ? "ID: {$record->causer_id}" : null)
                    ->searchable(),

                TextColumn::make('event')
                    ->label(__('Event'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?? 'unknown')->headline()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('subject_type')
                    ->label(__('Subject'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ActivitySubjectType::labelFromDatabase($state))
                    ->description(fn (Activity $record): ?string => $record->subject_id ? "ID: {$record->subject_id}" : null),

                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(80)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->searchable()
                    ->toggleable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('event')
                    ->label(__('Event'))
                    ->options([
                        'created' => __('Created'),
                        'updated' => __('Updated'),
                        'deleted' => __('Deleted'),
                    ]),
                SelectFilter::make('subject_type')
                    ->label(__('Subject Type'))
                    ->options(ActivitySubjectType::labels()),
                Filter::make('subject_id')
                    ->label(__('Subject ID'))
                    ->form([
                        TextInput::make('value')
                            ->label(__('Subject ID')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $value): Builder => $query->where('subject_id', $value)
                    )),
                Filter::make('created_at')
                    ->label(__('Date Range'))
                    ->form([
                        DatePicker::make('from')->label(__('From')),
                        DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        /** @var Builder $query */
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('7xl')
                    ->schema([
                        Section::make(__('Activity Overview'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label(__('Log Time'))
                                            ->dateTime('d M Y H:i:s'),
                                        TextEntry::make('event')
                                            ->label(__('Event'))
                                            ->badge()
                                            ->formatStateUsing(fn (?string $state): string => str($state ?? 'unknown')->headline()->toString()),
                                        TextEntry::make('causer.name')
                                            ->label(__('Causer'))
                                            ->placeholder(__('System')),
                                        TextEntry::make('causer_id')
                                            ->label(__('Causer ID'))
                                            ->placeholder('-'),
                                        TextEntry::make('subject_type')
                                            ->label(__('Subject Type'))
                                            ->badge()
                                            ->formatStateUsing(fn (?string $state): string => ActivitySubjectType::labelFromDatabase($state)),
                                        TextEntry::make('subject_id')
                                            ->label(__('Subject ID'))
                                            ->placeholder('-'),
                                        TextEntry::make('description')
                                            ->label(__('Description'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Section::make(__('Field Changes'))
                            ->schema([
                                RepeatableEntry::make('change_rows')
                                    ->label('')
                                    ->state(fn (Activity $record): array => self::changeRows($record))
                                    ->schema([
                                        TextEntry::make('field')->label(__('Field')),
                                        TextEntry::make('old')->label(__('Old'))->placeholder('-'),
                                        TextEntry::make('new')->label(__('New'))->placeholder('-'),
                                    ])
                                    ->table([
                                        TableColumn::make(__('Field')),
                                        TableColumn::make(__('Old')),
                                        TableColumn::make(__('New')),
                                    ]),
                            ]),
                        Section::make(__('Additional Properties'))
                            ->schema([
                                KeyValueEntry::make('properties')
                                    ->label('')
                                    ->keyLabel(__('Property'))
                                    ->valueLabel(__('Value'))
                                    ->state(fn (Activity $record): array => self::stringifyArrayValues($record->properties)),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageActivities::route('/'),
        ];
    }

    /**
     * @return list<array{field: string, old: string|null, new: string|null}>
     */
    private static function changeRows(Activity $record): array
    {
        [$oldValues, $newValues] = self::extractChangeBuckets($record);

        /** @var list<string> $fields */
        $fields = array_values(array_unique([
            ...array_keys($oldValues),
            ...array_keys($newValues),
        ]));

        sort($fields);

        return array_map(
            fn (string $field): array => [
                'field' => $field,
                'old' => self::stringifyValue($oldValues[$field] ?? null),
                'new' => self::stringifyValue($newValues[$field] ?? null),
            ],
            $fields,
        );
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private static function extractChangeBuckets(Activity $record): array
    {
        $attributeChanges = self::normalizeToArray($record->attribute_changes);
        $properties = self::normalizeToArray($record->properties);

        $oldValues = [];
        $newValues = [];

        if (is_array($attributeChanges['old'] ?? null) || is_array($attributeChanges['attributes'] ?? null)) {
            $oldValues = Arr::dot(is_array($attributeChanges['old'] ?? null) ? $attributeChanges['old'] : []);
            $newValues = Arr::dot(is_array($attributeChanges['attributes'] ?? null) ? $attributeChanges['attributes'] : []);

            return [$oldValues, $newValues];
        }

        if (is_array($properties['old'] ?? null) || is_array($properties['attributes'] ?? null)) {
            $oldValues = Arr::dot(is_array($properties['old'] ?? null) ? $properties['old'] : []);
            $newValues = Arr::dot(is_array($properties['attributes'] ?? null) ? $properties['attributes'] : []);

            return [$oldValues, $newValues];
        }

        return [[], []];
    }

    /**
     * @param  array<string, mixed>|Collection<int|string, mixed>|Arrayable<int|string, mixed>|stdClass|null  $values
     * @return array<string, string>
     */
    private static function stringifyArrayValues(array|Collection|Arrayable|stdClass|null $values): array
    {
        $normalizedValues = self::normalizeToArray($values);

        $result = [];

        foreach (Arr::dot($normalizedValues) as $key => $value) {
            $result[$key] = self::stringifyValue($value) ?? '-';
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>|Collection<int|string, mixed>|Arrayable<int|string, mixed>|stdClass|null  $value
     * @return array<string, mixed>
     */
    private static function normalizeToArray(array|Collection|Arrayable|stdClass|null $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Collection) {
            /** @var array<string, mixed> $normalized */
            $normalized = $value->toArray();

            return $normalized;
        }

        if ($value instanceof Arrayable) {
            /** @var array<string, mixed> $normalized */
            $normalized = $value->toArray();

            return $normalized;
        }

        if ($value instanceof stdClass) {
            /** @var array<string, mixed> $normalized */
            $normalized = (array) $value;

            return $normalized;
        }

        return $value;
    }

    private static function stringifyValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);

            return $encoded === false ? '[unserializable array]' : $encoded;
        }

        if (is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);

            return $encoded === false ? '[unserializable object]' : $encoded;
        }

        return (string) $value;
    }
}
