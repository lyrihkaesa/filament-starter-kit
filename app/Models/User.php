<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Actions\Media\DeleteAllMediaUsagesAction;
use App\Actions\Media\SyncMediaUsageAction;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasActivity;
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasPanelShield;
    use HasRoles;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are guarded from mass assignment.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    // @codeCoverageIgnoreStart
    /**
     * @return BelongsTo<User, $this>
     */
    public function deletedBy(): BelongsTo
    {
        /** @var BelongsTo<User, $this> $relation */
        $relation = $this->belongsTo(self::class, 'deleted_by');

        return $relation->withTrashed();
    }

    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    public function isAnonymous(): bool
    {
        return $this->anonymized_at !== null;
    }

    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    public function isSoftDeleted(): bool
    {
        return $this->deleted_at !== null && $this->anonymized_at === null;
    }

    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    public function isDeletedBySelf(): bool
    {
        return $this->deleted_at !== null && $this->deleted_by === $this->id;
    }

    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    public function isDeletedByAdmin(): bool
    {
        return $this->deleted_at !== null && $this->deleted_by !== null && $this->deleted_by !== $this->id;
    }

    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    public function isActive(): bool
    {
        return $this->deleted_at === null && $this->anonymized_at === null;
    }

    // @codeCoverageIgnoreEnd

    public function anonymize(): void
    {
        DB::transaction(function (): void {
            $uuid = Str::uuid()->toString();

            // Clear roles and permissions
            $this->syncRoles([]);
            $this->syncPermissions([]);

            $this->forceFill([
                'name' => 'Anonymous User',
                'email' => sprintf('anonymous_%s@example.com', $uuid),
                'avatar_curator_id' => null,
                'email_verified_at' => null,
                'password' => bcrypt(Str::random(40)),
                'anonymized_at' => now(),
            ])->saveQuietly();

            // Ensure they stay "deleted" if they were soft-deleted
            if (! $this->trashed()) {
                $this->deleteQuietly();
            }
        });
    }

    // @codeCoverageIgnoreStart
    public function canAccessPanel(Panel $panel): bool
    {
        return ! $this->isAnonymous();
    }

    public function canImpersonate(): bool
    {
        return ! $this->isAnonymous();
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->isAnonymous();
    }

    // @codeCoverageIgnoreEnd

    /**
     * @return BelongsTo<CuratorMedia, $this>
     */
    public function avatarMedia(): BelongsTo
    {
        return $this->belongsTo(CuratorMedia::class, 'avatar_curator_id');
    }

    /**
     * Filament override implements HasAvatar
     *
     * @see HasAvatar
     */
    // @codeCoverageIgnoreStart
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->isAnonymous()) {
            return null;
        }

        if ($this->avatarMedia !== null) {
            return $this->avatarMedia->url;
        }

        return null;
    }

    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    protected static function booted(): void
    {
        self::forceDeleting(function (self $user): bool {
            if ($user->isDeletedBySelf()) {
                $user->anonymize();

                return false;
            }

            // Fallback for null deleted_by (treat as self-deleted for legacy compatibility)
            if ($user->deleted_by === null) {
                $user->anonymize();

                return false;
            }

            return true;
        });

        self::saved(function (self $user): void {
            if ($user->isDirty('avatar_curator_id')) {
                resolve(SyncMediaUsageAction::class)->handle(
                    $user,
                    'avatar_curator_id',
                    (string) $user->avatar_curator_id,
                );
            }
        });

        self::restored(function (self $user): void {
            if ($user->avatar_curator_id) {
                resolve(SyncMediaUsageAction::class)->handle(
                    $user,
                    'avatar_curator_id',
                    (string) $user->avatar_curator_id,
                );
            }
        });

        self::deleting(function (self $user): void {
            // Track media usage removal
            resolve(DeleteAllMediaUsagesAction::class)->handle($user);
        });
    }

    // @codeCoverageIgnoreEnd

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'anonymized_at' => 'datetime',
            'locale' => 'string',
            'timezone' => 'string',
            'theme' => 'string',
        ];
    }
}
