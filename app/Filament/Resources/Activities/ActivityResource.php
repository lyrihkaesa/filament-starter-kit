<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Models\Activity;
use App\Models\User;
use App\Support\Activity\ActivitySubjectType;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
                TextColumn::make('changes_count')
                    ->label(__('Changes'))
                    ->state(function (Activity $record): int {
                        [$old, $new] = self::extractChangeBuckets($record);

                        return match ($record->description) {
                            'created', 'updated' => count($new),
                            'deleted' => count($old),
                            default => count($new) ?: count($old),
                        };
                    })
                    ->badge()
                    ->color('primary')
                    ->placeholder('0')
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
                        function (Builder $query, mixed $value): Builder {
                            if (! is_scalar($value)) {
                                return $query;
                            }

                            return $query->where('subject_id', (string) $value);
                        }
                    )),
                Filter::make('created_at')
                    ->label(__('Date Range'))
                    ->form([
                        DatePicker::make('from')->label(__('From')),
                        DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, function (Builder $query, mixed $date): Builder {
                            if (! is_scalar($date)) {
                                return $query;
                            }

                            return $query->whereDate('created_at', '>=', (string) $date);
                        })
                        ->when($data['until'] ?? null, function (Builder $query, mixed $date): Builder {
                            if (! is_scalar($date)) {
                                return $query;
                            }

                            return $query->whereDate('created_at', '<=', (string) $date);
                        })),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('7xl')
                    ->schema([
                        Section::make(__('Activity Overview'))
                            ->columns(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('Log Time'))
                                    ->state(fn (Activity $record): string => $record->created_at?->format('Y-m-d H:i:s') ?? '-')
                                    ->inlineLabel(),
                                TextEntry::make('causer.name')
                                    ->label(__('User'))
                                    ->state(function (Activity $record): string {
                                        $name = data_get($record, 'causer.name');

                                        return is_string($name) ? $name : (string) __('System');
                                    })
                                    ->inlineLabel(),
                                TextEntry::make('description')
                                    ->label(__('Event'))
                                    ->state(fn (Activity $record): string => __($record->description))
                                    ->inlineLabel(),
                            ]),
                        Section::make(__('Subject Details'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('subject_type')
                                    ->label(__('Subject Type'))
                                    ->state(fn (Activity $record): string => ActivitySubjectType::labelFromDatabase($record->subject_type))
                                    ->inlineLabel(),
                                TextEntry::make('subject_id')
                                    ->label(__('Subject ID'))
                                    ->state(fn (Activity $record): mixed => $record->subject_id)
                                    ->inlineLabel(),
                            ]),
                        Section::make(__('Changes'))
                            ->visible(fn (Activity $record): bool => self::hasProperties($record))
                            ->schema(function (Activity $record): array {
                                [$oldValues, $newValues] = self::extractChangeBuckets($record);

                                if (empty($oldValues) && empty($newValues)) {
                                    return [
                                        TextEntry::make('properties')
                                            ->label(__('Metadata'))
                                            ->state(fn (Activity $record): string => (string) json_encode(self::stringifyArrayValues($record->properties))),
                                    ];
                                }

                                $oldStringified = self::stringifyArrayValues($oldValues);
                                $newStringified = self::stringifyArrayValues($newValues);

                                $allKeys = array_unique(array_merge(array_keys($oldStringified), array_keys($newStringified)));

                                $rows = [];
                                $sensitiveKeys = ['password', 'remember_token', 'token', 'access_token', 'refresh_token', 'secret', 'key', 'api_key', 'signature'];

                                foreach ($allKeys as $key) {
                                    $oldVal = $oldStringified[$key] ?? '-';
                                    $newVal = $newStringified[$key] ?? '-';

                                    if ($oldVal === $newVal) {
                                        continue;
                                    }

                                    $isSensitive = false;
                                    foreach ($sensitiveKeys as $sensitive) {
                                        if (str_contains(mb_strtolower($key), $sensitive)) {
                                            $isSensitive = true;
                                            break;
                                        }
                                    }

                                    if ($isSensitive) {
                                        $oldVal = ($oldVal === '-') ? '-' : '********';
                                        $newVal = ($newVal === '-') ? '-' : '********';
                                    }

                                    $rows[] = [
                                        'key' => $key,
                                        'old' => $oldVal,
                                        'new' => $newVal,
                                    ];
                                }

                                if ($rows === []) {
                                    return [];
                                }

                                $tableHtml = '<div class="overflow-x-auto"><table class="w-full text-sm text-left border-collapse">';
                                $tableHtml .= '<thead class="bg-gray-50 dark:bg-white/5"><tr>';
                                $tableHtml .= '<th class="px-4 py-2 border border-gray-200 dark:border-gray-800 font-bold">'.__('Key').'</th>';
                                $tableHtml .= '<th class="px-4 py-2 border border-gray-200 dark:border-gray-800 font-bold">'.__('Before (Old)').'</th>';
                                $tableHtml .= '<th class="px-4 py-2 border border-gray-200 dark:border-gray-800 font-bold">'.__('After (New)').'</th>';
                                $tableHtml .= '</tr></thead>';
                                $tableHtml .= '<tbody>';

                                foreach ($rows as $row) {
                                    $tableHtml .= '<tr>';
                                    $tableHtml .= '<td class="px-4 py-2 border border-gray-200 dark:border-gray-800 font-medium"><code>'.e($row['key']).'</code></td>';
                                    $tableHtml .= '<td class="px-4 py-2 border border-gray-200 dark:border-gray-800 text-danger-600 dark:text-danger-400">'.e($row['old']).'</td>';
                                    $tableHtml .= '<td class="px-4 py-2 border border-gray-200 dark:border-gray-800 text-success-600 dark:text-success-400">'.e($row['new']).'</td>';
                                    $tableHtml .= '</tr>';
                                }

                                $tableHtml .= '</tbody></table></div>';

                                return [
                                    Html::make($tableHtml),
                                ];
                            }),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user instanceof User) {
            return $query;
        }

        if ($user->hasRole(['super_admin', 'admin'])) {
            return $query;
        }

        if ($user->hasRole('member')) {
            $userId = $user->getKey();

            if (! is_scalar($userId)) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->whereIn('causer_type', [ActivitySubjectType::USER, User::class])
                ->where('causer_id', (string) $userId);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageActivities::route('/'),
        ];
    }

    private static function hasProperties(Activity $record): bool
    {
        if (self::normalizeToArray($record->properties) !== []) {
            return true;
        }

        return self::normalizeToArray($record->attribute_changes) !== [];
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
     * @return array<string, mixed>
     */
    private static function normalizeToArray(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            /** @var array<string, mixed> $data */
            $data = json_decode($value, true) ?? [];

            return $data;
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
            /** @var array<int|string, mixed> $objectArray */
            $objectArray = (array) $value;

            return self::normalizeArrayKeys($objectArray);
        }

        if (is_array($value)) {
            return self::normalizeArrayKeys($value);
        }

        return [];
    }

    /**
     * @param  array<int|string, mixed>  $value
     * @return array<string, mixed>
     */
    private static function normalizeArrayKeys(array $value): array
    {
        $normalized = [];

        foreach ($value as $key => $item) {
            $normalized[(string) $key] = $item;
        }

        return $normalized;
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

        return null;
    }
}
