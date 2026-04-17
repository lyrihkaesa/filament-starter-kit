<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Support\Activity\ActivitySubjectType;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
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

    public static function getNavigationGroup(): string
    {
        return __('System Management');
    }

    public static function getModelLabel(): string
    {
        return __('Activity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activity');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Log Time'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label(__('User'))
                    ->searchable()
                    ->placeholder(__('System')),
                TextColumn::make('description')
                    ->label(__('Event'))
                    ->formatStateUsing(fn (string $state): string => __($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label(__('Subject'))
                    ->formatStateUsing(fn (string $state): string => ActivitySubjectType::labelFromDatabase($state)),
                TextColumn::make('subject_id')
                    ->label(__('ID'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('properties')
                    ->label(__('Changes'))
                    ->icon(fn (Activity $record): ?string => ($record->properties->isNotEmpty() ?? false) ? 'heroicon-o-eye' : null)
                    ->color('primary')
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
                        fn (Builder $query, mixed $value): Builder => $query->where('subject_id', (string) $value)
                    )),
                Filter::make('created_at')
                    ->label(__('Date Range'))
                    ->form([
                        DatePicker::make('from')->label(__('From')),
                        DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, mixed $date): Builder => $query->whereDate('created_at', '>=', (string) $date))
                        ->when($data['until'] ?? null, fn (Builder $query, mixed $date): Builder => $query->whereDate('created_at', '<=', (string) $date))),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('7xl')
                    ->schema([
                        Section::make(__('Activity Overview'))
                            ->columns(3)
                            ->schema([
                                TextColumn::make('created_at')->label(__('Log Time'))->dateTime()->inline(),
                                TextColumn::make('causer.name')->label(__('User'))->placeholder(__('System'))->inline(),
                                TextColumn::make('description')
                                    ->label(__('Event'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'created' => 'success',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        default => 'gray',
                                    })
                                    ->inline(),
                            ]),
                        Section::make(__('Subject Details'))
                            ->columns(2)
                            ->schema([
                                TextColumn::make('subject_type')->label(__('Subject Type'))->formatStateUsing(fn (string $state): string => ActivitySubjectType::labelFromDatabase($state))->inline(),
                                TextColumn::make('subject_id')->label(__('Subject ID'))->inline(),
                            ]),
                        Section::make(__('Changes'))
                            ->visible(fn (Activity $record): bool => $record->properties->isNotEmpty() ?? false)
                            ->schema(function (Activity $record): array {
                                [$oldValues, $newValues] = self::extractChangeBuckets($record);

                                if (empty($oldValues) && empty($newValues)) {
                                    return [
                                        TextColumn::make('properties')
                                            ->label(__('Metadata'))
                                            ->state(fn (Activity $record): array => self::stringifyArrayValues($record->properties))
                                            ->listWithLineBreaks(),
                                    ];
                                }

                                $oldStringified = self::stringifyArrayValues($oldValues);
                                $newStringified = self::stringifyArrayValues($newValues);

                                $schema = [];
                                $allKeys = array_unique(array_merge(array_keys($oldStringified), array_keys($newStringified)));

                                foreach ($allKeys as $key) {
                                    $oldVal = $oldStringified[$key] ?? '-';
                                    $newVal = $newStringified[$key] ?? '-';

                                    if ($oldVal === $newVal) {
                                        continue;
                                    }

                                    $schema[] = Section::make($key)
                                        ->columns(2)
                                        ->compact()
                                        ->schema([
                                            TextColumn::make('old_'.$key)
                                                ->label(__('Before'))
                                                ->state($oldVal)
                                                ->color('danger')
                                                ->inline(),
                                            TextColumn::make('new_'.$key)
                                                ->label(__('After'))
                                                ->state($newVal)
                                                ->color('success')
                                                ->inline(),
                                        ]);
                                }

                                return $schema;
                            }),
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
     * @return array{array<string, mixed>, array<string, mixed>}
     */
    private static function extractChangeBuckets(Activity $record): array
    {
        $attributeChanges = self::normalizeToArray($record->attribute_changes);
        $properties = self::normalizeToArray($record->properties);

        if (is_array($attributeChanges['old'] ?? null) || is_array($attributeChanges['attributes'] ?? null)) {
            /** @var array<string, mixed> $oldValues */
            $oldValues = Arr::dot(is_array($attributeChanges['old'] ?? null) ? $attributeChanges['old'] : []);
            /** @var array<string, mixed> $newValues */
            $newValues = Arr::dot(is_array($attributeChanges['attributes'] ?? null) ? $attributeChanges['attributes'] : []);

            return [$oldValues, $newValues];
        }

        if (is_array($properties['old'] ?? null) || is_array($properties['attributes'] ?? null)) {
            /** @var array<string, mixed> $oldValues */
            $oldValues = Arr::dot(is_array($properties['old'] ?? null) ? $properties['old'] : []);
            /** @var array<string, mixed> $newValues */
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

        /** @var array<string, string> $result */
        $result = [];

        foreach (Arr::dot($normalizedValues) as $key => $value) {
            $result[(string) $key] = self::stringifyValue($value) ?? '-';
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
            /** @var array<string, mixed> $data */
            $data = $value->toArray();

            return $data;
        }

        if ($value instanceof Arrayable) {
            /** @var array<string, mixed> $data */
            $data = $value->toArray();

            return $data;
        }

        if ($value instanceof stdClass) {
            return (array) $value;
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    private static function stringifyValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_array($value) || is_object($value)) {
            return (string) json_encode($value);
        }

        return (string) $value;
    }
}
